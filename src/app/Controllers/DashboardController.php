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
        $viewPath = "";

        // 2. ROL BELİRLEME (Switch-Case ile daha okunaklı ve güvenli)
        if ($s_roleId === "3" || $s_role === "coach") {
            $viewPath = 'admin/coach_dashboard.php';
        } elseif ($s_roleId === "2" || $s_role === "clubadmin") {
            $viewPath = 'admin/dashboard_club.php';
        } elseif ($s_roleId === "1" || $s_role === "systemadmin") {
            $viewPath = 'admin/dashboard.php';
        }

        // 3. GÜVENLİK KONTROLÜ (Eğer hâlâ boşsa)
        if (empty($viewPath)) {
            die("Erişim Hatası! <br> Gelen ID: '$s_roleId' <br> Gelen Rol: '$s_role'");
        }

        // 4. VERİLERİ ÇEK (Sadece belirlenen role göre)
        try {
            if ($viewPath === 'admin/coach_dashboard.php' && $userId) {
                // Antrenör verileri
                $stats['totalStudents'] = $this->getScalar("SELECT COUNT(s.StudentID) FROM Students s JOIN Groups g ON s.GroupID = g.GroupID WHERE g.TrainerID = ? AND s.IsActive = 1", [$userId]);
                $stats['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE TrainerID = ?", [$userId]);
      
                // Bugünün antrenmanları sorgusu
                $sql = "SELECT ts.*, g.GroupName,
                        (SELECT COUNT(*) FROM Attendance WHERE SessionID = ts.SessionID) as AttendanceCount
                        FROM TrainingSessions ts 
                        JOIN Groups g ON ts.GroupID = g.GroupID 
                        WHERE g.TrainerID = ? 
                        ORDER BY ts.StartTime ASC"; // Not: Geliştirme aşamasında tüm dersleri görmek için tarih filtresi kaldırılmıştır.

                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                $todayTrainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

       

            } elseif ($viewPath === 'admin/dashboard_club.php' && $clubId) {
                // Kulüp yöneticisi verileri
                $stats['totalStudents'] = $this->getScalar("SELECT COUNT(*) FROM Students WHERE ClubID = ?", [$clubId]);
                $stats['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE ClubID = ?", [$clubId]);
                $stats['totalCoaches'] = $this->getScalar("SELECT COUNT(*) FROM Users WHERE ClubID = ? AND RoleID = 3", [$clubId]);

            } elseif ($viewPath === 'admin/dashboard.php') {
                $stats['totalClubs'] = $this->getScalar("SELECT COUNT(*) FROM Clubs");
            }
        } catch (Exception $e) {
            // Hata olsa bile sayfa yüklensin, sadece hata loglansın
            error_log("Dashboard Veri Hatası: " . $e->getMessage());
        }

        $data = [
            'role' => $_SESSION['role'] ?? '',
            'name' => $_SESSION['name'] ?? 'Kullanıcı',
            'stats' => $stats,
            'todayTrainings' => $todayTrainings,
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