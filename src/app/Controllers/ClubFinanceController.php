<?php

class ClubFinanceController {
    private $db;

    public function __construct() {
        try {
            if (file_exists(__DIR__ . '/../Config/Database.php')) {
                require_once __DIR__ . '/../Config/Database.php';
            }
            
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
            // Öğrenci verilerini, toplam ödemelerini ve bir sonraki ödeme tarihlerini çekiyoruz
            $query = "SELECT s.StudentID, s.FullName, g.GroupName, s.NextPaymentDate,
                      (SELECT SUM(Amount) FROM Payments WHERE StudentID = s.StudentID) as TotalPaid,
                      (SELECT COUNT(*) FROM Payments WHERE StudentID = s.StudentID) as PaidMonths
                      FROM Students s
                      LEFT JOIN Groups g ON s.GroupID = g.GroupID
                      WHERE s.ClubID = ? AND s.IsActive = 1
                      ORDER BY s.NextPaymentDate ASC"; // Tarihi yaklaşan en üstte
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$clubId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $today = strtotime('today');
            $nextWeek = strtotime('+7 days');

            // Her öğrenci için işlem yap
            foreach ($students as &$st) {
                // 1. Son 12 Ödeme Geçmişini Çek (SQL Server için TOP 12)
                $histStmt = $this->db->prepare("SELECT TOP 12 Amount, PaymentMonth, PaymentDate 
                                                FROM Payments 
                                                WHERE StudentID = ? 
                                                ORDER BY PaymentDate DESC");
                $histStmt->execute([$st['StudentID']]);
                $st['payment_history'] = $histStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // 2. Durum Belirle (Gecikmiş mi? Yaklaşıyor mu?)
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
        if(isset($_SESSION)) $data = array_merge($_SESSION, $data);
        extract($data);
        ob_start();
        
        $baseDir = __DIR__ . '/../';
        $viewsFolder = is_dir($baseDir . 'Views') ? 'Views' : 'views';
        $viewPath = $baseDir . $viewsFolder . "/admin/{$view}.php";

        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            die("Görünüm dosyası bulunamadı: $viewPath");
        }
        
        $content = ob_get_clean();
        $layoutPath = $baseDir . $viewsFolder . '/layouts/admin_layout.php';
        
        if (file_exists($layoutPath)) include $layoutPath; else echo $content;
    }
}