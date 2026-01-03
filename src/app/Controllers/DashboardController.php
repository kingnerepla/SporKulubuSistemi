<?php
// Hataları Gizle/Göster
ini_set('display_errors', 1);
error_reporting(E_ALL);

class DashboardController {
    private $db;

    public function __construct() {
        // 1. SAAT DİLİMİ AYARI
        date_default_timezone_set('Europe/Istanbul');

        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // 2. TEMEL DEĞİŞKENLER
        $s_role   = trim(strtolower((string)($_SESSION['role'] ?? '')));
        $s_roleId = trim((string)($_SESSION['role_id'] ?? $_SESSION['RoleID'] ?? '0'));
        $userId   = $_SESSION['user_id'] ?? null;
        $clubId   = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;

        // Varsayılan Veriler
        $stats = ['totalClubs' => 0, 'totalStudents' => 0, 'totalGroups' => 0, 'totalCoaches' => 0, 'expectedRevenue' => 0, 'receivedRevenue' => 0];
        $todayTrainings = [];
        $parentStudents = [];
        $criticalClubs = [];
        $club = []; 
        $viewPath = "";

        // 3. ROL VE VIEW BELİRLEME
        if ($s_roleId === "4" || $s_role === "parent") {
            $viewPath = 'parent/dashboard.php';
        } elseif ($s_roleId === "3" || $s_role === "coach") {
            $viewPath = 'admin/coach_dashboard.php';
        } elseif ($s_roleId === "2" || $s_role === "clubadmin") {
            $viewPath = 'admin/dashboard_club.php';
        } elseif ($s_roleId === "1" || $s_role === "systemadmin") {
            $viewPath = 'admin/dashboard.php';
        }

        // 4. GÜVENLİK
        if (empty($viewPath)) {
            header("Location: login.php");
            exit;
        }

        // 5. VERİLERİ ÇEK
        try {
            // --- VELİ (GÜNCELLENEN KISIM) ---
            if (($s_roleId === "4" || $s_role === "parent") && $userId) {
                // a. Velinin öğrencilerini ve Grup/Koç ismini çek
                $sql = "SELECT s.*, g.GroupName, 
                        (SELECT FullName FROM Users WHERE UserID = g.CoachID) as CoachName
                        FROM Students s 
                        LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                        WHERE s.ParentID = ? AND s.IsActive = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                $parentStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // b. Her öğrenci için detayları (Yoklama + Finans) doldur
                foreach ($parentStudents as &$stu) {
                    $sid = $stu['StudentID'];

                    // 1. Son 5 Yoklama (SQL Server için TOP 5)
                    $stmtAtt = $this->db->prepare("SELECT TOP 5 [Date], IsPresent FROM Attendance WHERE StudentID = ? ORDER BY [Date] DESC");
                    $stmtAtt->execute([$sid]);
                    $stu['attendance_log'] = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);

                    // 2. Devamlılık Oranı (Son 30 Gün)
                    $startDate = date('Y-m-d', strtotime('-30 days'));
                    $stmtStat = $this->db->prepare("
                        SELECT COUNT(*) as Total, 
                        SUM(CASE WHEN IsPresent=1 THEN 1 ELSE 0 END) as Present 
                        FROM Attendance 
                        WHERE StudentID = ? AND [Date] >= ?
                    ");
                    $stmtStat->execute([$sid, $startDate]);
                    $stat = $stmtStat->fetch(PDO::FETCH_ASSOC);
                    $stu['attendance_rate'] = ($stat['Total'] > 0) ? round(($stat['Present'] / $stat['Total']) * 100) : 0;

                    // 3. Son Ödemeler
                    $stmtPay = $this->db->prepare("SELECT TOP 5 Amount, PaymentDate FROM Payments WHERE StudentID = ? ORDER BY PaymentDate DESC");
                    $stmtPay->execute([$sid]);
                    $stu['payment_log'] = $stmtPay->fetchAll(PDO::FETCH_ASSOC);
                }

                $stats['totalStudents'] = count($parentStudents);
            } 
            
            // --- ANTRENÖR ---
            elseif (($s_roleId === "3" || $s_role === "coach") && $userId) {
                $stats['totalStudents'] = $this->getScalar("SELECT COUNT(s.StudentID) FROM Students s JOIN Groups g ON s.GroupID = g.GroupID WHERE g.CoachID = ? AND s.IsActive = 1", [$userId]);
                $stats['totalGroups']   = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE CoachID = ?", [$userId]);
                
                $todayName = date('N'); // 1-7
                $sql = "SELECT gs.*, g.GroupName, g.GroupID,
                        (SELECT COUNT(*) FROM Attendance a WHERE a.GroupID = g.GroupID AND a.Date = CAST(GETDATE() AS DATE)) as AttendanceCount 
                        FROM GroupSchedule gs 
                        JOIN Groups g ON gs.GroupID = g.GroupID 
                        WHERE g.CoachID = ? AND gs.DayOfWeek = ? 
                        ORDER BY gs.StartTime ASC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId, $todayName]);
                $todayTrainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } 
            
            // --- KULÜP YÖNETİCİSİ ---
            elseif (($s_roleId === "2" || $s_role === "clubadmin") && $clubId) {
                $stats['totalStudents'] = $this->getScalar("SELECT COUNT(*) FROM Students WHERE ClubID = ? AND IsActive = 1", [$clubId]);
                $stats['totalGroups']   = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE ClubID = ?", [$clubId]);
                $stats['totalCoaches']  = $this->getScalar("SELECT COUNT(*) FROM Users WHERE ClubID = ? AND RoleID = 3 AND IsActive = 1", [$clubId]);

                $stats['expectedRevenue'] = $this->getScalar("SELECT SUM(PackageFee) FROM Students WHERE ClubID = ? AND IsActive = 1", [$clubId]);
                
                try {
                    $stats['receivedRevenue'] = $this->getScalar("SELECT SUM(Amount) FROM Payments WHERE ClubID = ? AND MONTH(PaymentDate) = MONTH(GETDATE()) AND YEAR(PaymentDate) = YEAR(GETDATE())", [$clubId]);
                } catch (Exception $e) { $stats['receivedRevenue'] = 0; }

                try {
                    $sqlPay = "SELECT TOP 5 p.*, s.FullName 
                               FROM Payments p 
                               JOIN Students s ON p.StudentID = s.StudentID 
                               WHERE p.ClubID = ? 
                               ORDER BY p.PaymentDate DESC";
                    $stmtPay = $this->db->prepare($sqlPay);
                    $stmtPay->execute([$clubId]);
                    $criticalClubs = $stmtPay->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $criticalClubs = []; }

                $stmtClub = $this->db->prepare("SELECT * FROM Clubs WHERE ClubID = ?");
                $stmtClub->execute([$clubId]);
                $club = $stmtClub->fetch(PDO::FETCH_ASSOC);
            }

        } catch (Exception $e) {
            error_log("Dashboard Hatası: " . $e->getMessage());
        }

        // VIEW'A GÖNDER
        $data = [
            'role' => $s_role,
            'name' => $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'Kullanıcı',
            'stats' => $stats,
            'todayTrainings' => $todayTrainings,
            'students' => $parentStudents, // Dolu öğrenci dizisi
            'criticalClubs' => $criticalClubs, 
            'club' => $club, 
            'clubName' => $_SESSION['selected_club_name'] ?? $_SESSION['club_name'] ?? ''
        ];

        $this->render(dirname(__DIR__) . '/Views/' . $viewPath, $data);
    }

    public function updatePermission() {
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role !== 'clubadmin' && $role !== 'admin') {
            http_response_code(403);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? 0;
            $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
            try {
                $sql = "UPDATE Clubs SET CoachReportAccess = ? WHERE ClubID = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$status, $clubId]);
                echo "Güncellendi";
            } catch (Exception $e) {
                http_response_code(500);
            }
            exit;
        }
    }

    private function getScalar($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetchColumn();
            return $res !== false ? (float)$res : 0;
        } catch (Exception $e) { return 0; }
    }

    private function render($path, $data = []) {
        extract($data);
        ob_start();
        if (file_exists($path)) {
            include $path;
        } else {
            // Varsayılan olarak admin dashboard'a düşmesin, hata basabiliriz veya boş sayfa
            echo "View dosyası bulunamadı: $path";
        }
        $content = ob_get_clean();
        include dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }
}