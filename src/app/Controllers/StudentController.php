<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

class StudentController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // --- 1. AKTİF ÖĞRENCİLER LİSTESİ ---
    public function index() {
        $this->listStudents(1, 'students');
    }

    // --- 2. ARŞİVLENMİŞ ÖĞRENCİLER LİSTESİ ---
    public function archived() {
        $this->listStudents(0, 'students_archived');
    }

    // --- ORTAK LİSTELEME FONKSİYONU (SQL DÜZELTMELERİ BURADA) ---
    private function listStudents($isActive, $viewPage) {
        $role = trim(strtolower($_SESSION['role'] ?? 'coach'));
        $userId = $_SESSION['user_id'];
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        
        // Grupları Çek
        $stmtGroups = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ? ORDER BY GroupName ASC");
        $stmtGroups->execute([$clubId]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        // ÖĞRENCİLERİ ÇEK (COALESCE ile Telefon Birleştirme)
        // Eğer Veli Hesabında telefon varsa onu al, yoksa Öğrenci tablosundaki eski telefonu al.
        $sqlBase = "SELECT s.*, g.GroupName, 
                           u.FullName as ParentName, 
                           COALESCE(u.Phone, s.ParentPhone) as DisplayPhone 
                    FROM Students s
                    LEFT JOIN Groups g ON s.GroupID = g.GroupID
                    LEFT JOIN Users u ON s.ParentID = u.UserID "; 

        if ($role === 'coach') {
            $sql = $sqlBase . " WHERE g.TrainerID = ? AND s.IsActive = ? ORDER BY g.GroupName ASC, s.FullName ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $isActive]);
        } else {
            $sql = $sqlBase . " WHERE s.ClubID = ? AND s.IsActive = ? ORDER BY g.GroupName ASC, s.FullName ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId, $isActive]);
        }
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render($viewPage, ['students' => $students, 'groups' => $groups]);
    }

    // --- 3. YENİ KAYIT (KARDEŞ KONTROLÜ + VELİ OLUŞTURMA) ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                
                // Form Verileri
                $studentName = $_POST['full_name'];
                $birthDate   = $_POST['birth_date'];
                $groupId     = $_POST['group_id'] ?: null;
                $monthlyFee  = $_POST['monthly_fee'] ?? 0;
                $joinDate    = $_POST['join_date'] ?? date('Y-m-d');
                
                $parentName  = $_POST['parent_name']; 
                $parentPhone = trim($_POST['parent_phone']);

                $parentId = null;
                $parentRoleId = 4; // Parent Rol ID

                if (!empty($parentPhone)) {
                    // Veli Kontrolü (Aynı telefon numarası ve Rolü Parent olan var mı?)
                    $stmtCheck = $this->db->prepare("SELECT UserID FROM Users WHERE Phone = ? AND RoleID = ?");
                    $stmtCheck->execute([$parentPhone, $parentRoleId]);
                    $existingParent = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if ($existingParent) {
                        // Veli zaten varsa ID'sini al (Kardeş Kaydı)
                        $parentId = $existingParent['UserID'];
                    } else {
                        // Yeni Veli Oluştur
                        $defaultPasswordHash = password_hash('123456', PASSWORD_DEFAULT);
                        
                        $stmtNewUser = $this->db->prepare("INSERT INTO Users 
                            (FullName, PasswordHash, RoleID, Phone, ClubID, IsActive, CreatedAt) 
                            VALUES (?, ?, ?, ?, ?, 1, GETDATE())");
                        
                        $stmtNewUser->execute([$parentName, $defaultPasswordHash, $parentRoleId, $parentPhone, $clubId]);
                        $parentId = $this->db->lastInsertId();
                    }
                }

                // Öğrenciyi Ekle (JoinDate yerine CreatedAt kullanıldı)
                $stmtStudent = $this->db->prepare("INSERT INTO Students 
                    (ClubID, GroupID, ParentID, FullName, BirthDate, MonthlyFee, ParentPhone, CreatedAt, NextPaymentDate, IsActive) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                
                $stmtStudent->execute([
                    $clubId, $groupId, $parentId, $studentName, $birthDate, $monthlyFee, $parentPhone, $joinDate, $joinDate
                ]);

                $this->db->commit();
                $_SESSION['success_message'] = "Kayıt başarıyla tamamlandı.";
                header("Location: index.php?page=students");
                exit();

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Kayıt Hatası: " . $e->getMessage());
            }
        }
    }

    // --- 4. DÜZENLEME SAYFASI (EDIT) ---
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header("Location: index.php?page=students"); exit; }

        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];

        $sql = "SELECT s.*, u.FullName as ParentName, u.Phone as ParentPhoneAccount 
                FROM Students s LEFT JOIN Users u ON s.ParentID = u.UserID 
                WHERE s.StudentID = ? AND s.ClubID = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $clubId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) die("Öğrenci bulunamadı.");

        $stmtGroups = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ?");
        $stmtGroups->execute([$clubId]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        $this->render('student_edit', ['student' => $student, 'groups' => $groups]);
    }

   // --- 5. GÜNCELLEME İŞLEMİ (UPDATE) - VELİ GÜNCELLEMELİ ---
   public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Güvenlik: İsim boşsa hata ver
                if (empty($_POST['full_name'])) die("Hata: Öğrenci adı boş olamaz!");

                $this->db->beginTransaction();

                $id = $_POST['student_id'];
                $parentId = $_POST['parent_id']; // Formdan gelen Veli ID (Users tablosu için)
                
                // 1. Öğrenci Verileri
                $studentName = $_POST['full_name'];
                $birthDate = $_POST['birth_date'];
                $groupId = $_POST['group_id'] ?: null;
                $monthlyFee = $_POST['monthly_fee'];
                
                // 2. Veli Verileri
                $parentName = $_POST['parent_name'];
                $parentPhone = trim($_POST['parent_phone']); // Maskeli gelebilir, temizlemek gerekebilir

                // A. Öğrenciyi Güncelle
                $sqlStudent = "UPDATE Students SET FullName = ?, BirthDate = ?, GroupID = ?, MonthlyFee = ? WHERE StudentID = ?";
                $this->db->prepare($sqlStudent)->execute([$studentName, $birthDate, $groupId, $monthlyFee, $id]);

                // B. Veli Hesabını Güncelle (Eğer Veli ID varsa)
                if (!empty($parentId) && !empty($parentName)) {
                    $sqlParent = "UPDATE Users SET FullName = ?, Phone = ? WHERE UserID = ?";
                    $this->db->prepare($sqlParent)->execute([$parentName, $parentPhone, $parentId]);
                }

                $this->db->commit();
                
                $_SESSION['success_message'] = "Öğrenci ve Veli bilgileri güncellendi.";
                header("Location: index.php?page=students");
                exit();

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Güncelleme Hatası: " . $e->getMessage());
            }
        }
    }


    // --- 6. SİLME (ARŞİVLEME) ---
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->db->prepare("UPDATE Students SET IsActive = 0 WHERE StudentID = ?")->execute([$id]);
            $_SESSION['success_message'] = "Öğrenci arşive taşındı.";
        }
        header("Location: index.php?page=students");
        exit;
    }

    // --- 7. GERİ YÜKLEME (RESTORE) - GRUBU SEÇEREK ---
    public function restore() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $studentId = $_POST['student_id'];
                $newGroupId = $_POST['group_id'];
                $this->db->prepare("UPDATE Students SET IsActive = 1, GroupID = ? WHERE StudentID = ?")
                         ->execute([$newGroupId, $studentId]);
                $_SESSION['success_message'] = "Öğrenci geri yüklendi.";
                header("Location: index.php?page=students");
                exit;
            } catch (Exception $e) { die("Hata: " . $e->getMessage()); }
        }
    }

    // --- 8. TAMAMEN SİLME (DESTROY) ---
    public function destroy() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            try {
                $this->db->prepare("DELETE FROM Students WHERE StudentID = ?")->execute([$id]);
                $_SESSION['success_message'] = "Kalıcı olarak silindi.";
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Silinemedi (İlişkili veri olabilir).";
            }
        }
        header("Location: index.php?page=students_archived");
        exit;
    }

    private function render($view, $data = []) {
        if(isset($_SESSION)) $data = array_merge($_SESSION, $data);
        extract($data);
        ob_start();
        $baseDir = __DIR__ . '/../';
        $viewsFolder = is_dir($baseDir . 'Views') ? 'Views' : 'views';
        $viewFile = $baseDir . $viewsFolder . "/admin/{$view}.php";
        if (file_exists($viewFile)) include $viewFile;
        $content = ob_get_clean();
        $layoutPath = $baseDir . $viewsFolder . '/layouts/admin_layout.php';
        if (file_exists($layoutPath)) include $layoutPath; else echo $content;
    }
}