<?php

class ClubController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        if ($_SESSION['role'] !== 'SystemAdmin') {
            header("Location: index.php?page=dashboard");
            exit;
        }
    
        try {
            // En basit sorgu: Önce sadece kulüpleri çekelim
            // Eğer Students tablosuyla ilgili bir isim hatası varsa bu sorgu etkilenmez.
            $stmt = $this->db->query("SELECT * FROM Clubs ORDER BY ClubName ASC");
            $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Hata varsa ekrana bas ki görelim
            die("Kulüp Listeleme Hatası: " . $e->getMessage());
        }
    
        $data = ['clubs' => $clubs];
        $this->render(__DIR__ . '/../Views/admin/clubs.php', $data);
    }

    private function render($viewPath, $data = []) {
        extract($data);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}