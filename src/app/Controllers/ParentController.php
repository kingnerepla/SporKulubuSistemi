<?php
// Hata raporlamayı aç
ini_set('display_errors', 1);
error_reporting(E_ALL);

class PaymentController {
    private $db;

    public function __construct() {
        // Veritabanı bağlantısı
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        if (class_exists('Database')) {
            $this->db = (new Database())->getConnection();
        } else {
            die("Veritabanı bağlantı dosyası yüklenemedi.");
        }
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;
        $studentFilter = $_GET['student_id'] ?? null;
        
        // Session mesajlarını al ve sil
        $error = $_SESSION['error_message'] ?? null;
        $success = $_SESSION['success_message'] ?? null;
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        try {
            // 1. Ödemeler
            $sql = "SELECT p.*, s.FullName as StudentName, g.GroupName 
                    FROM Payments p 
                    INNER JOIN Students s ON p.StudentID = s.StudentID 
                    LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                    WHERE p.ClubID = ?";
            if ($studentFilter) { $sql .= " AND p.StudentID = " . (int)$studentFilter; }
            $sql .= " ORDER BY p.PaymentDate DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Kasa
            $totalIncome = 0;
            foreach ($payments as $pay) { $totalIncome += (float)$pay['Amount']; }

            // 3. Öğrenciler
            $stmtSt = $this->db->prepare("SELECT StudentID, FullName, MonthlyFee FROM Students WHERE ClubID = ? AND IsActive = 1");
            $stmtSt->execute([$clubId]);
            $students = $stmtSt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Render'a gönder
            $this->render('payments', [
                'payments' => $payments,
                'totalIncome' => $totalIncome,
                'students' => $students,
                'error' => $error,
                'success' => $success
            ]);

        } catch (Exception $e) {
            die("Veri Hatası: " . $e->getMessage());
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                
                // Collection Tipi ile Kayıt
                $stmt = $this->db->prepare("INSERT INTO Payments 
                    ([StudentID], [Amount], [PaymentType], [Description], [PaymentDate], [PaymentMonth], [ClubID], [Type], [CreatedBy]) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $_POST['student_id'], $_POST['amount'], $_POST['payment_type'], 
                    $_POST['description'] ?? 'Aidat Tahsilatı', $_POST['payment_date'], 
                    $_POST['payment_month'], ($_SESSION['selected_club_id'] ?? $_SESSION['club_id']), 
                    'Collection', ($_SESSION['user_id'] ?? 1)
                ]);

                $this->db->prepare("UPDATE Students SET NextPaymentDate = ? WHERE StudentID = ?")
                         ->execute([$_POST['next_payment_date'], $_POST['student_id']]);

                $this->db->commit();
                $_SESSION['success_message'] = "Tahsilat kaydedildi.";
                header("Location: index.php?page=payments");
                exit();

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                $_SESSION['error_message'] = "Hata: " . $e->getMessage();
                header("Location: index.php?page=payments");
                exit();
            }
        }
    }

    /**
     * MENÜLERİ GETİREN SİHİRLİ FONKSİYON
     */
    private function render($view, $data = []) {
        // Session verilerini görünüm için birleştir (Layout dosyasının ihtiyaç duyduğu veriler)
        if(isset($_SESSION)) {
            $data = array_merge($_SESSION, $data);
        }
        
        extract($data);
        
        // 1. İçeriği Hazırla
        ob_start();
        $baseDir = __DIR__ . '/../';
        
        // KLASÖR ADI TESPİTİ (Views mi views mi?)
        $viewsFolder = is_dir($baseDir . 'Views') ? 'Views' : 'views';
        
        $viewFile = $baseDir . $viewsFolder . "/admin/{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<h1>HATA: İçerik dosyası ($view.php) bulunamadı!</h1>";
        }
        $content = ob_get_clean();
        
        // 2. Layout Dosyasını Yükle (Senin attığın admin_layout.php)
        $layoutPath = $baseDir . $viewsFolder . '/layouts/admin_layout.php';
        
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            // Eğer layout bulunamazsa hatayı ve içeriği bas
            echo "<div style='background:red; color:white; padding:10px; text-align:center;'>";
            echo "Layout Dosyası Bulunamadı: $layoutPath <br>";
            echo "Lütfen klasör adının (Views/views) doğru olduğundan emin olun.";
            echo "</div>";
            echo $content;
        }
    }
}