<?php

class GroupScheduleController {
    private $db;

    public function __construct() { 
        $this->db = (new Database())->getConnection(); 
    }

    // 1. Grupları Listele (Hata aldığın metot burasıydı, geri eklendi)
    public function trainingGroups() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        $stmt = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmt->execute([$clubId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->render('training_groups_list', ['groups' => $groups]);
    }

    // 2. Grup Takvimi (Detay Görünümü)
    public function groupCalendar() {
        $groupId = $_GET['id'] ?? 0;
        $stmtG = $this->db->prepare("SELECT GroupName FROM Groups WHERE GroupID = ?");
        $stmtG->execute([$groupId]);
        $group = $stmtG->fetch(PDO::FETCH_ASSOC);

        $stmtS = $this->db->prepare("SELECT * FROM TrainingSessions WHERE GroupID = ? ORDER BY TrainingDate ASC");
        $stmtS->execute([$groupId]);
        $sessions = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        $this->render('group_calendar', ['group' => $group, 'sessions' => $sessions, 'groupId' => $groupId]);
    }

    // 3. Durum Güncelleme (Dashboard'a fırlatmayı engelleyen güvenli sürüm)
    public function updateSessionStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionId = $_POST['session_id'] ?? null;
            $status    = $_POST['status'] ?? null;
            $note      = $_POST['note'] ?? null;
            $groupId   = $_POST['group_id'] ?? $_GET['group_id'] ?? null;
    
            if (!$sessionId) {
                header("Location: index.php?page=dashboard");
                exit;
            }
    
            try {
                // Azure SQL için güvenli güncelleme
                $sql = "UPDATE [dbo].[TrainingSessions] SET [Status] = ?, [Note] = ? WHERE [SessionID] = ?";
                $this->db->prepare($sql)->execute([$status, $note, $sessionId]);
    
                // Geri dönüş yolu kontrolü
                if (!$groupId) {
                    $check = $this->db->prepare("SELECT GroupID FROM [dbo].[TrainingSessions] WHERE [SessionID] = ?");
                    $check->execute([$sessionId]);
                    $groupId = $check->fetchColumn();
                }
    
                header("Location: index.php?page=group_calendar&id=" . $groupId . "&msg=updated");
                exit;
            } catch (Exception $e) {
                die("Güncelleme Hatası: " . $e->getMessage());
            }
        }
    }

    // 4. Tekli Antrenman Silme (Kolon hataları temizlendi)
    public function deleteSingleSession() {
        $sessionId = $_GET['id'] ?? null;
        $groupId = $_GET['group_id'] ?? null;
    
        if (!$sessionId) {
            header("Location: index.php?page=group_calendar&id=$groupId");
            exit;
        }
    
        try {
            $this->db->beginTransaction();
            // Yoklama tablosu bağımlılığını temizle (Hata alsa bile devam et)
            try {
                $this->db->prepare("DELETE FROM [dbo].[Attendance] WHERE [SessionID] = ? OR [TrainingSessionID] = ?")->execute([$sessionId, $sessionId]);
            } catch (Exception $e) {}
    
            // Seansı sil
            $stmt2 = $this->db->prepare("DELETE FROM [dbo].[TrainingSessions] WHERE [SessionID] = ?");
            $stmt2->execute([$sessionId]);
    
            $this->db->commit();
            header("Location: index.php?page=group_calendar&id=$groupId&msg=deleted");
            exit;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            die("Silme Hatası: " . $e->getMessage());
        }
    }

    // 5. Antrenman Şablonunu Kaydet
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groupId = $_POST['group_id'];
            $days = $_POST['days'] ?? [];
            try {
                $this->db->beginTransaction();
                $this->db->prepare("DELETE FROM GroupSchedules WHERE GroupID = ?")->execute([$groupId]);
                $ins = $this->db->prepare("INSERT INTO GroupSchedules (GroupID, DayOfWeek, StartTime, EndTime) VALUES (?, ?, ?, ?)");
                foreach ($days as $dayNum => $times) {
                    if (!empty($times['start']) && !empty($times['end'])) {
                        $ins->execute([$groupId, $dayNum, $times['start'], $times['end']]);
                    }
                }
                $this->db->commit();
                header("Location: index.php?page=group_schedule&id=$groupId&success=template_saved");
                exit;
            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Şablon Hatası: " . $e->getMessage());
            }
        }
    }

    // 6. Otomatik Antrenman Oluşturma (Generate)
    public function generateSessions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $groupId = $_POST['group_id'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];

        $stmt = $this->db->prepare("SELECT * FROM GroupSchedules WHERE GroupID = ?");
        $stmt->execute([$groupId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $current = new DateTime($startDate);
        $last = new DateTime($endDate);
        $last->modify('+1 day');

        $ins = $this->db->prepare("INSERT INTO [dbo].[TrainingSessions] (GroupID, ClubID, TrainingDate, StartTime, EndTime, Status) VALUES (?, ?, ?, ?, ?, 'Scheduled')");
        while ($current < $last) {
            $dayOfWeek = $current->format('N'); 
            foreach ($schedules as $sch) {
                if ($sch['DayOfWeek'] == $dayOfWeek) {
                    $ins->execute([$groupId, $clubId, $current->format('Y-m-d'), $sch['StartTime'], $sch['EndTime']]);
                }
            }
            $current->modify('+1 day');
        }
        header("Location: index.php?page=group_calendar&id=$groupId&msg=sessions_generated");
        exit;
    }

    // 7. Render (Görünüm Yükleyici)
    private function render($view, $data = []) {
        extract($data); ob_start();
        $viewPath = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($viewPath)) { include $viewPath; }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}