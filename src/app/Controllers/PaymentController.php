<?php
class PaymentController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // --- TAHSİLAT KAYDET (KONTÖR YÜKLEME) ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                
                $studentId = $_POST['student_id'];
                $amount = $_POST['amount'];
                $paymentDate = $_POST['payment_date'];
                
                // KRİTİK NOKTA: Kaç ders yüklenecek?
                // Formdan geliyorsa onu al, yoksa öğrencinin standart paketini (8, 12 vs) bulup ekle.
                $sessionsToAdd = $_POST['sessions_to_add'] ?? 0;

                if ($sessionsToAdd == 0) {
                    // Güvenlik: Eğer formdan 0 geldiyse, veritabanındaki paketi çek
                    $stmtSt = $this->db->prepare("SELECT StandardSessions FROM Students WHERE StudentID = ?");
                    $stmtSt->execute([$studentId]);
                    $stInfo = $stmtSt->fetch(PDO::FETCH_ASSOC);
                    $sessionsToAdd = $stInfo['StandardSessions'] ?? 8; // Varsayılan 8
                }
                
                $description = $_POST['description'];
                $method = $_POST['method'];

                // 1. Ödemeyi Kaydet (Muhasebe için)
                $sql = "INSERT INTO Payments (ClubID, StudentID, Amount, PaymentDate, PaymentType, Method, Description, CreatedAt) 
                        VALUES (?, ?, ?, ?, 'CourseFee', ?, ?, GETDATE())";
                $this->db->prepare($sql)->execute([$clubId, $studentId, $amount, $paymentDate, $method, $description]);

                // 2. KONTÖR YÜKLE (RemainingSessions Artır)
                // Mantık: Mevcut Kalan + Yeni Yüklenen
                $updSql = "UPDATE Students SET RemainingSessions = RemainingSessions + ?, LastPaymentDate = GETDATE() WHERE StudentID = ?";
                $this->db->prepare($updSql)->execute([$sessionsToAdd, $studentId]);

                $this->db->commit();
                
                $_SESSION['success_message'] = "Tahsilat yapıldı. Öğrenciye +{$sessionsToAdd} ders hakkı yüklendi.";
                header("Location: index.php?page=club_finance"); 
                exit();

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Hata: " . $e->getMessage());
            }
        }
    }
}