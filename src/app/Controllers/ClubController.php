<?php

class ClubController {
    private $db;

    public function __construct() {
        $dbConfig = new Database();
        $this->db = $dbConfig->getConnection();
    }

    public function index() {
        // Verileri çek
        $stmt = $this->db->query("SELECT * FROM Clubs ORDER BY CreatedAt DESC");
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Görünümü render et (Layout'u bu fonksiyon çağıracak)
        $this->render(__DIR__ . '/../Views/admin/clubs.php', [
            'clubs' => $clubs
        ]);
    }

    public function detail() {
        $id = $_GET['id'] ?? 0;
        $stmt = $this->db->prepare("SELECT * FROM Clubs WHERE ClubID = ?");
        $stmt->execute([$id]);
        $club = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->render(__DIR__ . '/../Views/admin/club_detail.php', [
            'club' => $club
        ]);
    }

    // --- SİHİRLİ FONKSİYON: İç içe geçmeyi engeller ---
    private function render($viewPath, $data = []) {
        extract($data); // Verileri değişken olarak içeri aktarır
        
        ob_start(); // Arka planda çıktıyı yakala
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "Görünüm dosyası bulunamadı: $viewPath";
        }
        $content = ob_get_clean(); // Çıktıyı $content değişkenine at
        
        // Sadece TEK BİR KEZ layout'u çağır
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}