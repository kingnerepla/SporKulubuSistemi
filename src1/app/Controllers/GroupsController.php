<?php
require_once __DIR__ . '/../Config/Database.php';

class GroupsController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // Oturumdaki Kulüp ID'sini al
        $clubId = $_SESSION['club_id'] ?? null;

        // EĞER KULÜP ID YOKSA HATA VERMESİN, BOŞ LİSTE DÖNSÜN (Güvenlik)
        if (!$clubId && $_SESSION['role'] != 'SystemAdmin') {
            $groups = [];
            $trainers = [];
        } else {
            // 1. Grupları Listele (SADECE BU KULÜBE AİT OLANLAR)
            $sql = "SELECT Groups.*, Users.FullName as TrainerName,
                    (SELECT COUNT(*) FROM Students WHERE Students.GroupID = Groups.GroupID AND Students.IsActive = 1) as StudentCount
                    FROM Groups
                    LEFT JOIN Users ON Groups.TrainerID = Users.UserID
                    WHERE Groups.ClubID = ? 
                    ORDER BY Groups.GroupName ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
            $groups = $stmt->fetchAll();

            // 2. Antrenörleri Getir (Dropdown İçin - SADECE BU KULÜBÜN HOCALARI)
            $stmtTrainers = $this->db->prepare("
                SELECT Users.UserID, Users.FullName 
                FROM Users 
                INNER JOIN Roles ON Users.RoleID = Roles.RoleID 
                WHERE Roles.RoleName = 'Trainer' AND Users.IsActive = 1 AND Users.ClubID = ?
            ");
            $stmtTrainers->execute([$clubId]);
            $trainers = $stmtTrainers->fetchAll();
        }

        ob_start();
        require_once __DIR__ . '/../Views/admin/groups.php';
        $content = ob_get_clean();

        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // GÜNCELLEME: Oturumdan ClubID'yi al
            $clubId = $_SESSION['club_id']; 
            
            if (!$clubId) {
                die("HATA: Kulüp bilgisi bulunamadı. Lütfen tekrar giriş yapın.");
            }

            $name = $_POST['group_name'];
            $trainerId = !empty($_POST['trainer_id']) ? $_POST['trainer_id'] : NULL;

            // SQL GÜNCELLEMESİ: ClubID sütununu da ekledik
            $stmt = $this->db->prepare("INSERT INTO Groups (GroupName, TrainerID, ClubID) VALUES (?, ?, ?)");
            $stmt->execute([$name, $trainerId, $clubId]);
            
            header("Location: index.php?page=groups&success=created");
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['group_id'];
            $name = $_POST['group_name'];
            $trainerId = !empty($_POST['trainer_id']) ? $_POST['trainer_id'] : NULL;

            $stmt = $this->db->prepare("UPDATE Groups SET GroupName = ?, TrainerID = ? WHERE GroupID = ?");
            $stmt->execute([$name, $trainerId, $id]);
            
            header("Location: index.php?page=groups&success=updated");
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'];
        
        // Önce grupta öğrenci var mı kontrol et!
        $check = $this->db->prepare("SELECT COUNT(*) FROM Students WHERE GroupID = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            header("Location: index.php?page=groups&error=has_students");
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM Groups WHERE GroupID = ?");
        $stmt->execute([$id]);
        header("Location: index.php?page=groups&success=deleted");
        exit;
    }
}