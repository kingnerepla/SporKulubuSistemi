<?php

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // Oturumdan rolü al, eğer yoksa 'Guest' ata
        $role = $_SESSION['role'] ?? 'Guest';
        
        // View'a gönderilecek verileri hazırla
        $data = [
            'title' => 'Özet Panel',
            'role'  => $role,
            'name'  => $_SESSION['name'] ?? 'Kullanıcı'
        ];

        // Role göre hangi dashboard görünümünün yükleneceğine karar ver
        // Views/admin/ altında bu dosyaların olması gerekir
        switch ($role) {
            case 'SystemAdmin':
                $view = __DIR__ . '/../Views/admin/dashboard_system.php';
                break;
            case 'ClubAdmin':
                $view = __DIR__ . '/../Views/admin/dashboard_club.php';
                break;
            case 'Teacher':
                $view = __DIR__ . '/../Views/admin/dashboard_teacher.php';
                break;
            default:
                $view = __DIR__ . '/../Views/admin/dashboard.php';
        }

        $this->render($view, $data);
    }

    private function render($viewPath, $data = []) {
        // Dizideki anahtarları ($role, $title, $name) değişken olarak içeri aktarır
        extract($data);
        
        ob_start();
        if(file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Dosya yoksa kullanıcıya şık bir karşılama göster
            echo "
            <div class='container mt-5'>
                <div class='card border-0 shadow-sm p-5 text-center'>
                    <i class='fa-solid fa-person-digging fa-4x text-warning mb-3'></i>
                    <h2 class='fw-bold'>Panel Hazırlanıyor</h2>
                    <p class='text-muted'>Hoş geldiniz <strong>{$name}</strong> ({$role}). Bu bölüm için özel grafikler ve raporlar yakında burada olacak.</p>
                </div>
            </div>";
        }
        $content = ob_get_clean();
        
        // Ana layout'u çağır
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}