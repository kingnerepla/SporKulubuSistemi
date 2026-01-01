<?php

class ClubFinanceController {
    // 1. ÖNEMLİ: db değişkenini private olarak tanımlıyoruz
    private $db;

    public function __construct() {
        // 2. Database sınıfı index.php'de yüklendiği için direkt kullanıyoruz
        // Eğer Database sınıfı bulunamazsa veya bağlantı başarısızsa hata vermesini sağlıyoruz
        try {
            if (class_exists('Database')) {
                $dbInstance = new Database();
                $this->db = $dbInstance->getConnection();
                
                if (!$this->db) {
                    throw new Exception("Veritabanı bağlantısı kurulamadı (Connection Null).");
                }
            } else {
                throw new Exception("Database sınıfı sistemde bulunamadı.");
            }
        } catch (Exception $e) {
            die("Kritik Hata: " . $e->getMessage());
        }
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;

        if (!$clubId) {
            header("Location: index.php?page=dashboard");
            exit;
        }

        try {
            // Artık $this->db dolu olduğu için prepare() hata vermeyecektir
            $query = "SELECT s.StudentID, s.FullName, g.GroupName, s.NextPaymentDate,
                      (SELECT SUM(Amount) FROM Payments WHERE StudentID = s.StudentID) as TotalPaid,
                      (SELECT COUNT(*) FROM Payments WHERE StudentID = s.StudentID) as PaidMonths
                      FROM Students s
                      LEFT JOIN Groups g ON s.GroupID = g.GroupID
                      WHERE s.ClubID = ? AND s.IsActive = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$clubId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $today = strtotime('today');
            $nextWeek = strtotime('+7 days');

            foreach ($students as &$st) {
                // Her öğrencinin son 12 ödeme kaydı
                $histStmt = $this->db->prepare("SELECT TOP 12 Amount, PaymentMonth, PaymentDate 
                                                FROM Payments 
                                                WHERE StudentID = ? 
                                                ORDER BY PaymentDate DESC");
                $histStmt->execute([$st['StudentID']]);
                $st['payment_history'] = $histStmt->fetchAll(PDO::FETCH_ASSOC);
                
                $paymentTime = !empty($st['NextPaymentDate']) ? strtotime($st['NextPaymentDate']) : null;

                $st['is_overdue'] = ($paymentTime !== null && $paymentTime < $today);
                $st['is_upcoming'] = ($paymentTime !== null && $paymentTime >= $today && $paymentTime <= $nextWeek);
            }

            $data = ['students' => $students];
            $this->render('club_finance', $data);

        } catch (Exception $e) {
            die("Kulüp Finans Hatası: " . $e->getMessage());
        }
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        $viewPath = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            die("Görünüm dosyası bulunamadı: $viewPath");
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}