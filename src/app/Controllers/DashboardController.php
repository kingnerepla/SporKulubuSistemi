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

        // İstatistikleri başlat
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

        // HATA ÖNLEME: Varsayılan bir view yolu atıyoruz
        $view = __DIR__ . '/../Views/admin/dashboard_club.php';

        try {
            // Role kontrolü (Küçük/Büyük harf duyarlılığını önlemek için strtolower kullanıldı)
            $checkRole = strtolower($role);

            if ($checkRole === 'systemadmin' || $checkRole === 'superadmin') {
                // SÜPER ADMİN VERİLERİ
                $stats['totalClubs'] = $this->db->query("SELECT COUNT(*) FROM Clubs")->fetchColumn() ?: 0;
                $stats['totalGroups'] = $this->db->query("SELECT COUNT(*) FROM Groups")->fetchColumn() ?: 0;
                
                $view = __DIR__ . '/../Views/admin/dashboard.php';
            } 
            else {
                // KULÜP ADMİN VEYA DİĞER ROLLER
                if ($clubId) {
                    // Toplam Öğrenci
                    $stmtS = $this->db->prepare("SELECT COUNT(*) FROM Students WHERE ClubID = ?");
                    $stmtS->execute([$clubId]);
                    $stats['totalStudents'] = $stmtS->fetchColumn() ?: 0;

                    // Aktif Grup Sayısı
                    $stmtG = $this->db->prepare("SELECT COUNT(*) FROM Groups WHERE ClubID = ?");
                    $stmtG->execute([$clubId]);
                    $stats['totalGroups'] = $stmtG->fetchColumn() ?: 0;

                    // Toplam Antrenör
                    $stmtC = $this->db->prepare("SELECT COUNT(*) FROM Coaches WHERE ClubID = ?");
                    $stmtC->execute([$clubId]);
                    $stats['totalCoaches'] = $stmtC->fetchColumn() ?: 0;
                }
                $view = __DIR__ . '/../Views/admin/dashboard_club.php';
            }
        } catch (Exception $e) {
            // Hata durumunda loglanabilir
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
        
        // HATA ÖNLEME: viewPath null gelirse veya dosya yoksa kontrol et
        if (!empty($viewPath) && file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "<div class='container mt-5 alert alert-warning'>";
            echo "<h4>Dashboard Hazırlanıyor...</h4>";
            echo "<p>Hoş geldiniz <strong>$name</strong>. View dosyası bulunamadı veya yetki tanımlamanız eksik.</p>";
            echo "</div>";
        }
        
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}