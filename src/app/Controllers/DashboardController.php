<?php

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // 1. Verileri temizle
        $s_role   = trim(strtolower((string)($_SESSION['role'] ?? '')));
        $s_roleId = trim((string)($_SESSION['role_id'] ?? $_SESSION['RoleID'] ?? '0'));
        $userId   = $_SESSION['user_id'] ?? null;
        $clubId   = $_SESSION['club_id'] ?? null;

        $stats = ['totalClubs' => 0, 'totalStudents' => 0, 'totalGroups' => 0, 'totalCoaches' => 0];
        $todayTrainings = [];
        $parentStudents = []; // Veli için öğrenci listesi
        $viewPath = "";

        // 2. ROL BELİRLEME (Veli - RoleID 4 eklendi)
        if ($s_roleId === "4" || $s_role === "parent") {
            $viewPath = 'parent/dashboard.php'; // Veli Dashboard yolu
        } elseif ($s_roleId === "3" || $s_role === "coach") {
            $viewPath = 'admin/coach_dashboard.php';
        } elseif ($s_roleId === "2" || $s_role === "clubadmin") {
            $viewPath = 'admin/dashboard_club.php';
        } elseif ($s_roleId === "1" || $s_role === "systemadmin") {
            $viewPath = 'admin/dashboard.php';
        }

        // 3. GÜVENLİK KONTROLÜ
        if (empty($viewPath)) {
            die("Erişim Hatası! <br> Gelen ID: '$s_roleId' <br> Gelen Rol: '$s_role'");
        }

        // 4. VERİLERİ ÇEK
        try {
            if ($viewPath === 'parent/dashboard.php' && $userId) {
                // VELİ VERİLERİ: Velinin çocuklarını getir
                $sql = "SELECT s.*, g.GroupName 
                        FROM Students s 
                        LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                        WHERE s.ParentID = ? AND s.IsActive = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                $parentStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Her çocuk için son yoklamaları getir
                foreach ($parentStudents as &$student) {
                    $stmtA = $this->db->prepare("SELECT TOP 5 AttendanceDate, Status FROM Attendance WHERE StudentID = ? ORDER BY AttendanceDate DESC");
                    $stmtA->execute([$student['StudentID']]);
                    $student['last_attendance'] = $stmtA->fetchAll(PDO::FETCH_ASSOC);
                }
                
                // Veli istatistikleri (Örnek: Toplam çocuk sayısı)
                $stats['totalStudents'] = count($parentStudents);

            } elseif ($viewPath === 'admin/coach_dashboard.php' && $userId) {
                // Antrenör verileri aynı kalıyor...
                $stats['totalStudents'] = $this->getScalar("SELECT COUNT(s.StudentID) FROM Students s JOIN Groups g ON s.GroupID = g.GroupID WHERE g.TrainerID = ? AND s.IsActive = 1", [$userId]);
                $stats['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE TrainerID = ?", [$userId]);
                
                $sql = "SELECT ts.*, g.GroupName,
                        (SELECT COUNT(*) FROM Attendance WHERE SessionID = ts.SessionID) as AttendanceCount
                        FROM TrainingSessions ts 
                        JOIN Groups g ON ts.GroupID = g.GroupID 
                        WHERE g.TrainerID = ? 
                        ORDER BY ts.StartTime ASC";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                $todayTrainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } elseif ($viewPath === 'admin/dashboard_club.php' && $clubId) {
                // Kulüp yöneticisi verileri aynı kalıyor...
                $stats['totalStudents'] = $this->getScalar("SELECT COUNT(*) FROM Students WHERE ClubID = ?", [$clubId]);
                $stats['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE ClubID = ?", [$clubId]);
                $stats['totalCoaches'] = $this->getScalar("SELECT COUNT(*) FROM Users WHERE ClubID = ? AND RoleID = 3", [$clubId]);

            } elseif ($viewPath === 'admin/dashboard.php') {
                $stats['totalClubs'] = $this->getScalar("SELECT COUNT(*) FROM Clubs");
            }
        } catch (Exception $e) {
            error_log("Dashboard Veri Hatası: " . $e->getMessage());
        }

        $data = [
            'role' => $s_role,
            'name' => $_SESSION['name'] ?? 'Kullanıcı',
            'stats' => $stats,
            'todayTrainings' => $todayTrainings,
            'students' => $parentStudents, // Veli için eklenen veri
            'attendanceRate' => 95, // Statik veya hesaplanmış oran
            'paymentStatus' => 'Güncel', // Statik veya finansal veri
            'clubName' => $_SESSION['selected_club_name'] ?? $_SESSION['club_name'] ?? 'Yönetim Paneli'
        ];

        $this->render(dirname(__DIR__) . '/Views/' . $viewPath, $data);
    }

    private function getScalar($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetchColumn();
            return $res !== false ? (int)$res : 0;
        } catch (Exception $e) { return 0; }
    }

    private function render($path, $data = []) {
        extract($data);
        ob_start();
        if (file_exists($path)) {
            include $path;
        } else {
            die("HATA: Görünüm dosyası bulunamadı! <br> Aranan Yol: $path");
        }
        $content = ob_get_clean();
        include dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }
}