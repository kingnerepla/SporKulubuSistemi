<?php
require_once __DIR__ . '/../Config/Database.php';

class PaymentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $role = $_SESSION['role'];
        $clubId = $_SESSION['club_id'];
    
        if ($role === 'SystemAdmin') {
            $stmt = $this->db->query("SELECT Payments.*, Students.FullName, Clubs.ClubName FROM Payments 
                                      JOIN Students ON Payments.StudentID = Students.StudentID
                                      JOIN Clubs ON Students.ClubID = Clubs.ClubID");
        } else {
            $stmt = $this->db->prepare("SELECT Payments.*, Students.FullName FROM Payments 
                                        JOIN Students ON Payments.StudentID = Students.StudentID 
                                        WHERE Students.ClubID = ?");
            $stmt->execute([$clubId]);
        }
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // SENİN LİSTENE GÖRE DOĞRU YOL:
        require_once __DIR__ . '/../Views/admin/payments.php';
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