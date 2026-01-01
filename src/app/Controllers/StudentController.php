<?php

class StudentController {
    private $db;

    public function __construct() {
        // Veritabanı bağlantısı
        $this->db = (new Database())->getConnection();
    }

    /**
     * ÖĞRENCİ LİSTESİ
     * Hem Admin hem de Antrenör için gruba göre sıralı liste üretir.
     */
    public function index() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        $userId = $_SESSION['user_id'];
        $clubId = $_SESSION['club_id'];

        try {
            if ($role === 'coach') {
                // Antrenör: Sadece kendi sorumlu olduğu grupların öğrencilerini görür
                $sql = "SELECT s.StudentID, s.FullName, s.BirthDate, s.Notes, s.MonthlyFee, 
                               g.GroupName, u.FullName as ParentName, u.Phone as ParentPhone
                        FROM Students s
                        JOIN Groups g ON s.GroupID = g.GroupID
                        LEFT JOIN Users u ON s.ParentID = u.UserID
                        WHERE g.TrainerID = ? AND s.IsActive = 1
                        ORDER BY g.GroupName ASC, s.FullName ASC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Antrenör özel sayfasına gönder
                $this->render('coach_students', ['students' => $students]);
                
            } else {
                // Admin: Kulüpteki tüm aktif öğrencileri gruba göre sıralı görür
                $sql = "SELECT s.*, g.GroupName, u.FullName as ParentName, u.Phone as ParentPhone
                        FROM Students s 
                        LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                        LEFT JOIN Users u ON s.ParentID = u.UserID
                        WHERE s.ClubID = ? AND s.IsActive = 1
                        ORDER BY CASE WHEN g.GroupName IS NULL THEN 1 ELSE 0 END, g.GroupName ASC, s.FullName ASC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$clubId]);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Admin standart sayfasına gönder
                $this->render('students', ['students' => $students]);
            }
        } catch (Exception $e) {
            die("Sistem Hatası: " . $e->getMessage());
        }
    }

    /**
     * ÖĞRENCİ SİLME (Arşive Gönderme)
     */
    public function delete() {
        $studentId = $_GET['id'] ?? null;
        $clubId = $_SESSION['club_id'];

        if ($studentId) {
            try {
                // Güvenlik: Sadece kendi kulübündeki öğrenciyi silebilir
                $stmt = $this->db->prepare("UPDATE Students SET IsActive = 0 WHERE StudentID = ? AND ClubID = ?");
                $stmt->execute([$studentId, $clubId]);
                
                header("Location: index.php?page=students&status=deleted");
                exit;
            } catch (Exception $e) {
                die("Silme Hatası: " . $e->getMessage());
            }
        }
    }

    /**
     * RENDER YARDIMCISI
     * Belirtilen view dosyasını admin_layout içine gömer.
     */
    private function render($view, $data = []) {
        extract($data);
        ob_start();
        
        // View dosyasının varlığını kontrol et
        $viewFile = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<div class='alert alert-danger'>Hata: Views/admin/{$view}.php dosyası bulunamadı!</div>";
        }
        
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}