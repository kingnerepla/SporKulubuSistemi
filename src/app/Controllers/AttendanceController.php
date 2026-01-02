<?php
class AttendanceController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();

        // GÜVENLİK KONTROLÜ
        $role = strtolower($_SESSION['role'] ?? '');
        $allowedRoles = ['clubadmin', 'admin', 'systemadmin', 'superadmin', 'coach', 'trainer'];
        
        if (!in_array($role, $allowedRoles)) {
            die('<div class="alert alert-danger m-5">Bu sayfaya erişim yetkiniz yok.</div>');
        }
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        $role = strtolower($_SESSION['role'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Tarih Seçimi (Varsayılan: Bugün)
        $date = $_GET['date'] ?? date('Y-m-d');
        // Bugün haftanın kaçıncı günü? (1: Pzt ... 7: Paz)
        $dayOfWeek = date('N', strtotime($date));

        // 1. KULÜBÜN TÜM GRUPLARINI ÇEK
        $stmt = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ? ORDER BY GroupName ASC");
        $stmt->execute([$clubId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. HER GRUBU İŞLE (SAAT VE YETKİ KONTROLÜ)
        foreach ($groups as &$g) {
            
            // A. BUGÜN BU GRUBUN DERSİ VAR MI? (Yeni Tablodan Bakıyoruz)
            $schStmt = $this->db->prepare("SELECT StartTime, EndTime FROM GroupSchedule WHERE GroupID = ? AND DayOfWeek = ? ORDER BY StartTime");
            $schStmt->execute([$g['GroupID'], $dayOfWeek]);
            $schedules = $schStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $g['today_times'] = [];
            $g['is_lesson_day'] = false;

            if ($schedules) {
                $g['is_lesson_day'] = true;
                foreach($schedules as $sch) {
                    // Saati temizle (17:00:00 -> 17:00)
                    $start = substr($sch['StartTime'], 0, 5);
                    $end = substr($sch['EndTime'], 0, 5);
                    $g['today_times'][] = "$start - $end";
                }
            }

            // B. DAHA ÖNCE YOKLAMA ALINMIŞ MI?
            $check = $this->db->prepare("SELECT COUNT(*) FROM Attendance WHERE GroupID = ? AND [Date] = ?");
            $check->execute([$g['GroupID'], $date]);
            $g['is_taken'] = ($check->fetchColumn() > 0);
            $g['student_count'] = $check->fetchColumn(); // (Opsiyonel sayaç)

            // C. ERİŞİM YETKİSİ (KİLİT)
            // Varsayılan: Kapalı
            $g['can_access'] = false;

            // Kural 1: Yöneticiyse her zaman girebilir.
            if (in_array($role, ['clubadmin', 'admin', 'systemadmin', 'superadmin'])) {
                $g['can_access'] = true;
            } 
            // Kural 2: Antrenörse...
            elseif ($role == 'coach' || $role == 'trainer') {
                // Sadece kendi atandığı grup mu?
                if ($g['CoachID'] == $userId) {
                    // Kendi grubuysa, ders günü olmasa bile girebilsin mi? 
                    // Genelde "Sadece ders günü girsin" istenir ama esneklik için "Kendi grubuysa girsin" diyelim.
                    // Eğer sadece ders günü girsin istersen: if ($g['is_lesson_day']) ekle.
                    $g['can_access'] = true; 
                }
            }
        }

        $this->render('attendance', [
            'groups' => $groups,
            'selectedDate' => $date,
            'userRole' => $role
        ]);
    }

    // --- YOKLAMAYI KAYDET ---
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $groupId = $_POST['group_id'];
                $date = $_POST['date']; 
                $statuses = $_POST['status'] ?? []; 

                foreach ($statuses as $studentId => $status) {
                    $status = (int)$status; 

                    // Mevcut durumu kontrol et
                    $checkStmt = $this->db->prepare("SELECT AttendanceID, IsPresent FROM Attendance WHERE StudentID = ? AND [Date] = ?");
                    $checkStmt->execute([$studentId, $date]);
                    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        // Güncelleme (Hata düzeltme senaryosu)
                        $oldStatus = (int)$existing['IsPresent'];
                        
                        // Gelmedi -> Geldi olduysa (1 düş)
                        if ($oldStatus == 0 && $status == 1) $this->modifySessions($studentId, -1);
                        // Geldi -> Gelmedi olduysa (1 iade et)
                        elseif ($oldStatus == 1 && $status == 0) $this->modifySessions($studentId, +1);

                        $this->db->prepare("UPDATE Attendance SET IsPresent = ? WHERE AttendanceID = ?")->execute([$status, $existing['AttendanceID']]);

                    } else {
                        // Yeni Kayıt
                        $this->db->prepare("INSERT INTO Attendance (StudentID, GroupID, [Date], IsPresent, CreatedAt) VALUES (?, ?, ?, ?, GETDATE())")->execute([$studentId, $groupId, $date, $status]);
                        // Geldi ise düş
                        if ($status == 1) $this->modifySessions($studentId, -1);
                    }
                }

                $this->db->commit();
                $_SESSION['success_message'] = "Yoklama başarıyla kaydedildi.";
                header("Location: index.php?page=attendance&group_id=$groupId&date=$date");
                exit;

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // Yardımcı: Kontör Düş/Ekle
    private function modifySessions($studentId, $amount) {
        $sql = "UPDATE Students SET RemainingSessions = RemainingSessions + ? WHERE StudentID = ?";
        $this->db->prepare($sql)->execute([$amount, $studentId]);
    }
    public function sendMail() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        $groupId = $_GET['group_id'];
        $month = $_GET['month'];
        $year = $_GET['year'];
        $toEmail = $_GET['email'] ?? $_SESSION['email']; // Varsayılan: Giriş yapanın maili
    
        // 1. Grup Adını Al
        $stmt = $this->db->prepare("SELECT GroupName FROM Groups WHERE GroupID = ?");
        $stmt->execute([$groupId]);
        $groupName = $stmt->fetchColumn();
    
        // 2. Basit HTML İçerik Oluştur (Özet Rapor)
        $subject = "Yoklama Raporu: $groupName ($month/$year)";
        
        $message = "<html><body>";
        $message .= "<h2>$groupName - Aylık Yoklama Özeti</h2>";
        $message .= "<p><b>Dönem:</b> $month / $year</p>";
        $message .= "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        $message .= "<tr><th>Öğrenci</th><th>Toplam Katılım</th></tr>";
    
        // Öğrenci verilerini çek
        $stuSql = "SELECT StudentID, FullName FROM Students WHERE GroupID = ? AND IsActive = 1";
        $stmtStu = $this->db->prepare($stuSql);
        $stmtStu->execute([$groupId]);
        $students = $stmtStu->fetchAll(PDO::FETCH_ASSOC);
    
        foreach($students as $s) {
            $attSql = "SELECT COUNT(*) FROM Attendance WHERE StudentID = ? AND GroupID = ? AND MONTH([Date]) = ? AND YEAR([Date]) = ? AND IsPresent = 1";
            $stmtAtt = $this->db->prepare($attSql);
            $stmtAtt->execute([$s['StudentID'], $groupId, $month, $year]);
            $count = $stmtAtt->fetchColumn();
            
            $message .= "<tr><td>{$s['FullName']}</td><td align='center'>$count Ders</td></tr>";
        }
        
        $message .= "</table><br><p>Bu rapor Spor CRM sistemi tarafından otomatik oluşturulmuştur.</p>";
        $message .= "</body></html>";
    
        // 3. Mail Başlıkları
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@sporcrm.com" . "\r\n";
    
        // 4. Gönder
        if(mail($toEmail, $subject, $message, $headers)) {
            $_SESSION['success_message'] = "Rapor özeti $toEmail adresine başarıyla gönderildi.";
        } else {
            $_SESSION['error_message'] = "Mail gönderimi sırasında bir hata oluştu.";
        }
    
        header("Location: index.php?page=attendance_report&group_id=$groupId&month=$month&year=$year");
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