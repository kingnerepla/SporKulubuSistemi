<?php

class GroupScheduleController {
    private $db;
    public function __construct() { 
        // Database sınıfı zaten index.php'de yüklendiği için direkt bağlanıyoruz
        $this->db = (new Database())->getConnection(); 
    }

    // Program Düzenleme Ekranı
    public function edit() {
        $groupId = $_GET['id'];
        $stmt = $this->db->prepare("SELECT * FROM GroupSchedules WHERE GroupID = ?");
        $stmt->execute([$groupId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('group_schedule_edit', ['groupId' => $groupId, 'schedules' => $schedules]);
    }

    // Takvimi Listele
    public function sessions() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        $groupId = $_GET['group_id'] ?? null;

        $sql = "SELECT ts.*, g.GroupName 
                FROM TrainingSessions ts 
                JOIN Groups g ON ts.GroupID = g.GroupID 
                WHERE ts.ClubID = ? " . ($groupId ? "AND ts.GroupID = ?" : "") . " 
                ORDER BY ts.TrainingDate DESC, ts.StartTime ASC";
        
        $stmt = $this->db->prepare($sql);
        $params = $groupId ? [$clubId, $groupId] : [$clubId];
        $stmt->execute($params);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('training_sessions_list', ['sessions' => $sessions]);
    }

    public function trainingGroups() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        $stmt = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmt->execute([$clubId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $this->render('training_groups_list', ['groups' => $groups]);
    }

    // 2. ADIM: Seçilen Grubun Takvimini Göster
    public function groupCalendar() {
        $groupId = $_GET['id'];
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];

        $stmtG = $this->db->prepare("SELECT GroupName FROM Groups WHERE GroupID = ?");
        $stmtG->execute([$groupId]);
        $group = $stmtG->fetch(PDO::FETCH_ASSOC);

        $stmtS = $this->db->prepare("SELECT * FROM TrainingSessions WHERE GroupID = ? ORDER BY TrainingDate ASC");
        $stmtS->execute([$groupId]);
        $sessions = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        $this->render('group_calendar', ['group' => $group, 'sessions' => $sessions]);
    }
   
    // Ders Durumunu Değiştir (Gereksiz debug kaldırıldı, yönlendirme eklendi)
    public function updateSessionStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionId = $_POST['session_id'];
            $status = $_POST['status'];
            $note = $_POST['note'] ?? null;

            $sql = "UPDATE TrainingSessions SET Status = ?, Note = ? WHERE SessionID = ?";
            $this->db->prepare($sql)->execute([$status, $note, $sessionId]);

            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
    // 1. Şablonu Kaydet - AYNI SAYFADA KALIR
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
                
                // Düzenleme: Kendi sayfasına geri döner ve başarı mesajı ekler
                header("Location: index.php?page=group_schedule&id=$groupId&success=template_saved");
                exit;
                
            } catch (Exception $e) {
                $this->db->rollBack();
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // 2. Antrenmanları Otomatik Oluştur - TAKVİME GİDER
    public function generateSessions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $groupId = $_POST['group_id'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];

        $stmt = $this->db->prepare("SELECT * FROM GroupSchedules WHERE GroupID = ?");
        $stmt->execute([$groupId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($schedules)) {
            die("Hata: Grubun haftalık program şablonu bulunamadı. Lütfen önce programı ayarlayın.");
        }

        $current = new DateTime($startDate);
        $last = new DateTime($endDate);
        $last->modify('+1 day');

        $ins = $this->db->prepare("INSERT INTO TrainingSessions (GroupID, ClubID, TrainingDate, StartTime, EndTime, Status) VALUES (?, ?, ?, ?, ?, 'Scheduled')");

        while ($current < $last) {
            $dayOfWeek = $current->format('N'); 
            foreach ($schedules as $sch) {
                if ($sch['DayOfWeek'] == $dayOfWeek) {
                    $ins->execute([$groupId, $clubId, $current->format('Y-m-d'), $sch['StartTime'], $sch['EndTime']]);
                }
            }
            $current->modify('+1 day');
        }
        
        // Düzenleme: İşlem bittikten sonra doğrudan Takvim (group_calendar) sayfasına gider
        header("Location: index.php?page=group_calendar&id=$groupId&msg=sessions_generated");
        exit;
    }
 

    // SENİN ORİJİNAL RENDER METODUN (HİÇ DOKUNULMADI)
    private function render($view, $data = []) {
        extract($data); ob_start();
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}