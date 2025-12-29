<?php

class StudentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // app/Controllers/StudentController.php (İlgili kısım)

    public function index() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        $userId = $_SESSION['user_id'];
        $clubId = $_SESSION['club_id'];

        if ($role === 'coach') {
            // 'Notes' kolonu tabloda olmadığı için sorgudan çıkarıldı
            $sql = "SELECT s.StudentID, s.FullName, s.BirthDate, g.GroupName
                    FROM Students s
                    JOIN Groups g ON s.GroupID = g.GroupID
                    WHERE g.TrainerID = ? AND s.IsActive = 1
                    ORDER BY g.GroupName, s.FullName";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
        } else {
            // Admin sorgusunda s.* kullandığımız için eğer Notes yoksa hata vermez 
            // ama diğer kolon isimlerinin doğruluğundan emin olmalısınız.
            $sql = "SELECT s.*, g.GroupName 
                    FROM Students s 
                    LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                    WHERE s.ClubID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
        }

        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $view = ($role === 'coach') ? 'coach_students' : 'admin_students';
        $this->render($view, ['students' => $students]);
    }

    /**
     * ÖĞRENCİ DÜZENLEME FORMU
     * Antrenörün bu sayfaya erişimi engellenmiştir.
     */
    public function edit() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        
        // GÜVENLİK KONTROLÜ: Antrenör düzenleme sayfasına giremez.
        if ($role === 'coach') {
            header("Location: index.php?page=dashboard&error=unauthorized");
            exit;
        }

        $id = $_GET['id'] ?? null;
        $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);
    
        $sql = "SELECT s.*, u.FullName as ParentName, u.Email as ParentPhone 
                FROM Students s 
                LEFT JOIN Users u ON s.ParentID = u.UserID 
                WHERE s.StudentID = ? AND s.ClubID = ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $clubId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$student) {
            header("Location: index.php?page=students&error=notfound");
            exit;
        }
    
        $stmtG = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmtG->execute([$clubId]);
        $groups = $stmtG->fetchAll(PDO::FETCH_ASSOC);
    
        $this->render('student_edit', ['student' => $student, 'groups' => $groups]);
    }

    /**
     * GÜNCELLEME İŞLEMİ
     * Sadece yönetici yetkisi olanlar veri kaydedebilir.
     */
    public function update() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));

        // GÜVENLİK KONTROLÜ: Antrenör veri güncelleyemez.
        if ($role === 'coach') {
            die("Yetki Hatası: Öğrenci bilgilerini değiştirme yetkiniz bulunmamaktadır.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'] ?? null;
            $parentId  = $_POST['parent_id'] ?? null;
            $name      = $_POST['student_name'] ?? '';
            $parentName = $_POST['parent_name'] ?? '';
            $phone     = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? ''); 
            $monthlyFee = $_POST['monthly_fee'] ?? 0;
            $groupId   = !empty($_POST['group_id']) ? $_POST['group_id'] : null;
    
            try {
                if (!$studentId || !$parentId) throw new Exception("Eksik veri gönderildi.");

                $this->db->beginTransaction();

                // 1. Veli (Users) Tablosunu Güncelle
                $sqlUser = "UPDATE Users SET FullName = ?, Email = ? WHERE UserID = ?";
                $this->db->prepare($sqlUser)->execute([$parentName, $phone, $parentId]);
    
                // 2. Öğrenci Tablosunu Güncelle
                $sqlStudent = "UPDATE Students SET FullName = ?, MonthlyFee = ?, GroupID = ? WHERE StudentID = ?";
                $this->db->prepare($sqlStudent)->execute([$name, $monthlyFee, $groupId, $studentId]);
    
                $this->db->commit();
                
                header("Location: index.php?page=students&status=updated");
                exit;

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Güncelleme Hatası: " . $e->getMessage());
            }
        }
    }

    /**
     * SİLME İŞLEMİ
     */
    public function delete() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        if ($role === 'coach') {
            die("Yetki Hatası: Kayıt silme yetkiniz yoktur.");
        }
        
        // Silme işlemleri buraya...
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        $viewFile = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "Görünüm dosyası bulunamadı: $view";
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}