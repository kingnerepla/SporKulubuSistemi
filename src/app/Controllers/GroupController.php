<?php
require_once __DIR__ . '/../Config/Database.php';

class GroupController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // 1. Grupları Listele (Kulüp Adı ve Antrenör Adıyla Birlikte)
        $sql = "SELECT Groups.*, Clubs.ClubName, Users.FullName as TrainerName 
                FROM Groups 
                INNER JOIN Clubs ON Groups.ClubID = Clubs.ClubID
                LEFT JOIN Users ON Groups.TrainerID = Users.UserID
                ORDER BY Groups.CreatedAt DESC";
        $groups = $this->db->query($sql)->fetchAll();

        // 2. Yeni Ekleme Formu İçin Verileri Çek
        // Sadece Antrenörleri (RoleID = 3 varsayıyoruz, kontrol etmelisin) listele
        // Not: Roles tablosunda Trainer'ın ID'sine bakmak daha garantidir ama şimdilik Users üzerinden gidelim.
        // Daha profesyoneli: INNER JOIN Roles yapıp RoleName='Trainer' olanları çekmektir.
        
        $trainers = $this->db->query("
            SELECT Users.UserID, Users.FullName 
            FROM Users 
            INNER JOIN Roles ON Users.RoleID = Roles.RoleID 
            WHERE Roles.RoleName = 'Trainer' AND Users.IsActive = 1
        ")->fetchAll();

        $clubs = $this->db->query("SELECT * FROM Clubs WHERE IsActive = 1")->fetchAll();

        ob_start();
        require_once __DIR__ . '/../Views/admin/groups.php';
        $content = ob_get_clean();

        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clubId = $_POST['club_id'];
            $name = $_POST['group_name'];
            $trainerId = !empty($_POST['trainer_id']) ? $_POST['trainer_id'] : NULL;
            
            try {
                $stmt = $this->db->prepare("INSERT INTO Groups (ClubID, GroupName, TrainerID) VALUES (?, ?, ?)");
                $stmt->execute([$clubId, $name, $trainerId]);
                header("Location: index.php?page=groups&success=1");
            } catch (PDOException $e) {
                die("Hata: " . $e->getMessage());
            }
        }
    }
}