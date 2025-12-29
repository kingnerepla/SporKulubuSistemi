<?php

class GroupScheduleController {
    private $db;

    public function __construct() { 
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

    // Tüm Antrenmanları Listele
    public function sessions() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        $groupId = $_GET['group_id'] ?? null;
        $sql = "SELECT ts.*, g.GroupName FROM TrainingSessions ts JOIN Groups g ON ts.GroupID = g.GroupID WHERE ts.ClubID = ? " . ($groupId ? "AND ts.GroupID = ?" : "") . " ORDER BY ts.TrainingDate DESC, ts.StartTime ASC";
        $stmt = $this->db->prepare($sql);
        $params = $groupId ? [$clubId, $groupId] : [$clubId];
        $stmt->execute($params);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->render('training_sessions_list', ['sessions' => $sessions]);
    }

    // Grupları Listele
    public function trainingGroups() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        $stmt = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmt->execute([$clubId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->render('training_groups_list', ['groups' => $groups]);
    }

    // Grup Takvimi
    public function groupCalendar() {
        $groupId = $_GET['id'];
        $stmtG = $this->db->prepare("SELECT GroupName FROM Groups WHERE GroupID = ?");
        $stmtG->execute([$groupId]);
        $group = $stmtG->fetch(PDO::FETCH_ASSOC);

        $stmtS = $this->db->prepare("SELECT * FROM TrainingSessions WHERE GroupID = ? ORDER BY TrainingDate ASC");
        $stmtS->execute([$groupId]);
        $sessions = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        $this->render('group_calendar', ['group' => $group, 'sessions' => $sessions, 'groupId' => $groupId]);
    }

    // TEKLİ SİLME
    public function deleteSingleSession() {
        $sessionId = $_GET['id'] ?? null;
        $groupId = $_GET['group_id'] ?? null;
    
        if (!$sessionId) {
            header("Location: index.php?page=group_calendar&id=$groupId");
            exit;
        }
    
        try {
            $this->db->beginTransaction();
    
            // Önce varsa yoklama kayıtlarını sil (Yabancı anahtar hatasını önlemek için)
            $stmt1 = $this->db->prepare("DELETE FROM [dbo].[Attendance] WHERE [SessionID] = ?");
            $stmt1->execute([$sessionId]);
    
            // Sonra antrenmanı sil
            $stmt2 = $this->db->prepare("DELETE FROM [dbo].[TrainingSessions] WHERE [SessionID] = ?");
            $stmt2->execute([$sessionId]);
    
            $this->db->commit();
            header("Location: index.php?page=group_calendar&id=$groupId&msg=deleted");
            exit;
    
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            die("Silme Hatası: " . $e->getMessage());
        }
    }

    // DURUM GÜNCELLEME (Düzeltildi)
    public function updateSessionStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Formdan gelen verileri al
            $sessionId = $_POST['session_id'] ?? null;
            $status    = $_POST['status'] ?? null;
            $note      = $_POST['note'] ?? null;
            
            // Grup ID'sini hem POST hem GET hem de REQUEST içinden kovalıyoruz
            $groupId   = $_POST['group_id'] ?? $_GET['group_id'] ?? $_REQUEST['group_id'] ?? null;
    
            if (!$sessionId) {
                header("Location: index.php?page=dashboard&error=missing_id");
                exit;
            }
    
            try {
                // 1. Durumu Güncelle (Kolon isimlerini [] içine alarak Azure'u zorluyoruz)
                $sql = "UPDATE [dbo].[TrainingSessions] 
                        SET [Status] = ?, [Note] = ? 
                        WHERE [SessionID] = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$status, $note, $sessionId]);
    
                // 2. Yönlendirme Güvenliği: Eğer hala groupId yoksa veritabanından seansın grubunu bul
                if (!$groupId || $groupId == 0) {
                    $check = $this->db->prepare("SELECT GroupID FROM [dbo].[TrainingSessions] WHERE [SessionID] = ?");
                    $check->execute([$sessionId]);
                    $groupId = $check->fetchColumn();
                }
    
                // 3. KESİN YÖNLENDİRME (URL'yi manuel inşa ediyoruz)
                $finalUrl = "index.php?page=group_calendar&id=" . (int)$groupId . "&msg=updated";
                header("Location: " . $finalUrl);
                exit; // PHP'nin başka bir yere sapmasını engeller
    
            } catch (Exception $e) {
                // Hata olursa Dashboard'a atma, hatayı bas ki görelim
                die("SQL HATASI: " . $e->getMessage());
            }
        }
    }

    // ŞABLON KAYDET
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
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // ANTRENMANLARI OTOMATİK OLUŞTUR
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
        header("Location: index.php?page=group_calendar&id=$groupId&msg=sessions_generated");
        exit;
    }

    private function render($view, $data = []) {
        extract($data); ob_start();
        $viewPath = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "Görünüm dosyası bulunamadı: $view";
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}