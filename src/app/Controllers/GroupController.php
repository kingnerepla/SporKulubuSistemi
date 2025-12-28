<?php
require_once __DIR__ . '/../Config/Database.php';

class GroupController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $role = $_SESSION['role'];
        $clubId = $_SESSION['club_id'];
    
        if ($role === 'SystemAdmin') {
            $stmt = $this->db->query("SELECT Groups.*, Clubs.ClubName FROM Groups LEFT JOIN Clubs ON Groups.ClubID = Clubs.ClubID");
        } else {
            $stmt = $this->db->prepare("SELECT Groups.*, Clubs.ClubName FROM Groups LEFT JOIN Clubs ON Groups.ClubID = Clubs.ClubID WHERE Groups.ClubID = ?");
            $stmt->execute([$clubId]);
        }
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // SENİN LİSTENE GÖRE DOĞRU YOL:
        require_once __DIR__ . '/../Views/admin/groups.php';
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