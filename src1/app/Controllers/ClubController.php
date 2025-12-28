<?php
require_once __DIR__ . '/../Config/Database.php';

class ClubController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // Sadece SystemAdmin kulüpleri yönetebilir
        if (($_SESSION['role'] ?? '') !== 'SystemAdmin') {
            echo "<script>alert('Bu sayfaya erişim yetkiniz yok!'); window.location.href='index.php?page=dashboard';</script>";
            exit;
        }

        try {
            $stmt = $this->db->query("SELECT * FROM Clubs ORDER BY ClubID DESC");
            $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $clubs = [];
        }

        require_once __DIR__ . '/../Views/admin/clubs.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clubName = $_POST['club_name'] ?? '';

            if (empty($clubName)) {
                echo "<script>alert('Kulüp adı boş olamaz!'); history.back();</script>";
                exit;
            }

            try {
                $sql = "INSERT INTO Clubs (ClubName) VALUES (?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$clubName]);

                echo "<script>alert('Kulüp Başarıyla Eklendi'); window.location.href='index.php?page=clubs';</script>";
            } catch (PDOException $e) {
                die("Veritabanı Hatası: " . $e->getMessage());
            }
        }
    }
}