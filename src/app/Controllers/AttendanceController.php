<?php

class AttendanceController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Yoklama Ana Sayfası (Grup Seçimi ve Tarih)
    public function index() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        $groupId = $_GET['group_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');

        // Kulübün gruplarını getir
        $stmtGroups = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmtGroups->execute([$clubId]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        if ($groupId) {
            // Seçili gruptaki öğrencileri ve o tarihteki mevcut yoklama durumunu getir
            $sql = "SELECT s.StudentID, s.FullName, 
                    (SELECT Status FROM Attendance WHERE StudentID = s.StudentID AND AttendanceDate = ?) as AttendanceStatus
                    FROM Students s 
                    WHERE s.GroupID = ? AND s.IsActive = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$date, $groupId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->render('attendance_take', [
            'groups' => $groups, 
            'students' => $students, 
            'selectedGroup' => $groupId,
            'selectedDate' => $date
        ]);
    }

    // Yoklamayı Kaydetme
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
            $groupId = $_POST['group_id'];
            $date = $_POST['date'];
            $attendanceData = $_POST['status'] ?? []; // Gelen öğrenciler [student_id => status]

            try {
                $this->db->beginTransaction();

                // Önce o günün o grup için eski kayıtlarını temizle (Update yerine Re-insert mantığı daha pratiktir)
                $del = $this->db->prepare("DELETE FROM Attendance WHERE GroupID = ? AND AttendanceDate = ?");
                $del->execute([$groupId, $date]);

                // Yeni yoklamayı ekle
                $ins = $this->db->prepare("INSERT INTO Attendance (StudentID, GroupID, ClubID, AttendanceDate, Status, CreatedBy) VALUES (?, ?, ?, ?, ?, ?)");
                
                // Gruptaki tüm aktif öğrencileri al
                $stmtAll = $this->db->prepare("SELECT StudentID FROM Students WHERE GroupID = ? AND IsActive = 1");
                $stmtAll->execute([$groupId]);
                $allStudents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

                foreach ($allStudents as $std) {
                    $status = isset($attendanceData[$std['StudentID']]) ? 1 : 0;
                    $ins->execute([$std['StudentID'], $groupId, $clubId, $date, $status, $_SESSION['user_id']]);
                }

                $this->db->commit();
                header("Location: index.php?page=attendance&group_id=$groupId&date=$date&success=1");
            } catch (Exception $e) {
                $this->db->rollBack();
                die("Yoklama Hatası: " . $e->getMessage());
            }
        }
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}