<?php

class GroupScheduleController {
    private $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }

    // Program Düzenleme Ekranı
    public function edit() {
        $groupId = $_GET['id'];
        $stmt = $this->db->prepare("SELECT * FROM GroupSchedules WHERE GroupID = ?");
        $stmt->execute([$groupId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('group_schedule_edit', ['groupId' => $groupId, 'schedules' => $schedules]);
    }

    // Şablonu Kaydet
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
                header("Location: index.php?page=groups&success=template_saved");
            } catch (Exception $e) {
                $this->db->rollBack();
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // --- BURASI KRİTİK: Otomatik Takvim Oluşturma ---
    public function generateSessions() {
        $groupId = $_POST['group_id'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];

        // 1. Grubun şablonunu al
        $stmt = $this->db->prepare("SELECT * FROM GroupSchedules WHERE GroupID = ?");
        $stmt->execute([$groupId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Tarih aralığını döngüye al
        $current = new DateTime($startDate);
        $last = new DateTime($endDate);
        $last->modify('+1 day');

        $ins = $this->db->prepare("INSERT INTO TrainingSessions (GroupID, ClubID, TrainingDate, StartTime, EndTime, Status) VALUES (?, ?, ?, ?, ?, 'Scheduled')");

        while ($current < $last) {
            $dayOfWeek = $current->format('N'); // 1 (Pzt) - 7 (Paz)
            foreach ($schedules as $sch) {
                if ($sch['DayOfWeek'] == $dayOfWeek) {
                    $ins->execute([$groupId, $clubId, $current->format('Y-m-d'), $sch['StartTime'], $sch['EndTime']]);
                }
            }
            $current->modify('+1 day');
        }
        header("Location: index.php?page=attendance&group_id=$groupId&msg=sessions_generated");
    }

    private function render($view, $data = []) {
        extract($data); ob_start();
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}