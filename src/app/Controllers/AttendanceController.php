<?php
require_once __DIR__ . '/../Config/Database.php';

class AttendanceController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // 1. Yoklama Alınacak Grubu Seçme Ekranı
    public function index() {
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?page=login"); exit; }
        
        $clubId = $_SESSION['club_id'];
        $role = $_SESSION['role'];
        $userId = $_SESSION['user_id'];

        // EĞER ANTRENÖRSE SADECE KENDİ GRUPLARINI GETİR
        if ($role === 'Trainer') {
            $stmt = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ? AND TrainerID = ? ORDER BY GroupName ASC");
            $stmt->execute([$clubId, $userId]);
        } 
        // YÖNETİCİLER HEPSİNİ GÖRÜR
        else {
            $stmt = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ? ORDER BY GroupName ASC");
            $stmt->execute([$clubId]);
        }
        
        $groups = $stmt->fetchAll();

        // ... (Kalan kodlar aynı) ...

    // 2. Seçilen Grubun Öğrenci Listesini Getir (Form)
    public function take() {
        $groupId = $_GET['group_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d'); // Tarih seçilmediyse bugün

        if (!$groupId) {
            header("Location: index.php?page=attendance");
            exit;
        }

        // Grup Bilgisi
        $stmt = $this->db->prepare("SELECT * FROM Groups WHERE GroupID = ?");
        $stmt->execute([$groupId]);
        $group = $stmt->fetch();

        // O gruptaki öğrencileri çek
        $stmtStudents = $this->db->prepare("SELECT * FROM Students WHERE GroupID = ? AND IsActive = 1");
        $stmtStudents->execute([$groupId]);
        $students = $stmtStudents->fetchAll();

        // Daha önce o gün için yoklama alınmış mı? (Varsa onları getir)
        $stmtCheck = $this->db->prepare("SELECT StudentID, IsPresent FROM Attendance WHERE GroupID = ? AND Date = ?");
        $stmtCheck->execute([$groupId, $date]);
        $existingRecords = $stmtCheck->fetchAll(PDO::FETCH_KEY_PAIR); // [StudentID => IsPresent] formatında döner

        ob_start();
        require_once __DIR__ . '/../Views/admin/attendance_form.php';
        $content = ob_get_clean();

        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    // 3. Yoklamayı Kaydet
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groupId = $_POST['group_id'];
            $date = $_POST['date'];
            $attendanceData = $_POST['attendance'] ?? []; // İşaretlenenler (Sadece 'Geldi' olanlar gelir)

            // Önce o günün eski kaydını temizle (Güncelleme mantığı yerine Sil-Yükle yapıyoruz, daha basit)
            $delStmt = $this->db->prepare("DELETE FROM Attendance WHERE GroupID = ? AND Date = ?");
            $delStmt->execute([$groupId, $date]);

            // Yeni kayıtları ekle
            $insStmt = $this->db->prepare("INSERT INTO Attendance (GroupID, StudentID, Date, IsPresent) VALUES (?, ?, ?, ?)");

            // Formdan gelen tüm öğrencileri döngüye sokmalıyız ama $_POST['attendance'] sadece checkbox işaretli olanları gönderir.
            // Bu yüzden önce o grubun TÜM öğrencilerini bilmeliyiz.
            $allStudents = $this->db->prepare("SELECT StudentID FROM Students WHERE GroupID = ?");
            $allStudents->execute([$groupId]);
            
            foreach ($allStudents->fetchAll() as $student) {
                $studentId = $student['StudentID'];
                // Eğer checkbox işaretliyse 1, değilse 0
                $isPresent = isset($attendanceData[$studentId]) ? 1 : 0;
                $insStmt->execute([$groupId, $studentId, $date, $isPresent]);
            }

            header("Location: index.php?page=attendance&success=1");
        }
    }
}