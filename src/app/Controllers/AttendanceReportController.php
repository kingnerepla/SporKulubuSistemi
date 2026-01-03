<?php
// Hataları göster
ini_set('display_errors', 1);
error_reporting(E_ALL);

class AttendanceReportController {
    private $db;

    public function __construct() {
        date_default_timezone_set('Europe/Istanbul');
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
        
        $role = strtolower($_SESSION['role'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;
        
        $allowed = ['clubadmin', 'admin', 'systemadmin', 'superadmin', 'coach', 'trainer'];
        if (!in_array($role, $allowed)) die('Erişim yetkiniz yok.');

        if ($role == 'coach' || $role == 'trainer') {
            $stmt = $this->db->prepare("SELECT CanViewReports FROM Users WHERE UserID = ?");
            $stmt->execute([$userId]);
            if (!$stmt->fetchColumn()) {
                echo '<div class="alert alert-danger m-4 text-center">Yetkisiz Erişim</div>'; exit;
            }
        }
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        $role = strtolower($_SESSION['role'] ?? '');
        $userId = $_SESSION['user_id'] ?? 0;
        $isCoach = ($role == 'coach' || $role == 'trainer');
        
        // FİLTRELER
        $selectedYear = (int)($_GET['year'] ?? date('Y'));
        $selectedGroupId = $_GET['group_id'] ?? null;

        // 1. GRUP LİSTESİ (Dropdown İçin)
        $allGroups = [];
        if ($isCoach) {
            $stmtG = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE CoachID = ? AND ClubID = ? ORDER BY GroupName ASC");
            $stmtG->execute([$userId, $clubId]);
            $allGroups = $stmtG->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmtAll = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ? ORDER BY GroupName ASC");
            $stmtAll->execute([$clubId]);
            $allGroups = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
        }

        // 2. HEDEF GRUPLARI BELİRLE
        // Eğer seçim yapıldıysa o grubu, yapılmadıysa LİSTEDEKİ TÜM GRUPLARI al.
        $targetGroups = [];
        if ($selectedGroupId) {
            foreach ($allGroups as $g) {
                if ($g['GroupID'] == $selectedGroupId) {
                    $targetGroups[] = $g;
                    break;
                }
            }
        } else {
            $targetGroups = $allGroups;
        }

        // 3. HER GRUP İÇİN VERİLERİ HAZIRLA
        $finalReports = [];

        foreach ($targetGroups as $group) {
            $gId = $group['GroupID'];

            // a. Öğrenciler
            $stmtS = $this->db->prepare("SELECT StudentID, FullName FROM Students WHERE GroupID = ? AND IsActive = 1 ORDER BY FullName ASC");
            $stmtS->execute([$gId]);
            $students = $stmtS->fetchAll(PDO::FETCH_ASSOC);

            if (empty($students)) continue; // Öğrencisi yoksa raporlama

            // b. Döngü Sayısı (Haftalık Ders x 4)
            $stmtSch = $this->db->prepare("SELECT COUNT(DISTINCT DayOfWeek) FROM GroupSchedule WHERE GroupID = ?");
            $stmtSch->execute([$gId]);
            $weeklyFreq = $stmtSch->fetchColumn();
            if ($weeklyFreq < 1) $weeklyFreq = 1; // En az 1 kabul et
            
            $cycleCount = $weeklyFreq * 4; // Paket Boyutu (4, 8, 12 vs.)

            // c. Yıl içindeki Tarihler
            $stmtDates = $this->db->prepare("SELECT DISTINCT [Date] FROM Attendance WHERE GroupID = ? AND YEAR([Date]) = ? ORDER BY [Date] ASC");
            $stmtDates->execute([$gId, $selectedYear]);
            $allDates = $stmtDates->fetchAll(PDO::FETCH_COLUMN);

            // d. Yoklama Verisi
            $attendanceMatrix = [];
            if (!empty($allDates)) {
                $stmtA = $this->db->prepare("SELECT StudentID, [Date], IsPresent FROM Attendance WHERE GroupID = ? AND YEAR([Date]) = ?");
                $stmtA->execute([$gId, $selectedYear]);
                $rawAtt = $stmtA->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rawAtt as $r) {
                    $attendanceMatrix[$r['StudentID']][$r['Date']] = $r['IsPresent'];
                }
            }

            // e. Tarihleri Parçala (Chunks)
            $dateChunks = array_chunk($allDates, $cycleCount);
            $reportChunks = [];
            foreach ($dateChunks as $idx => $chunk) {
                $reportChunks[] = [
                    'period_no' => $idx + 1,
                    'start' => reset($chunk),
                    'end' => end($chunk),
                    'dates' => $chunk
                ];
            }

            // Bu grubun raporunu ana diziye ekle
            $finalReports[] = [
                'group_info' => $group,
                'students' => $students,
                'cycle_count' => $cycleCount,
                'chunks' => $reportChunks,
                'attendance' => $attendanceMatrix
            ];
        }

        $this->render('attendance_report', [
            'isCoach'        => $isCoach,
            'allGroups'      => $allGroups,
            'selectedGroupId'=> $selectedGroupId,
            'selectedYear'   => $selectedYear,
            'finalReports'   => $finalReports
        ]);
    }

    public function sendMail() { $this->index(); }

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