<?php
require_once __DIR__ . '/../Config/Database.php';

class PaymentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;
        // Filtreleme: Belirli bir öğrencinin geçmişine bakılıyorsa
        $studentFilter = $_GET['student_id'] ?? null;

        try {
            // 1. Ödemeler Listesi (Filtreye göre)
            $sql = "SELECT p.*, s.FullName as StudentName, g.GroupName 
                    FROM Payments p 
                    JOIN Students s ON p.StudentID = s.StudentID 
                    LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                    WHERE s.ClubID = ?";
            
            if ($studentFilter) { $sql .= " AND p.StudentID = " . (int)$studentFilter; }
            $sql .= " ORDER BY p.PaymentDate DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
            $data['payments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Kasa Toplamı
            $data['totalIncome'] = array_sum(array_column($data['payments'], 'Amount'));

            // 3. Öğrenci Listesi (Modal için)
            $stmtSt = $this->db->prepare("SELECT StudentID, FullName, (SELECT GroupName FROM Groups WHERE GroupID = Students.GroupID) as GroupName FROM Students WHERE ClubID = ? AND IsActive = 1");
            $stmtSt->execute([$clubId]);
            $data['students'] = $stmtSt->fetchAll(PDO::FETCH_ASSOC);

            extract($data);
            require_once __DIR__ . '/../Views/admin/payments.php';

        } catch (Exception $e) { die("Hata: " . $e->getMessage()); }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
    
                $studentId = $_POST['student_id'];
                $amount = $_POST['amount'];
                $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
                $paymentMonth = $_POST['payment_month']; // Örn: 2026-01 (Aidat Ayı)
                $nextVade = $_POST['next_payment_date']; // Örn: 2026-02-01 (Gelecek Vade)
    
                // 1. Kulüp Kasasına Kayıt (Hangi ayın aidatı olduğuyla birlikte)
                $stmt = $this->db->prepare("INSERT INTO Payments 
                    (StudentID, ClubID, Amount, PaymentMonth, PaymentDate, PaymentType, Description) 
                    VALUES (?, ?, ?, ?, GETDATE(), ?, ?)");
                $stmt->execute([
                    $studentId, 
                    $clubId, 
                    $amount, 
                    $paymentMonth, 
                    $_POST['payment_type'] ?? 'Nakit',
                    $_POST['description'] ?? ''
                ]);
    
                // 2. Öğrencinin Vadesini Bir Sonraki Aya Ötele
                $stmtUpdate = $this->db->prepare("UPDATE Students SET NextPaymentDate = ? WHERE StudentID = ?");
                $stmtUpdate->execute([$nextVade, $studentId]);
    
                $this->db->commit();
                
                // Dashboard'a atmaması için tam yönlendirme
                header("Location: index.php?page=payments&success=1");
                exit();
    
            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Kulüp Tahsilat Hatası: " . $e->getMessage());
            }
        }
    }
}