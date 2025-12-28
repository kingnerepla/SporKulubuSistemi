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
            die("Hata: Kulüp seçilmedi.");
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

    // ÖĞRENCİ EKLEME FORMU
    public function create() {
        $role = strtolower(trim($_SESSION['role'] ?? 'guest'));
        $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);

        $stmt = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmt->execute([$clubId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('student_add', ['groups' => $groups, 'role' => $role]);
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
            die("Hata: Öğrenci bulunamadı.");
        }
    
        $stmtG = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmtG->execute([$clubId]);
        $groups = $stmtG->fetchAll(PDO::FETCH_ASSOC);
    
        $this->render('student_edit', ['student' => $student, 'groups' => $groups]);
    }

    // GÜNCELLEME İŞLEMİ
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'];
            $parentId  = $_POST['parent_id'];
            $name      = $_POST['student_name'];
            $parentName = $_POST['parent_name'];
            $phone     = preg_replace('/[^0-9]/', '', $_POST['phone']); 
            $monthlyFee = $_POST['monthly_fee'];
            $groupId   = !empty($_POST['group_id']) ? $_POST['group_id'] : null;
    
            try {
                $this->db->beginTransaction();

                // Telefon Numarası Çakışma Kontrolü (Başkası kullanıyor mu?)
                $stmtCheck = $this->db->prepare("SELECT UserID FROM Users WHERE Email = ? AND UserID != ?");
                $stmtCheck->execute([$phone, $parentId]);
                if ($stmtCheck->fetch()) {
                    throw new Exception("Bu telefon numarası zaten başka bir veli tarafından kullanılıyor!");
                }
    
                // 1. Öğrenci Tablosunu Güncelle
                $sqlStudent = "UPDATE Students SET FullName = ?, MonthlyFee = ?, GroupID = ? WHERE StudentID = ?";
                $this->db->prepare($sqlStudent)->execute([$name, $monthlyFee, $groupId, $studentId]);
    
                // 2. Veli (Users) Tablosunu Güncelle
                $sqlUser = "UPDATE Users SET FullName = ?, Email = ? WHERE UserID = ?";
                $this->db->prepare($sqlUser)->execute([$parentName, $phone, $parentId]);
    
                $this->db->commit();
                header("Location: index.php?page=students&updated=1");
                exit;
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                die("Güncelleme Hatası: " . $e->getMessage());
            }
        }
    }

    // SİLME İŞLEMİ
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM Students WHERE StudentID = ?");
            $stmt->execute([$id]);
        }
        header("Location: index.php?page=students&deleted=1");
        exit;
    }

    // VERİTABANINA KAYDETME (VELİ HESABIYLA BİRLİKTE)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentName = $_POST['student_name'] ?? null;
            $parentName  = $_POST['parent_name'] ?? null;
            $phone       = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
            $monthlyFee  = $_POST['monthly_fee'] ?? 0;
            $groupId     = !empty($_POST['group_id']) ? $_POST['group_id'] : null;
            
            $role = strtolower($_SESSION['role'] ?? 'guest');
            $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);
    
            if (empty($studentName)) {
                die("Hata: Öğrenci adı boş bırakılamaz.");
            }
    
            try {
                $this->db->beginTransaction();
    
                // 1. Veli kullanıcıyı kontrol et
                $stmtCheck = $this->db->prepare("SELECT UserID FROM Users WHERE Email = ?");
                $stmtCheck->execute([$phone]);
                $existingParent = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
                if (!$existingParent) {
                    $sqlUser = "INSERT INTO Users (FullName, Email, PasswordHash, RoleID, ClubID, IsActive) VALUES (?, ?, ?, 4, ?, 1)";
                    $stmtUser = $this->db->prepare($sqlUser);
                    $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
                    $stmtUser->execute([$parentName, $phone, $hashedPassword, $clubId]);
                    $parentId = $this->db->lastInsertId();
                } else {
                    $parentId = $existingParent['UserID'];
                }
    
                // 2. Öğrenciyi kaydet
                $sqlStudent = "INSERT INTO Students (FullName, ParentID, GroupID, MonthlyFee, ClubID, IsActive) VALUES (?, ?, ?, ?, ?, 1)";
                $stmtStudent = $this->db->prepare($sqlStudent);
                $stmtStudent->execute([$studentName, $parentId, $groupId, $monthlyFee, $clubId]);
    
                $this->db->commit();
                header("Location: index.php?page=students&success=1");
                exit;
    
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                die("Kayıt Hatası: " . $e->getMessage());
            }
        }
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}