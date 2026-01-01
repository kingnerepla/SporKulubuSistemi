<?php
// 1. ADIM: Hataları Zorla Göster (Beyaz ekranı engeller)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class PaymentController {
    private $db;

    public function __construct() {
        // Database bağlantısı kontrolü
        if (!class_exists('Database')) {
            die("<h3 style='color:red'>HATA: Database sınıfı yüklenemedi. index.php yapılandırmasını kontrol edin.</h3>");
        }
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;
        $studentFilter = $_GET['student_id'] ?? null;

        // Session mesajlarını al
        $errorMessage = $_SESSION['error_message'] ?? null;
        $successMessage = $_SESSION['success_message'] ?? null;
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        try {
            // Veri Çekme İşlemleri
            $sql = "SELECT p.*, s.FullName as StudentName, g.GroupName 
                    FROM Payments p 
                    INNER JOIN Students s ON p.StudentID = s.StudentID 
                    LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                    WHERE p.ClubID = ?";
            
            if ($studentFilter) { 
                $sql .= " AND p.StudentID = " . (int)$studentFilter; 
            }
            $sql .= " ORDER BY p.PaymentDate DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalIncome = 0;
            foreach ($payments as $pay) { $totalIncome += (float)$pay['Amount']; }

            $stmtSt = $this->db->prepare("SELECT StudentID, FullName, MonthlyFee FROM Students WHERE ClubID = ? AND IsActive = 1");
            $stmtSt->execute([$clubId]);
            $students = $stmtSt->fetchAll(PDO::FETCH_ASSOC);

            // Verileri Render Metoduna Gönder
            $this->render('payments', [
                'payments' => $payments,
                'totalIncome' => $totalIncome,
                'students' => $students,
                'error' => $errorMessage,
                'success' => $successMessage
            ]);

        } catch (Exception $e) {
            die("<h3 style='color:red'>SQL Hatası: " . $e->getMessage() . "</h3>");
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $studentId = $_POST['student_id'];
                $amount = $_POST['amount'];
                $nextDate = $_POST['next_payment_date']; 
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                $createdBy = $_SESSION['user_id'] ?? 1;

                $stmt = $this->db->prepare("INSERT INTO Payments 
                    ([StudentID], [Amount], [PaymentType], [Description], [PaymentDate], [PaymentMonth], [ClubID], [Type], [CreatedBy]) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $studentId, $amount, $_POST['payment_type'], 
                    $_POST['description'] ?? 'Aidat Tahsilatı', 
                    $_POST['payment_date'], $_POST['payment_month'], 
                    $clubId, 'Collection', $createdBy
                ]);

                $stmtUpdate = $this->db->prepare("UPDATE Students SET NextPaymentDate = ? WHERE StudentID = ?");
                $stmtUpdate->execute([$nextDate, $studentId]);

                $this->db->commit();
                
                $_SESSION['success_message'] = "İşlem Başarılı";
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
     * BEYAZ EKRAN SORUNUNU ÇÖZEN RENDER METODU
     * Dosya yollarını ekrana yazdırarak debug eder.
     */
    private function render($view, $data = []) {
        extract($data);
        
        // 1. View Klasörünü Tespit Et (Büyük/Küçük Harf Duyarlı)
        // Linux sunucularda 'Views' ile 'views' farklıdır.
        $baseDir = __DIR__ . '/../';
        $viewFolder = is_dir($baseDir . 'Views') ? 'Views' : 'views';
        
        // 2. İçerik Dosyasını Bul
        $viewPath = $baseDir . $viewFolder . "/admin/{$view}.php";
        
        if (!file_exists($viewPath)) {
            // HATA VARSA EKRANA BASIYORUZ
            echo "<div style='background:white; color:red; padding:20px; border:2px solid red;'>";
            echo "<h1>DOSYA BULUNAMADI!</h1>";
            echo "<p>Sistem şu dosyayı aradı ama bulamadı:</p>";
            echo "<code>" . realpath($baseDir) . "/$viewFolder/admin/$view.php</code><br><br>";
            echo "<strong>Kontrol Edin:</strong><br>";
            echo "1. Klasör adınız <b>Views</b> mi yoksa <b>views</b> mi?<br>";
            echo "2. Dosya adınız <b>payments.php</b> mi?<br>";
            echo "</div>";
            exit;
        }

        // 3. Layout Dosyasını Bul ve Yükle
        $layoutPath = $baseDir . $viewFolder . '/layouts/admin_layout.php';
        
        if (!file_exists($layoutPath)) {
             // Layout yoksa standart layout dene
             $layoutPath = $baseDir . $viewFolder . '/layouts/layout.php';
        }

        // Yükleme İşlemi
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            // Layout hiç bulunamazsa sadece içeriği ve uyarıyı bas
            echo "<div style='background:orange; padding:10px; text-align:center;'>UYARI: Menü dosyası (admin_layout.php) bulunamadı. İçerik yalın yükleniyor.</div>";
            echo $content;
        }
    }
}