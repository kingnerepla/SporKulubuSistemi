<?php
// app/Controllers/StudentController.php

class StudentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // ÖĞRENCİ LİSTESİ
    public function index() {
        $role = strtolower(trim($_SESSION['role'] ?? 'guest'));
        $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);

        if (!$clubId && $role !== 'systemadmin') {
            header("Location: index.php?page=dashboard&error=noclub"); // Kulüp yoksa atar
            exit;
        }

        $sql = "SELECT s.*, g.GroupName, u.FullName as ParentName, u.Email as ParentPhone
                FROM Students s
                LEFT JOIN Groups g ON s.GroupID = g.GroupID
                LEFT JOIN Users u ON s.ParentID = u.UserID
                WHERE s.ClubID = ?
                ORDER BY s.FullName ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('students', ['students' => $students, 'role' => $role]);
    }

    // ÖĞRENCİ DÜZENLEME FORMU
    public function edit() {
        $id = $_GET['id'] ?? null;
        $role = strtolower($_SESSION['role'] ?? 'guest');
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

    // GÜNCELLEME İŞLEMİ
    public function update() {
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

                // 1. Veli (Users) Tablosunu Güncelle (Email kısmına telefon yazıyoruz)
                $sqlUser = "UPDATE Users SET FullName = ?, Email = ? WHERE UserID = ?";
                $this->db->prepare($sqlUser)->execute([$parentName, $phone, $parentId]);
    
                // 2. Öğrenci Tablosunu Güncelle
                $sqlStudent = "UPDATE Students SET FullName = ?, MonthlyFee = ?, GroupID = ? WHERE StudentID = ?";
                $this->db->prepare($sqlStudent)->execute([$name, $monthlyFee, $groupId, $studentId]);
    
                $this->db->commit();
                
                // BURASI: Dashboard'a atmaması için kesin yönlendirme
                header("Location: index.php?page=students&status=updated");
                exit;

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Güncelleme Hatası: " . $e->getMessage());
            }
        }
    }

    // ... create, store ve delete metotları aynı kalabilir ...

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