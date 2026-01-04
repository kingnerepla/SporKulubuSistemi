<?php
// Hataları Gizle/Göster
ini_set('display_errors', 1);
error_reporting(E_ALL);

class DashboardController {
    private $db;

    public function __construct() {
        date_default_timezone_set('Europe/Istanbul');
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $s_role   = trim(strtolower((string)($_SESSION['role'] ?? '')));
        $s_roleId = trim((string)($_SESSION['role_id'] ?? $_SESSION['RoleID'] ?? '0'));
        $userId   = $_SESSION['user_id'] ?? null;
        $clubId   = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;

        $data = [
            'role' => $s_role,
            'name' => $_SESSION['full_name'] ?? 'Kullanıcı',
            'stats' => [],
            'todayTrainings' => [],
            'students' => [],
            'debtStudents' => [],
            'criticalClubs' => [],
            'allClubs' => [], 
            'saasHistory' => [], // Ortak tahsilat geçmişi alanı
            'club' => [],
            'currentDebt' => 0,
            'clubName' => $_SESSION['selected_club_name'] ?? $_SESSION['club_name'] ?? ''
        ];

        $viewPath = "";

        try {
            // --- 1. SİSTEM YÖNETİCİSİ (Süper Admin) ---
            if ($s_roleId === "1" || $s_role === "systemadmin") {
                $viewPath = 'admin/system_dashboard.php';
                
                $data['stats']['totalClubs'] = $this->getScalar("SELECT COUNT(*) FROM Clubs");
                $data['stats']['totalStudents'] = $this->getScalar("SELECT COUNT(*) FROM Students WHERE IsActive = 1");
                $data['stats']['totalCoaches'] = $this->getScalar("SELECT COUNT(*) FROM Users WHERE RoleID = 3 AND IsActive = 1");
                $data['stats']['totalRevenueAllTime'] = $this->getScalar("SELECT SUM(Amount) FROM Payments");

                $sqlClubs = "SELECT c.ClubID, c.ClubName, c.IsActive, c.CreatedAt, c.LicenseEndDate,
                                c.MonthlyPerStudentFee, c.AnnualLicenseFee,
                                (SELECT COUNT(*) FROM Students WHERE ClubID = c.ClubID AND IsActive = 1) as StudentCount,
                                (SELECT MAX(PaymentDate) FROM Payments WHERE ClubID = c.ClubID) as LastActivity
                             FROM Clubs c 
                             ORDER BY c.IsActive DESC, c.CreatedAt DESC";
                $stmtClubs = $this->db->prepare($sqlClubs);
                $stmtClubs->execute();
                $allClubs = $stmtClubs->fetchAll(PDO::FETCH_ASSOC);

                $totalExpectedFromClubs = 0;
                foreach ($allClubs as &$c) {
                    $license_debt = (float)($c['AnnualLicenseFee'] ?? 0);
                    $monthly_debt = (float)(($c['StudentCount'] ?? 0) * ($c['MonthlyPerStudentFee'] ?? 0));
                    $total_paid = (float)$this->getScalar("SELECT SUM(Amount) FROM SaasPayments WHERE ClubID = ?", [$c['ClubID']]);
                    
                    $c['license_fee_debt'] = $license_debt;
                    $c['monthly_usage_debt'] = $monthly_debt;
                    $c['current_debt'] = ($license_debt + $monthly_debt) - $total_paid;
                    
                    $totalExpectedFromClubs += $c['current_debt'];
                }
                $data['allClubs'] = $allClubs;
                $data['stats']['totalExpected'] = $totalExpectedFromClubs;

                // SON 10 SAAS TAHSİLATINI ÇEK (Global)
                $stmtHistory = $this->db->prepare("
                    SELECT TOP 10 sp.*, c.ClubName 
                    FROM SaasPayments sp 
                    JOIN Clubs c ON sp.ClubID = c.ClubID 
                    ORDER BY sp.PaymentDate DESC
                ");
                $stmtHistory->execute();
                $data['saasHistory'] = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
            }

            // --- 2. KULÜP YÖNETİCİSİ ---
            elseif ($s_roleId === "2" || $s_role === "clubadmin") {
                $viewPath = 'admin/dashboard_club.php';
                
                $stmtClub = $this->db->prepare("SELECT * FROM Clubs WHERE ClubID = ?");
                $stmtClub->execute([$clubId]);
                $data['club'] = $stmtClub->fetch(PDO::FETCH_ASSOC);

                $data['stats']['totalStudents'] = (int)$this->getScalar("SELECT COUNT(*) FROM Students WHERE ClubID = ? AND IsActive = 1", [$clubId]);
                $data['stats']['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE ClubID = ?", [$clubId]);
                $data['stats']['totalCoaches'] = $this->getScalar("SELECT COUNT(*) FROM Users WHERE ClubID = ? AND RoleID = 3 AND IsActive = 1", [$clubId]);
                $data['stats']['expectedRevenue'] = $this->getScalar("SELECT SUM(PackageFee) FROM Students WHERE ClubID = ? AND IsActive = 1", [$clubId]);
                $data['stats']['receivedRevenue'] = $this->getScalar("SELECT SUM(Amount) FROM Payments WHERE ClubID = ? AND MONTH(PaymentDate) = MONTH(GETDATE()) AND YEAR(PaymentDate) = YEAR(GETDATE())", [$clubId]);

                if ($data['club']) {
                    $licenseDebt = (float)($data['club']['AnnualLicenseFee'] ?? 0);
                    $usageDebt   = (float)($data['stats']['totalStudents'] * ($data['club']['MonthlyPerStudentFee'] ?? 0));
                    $totalPaid   = (float)$this->getScalar("SELECT SUM(Amount) FROM SaasPayments WHERE ClubID = ?", [$clubId]);
                    $data['currentDebt'] = ($licenseDebt + $usageDebt) - $totalPaid;
                }

                $stmtDebt = $this->db->prepare("SELECT TOP 5 StudentID, FullName, RemainingSessions, PackageFee FROM Students WHERE ClubID = ? AND IsActive = 1 AND RemainingSessions <= 2 ORDER BY RemainingSessions ASC");
                $stmtDebt->execute([$clubId]);
                $data['debtStudents'] = $stmtDebt->fetchAll(PDO::FETCH_ASSOC);

                $stmtPay = $this->db->prepare("SELECT TOP 5 p.*, s.FullName FROM Payments p JOIN Students s ON p.StudentID = s.StudentID WHERE p.ClubID = ? ORDER BY p.PaymentDate DESC");
                $stmtPay->execute([$clubId]);
                $data['criticalClubs'] = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

                // 🔥 KULÜBÜN KENDİ SAAS ÖDEME GEÇMİŞİNİ ÇEK
                $stmtSaas = $this->db->prepare("SELECT * FROM SaasPayments WHERE ClubID = ? ORDER BY PaymentDate DESC");
                $stmtSaas->execute([$clubId]);
                $data['saasHistory'] = $stmtSaas->fetchAll(PDO::FETCH_ASSOC);
            }

            // --- 3. ANTRENÖR ---
            elseif ($s_roleId === "3" || $s_role === "coach") {
                $viewPath = 'admin/coach_dashboard.php';
                $data['stats']['totalStudents'] = $this->getScalar("SELECT COUNT(s.StudentID) FROM Students s JOIN Groups g ON s.GroupID = g.GroupID WHERE g.CoachID = ? AND s.IsActive = 1", [$userId]);
                $data['stats']['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE CoachID = ?", [$userId]);
                $todayName = date('N'); 
                $todayDate = date('Y-m-d'); 
                $sqlTrainings = "SELECT gs.*, g.GroupName, g.GroupID,
                                (SELECT COUNT(*) FROM Attendance a WHERE a.GroupID = g.GroupID AND CONVERT(date, a.[Date]) = CONVERT(date, ?)) as AttendanceCount 
                                FROM GroupSchedule gs JOIN Groups g ON gs.GroupID = g.GroupID 
                                WHERE g.CoachID = ? AND gs.DayOfWeek = ? ORDER BY gs.StartTime ASC";
                $stmtT = $this->db->prepare($sqlTrainings);
                $stmtT->execute([$todayDate, $userId, $todayName]);
                $data['todayTrainings'] = $stmtT->fetchAll(PDO::FETCH_ASSOC);
            }

            // --- 4. VELİ ---
            elseif ($s_roleId === "4" || $s_role === "parent") {
                $viewPath = 'parent/dashboard.php';
                $sqlParent = "SELECT s.*, g.GroupName, (SELECT FullName FROM Users WHERE UserID = g.CoachID) as CoachName FROM Students s LEFT JOIN Groups g ON s.GroupID = g.GroupID WHERE s.ParentID = ? AND s.IsActive = 1";
                $stmtP = $this->db->prepare($sqlParent);
                $stmtP->execute([$userId]);
                $data['students'] = $stmtP->fetchAll(PDO::FETCH_ASSOC);
                foreach ($data['students'] as &$stu) {
                    $stu['low_balance_warning'] = ($stu['RemainingSessions'] <= 2);
                    $stmtAtt = $this->db->prepare("SELECT TOP 5 [Date], IsPresent FROM Attendance WHERE StudentID = ? ORDER BY [Date] DESC");
                    $stmtAtt->execute([$stu['StudentID']]);
                    $stu['attendance_log'] = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);
                    $startDate = date('Y-m-d', strtotime('-30 days'));
                    $stmtStat = $this->db->prepare("SELECT COUNT(*) as Total, SUM(CASE WHEN IsPresent=1 THEN 1 ELSE 0 END) as Present FROM Attendance WHERE StudentID = ? AND [Date] >= ?");
                    $stmtStat->execute([$stu['StudentID'], $startDate]);
                    $stat = $stmtStat->fetch(PDO::FETCH_ASSOC);
                    $stu['attendance_rate'] = ($stat['Total'] > 0) ? round(($stat['Present'] / $stat['Total']) * 100) : 0;
                }
            }

        } catch (Exception $e) {
            error_log("Dashboard Hatası: " . $e->getMessage());
        }

        if (empty($viewPath)) { header("Location: index.php?page=login"); exit; }
        $this->render(dirname(__DIR__) . '/Views/' . $viewPath, $data);
    }

    public function toggleClubStatus() {
        if (($_SESSION['role_id'] ?? 0) != 1) { die("Yetkisiz erişim."); }
        $id = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? 0;
        if ($id) {
            $stmt = $this->db->prepare("UPDATE Clubs SET IsActive = ? WHERE ClubID = ?");
            $stmt->execute([$status, $id]);
            $_SESSION['success_message'] = "Kulüp durumu başarıyla güncellendi.";
        }
        header("Location: index.php?page=dashboard");
        exit;
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
        if (file_exists($path)) { include $path; } 
        else { echo "View bulunamadı: $path"; }
        $content = ob_get_clean();
        include dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }
}