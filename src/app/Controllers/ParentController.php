<?php
class ParentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function dashboard() {
        // 1. Oturum Kontrolü
        if (!isset($_SESSION['parent_logged_in']) || !isset($_SESSION['student_id'])) {
            header("Location: index.php?page=parent_login");
            exit;
        }
    
        // HATA 1 ÇÖZÜMÜ: student_id'yi session'dan alıp değişkene tanımlıyoruz
        $studentId = $_SESSION['student_id'];
    
        // 1. Öğrenci ve Grup Bilgileri
        $stmt = $this->db->prepare("
            SELECT s.*, g.GroupName 
            FROM Students s 
            LEFT JOIN Groups g ON s.GroupID = g.GroupID 
            WHERE s.StudentID = ?
        ");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // 2. Ödeme Özeti
        $stmtPay = $this->db->prepare("SELECT SUM(Amount) as total FROM Payments WHERE StudentID = ?");
        $stmtPay->execute([$studentId]);
        $totalPaid = $stmtPay->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
        // HATA 2 ÇÖZÜMÜ: SQL Server için COUNT field hatasını gidermek adına SUM/CASE yapısı
        // Status = 1 ise 1 ekle, değilse 0 ekle mantığı T-SQL'de daha kararlıdır.
        $stmtAtt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN Status = 1 THEN 1 ELSE 0 END) as attended,
                SUM(CASE WHEN Status = 0 THEN 1 ELSE 0 END) as missed
            FROM Attendance WHERE StudentID = ?
        ");
        $stmtAtt->execute([$studentId]);
        $stats = $stmtAtt->fetch(PDO::FETCH_ASSOC);
    
        // Boş gelme ihtimaline karşı varsayılan değerler
        $stats['attended'] = $stats['attended'] ?? 0;
        $stats['missed'] = $stats['missed'] ?? 0;
    
        // 4. Son 10 Yoklama Kaydı
        $stmtHistory = $this->db->prepare("
            SELECT AttendanceDate, Status 
            FROM Attendance 
            WHERE StudentID = ? 
            ORDER BY AttendanceDate DESC
        ");
        $stmtHistory->execute([$studentId]);
        $attendanceHistory = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
    
        // View'a gönder
        include __DIR__ . '/../Views/parent/parent_dashboard.php';
    }
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $phone = trim($_POST['phone']);
            $password = trim($_POST['password']);
    
            // Önemli: [Password] şeklinde köşeli parantez kullanıyoruz
            $stmt = $this->db->prepare("SELECT StudentID, FullName, [Password] FROM Students WHERE ParentPhone = ? AND IsActive = 1");
            $stmt->execute([$phone]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Şifre kontrolü (Eğer hash kullanmıyorsak direkt karşılaştırma)
            if ($student && $student['Password'] == $password) {
                $_SESSION['parent_logged_in'] = true;
                $_SESSION['student_id'] = $student['StudentID'];
                $_SESSION['student_name'] = $student['FullName'];
                header("Location: index.php?page=parent_dashboard");
                exit;
            } else {
                header("Location: index.php?page=parent_login&error=1");
                exit;
            }
        }
    }
    public function loginPage() {
        $path = __DIR__ . '/../Views/parent/login.php';
        if (!file_exists($path)) {
            $path = __DIR__ . '/../../src/app/Views/parent/login.php';
        }
        include $path;
    }
    
    public function logout() {
        unset($_SESSION['parent_logged_in']);
        unset($_SESSION['student_id']);
        header("Location: index.php?page=parent_login");
    }
    private function render($view, $data = []) {
        extract($data);
        include __DIR__ . "/../Views/parent/{$view}.php";
    }
}