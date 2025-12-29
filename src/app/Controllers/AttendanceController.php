<?php

class AttendanceController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $sessionId = $_GET['session_id'] ?? null;
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? 'coach';
        $today = date('Y-m-d');
    
        // DURUM 1: Spesifik bir ders seçilerek gelindi
        if ($sessionId) {
            $sqlSession = "SELECT ts.StartTime, ts.SessionID, g.GroupName, g.GroupID, g.ClubID 
                           FROM TrainingSessions ts 
                           JOIN Groups g ON ts.GroupID = g.GroupID 
                           WHERE ts.SessionID = ?";
            $stmt = $this->db->prepare($sqlSession);
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$session) die("Hata: Ders kaydı bulunamadı.");

            // GÜVENLİK KONTROLÜ: Antrenör geçmiş günü düzenleyebilir mi?
            // StartTime kolonundan tarihi çekiyoruz (Eğer Datetime ise)
            $sessionDate = date('Y-m-d', strtotime($session['StartTime']));
            
            // Sadece bugünse veya kullanıcı Admin ise düzenlenebilir
            $isEditable = ($sessionDate === $today || $role !== 'coach');
    
            $sqlStudents = "SELECT s.StudentID, s.FullName, 
                            (SELECT Status FROM Attendance WHERE StudentID = s.StudentID AND AttendanceDate = ? AND GroupID = s.GroupID) as CurrentStatus
                            FROM Students s 
                            WHERE s.GroupID = ? AND s.IsActive = 1 
                            ORDER BY s.FullName ASC";
            
            $stmtStd = $this->db->prepare($sqlStudents);
            $stmtStd->execute([$sessionDate, $session['GroupID']]);
            $students = $stmtStd->fetchAll(PDO::FETCH_ASSOC);
    
            $this->render('attendance_take', [
                'session' => $session,
                'students' => $students,
                'date' => $sessionDate,
                'sessionId' => $sessionId,
                'isEditable' => $isEditable // View tarafında inputları disable yapmak için
            ]);
        } 
        // DURUM 2: Menüden tıklandı, bugünün derslerini listele
        else {
            $sqlToday = "SELECT ts.SessionID, ts.StartTime, g.GroupName 
                         FROM TrainingSessions ts 
                         JOIN Groups g ON ts.GroupID = g.GroupID 
                         WHERE g.TrainerID = ?
                         ORDER BY ts.StartTime ASC";
            
            $stmt = $this->db->prepare($sqlToday);
            $stmt->execute([$userId]);
            $todaysSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (count($todaysSessions) === 1) {
                header("Location: index.php?page=attendance&session_id=" . $todaysSessions[0]['SessionID']);
                exit;
            }
    
            $this->render('attendance_list', [
                'sessions' => $todaysSessions,
                'date' => $today
            ]);
        }
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groupId = $_POST['group_id'];
            $clubId = $_POST['club_id'];
            $date = $_POST['date']; // Formdan gelen tarih
            $statusData = $_POST['status'] ?? []; 
            $role = $_SESSION['role'] ?? 'coach';
            $today = date('Y-m-d');

            // --- GÜVENLİK: GEÇMİŞ TARİH KİLİDİ ---
            if ($role === 'coach' && $date < $today) {
                die("Yetki Hatası: Geçmiş tarihli yoklamalar üzerinde değişiklik yapamazsınız. Lütfen yöneticinize danışın.");
            }

            try {
                $this->db->beginTransaction();

                // Mevcut yoklamayı temizle
                $del = $this->db->prepare("DELETE FROM Attendance WHERE GroupID = ? AND AttendanceDate = ?");
                $del->execute([$groupId, $date]);

                // Yeni yoklamayı ekle
                $ins = $this->db->prepare("INSERT INTO Attendance (StudentID, GroupID, ClubID, AttendanceDate, Status, CreatedBy) VALUES (?, ?, ?, ?, ?, ?)");

                $stmtAll = $this->db->prepare("SELECT StudentID FROM Students WHERE GroupID = ? AND IsActive = 1");
                $stmtAll->execute([$groupId]);
                $allStudents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

                foreach ($allStudents as $std) {
                    $status = isset($statusData[$std['StudentID']]) ? 1 : 0;
                    $ins->execute([
                        $std['StudentID'], 
                        $groupId, 
                        $clubId, 
                        $date, 
                        $status, 
                        $_SESSION['user_id']
                    ]);
                }

                $this->db->commit();
                header("Location: index.php?page=dashboard&success=1");
            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Kayıt Hatası: " . $e->getMessage());
            }
        }
    }
    public function report() {
        $db = (new Database())->getConnection();
        $clubId = $_SESSION['club_id'];
        
        // Filtreleme parametreleri
        $groupId = $_GET['group_id'] ?? null;
        $monthYear = $_GET['month'] ?? date('Y-m'); // Varsayılan cari ay
        
        $parts = explode('-', $monthYear);
        $year = $parts[0];
        $month = $parts[1];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
        // Grupları getir (Filtre menüsü için)
        $stmtG = $db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ?");
        $stmtG->execute([$clubId]);
        $groups = $stmtG->fetchAll(PDO::FETCH_ASSOC);
    
        $reportData = [];
        if ($groupId) {
            // Yoklama verilerini çek
            $sql = "SELECT s.FullName, DAY(a.AttendanceDate) as DayNum, a.Status
                    FROM Students s
                    LEFT JOIN Attendance a ON s.StudentID = a.StudentID 
                    AND MONTH(a.AttendanceDate) = ? 
                    AND YEAR(a.AttendanceDate) = ?
                    WHERE s.GroupID = ? AND s.IsActive = 1
                    ORDER BY s.FullName, a.AttendanceDate";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$month, $year, $groupId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Veriyi sporcu bazlı grupla
            foreach ($results as $row) {
                $reportData[$row['FullName']][$row['DayNum']] = $row['Status'];
            }
        }
    
        $this->render('attendance_report', [
            'groups' => $groups,
            'reportData' => $reportData,
            'daysInMonth' => $daysInMonth,
            'selectedGroup' => $groupId,
            'selectedMonth' => $monthYear
        ]);
    }
    private function render($view, $data = []) {
        extract($data);
        ob_start();
        $filePath = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($filePath)) {
            include $filePath;
        } else {
            echo "Görünüm dosyası bulunamadı: " . $view;
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}