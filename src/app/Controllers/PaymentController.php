<?php
require_once __DIR__ . '/../Config/Database.php';

class PaymentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $clubId = $_SESSION['club_id'] ?? null;
        $role = $_SESSION['role'] ?? '';

        if (!$clubId && $role !== 'SystemAdmin') { header("Location: index.php?page=login"); exit; }

        // Ödemeleri Listele
        $sql = "SELECT Payments.*, Students.FullName as StudentName, Groups.GroupName 
                FROM Payments 
                INNER JOIN Students ON Payments.StudentID = Students.StudentID
                INNER JOIN Groups ON Students.GroupID = Groups.GroupID
                WHERE Groups.ClubID = ?
                ORDER BY Payments.PaymentDate DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $payments = $stmt->fetchAll();

        // Öğrenci Listesi (Dropdown için)
        $stmtStudents = $this->db->prepare("
            SELECT Students.StudentID, Students.FullName, Groups.GroupName 
            FROM Students 
            INNER JOIN Groups ON Students.GroupID = Groups.GroupID
            WHERE Groups.ClubID = ? AND Students.IsActive = 1
            ORDER BY Students.FullName ASC
        ");
        $stmtStudents->execute([$clubId]);
        $students = $stmtStudents->fetchAll();

        // Toplam Kasa
        $totalIncome = 0;
        foreach($payments as $p) { $totalIncome += $p['Amount']; }

        ob_start();
        require_once __DIR__ . '/../Views/admin/payments.php';
        $content = ob_get_clean();

        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'];
            $amount = $_POST['amount'];
            $paymentType = $_POST['payment_type'] ?? 'Nakit';
            $description = $_POST['description'];
            $date = $_POST['payment_date'] ?: date('Y-m-d');
            
            // YENİ EKLENEN: Ödeme Dönemi (Ay/Yıl)
            $paymentMonth = $_POST['payment_month']; // Formdan gelen veri (örn: 2025-10)

            // Validasyon
            if (empty($studentId) || empty($amount) || empty($paymentMonth)) {
                header("Location: index.php?page=payments&error=empty_fields");
                exit;
            }

            // HATA ÇÖZÜMÜ: PaymentMonth alanını SQL'e ekledik
            $stmt = $this->db->prepare("
                INSERT INTO Payments (StudentID, Amount, PaymentType, Description, PaymentDate, PaymentMonth) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            // execute dizisine de değişkeni ekledik
            $stmt->execute([$studentId, $amount, $paymentType, $description, $date, $paymentMonth]);

            header("Location: index.php?page=payments&success=created");
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'];
        // Güvenlik: Normalde burada bu ödemenin kulübe ait olup olmadığı kontrol edilmeli.
        $stmt = $this->db->prepare("DELETE FROM Payments WHERE PaymentID = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php?page=payments&success=deleted");
    }
}