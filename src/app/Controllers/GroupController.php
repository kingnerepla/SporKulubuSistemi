<?php
class GroupController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // --- GRUPLARI LİSTELE ---
    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];

        // Grupları Çek
        $sql = "SELECT g.*, u.FullName as CoachName,
                       (SELECT COUNT(*) FROM Students WHERE GroupID = g.GroupID AND IsActive = 1) as StudentCount
                FROM Groups g
                LEFT JOIN Users u ON g.CoachID = u.UserID
                WHERE g.ClubID = ? 
                ORDER BY g.GroupName ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Her grubun ders programını çek
        foreach ($groups as &$g) {
            $schedSql = "SELECT * FROM GroupSchedule WHERE GroupID = ? ORDER BY DayOfWeek, StartTime";
            $stmtSch = $this->db->prepare($schedSql);
            $stmtSch->execute([$g['GroupID']]);
            $g['Schedule'] = $stmtSch->fetchAll(PDO::FETCH_ASSOC);
        }

        // Antrenörleri Çek
        // Sadece 3 numaralı rolü (Antrenör) getirir. 
        $sqlCoaches = "SELECT UserID, FullName FROM Users WHERE ClubID = ? AND RoleID = 3 AND IsActive = 1";
        $stmtCoaches = $this->db->prepare($sqlCoaches);
        $stmtCoaches->execute([$clubId]);
        $coaches = $stmtCoaches->fetchAll(PDO::FETCH_ASSOC);

        $this->render('groups', ['groups' => $groups, 'coaches' => $coaches]);
    }

    // --- KAYDET / GÜNCELLE ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                
                $groupId = $_POST['group_id'] ?? null;
                $groupName = trim($_POST['group_name']);
                $coachId = !empty($_POST['coach_id']) ? $_POST['coach_id'] : null;

                // 1. GRUBU EKLE VEYA GÜNCELLE
                if (!empty($groupId)) {
                    // Update
                    $sql = "UPDATE Groups SET GroupName = ?, CoachID = ? WHERE GroupID = ? AND ClubID = ?";
                    $this->db->prepare($sql)->execute([$groupName, $coachId, $groupId, $clubId]);
                    
                    // Eski programı temizle (Sıfırdan ekleyeceğiz)
                    $this->db->prepare("DELETE FROM GroupSchedule WHERE GroupID = ?")->execute([$groupId]);
                } else {
                    // Insert
                    $sql = "INSERT INTO Groups (ClubID, GroupName, CoachID, CreatedAt) VALUES (?, ?, ?, GETDATE())";
                    $this->db->prepare($sql)->execute([$clubId, $groupName, $coachId]);
                    $groupId = $this->db->lastInsertId();
                }

                // 2. DERS PROGRAMINI EKLE (Critical Fix: Foreach kullanımı)
                if (isset($_POST['days']) && is_array($_POST['days'])) {
                    $days = $_POST['days'];
                    $starts = $_POST['starts'];
                    $ends = $_POST['ends'];

                    $insSch = $this->db->prepare("INSERT INTO GroupSchedule (GroupID, DayOfWeek, StartTime, EndTime) VALUES (?, ?, ?, ?)");

                    // Döngüyü indeks ile değil, key ile dönüyoruz (Kayma olmasın diye)
                    foreach ($days as $key => $dayVal) {
                        $startVal = $starts[$key] ?? null;
                        $endVal = $ends[$key] ?? null;

                        if (!empty($dayVal) && !empty($startVal) && !empty($endVal)) {
                            $insSch->execute([$groupId, $dayVal, $startVal, $endVal]);
                        }
                    }
                }

                $this->db->commit();
                $_SESSION['success_message'] = "İşlem başarıyla kaydedildi.";
                header("Location: index.php?page=groups");
                exit;

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Kayıt Hatası: " . $e->getMessage());
            }
        }
    }

    // --- SİL ---
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Öğrenci kontrolü
            $check = $this->db->prepare("SELECT COUNT(*) FROM Students WHERE GroupID = ? AND IsActive = 1");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                echo "<script>alert('Bu grupta aktif öğrenciler var! Silinemez.'); window.location.href='index.php?page=groups';</script>";
                exit;
            }

            $this->db->prepare("DELETE FROM Groups WHERE GroupID = ?")->execute([$id]);
            $_SESSION['success_message'] = "Grup silindi.";
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