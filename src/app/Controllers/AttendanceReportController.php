<?php
class AttendanceReportController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        
        // --- GÜVENLİK VE YETKİ KONTROLÜ ---
        $role = strtolower($_SESSION['role'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;
        
        // 1. Kimler girebilir? (Yönetici, Admin veya Antrenör)
        $allowed = ['clubadmin', 'admin', 'systemadmin', 'superadmin', 'coach', 'trainer'];
        if (!in_array($role, $allowed)) {
            die('Erişim yetkiniz yok.');
        }

        // 2. Eğer Antrenörse, "Rapor Görme Yetkisi" var mı?
        if ($role == 'coach' || $role == 'trainer') {
            // Veritabanından yetkisini taze çekelim (Session eski kalmış olabilir)
            $stmt = $this->db->prepare("SELECT CanViewReports FROM Users WHERE UserID = ?");
            $stmt->execute([$userId]);
            $canView = $stmt->fetchColumn();

            if (!$canView) {
                die('<div class="alert alert-danger m-5">Raporları görüntüleme yetkiniz bulunmuyor. Lütfen yöneticiyle görüşün.</div>');
            }
        }
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        
        // FİLTRELER
        $selectedGroupId = $_GET['group_id'] ?? null;
        $selectedMonth   = $_GET['month'] ?? date('m');
        $selectedYear    = $_GET['year'] ?? date('Y');

        // 1. GRUPLARI ÇEK (Dropdown için)
        // Eğer antrenörse SADECE kendi gruplarını görsün
        $role = strtolower($_SESSION['role'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;

        if ($role == 'coach' || $role == 'trainer') {
            $grpSql = "SELECT GroupID, GroupName FROM Groups WHERE ClubID = ? AND CoachID = ? ORDER BY GroupName ASC";
            $stmtGrp = $this->db->prepare($grpSql);
            $stmtGrp->execute([$clubId, $userId]);
        } else {
            // Yönetici hepsini görür
            $grpSql = "SELECT GroupID, GroupName FROM Groups WHERE ClubID = ? ORDER BY GroupName ASC";
            $stmtGrp = $this->db->prepare($grpSql);
            $stmtGrp->execute([$clubId]);
        }
        $groups = $stmtGrp->fetchAll(PDO::FETCH_ASSOC);

        // Eğer grup seçilmediyse ve gruplar varsa, ilkinin verisini getir
        if (!$selectedGroupId && !empty($groups)) {
            $selectedGroupId = $groups[0]['GroupID'];
        }

        $students = [];
        $attendanceData = [];
        $lessonDays = [];

        if ($selectedGroupId) {
            // 2. ÖĞRENCİLERİ ÇEK
            $stuSql = "SELECT StudentID, FullName FROM Students WHERE GroupID = ? AND IsActive = 1 ORDER BY FullName ASC";
            $stmtStu = $this->db->prepare($stuSql);
            $stmtStu->execute([$selectedGroupId]);
            $students = $stmtStu->fetchAll(PDO::FETCH_ASSOC);

            // 3. YOKLAMA VERİLERİNİ ÇEK (Matris için)
            // Sadece seçilen ay ve yıldaki veriler
            $attSql = "SELECT StudentID, DAY([Date]) as DayNum, IsPresent 
                       FROM Attendance 
                       WHERE GroupID = ? AND MONTH([Date]) = ? AND YEAR([Date]) = ?";
            $stmtAtt = $this->db->prepare($attSql);
            $stmtAtt->execute([$selectedGroupId, $selectedMonth, $selectedYear]);
            $rawAttendance = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);

            // Veriyi kolay kullanım için düzenle: $attendanceData[StudentID][Gün] = Durum
            foreach($rawAttendance as $row) {
                $attendanceData[$row['StudentID']][$row['DayNum']] = $row['IsPresent'];
                
                // Hangi günlerde ders yapılmış? (Takvimi boyamak için)
                $lessonDays[$row['DayNum']] = true; 
            }
        }

        $this->render('attendance_report', [
            'groups'         => $groups,
            'students'       => $students,
            'attendanceData' => $attendanceData,
            'lessonDays'     => $lessonDays,
            'selectedGroupId'=> $selectedGroupId,
            'selectedMonth'  => $selectedMonth,
            'selectedYear'   => $selectedYear
        ]);
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