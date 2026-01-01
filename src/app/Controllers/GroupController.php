<?php

class GroupController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        // 1. Oturum bilgilerini al
        $role = trim($_SESSION['role'] ?? 'Guest');
        $currentUserId = $_SESSION['user_id'] ?? null;

        // 2. Kulüp ID Belirleme (Sadece kendi kulübü)
        $clubId = ($role === 'SystemAdmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);

        if (!$clubId) {
            header("Location: index.php?page=dashboard&error=no_club_context");
            exit;
        }

        // 3. Grupları Getir (Sadece bu kulübe ait gruplar)
        $sqlGroups = "SELECT g.*, u.FullName as TrainerName,
                (SELECT COUNT(*) FROM Students s WHERE s.GroupID = g.GroupID AND s.IsActive = 1) as StudentCount
                FROM Groups g
                LEFT JOIN Users u ON g.TrainerID = u.UserID
                WHERE g.ClubID = ? 
                ORDER BY g.GroupName ASC";
        
        $stmtGroups = $this->db->prepare($sqlGroups);
        $stmtGroups->execute([$clubId]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        // 4. KRİTİK DÜZELTME: Sadece BU KULÜBÜN antrenörlerini getir
        $sqlTrainers = "SELECT UserID, FullName 
                        FROM Users 
                        WHERE ClubID = ? 
                        AND RoleID = 2 
                        AND IsActive = 1 
                        ORDER BY FullName ASC";
        
        $stmtTrainers = $this->db->prepare($sqlTrainers);
        $stmtTrainers->execute([$clubId]); // Sadece oturumdaki clubId kullanılıyor
        $trainers = $stmtTrainers->fetchAll(PDO::FETCH_ASSOC);

        // View'a gönderilecek veriler
        $data = [
            'groups'    => $groups,
            'trainers'  => $trainers,
            'clubId'    => $clubId,
            'role'      => $role
        ];

        $this->render('groups', $data);
    }

    // store, update ve render metodları aynı kalacak...
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clubId    = $_POST['club_id'] ?? $_SESSION['club_id'];
            $name      = trim($_POST['group_name'] ?? '');
            $trainerId = !empty($_POST['trainer_id']) ? $_POST['trainer_id'] : NULL;

            if (empty($name)) {
                header("Location: index.php?page=groups&error=empty_name");
                exit;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO Groups (GroupName, TrainerID, ClubID, CreatedAt) VALUES (?, ?, ?, GETDATE())");
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
            $groupId   = $_POST['group_id'] ?? null;
            $name      = trim($_POST['group_name'] ?? '');
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

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}