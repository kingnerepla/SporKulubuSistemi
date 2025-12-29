<?php

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // 1. Session verilerini ham halleriyle alalım
        $role = $_SESSION['role'] ?? 'Guest';
        $roleId = isset($_SESSION['role_id']) ? intval($_SESSION['role_id']) : 0;
        $name = $_SESSION['name'] ?? 'Kullanıcı';
        $clubId = $_SESSION['club_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        // --- TEŞHİS SATIRI (Sorun Çözülene Kadar Kalsın) ---
        // echo "Role: $role | RoleID: $roleId | ClubID: $clubId | UserID: $userId"; 

        $stats = ['totalClubs' => 0, 'totalStudents' => 0, 'totalGroups' => 0, 'totalCoaches' => 0];
        $todayTrainings = [];
        $recentActivity = [];

        // 2. DOSYA YOLUNU DİNAMİK OLARAK BELİRLEYELİM
        $viewPath = ""; 
        $checkRole = strtolower(trim($role));

        try {
            // ÖNCE ANTRENÖRÜ KONTROL EDELİM
            if ($roleId === 3 || $checkRole === 'coach') {
                if ($clubId && $userId) {
                    $stats['totalStudents'] = $this->getScalar("SELECT COUNT(s.StudentID) FROM Students s JOIN Groups g ON s.GroupID = g.GroupID WHERE g.TrainerID = ?", [$userId]);
                    $stats['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE TrainerID = ?", [$userId]);
                    
                    $sqlTrainings = "SELECT ts.*, g.GroupName FROM TrainingSchedule ts JOIN Groups g ON ts.GroupID = g.GroupID WHERE g.TrainerID = ? AND ts.TrainingDate = CAST(GETDATE() AS DATE)";
                    $stmt = $this->db->prepare($sqlTrainings);
                    $stmt->execute([$userId]);
                    $todayTrainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                $viewPath = 'admin/coach_dashboard.php';
            } 
            // SONRA SİSTEM YÖNETİCİSİ
            elseif ($roleId === 1 || $checkRole === 'systemadmin') {
                $stats['totalClubs'] = $this->getScalar("SELECT COUNT(*) FROM Clubs");
                $viewPath = 'admin/dashboard.php';
            } 
            // HER ŞEYDEN ÖNCE EĞER HİÇBİRİ DEĞİLSE (VEYA ROLEID 2 İSE) KULÜP YÖNETİCİSİ
            else {
                if ($clubId) {
                    $stats['totalStudents'] = $this->getScalar("SELECT COUNT(*) FROM Students WHERE ClubID = ?", [$clubId]);
                    $stats['totalCoaches'] = $this->getScalar("SELECT COUNT(*) FROM Users WHERE ClubID = ? AND RoleID = 3", [$clubId]);
                }
                $viewPath = 'admin/dashboard_club.php';
            }
        } catch (Exception $e) {
            error_log("Dashboard Hatası: " . $e->getMessage());
        }

        $data = [
            'role' => $role,
            'name' => $name,
            'stats' => $stats,
            'todayTrainings' => $todayTrainings,
            'recentActivity' => $recentActivity,
            'clubName' => $_SESSION['selected_club_name'] ?? $_SESSION['club_name'] ?? 'Yönetim Paneli'
        ];

        // Tam yolu render metoduna gönderiyoruz
        $fullPath = dirname(__DIR__) . '/Views/' . $viewPath;
        $this->render($fullPath, $data);
    }

    private function getScalar($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetchColumn();
            return $res !== false ? $res : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function render($path, $data = []) {
        extract($data);
        ob_start();
        if ($path && file_exists($path)) {
            include $path;
        } else {
            echo "<div class='alert alert-danger'>HATA: Dosya bulunamadı! <br> Aranan Yol: $path</div>";
        }
        $content = ob_get_clean();
        include dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }
}