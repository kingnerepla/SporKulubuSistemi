<?php
// Hataları görelim
ini_set('display_errors', 1);
error_reporting(E_ALL);

class AttendanceController {
    private $db;

    public function __construct() {
        // 🔥 1. SAAT DİLİMİ AYARI (Tarih kaymasını engeller)
        date_default_timezone_set('Europe/Istanbul');

        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();

        // Yetki Kontrolü
        $role = strtolower($_SESSION['role'] ?? '');
        $allowedRoles = ['clubadmin', 'admin', 'systemadmin', 'superadmin', 'coach', 'trainer'];
        if (!in_array($role, $allowedRoles)) {
            header("Location: index.php?page=dashboard");
            exit;
        }
    }

    // --- LİSTELEME SAYFASI ---
    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        $role = strtolower($_SESSION['role'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;
        $isAdmin = in_array($role, ['clubadmin', 'admin', 'systemadmin', 'superadmin']);

        // Tarih Belirleme (Saat dilimi ayarlandığı için artık doğru çalışır)
        if ($isAdmin) {
            $date = $_GET['date'] ?? date('Y-m-d');
        } else {
            $date = date('Y-m-d');
        }

        // Navigasyon
        $prevDate = date('Y-m-d', strtotime($date . ' -1 day'));
        $nextDate = date('Y-m-d', strtotime($date . ' +1 day'));

        // Tarih Başlığı
        $timestamp = strtotime($date);
        $days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $formattedDate = date('d', $timestamp) . ' ' . $months[date('n', $timestamp)] . ' ' . date('Y', $timestamp) . ' ' . $days[date('w', $timestamp)];
        $dayOfWeek = date('N', $timestamp);

        // 1. Grupları Getir
        $sqlGroups = "SELECT GroupID, GroupName FROM Groups WHERE ClubID = ?";
        $paramsGroups = [$clubId];

        if (!$isAdmin) {
            $sqlGroups .= " AND CoachID = ?";
            $paramsGroups[] = $userId;
        }
        $sqlGroups .= " ORDER BY GroupName ASC";

        $stmt = $this->db->prepare($sqlGroups);
        $stmt->execute($paramsGroups);
        $rawGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filteredGroups = []; // Filtrelenmiş grupları burada toplayacağız

        // 2. Grupların Detaylarını Doldur ve FİLTRELE
        foreach ($rawGroups as $g) {
            $gId = $g['GroupID'];

            // Ders Günü mü?
            $stmtSch = $this->db->prepare("SELECT StartTime, EndTime FROM GroupSchedule WHERE GroupID = ? AND DayOfWeek = ?");
            $stmtSch->execute([$gId, $dayOfWeek]);
            $schedules = $stmtSch->fetchAll(PDO::FETCH_ASSOC);

            $isLessonDay = !empty($schedules);

            // 🔥 2. FİLTRELEME MANTIĞI 🔥
            // Eğer Yönetici DEĞİLSE ve Bugün Ders YOKSA -> Bu grubu listeye ekleme, pas geç.
            if (!$isAdmin && !$isLessonDay) {
                continue; 
            }

            // Grup verilerini işle
            $g['is_lesson_day'] = $isLessonDay;
            $g['lesson_hours'] = '';
            
            if ($isLessonDay) {
                $times = [];
                foreach($schedules as $s) {
                    $times[] = substr($s['StartTime'],0,5) . "-" . substr($s['EndTime'],0,5);
                }
                $g['lesson_hours'] = implode(', ', $times);
            }

            // Yönetici her zaman işlem yapabilsin diye true yapıyoruz (Ama yukarıda continue ile atılmadıysa)
            if ($isAdmin) $g['is_lesson_day'] = true;

            // Öğrencileri Çek
            $stmtStu = $this->db->prepare("SELECT StudentID, FullName, RemainingSessions FROM Students WHERE GroupID = ? AND IsActive = 1 ORDER BY FullName ASC");
            $stmtStu->execute([$gId]);
            $g['students'] = $stmtStu->fetchAll(PDO::FETCH_ASSOC);

            // Mevcut Yoklamayı Çek
            $stmtAtt = $this->db->prepare("SELECT StudentID, IsPresent FROM Attendance WHERE GroupID = ? AND [Date] = ?");
            $stmtAtt->execute([$gId, $date]);
            $g['attendance'] = $stmtAtt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // İstatistik
            $g['present_count'] = 0;
            foreach($g['attendance'] as $status) {
                if($status == 1) $g['present_count']++;
            }

            // Grubu filtrelenmiş listeye ekle
            $filteredGroups[] = $g;
        }

        // View'a filtrelenmiş listeyi ($filteredGroups) gönderiyoruz
        $this->render('attendance', [
            'groups' => $filteredGroups,
            'selectedDate' => $date,
            'formattedDate' => $formattedDate, 
            'prevDate' => $prevDate,           
            'nextDate' => $nextDate,           
            'isAdmin' => $isAdmin,
            'openGroupId' => $_GET['open_group'] ?? null 
        ]);
    }

    // --- KAYDETME İŞLEMİ ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                $role = strtolower($_SESSION['role'] ?? '');
                $userId = $_SESSION['user_id'] ?? 0;
                $isAdmin = in_array($role, ['clubadmin', 'admin', 'systemadmin', 'superadmin']);

                $groupId = $_POST['group_id'];
                
                // Tarihi alırken saat dilimi hatası olmasın
                date_default_timezone_set('Europe/Istanbul');
                $date = $isAdmin ? $_POST['date'] : date('Y-m-d');

                if (!$isAdmin) {
                    $check = $this->db->prepare("SELECT COUNT(*) FROM Groups WHERE GroupID = ? AND CoachID = ?");
                    $check->execute([$groupId, $userId]);
                    if ($check->fetchColumn() == 0) {
                        die('<div class="alert alert-danger">Yetkisiz işlem.</div>');
                    }
                }

                $postedStatus = $_POST['status'] ?? []; 
                
                $allStudentsStmt = $this->db->prepare("SELECT StudentID FROM Students WHERE GroupID = ? AND IsActive = 1");
                $allStudentsStmt->execute([$groupId]);
                $allStudents = $allStudentsStmt->fetchAll(PDO::FETCH_COLUMN);

                foreach ($allStudents as $studentId) {
                    $newStatus = isset($postedStatus[$studentId]) ? 1 : 0;
                    
                    $checkStmt = $this->db->prepare("SELECT AttendanceID, IsPresent FROM Attendance WHERE StudentID = ? AND GroupID = ? AND [Date] = ?");
                    $checkStmt->execute([$studentId, $groupId, $date]);
                    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        $oldStatus = (int)$existing['IsPresent'];
                        if ($oldStatus != $newStatus) {
                            if ($oldStatus == 0 && $newStatus == 1) $this->modifySessions($studentId, -1);
                            elseif ($oldStatus == 1 && $newStatus == 0) $this->modifySessions($studentId, +1);
                            
                            $this->db->prepare("UPDATE Attendance SET IsPresent = ? WHERE AttendanceID = ?")->execute([$newStatus, $existing['AttendanceID']]);
                        }
                    } else {
                        $this->db->prepare("INSERT INTO Attendance (ClubID, GroupID, StudentID, [Date], IsPresent, CreatedAt) VALUES (?, ?, ?, ?, ?, GETDATE())")
                                 ->execute([$clubId, $groupId, $studentId, $date, $newStatus]);
                        if ($newStatus == 1) $this->modifySessions($studentId, -1);
                    }
                }

                $this->db->commit();
                $_SESSION['success_message'] = "Yoklama kaydedildi.";
                
                header("Location: index.php?page=attendance&date=$date&open_group=$groupId");
                exit;

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                $_SESSION['error_message'] = "Hata: " . $e->getMessage();
                header("Location: index.php?page=attendance&date=$date");
                exit;
            }
        }
    }

    private function modifySessions($studentId, $amount) {
        try {
            $sql = "UPDATE Students SET RemainingSessions = RemainingSessions + ? WHERE StudentID = ?";
            $this->db->prepare($sql)->execute([$amount, $studentId]);
        } catch (Exception $e) {}
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