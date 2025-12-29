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

        // 1. Tüm anahtarları varsayılan 0 ile başlatıyoruz (View hatalarını önler)
        $stats = [
            'totalClubs' => 0,
            'totalRevenue' => 0,
            'pendingPayments' => 0,
            'expiredLicenses' => 0,
            'totalStudents' => 0,
            'totalGroups' => 0,
            'totalCoaches' => 0
        ];
        
        $recentActivity = [];
        $criticalClubs = []; // Admin view için gerekli

        try {
            $checkRole = strtolower($role);

            if ($checkRole === 'systemadmin' || $checkRole === 'superadmin') {
                // --- SİSTEM YÖNETİCİSİ (Süper Admin) ---
                $stats['totalClubs'] = $this->getScalar("SELECT COUNT(*) FROM [Clubs]");
                $stats['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM [Groups]");
                
                // Admin dashboard'da son aktiviteleri göster (Örn: Son eklenen kulüpler)
                $recentActivity = $this->db->query("SELECT TOP 5 [ClubName] as FullName, [CreatedAt] FROM [Clubs] ORDER BY [ClubID] DESC")->fetchAll(PDO::FETCH_ASSOC);
                
                $view = __DIR__ . '/../Views/admin/dashboard.php';
            } 
            else {
                // --- KULÜP YÖNETİCİSİ ---
                if ($clubId) {
                    $stats['totalStudents'] = $this->getScalar("SELECT COUNT(*) FROM [Students] WHERE [ClubID] = ? OR [club_id] = ?", [$clubId, $clubId]);
                    $stats['totalGroups'] = $this->getScalar("SELECT COUNT(*) FROM [Groups] WHERE [ClubID] = ?", [$clubId]);
                    $stats['totalCoaches'] = $this->getScalar("SELECT COUNT(*) FROM [Coaches] WHERE [ClubID] = ?", [$clubId]);

                    // YENİ KAYITLAR: Gerçek kolon isimlerin olan FullName ve CreatedAt kullanılıyor
                    $query = "SELECT TOP 5 [FullName], [CreatedAt] 
                              FROM [Students] 
                              WHERE [ClubID] = ? OR [club_id] = ? 
                              ORDER BY [StudentID] DESC";
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([$clubId, $clubId]);
                    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                $view = __DIR__ . '/../Views/admin/dashboard_club.php';
            }
        } catch (Exception $e) {
            error_log("Dashboard Hatası: " . $e->getMessage());
        }

        // View'a gönderilen data paketi
        $data = [
            'role' => $role,
            'name' => $name,
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'criticalClubs' => $criticalClubs,
            'clubName' => $_SESSION['club_name'] ?? 'Yönetim Paneli'
        ];

        $this->render($view, $data);
    }

    private function getScalar($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() ?: 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function render($viewPath, $data = []) {
        extract($data);
        ob_start();
        if (!empty($viewPath) && file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "Dashboard dosyası bulunamadı: " . htmlspecialchars($viewPath);
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}