<?php
require_once __DIR__ . '/../Config/Database.php';

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?page=login"); exit; }

        $role = $_SESSION['role'];
        $clubId = $_SESSION['club_id'];
        $stats = [];

        try {
            if ($role === 'SystemAdmin') {
                // SÜPER YÖNETİCİ SORGULARI
                $stats['total_clubs'] = $this->db->query("SELECT COUNT(*) FROM Clubs")->fetchColumn();
                $stats['total_students'] = $this->db->query("SELECT COUNT(*) FROM Students WHERE IsActive = 1")->fetchColumn();
                $stats['total_staff'] = $this->db->query("SELECT COUNT(*) FROM Users WHERE RoleID IN (2,3)")->fetchColumn();
            } else {
                // KULÜP YÖNETİCİSİ SORGULARI
                // HATA BURADAYDI: Kolon adının doğruluğundan emin olmalıyız. 
                // Eğer hata devam ederse veritabanında kolon adını 'KulupID' olarak kontrol et.
                
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM Students WHERE ClubID = ? AND IsActive = 1");
                $stmt->execute([$clubId]);
                $stats['my_students'] = $stmt->fetchColumn();

                $stmt = $this->db->prepare("SELECT COUNT(*) FROM Groups WHERE ClubID = ?");
                $stmt->execute([$clubId]);
                $stats['my_groups'] = $stmt->fetchColumn();

                // Kullanıcılar tablosunda ClubID kolonu olmayabilir (Süper adminlerde boş olabilir)
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE ClubID = ?");
                $stmt->execute([$clubId]);
                $stats['my_staff'] = $stmt->fetchColumn();
            }
        } catch (PDOException $e) {
            // Eğer hala kolon hatası alırsak, en azından ekranın açılması için sayıları 0 gösterelim
            $stats['my_students'] = 0;
            $stats['my_groups'] = 0;
            $stats['my_staff'] = 0;
            $stats['error_msg'] = "Bazı veriler kolon adı uyuşmazlığı nedeniyle çekilemedi.";
        }

        require_once __DIR__ . '/../Views/admin/dashboard.php';
    }
}