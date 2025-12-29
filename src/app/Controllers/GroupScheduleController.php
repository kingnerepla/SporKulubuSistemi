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

    // Grup Takvimi (Silme ve Düzenleme Yapılan Yer)
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

    // TEKLİ SİLME (Azure SessionID uyumlu)
    public function deleteSingleSession() {
        $sessionId = $_GET['id'] ?? null;
        $groupId = $_GET['group_id'] ?? null;
    
        if (!$sessionId) {
            header("Location: index.php?page=group_calendar&id=$groupId");
            exit;
        }
    
        try {
            $this->db->beginTransaction();
    
            // 1. ADIM: Attendance tablosundaki bağlantıyı kopar
            // Burada da SessionID ismini Azure'da gördüğün gibi tam yazıyoruz
            $stmt1 = $this->db->prepare("DELETE FROM [dbo].[TrainingSessions] WHERE [SessionID] = ?");
         
            $stmt1->execute([$sessionId]);
    
            // 2. ADIM: TrainingSessions tablosundan sil
            // SQL Server bazen dbo şemasını zorunlu tutar
            $stmt2 = $this->db->prepare("DELETE FROM TrainingSessions WHERE SessionID = ?");
            $stmt2->execute([$sessionId]);
    
            $this->db->commit();
            header("Location: index.php?page=group_calendar&id=$groupId&msg=deleted");
            exit;
    
        } catch (Exception $e) {
            $this->db->rollBack();
            // Eğer hata devam ederse, tablo yapısını ekrana bastıran bir debug yapalım
            die("Hata Devam Ediyor: " . $e->getMessage());
        }
    }

    // DURUM GÜNCELLEME - SQL Server için optimize edildi
    public function updateSessionStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Formdan gelen verileri alıyoruz
            $sessionId = $_POST['session_id'] ?? null;
            $status    = $_POST['status'] ?? null;
            $note      = $_POST['note'] ?? null;
            $groupId   = $_POST['group_id'] ?? $_GET['group_id'] ?? null;
    
            if (!$sessionId) {
                die("Hata: Oturum ID'si eksik.");
            }
    
            try {
                // SQL Server için en güvenli sorgu yazımı
                // Tablo ve kolon isimlerini [] içine alarak şemayı (dbo) belirtiyoruz
                $sql = "UPDATE [dbo].[TrainingSessions] 
                        SET [Status] = :status, [Note] = :note 
                        WHERE [SessionID] = :id";
                
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    ':status' => $status,
                    ':note'   => $note,
                    ':id'     => $sessionId
                ]);
    
                // İşlem başarılıysa veya başarısızsa her durumda takvime dön, Dashboard'a gitme
                $redirectUrl = "index.php?page=group_calendar&id=" . ($groupId ?? 0);
                
                if ($result) {
                    header("Location: " . $redirectUrl . "&msg=updated");
                } else {
                    // Sorgu çalıştı ama etkilenen satır yoksa
                    header("Location: " . $redirectUrl . "&error=no_change");
                }
                exit;
    
            } catch (Exception $e) {
                // Hata olursa Dashboard'a atmak yerine hatayı ekrana bas ki görelim
                echo "<h3>Veritabanı Hatası:</h3>";
                echo $e->getMessage();
                echo "<br><a href='javascript:history.back()'>Geri Dön</a>";
                exit;
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
                $this->db->rollBack();
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
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}