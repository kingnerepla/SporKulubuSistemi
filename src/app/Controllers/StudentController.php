<?php

class StudentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Öğrenci Listesi
    public function index() {
        // Eğer SystemAdmin bir kulüp seçmişse onu kullan, yoksa ClubAdmin'in kendi ID'sini kullan
        $clubId = ($role === 'SystemAdmin') ? $_SESSION['selected_club_id'] : $_SESSION['club_id'];
    
        if (!$clubId) {
            die("Erişim Reddedildi: Bir kulüp yetkisine sahip değilsiniz.");
        }
    
        $stmt = $this->db->prepare("SELECT * FROM Students WHERE ClubID = ?");
        $stmt->execute([$clubId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // ... render işlemi
    }

    // YENİ ÖĞRENCİ KAYDETME (POST)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'];
            $parentPhone = $_POST['parent_phone'];
            $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];

            $sql = "INSERT INTO Students (FullName, ParentPhone, ClubID, CreatedAt) VALUES (?, ?, ?, GETDATE())";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute([$fullName, $parentPhone, $clubId])) {
                header("Location: index.php?page=students&status=success");
            } else {
                header("Location: index.php?page=students&status=error");
            }
            exit;
        }
    }

    private function render($viewPath, $data = []) {
        extract($data);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}