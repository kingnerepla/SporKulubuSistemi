<?php

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $role = $_SESSION['role'] ?? 'Guest';
        $name = $_SESSION['name'] ?? 'Kullanıcı';
        $clubId = $_SESSION['club_id'] ?? null;

        // View'da hata almamak için tüm anahtarları 0 olarak tanımlıyoruz
        $stats = [
            'totalClubs' => 0,
            'totalRevenue' => 0,
            'pendingPayments' => 0,
            'expiredLicenses' => 0,
            'totalStudents' => 0,
            'totalGroups' => 0,
            'totalCoaches' => 0
        ];
        $criticalClubs = [];
        $recentActivity = [];

        if ($role === 'SystemAdmin') {
            // Süper Admin verilerini çek
            try {
                $stats['totalClubs'] = $this->db->query("SELECT COUNT(*) FROM Users WHERE RoleID = (SELECT RoleID FROM Roles WHERE RoleName = 'ClubAdmin')")->fetchColumn() ?: 0;
                // Diğer istatistikleri buraya sorgu olarak ekleyebilirsin
            } catch (Exception $e) {}
            $view = __DIR__ . '/../Views/admin/dashboard.php';
        } else {
            // Kulüp Admin verilerini çek
            try {
                if ($clubId) {
                    $stats['totalStudents'] = $this->db->query("SELECT COUNT(*) FROM Students WHERE ClubID = $clubId")->fetchColumn() ?: 0;
                }
            } catch (Exception $e) {}
            $view = __DIR__ . '/../Views/admin/dashboard_club.php';
        }

        $data = [
            'role' => $role,
            'name' => $name,
            'stats' => $stats,
            'criticalClubs' => $criticalClubs,
            'recentActivity' => $recentActivity,
            'clubName' => 'Spor CRM'
        ];

        $this->render($view, $data);
    }

    private function render($viewPath, $data = []) {
        extract($data);
        ob_start();
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "Dashboard Hazırlanıyor... Hoş geldiniz $name";
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}