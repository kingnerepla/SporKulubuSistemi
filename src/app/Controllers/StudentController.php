<?php

class StudentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /**
     * ÖĞRENCİ LİSTESİ
     */
    public function index() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        $userId = $_SESSION['user_id'];
        $clubId = $_SESSION['club_id'];

        if ($role === 'coach') {
            // Antrenör kendi öğrencilerini görür (Sadece Aktif olanlar: IsActive = 1)
            $sql = "SELECT s.StudentID, s.FullName, s.BirthDate, s.Notes, g.GroupName
                    FROM Students s
                    JOIN Groups g ON s.GroupID = g.GroupID
                    WHERE g.TrainerID = ? AND s.IsActive = 1
                    ORDER BY g.GroupName, s.FullName";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->render('coach_students', ['students' => $students]);
        } else {
            // Admin tüm listeyi görür (Sadece Aktif olanlar: IsActive = 1)
            $sql = "SELECT s.*, g.GroupName, u.FullName as ParentName, u.Email as ParentPhone
                    FROM Students s 
                    LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                    LEFT JOIN Users u ON s.ParentID = u.UserID
                    WHERE s.ClubID = ? AND s.IsActive = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->render('students', ['students' => $students]);
        }
    }

    /**
     * YENİ ÖĞRENCİ EKLEME FORMU
     */
    public function create() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        if ($role === 'coach') {
            header("Location: index.php?page=dashboard&error=unauthorized");
            exit;
        }

        $clubId = $_SESSION['club_id'];
        $stmtG = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmtG->execute([$clubId]);
        $groups = $stmtG->fetchAll(PDO::FETCH_ASSOC);

        $this->render('student_add', ['groups' => $groups]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentName = trim($_POST['student_name'] ?? '');
            $parentName  = trim($_POST['parent_name'] ?? '');
            $phone       = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? ''); 
            $monthlyFee  = $_POST['monthly_fee'] ?? 0;
            $notes       = $_POST['notes'] ?? '';
            $groupId     = !empty($_POST['group_id']) ? $_POST['group_id'] : null;
            $clubId      = $_SESSION['club_id'];
    
            // Tarih Dönüşümü
            $birthDate = null;
            $rawDate = $_POST['birth_date'] ?? null;
            if ($rawDate && strpos($rawDate, '.') !== false) {
                $parts = explode('.', $rawDate);
                $birthDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
    
            try {
                $this->db->beginTransaction();
    
                // 1. VELİ KONTROLÜ
                $checkUser = $this->db->prepare("SELECT UserID FROM Users WHERE Email = ? OR Phone = ?");
                $checkUser->execute([$phone, $phone]);
                $existingUser = $checkUser->fetch(PDO::FETCH_ASSOC);
    
                if ($existingUser) {
                    $parentId = $existingUser['UserID'];
                    // Mevcut veli ismini güncelle
                    $this->db->prepare("UPDATE Users SET FullName = ? WHERE UserID = ?")
                             ->execute([$parentName, $parentId]);
                } else {
                    // 2. YENİ VELİ EKLE (SQL Server için ID almanın en sağlam yolu)
                    $password = password_hash('123456', PASSWORD_DEFAULT);
                    $sqlUser = "INSERT INTO Users (FullName, Email, PasswordHash, Phone, RoleID, ClubID, IsActive, CreatedAt) 
                                OUTPUT INSERTED.UserID
                                VALUES (?, ?, ?, ?, 3, ?, 1, GETDATE())";
                    
                    $stmtUser = $this->db->prepare($sqlUser);
                    $stmtUser->execute([$parentName, $phone, $password, $phone, $clubId]);
                    
                    // OUTPUT INSERTED sayesinde ID'yi garantiye alıyoruz
                    $result = $stmtUser->fetch(PDO::FETCH_ASSOC);
                    $parentId = $result['UserID'];
                }
    
                if (!$parentId) {
                    throw new Exception("Veli ID'si alınamadı!");
                }
    
                // 3. ÖĞRENCİ KAYDI (Garantiye alınan ParentID ile)
                $sqlStudent = "INSERT INTO Students (FullName, BirthDate, GroupID, ParentID, MonthlyFee, Notes, ClubID, IsActive, CreatedAt) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 1, GETDATE())";
                
                $stmtStudent = $this->db->prepare($sqlStudent);
                $stmtStudent->execute([
                    $studentName, 
                    $birthDate, 
                    $groupId, 
                    $parentId, 
                    $monthlyFee, 
                    $notes, 
                    $clubId
                ]);
    
                $this->db->commit();
                header("Location: index.php?page=students&success=added");
                exit;
    
            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Sistem Hatası: " . $e->getMessage());
            }
        }
    
    }

    /**
     * ÖĞRENCİ SİLME (Pasife Çekme)
     */
    public function delete() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        if ($role === 'coach') {
            header("Location: index.php?page=dashboard&error=unauthorized");
            exit;
        }

        $studentId = $_GET['id'] ?? null;
        $clubId = $_SESSION['club_id'];

        if ($studentId) {
            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("SELECT ParentID FROM Students WHERE StudentID = ? AND ClubID = ?");
                $stmt->execute([$studentId, $clubId]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($student) {
                    // Öğrenciyi pasif yap
                    $this->db->prepare("UPDATE Students SET IsActive = 0 WHERE StudentID = ?")->execute([$studentId]);

                    // Veliyi pasif yap
                    if ($student['ParentID']) {
                        $this->db->prepare("UPDATE Users SET IsActive = 0 WHERE UserID = ?")->execute([$student['ParentID']]);
                    }
                }

                $this->db->commit();
                header("Location: index.php?page=students&status=deleted");
                exit;

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Silme Hatası: " . $e->getMessage());
            }
        }
    }

    /**
     * DÜZENLEME FORMU
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        $clubId = $_SESSION['club_id'];

        $sql = "SELECT s.*, u.FullName as ParentName, u.Email as ParentPhone 
                FROM Students s 
                LEFT JOIN Users u ON s.ParentID = u.UserID 
                WHERE s.StudentID = ? AND s.ClubID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $clubId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtG = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmtG->execute([$clubId]);
        $groups = $stmtG->fetchAll(PDO::FETCH_ASSOC);

        $this->render('student_edit', ['student' => $student, 'groups' => $groups]);
    }
    /**
     * ESKİ (PASİF) ÖĞRENCİ LİSTESİ
     */
    public function archived() {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        if ($role === 'coach') {
            header("Location: index.php?page=dashboard&error=unauthorized");
            exit;
        }

        $clubId = $_SESSION['club_id'];

        // Sadece IsActive = 0 olanları getiriyoruz
        $sql = "SELECT s.*, g.GroupName, u.FullName as ParentName, u.Email as ParentPhone
                FROM Students s 
                LEFT JOIN Groups g ON s.GroupID = g.GroupID 
                LEFT JOIN Users u ON s.ParentID = u.UserID
                WHERE s.ClubID = ? AND s.IsActive = 0
                ORDER BY s.FullName ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('students_archived', ['students' => $students]);
    }

    /**
     * ÖĞRENCİYİ GERİ AKTİF ETME
     */
    public function restore() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->db->prepare("UPDATE Students SET IsActive = 1 WHERE StudentID = ?")->execute([$id]);
            header("Location: index.php?page=students&status=restored");
            exit;
        }
    }
    /**
     * GÜNCELLEME İŞLEMİ
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'];
            $parentId  = $_POST['parent_id'];
            $name      = $_POST['student_name'];
            $notes     = $_POST['notes'] ?? '';
            $parentName = $_POST['parent_name'];
            $phone     = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? ''); 
            $monthlyFee = $_POST['monthly_fee'] ?? 0;
            $groupId   = !empty($_POST['group_id']) ? $_POST['group_id'] : null;

            $birthDate = null;
            $rawDate = $_POST['birth_date'] ?? null;
            if ($rawDate) {
                $parts = explode('.', $rawDate);
                if (count($parts) == 3) {
                    $birthDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                }
            }

            try {
                $this->db->beginTransaction();

                $sqlUser = "UPDATE Users SET FullName = ?, Email = ?, Phone = ? WHERE UserID = ?";
                $this->db->prepare($sqlUser)->execute([$parentName, $phone, $phone, $parentId]);

                $sqlStudent = "UPDATE Students SET FullName = ?, BirthDate = ?, Notes = ?, MonthlyFee = ?, GroupID = ? WHERE StudentID = ?";
                $this->db->prepare($sqlStudent)->execute([$name, $birthDate, $notes, $monthlyFee, $groupId, $studentId]);

                $this->db->commit();
                header("Location: index.php?page=students&status=updated");
                exit;
            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Hata: " . $e->getMessage());
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