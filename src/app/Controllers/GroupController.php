<?php
class GroupController {
    private $db;
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // Oturum bilgilerini al
        $role = $_SESSION['role'] ?? 'Guest';
        $currentUserId = $_SESSION['user_id'] ?? null; // Mevcut yöneticinin ID'si

        // Süper admin bir kulüp seçmişse o id'yi, kulüp admin ise kendi id'sini al
        $clubId = ($role === 'SystemAdmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);

        if (!$clubId && $role !== 'SystemAdmin') {
            die("Hata: Kulüp yetkisi bulunamadı.");
        }

        // 1. Grupları Getir (Antrenör adı ve Öğrenci sayısıyla birlikte)
        $sql = "SELECT g.*, u.FullName as TrainerName,
                (SELECT COUNT(*) FROM Students s WHERE s.GroupID = g.GroupID) as StudentCount
                FROM Groups g
                LEFT JOIN Users u ON g.TrainerID = u.UserID
                WHERE g.ClubID = ? 
                ORDER BY g.GroupName ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Antrenörleri Getir (Yönetici hariç, sadece RoleID = 2 olanlar)
        // NOT: RoleID = 2 antrenörleri temsil eder. UserID != ? ise Ahmet (Yönetici) listeye girmesin diye eklenmiştir.
        $stmtTrainers = $this->db->prepare("
            SELECT UserID, FullName FROM Users 
            WHERE ClubID = ? 
            AND RoleID = 2 
            AND IsActive = 1 
            AND UserID != ?
            ORDER BY FullName ASC
        ");
        $stmtTrainers->execute([$clubId, $currentUserId]);
        $trainers = $stmtTrainers->fetchAll(PDO::FETCH_ASSOC);

        // View'a gönderilecek veriler
        $data = [
            'groups' => $groups,
            'trainers' => $trainers,
            'clubId' => $clubId
        ];

        // İçeriği Layout'a gömme
        extract($data);
        ob_start();
        require_once __DIR__ . '/../Views/admin/groups.php';
        $content = ob_get_clean();

        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clubId = $_POST['club_id'] ?? $_SESSION['club_id'] ?? ($_SESSION['selected_club_id'] ?? null);
            $name = $_POST['group_name'] ?? '';
            $trainerId = !empty($_POST['trainer_id']) ? $_POST['trainer_id'] : NULL;

            if (empty($name)) {
                die("Hata: Grup adı boş olamaz.");
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO Groups (GroupName, TrainerID, ClubID) VALUES (?, ?, ?)");
                $stmt->execute([$name, $trainerId, $clubId]);
                header("Location: index.php?page=groups&success=created");
            } catch (PDOException $e) {
                die("Kayıt Hatası: " . $e->getMessage());
            }
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groupId = $_POST['group_id'] ?? null;
            $name = $_POST['group_name'] ?? '';
            $trainerId = !empty($_POST['trainer_id']) ? $_POST['trainer_id'] : NULL;

            try {
                $stmt = $this->db->prepare("UPDATE Groups SET GroupName = ?, TrainerID = ? WHERE GroupID = ?");
                $stmt->execute([$name, $trainerId, $groupId]);
                header("Location: index.php?page=groups&success=updated");
            } catch (PDOException $e) {
                die("Güncelleme Hatası: " . $e->getMessage());
            }
            exit;
        }
    }
}