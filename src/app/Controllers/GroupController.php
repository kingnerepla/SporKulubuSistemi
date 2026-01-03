<?php
// Hataları görelim
ini_set('display_errors', 1);
error_reporting(E_ALL);

class GroupController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // --- GRUPLARI LİSTELE ---
    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        $role = strtolower($_SESSION['role'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;

        // 1. Grupları Çek
        $sql = "SELECT g.*, u.FullName as CoachName,
                       (SELECT COUNT(*) FROM Students WHERE GroupID = g.GroupID AND IsActive = 1) as StudentCount
                FROM Groups g
                LEFT JOIN Users u ON g.CoachID = u.UserID
                WHERE g.ClubID = ?";
        
        $params = [$clubId];

        if ($role == 'coach' || $role == 'trainer') {
            $sql .= " AND g.CoachID = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY g.GroupName ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Programları Ekle
        foreach ($groups as &$g) {
            $stmtSch = $this->db->prepare("SELECT * FROM GroupSchedule WHERE GroupID = ? ORDER BY DayOfWeek, StartTime");
            $stmtSch->execute([$g['GroupID']]);
            $g['Schedule'] = $stmtSch->fetchAll(PDO::FETCH_ASSOC);
        }

        // 3. Antrenörleri Çek (Sadece Yönetici İçin)
        $coaches = [];
        if ($role != 'coach' && $role != 'trainer') {
            $stmtCoaches = $this->db->prepare("SELECT UserID, FullName FROM Users WHERE ClubID = ? AND RoleID = 3 AND IsActive = 1 ORDER BY FullName ASC");
            $stmtCoaches->execute([$clubId]);
            $coaches = $stmtCoaches->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->render('groups', ['groups' => $groups, 'coaches' => $coaches]);
    }

    // --- KAYDET / GÜNCELLE (TEK FONKSİYON) ---
    public function save() {
        // GÜVENLİK
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role == 'coach' || $role == 'trainer') {
            $_SESSION['error_message'] = "Yetkisiz işlem.";
            header("Location: index.php?page=groups");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                
                $groupId = $_POST['group_id'] ?? ''; // Boşsa INSERT, Doluysa UPDATE
                $groupName = trim($_POST['group_name']);
                $coachId = !empty($_POST['coach_id']) ? $_POST['coach_id'] : null;

                if (empty($groupId)) {
                    // --- INSERT (YENİ KAYIT) ---
                    $stmt = $this->db->prepare("INSERT INTO Groups (ClubID, GroupName, CoachID, CreatedAt) VALUES (?, ?, ?, GETDATE())");
                    $stmt->execute([$clubId, $groupName, $coachId]);
                    $groupId = $this->db->lastInsertId();
                    $_SESSION['success_message'] = "Grup oluşturuldu.";
                } else {
                    // --- UPDATE (GÜNCELLEME) ---
                    $stmt = $this->db->prepare("UPDATE Groups SET GroupName = ?, CoachID = ? WHERE GroupID = ?");
                    $stmt->execute([$groupName, $coachId, $groupId]);
                    
                    // Programı sil (yenisi eklenecek)
                    $this->db->prepare("DELETE FROM GroupSchedule WHERE GroupID = ?")->execute([$groupId]);
                    $_SESSION['success_message'] = "Grup güncellendi.";
                }

                // --- PROGRAMI KAYDET ---
                if (!empty($_POST['days'])) {
                    $sqlSched = "INSERT INTO GroupSchedule (GroupID, DayOfWeek, StartTime, EndTime) VALUES (?, ?, ?, ?)";
                    $stmtSched = $this->db->prepare($sqlSched);

                    foreach ($_POST['days'] as $key => $day) {
                        $start = $_POST['starts'][$key] ?? null;
                        $end = $_POST['ends'][$key] ?? null;
                        if ($day && $start && $end) {
                            $stmtSched->execute([$groupId, $day, $start, $end]);
                        }
                    }
                }

                $this->db->commit();
            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                $_SESSION['error_message'] = "Hata: " . $e->getMessage();
            }
            header("Location: index.php?page=groups");
            exit;
        }
    }

    // --- SİLME ---
    public function delete() {
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role == 'coach' || $role == 'trainer') {
            die("Yetkisiz işlem.");
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $check = $this->db->prepare("SELECT COUNT(*) FROM Students WHERE GroupID = ? AND IsActive = 1");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                $_SESSION['error_message'] = "Bu grupta aktif öğrenciler var! Önce öğrencileri taşıyın.";
            } else {
                try {
                    $this->db->beginTransaction();
                    $this->db->prepare("DELETE FROM GroupSchedule WHERE GroupID = ?")->execute([$id]);
                    $this->db->prepare("DELETE FROM Groups WHERE GroupID = ?")->execute([$id]);
                    $this->db->commit();
                    $_SESSION['success_message'] = "Grup silindi.";
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $_SESSION['error_message'] = "Silme hatası.";
                }
            }
        }
        header("Location: index.php?page=groups");
        exit;
    }

    private function render($view, $data = []) {
        if(isset($_SESSION)) $data = array_merge($_SESSION, $data);
        extract($data);
        ob_start();
        $baseDir = __DIR__ . '/../';
        $viewsFolder = is_dir($baseDir . 'Views') ? 'Views' : 'views';
        $viewFile = $baseDir . $viewsFolder . "/admin/{$view}.php";
        if (file_exists($viewFile)) include $viewFile;
        $content = ob_get_clean();
        $layoutPath = $baseDir . $viewsFolder . '/layouts/admin_layout.php';
        if (file_exists($layoutPath)) include $layoutPath; else echo $content;
    }
}