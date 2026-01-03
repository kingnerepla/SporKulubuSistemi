<?php
class ParentController {
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Güvenlik: Sadece veli girebilir
        if (($_SESSION['role'] ?? '') !== 'parent') {
            header("Location: index.php?page=login");
            exit;
        }

        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    /**
     * VELİ: Ödeme Geçmişi Sayfası
     */
    public function payments() {
        $parentId = $_SESSION['user_id'];

        // 1. Velinin Çocuklarını Bul
        $stmt = $this->db->prepare("SELECT StudentID, FullName, RemainingSessions FROM Students WHERE ParentID = ? AND IsActive = 1");
        $stmt->execute([$parentId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Her Çocuğun Ödemelerini Çek (Senin tablonun sütun isimlerine göre)
        foreach ($students as &$stu) {
            $stmtPay = $this->db->prepare("
                SELECT Amount, PaymentDate, PaymentType, Description, PaymentMonth 
                FROM Payments 
                WHERE StudentID = ? 
                ORDER BY PaymentDate DESC
            ");
            $stmtPay->execute([$stu['StudentID']]);
            $stu['payments'] = $stmtPay->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->render('parent/payments', ['students' => $students]);
    }

    /**
     * VELİ: Yoklama Geçmişi Sayfası
     */
    public function attendance() {
        $parentId = $_SESSION['user_id'];
    
        // 1. Velinin Çocuklarını Bul
        $stmt = $this->db->prepare("SELECT StudentID, FullName FROM Students WHERE ParentID = ? AND IsActive = 1");
        $stmt->execute([$parentId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Verileri toplu bir yapıya sokuyoruz
        foreach ($students as &$stu) {
            $stmtAtt = $this->db->prepare("
                SELECT [Date] as AttendanceDate, IsPresent as Status 
                FROM Attendance 
                WHERE StudentID = ? 
                ORDER BY [Date] DESC
            ");
            $stmtAtt->execute([$stu['StudentID']]);
            $stu['history'] = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);
    
            // İstatistikleri hesapla
            $present = 0;
            foreach($stu['history'] as $h) { if($h['Status'] == 1) $present++; }
            $stu['stats'] = [
                'present' => $present,
                'absent' => count($stu['history']) - $present
            ];
        }
    
        // BURAYA DİKKAT: Dosya ismini attendance_view olarak güncelledim
        $this->render('parent/attendance_view', ['students' => $students]);
    }

    /**
     * DİNAMİK RENDER (Veli Klasörüne Bakar)
     */
    private function render($view, $data = []) {
        if(isset($_SESSION)) $data = array_merge($_SESSION, $data);
        extract($data);
        
        ob_start();
        $baseDir = __DIR__ . '/../';
        $viewsFolder = is_dir($baseDir . 'Views') ? 'Views' : 'views';
        
        // Veli klasöründeki dosyayı yükle
        $viewFile = $baseDir . $viewsFolder . "/{$view}.php";
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<h1>Görünüm Dosyası Bulunamadı: $viewFile</h1>";
        }
        $content = ob_get_clean();
        
        $layoutPath = $baseDir . $viewsFolder . '/layouts/admin_layout.php';
        if (file_exists($layoutPath)) include $layoutPath; else echo $content;
    }
}