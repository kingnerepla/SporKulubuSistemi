<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

class StudentController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // --- LİSTELEME (GRUPLU GÖRÜNÜM) ---
    public function index() {
        $this->listStudents(1, 'students');
    }
    // --- AKILLI ARŞİVLEME VE AYRILMA İŞLEMİ ---
    public function archive_store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $studentId = $_POST['student_id'];
                $actionType = $_POST['archive_type']; // 'freeze' (Dondur) veya 'refund' (İade Et)
                $reason = $_POST['reason'];

                if ($actionType === 'refund') {
                    // SENARYO 1: İADE ET VE SIFIRLA
                    $amount = $_POST['refund_amount'] ?? 0;
                    
                    // 1. Kasadan Para Çıkışı (Eksi Tutar)
                    if ($amount > 0) {
                        $sqlPay = "INSERT INTO Payments (ClubID, StudentID, Amount, PaymentDate, PaymentType, Method, Description, CreatedAt) 
                                VALUES (?, ?, ?, GETDATE(), 'Refund', 'cash', ?, GETDATE())";
                        
                        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                        // Tutarı negatif kaydediyoruz (-500)
                        $this->db->prepare($sqlPay)->execute([$clubId, $studentId, -$amount, "İade: " . $reason]);
                    }

                    // 2. Hakkı Sıfırla ve Arşivle
                    $sqlStudent = "UPDATE Students SET IsActive = 0, RemainingSessions = 0 WHERE StudentID = ?";
                    $this->db->prepare($sqlStudent)->execute([$studentId]);

                    $_SESSION['success_message'] = "Öğrenciye iade yapıldı, bakiyesi sıfırlandı ve arşivlendi.";

                } else {
                    // SENARYO 2: DONDUR (HAKKI SAKLI KALSIN)
                    // Sadece pasife çekiyoruz, RemainingSessions sütununa DOKUNMUYORUZ.
                    $sqlStudent = "UPDATE Students SET IsActive = 0 WHERE StudentID = ?";
                    $this->db->prepare($sqlStudent)->execute([$studentId]);

                    $_SESSION['success_message'] = "Öğrenci donduruldu. Geri döndüğünde mevcut ders hakkıyla devam edebilir.";
                }

                $this->db->commit();
                header("Location: index.php?page=students");
                exit();

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("İşlem Hatası: " . $e->getMessage());
            }
        }
    }
    // --- ARŞİV LİSTESİ ---
    public function archived() {
        $this->listStudents(0, 'students_archived');
    }

    // ORTAK LİSTELEME FONKSİYONU
    private function listStudents($isActive, $viewPage) {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        
        // Grupları Çek (Filtreleme için)
        $stmtGroups = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ? ORDER BY GroupName ASC");
        $stmtGroups->execute([$clubId]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        // ÖĞRENCİLERİ ÇEK
        // DÜZELTME: "ORDER BY g.GroupName ASC" yaptık ki listede gruplar dağılmasın, başlık altında toplansın.
        $sql = "SELECT s.*, g.GroupName, 
                       u.FullName as ParentName, 
                       COALESCE(u.Phone, s.ParentPhone) as DisplayPhone 
                FROM Students s
                LEFT JOIN Groups g ON s.GroupID = g.GroupID
                LEFT JOIN Users u ON s.ParentID = u.UserID
                WHERE s.ClubID = ? AND s.IsActive = ? 
                ORDER BY g.GroupName ASC, s.FullName ASC"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId, $isActive]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render($viewPage, ['students' => $students, 'groups' => $groups]);
    }

    // --- KAYIT (STORE) ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                
                $fullName = $_POST['full_name'];
                $birthDate = $_POST['birth_date'];
                $groupId = $_POST['group_id'] ?: null;
                $standardSessions = $_POST['standard_sessions'] ?? 8;
                $packageFee = $_POST['package_fee'] ?? 0;
                
                $parentName = $_POST['parent_name']; 
                $parentPhone = trim($_POST['parent_phone']);
                $parentId = null;

                // Veli Kontrolü
                if (!empty($parentPhone)) {
                    $stmtCheck = $this->db->prepare("SELECT UserID FROM Users WHERE Phone = ? AND RoleID = 4");
                    $stmtCheck->execute([$parentPhone]);
                    $existingParent = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if ($existingParent) {
                        $parentId = $existingParent['UserID'];
                    } else {
                        $stmtNewUser = $this->db->prepare("INSERT INTO Users (FullName, PasswordHash, RoleID, Phone, ClubID, IsActive, CreatedAt) VALUES (?, ?, 4, ?, ?, 1, GETDATE())");
                        $stmtNewUser->execute([$parentName, password_hash('123456', PASSWORD_DEFAULT), $parentPhone, $clubId]);
                        $parentId = $this->db->lastInsertId();
                    }
                }

                $sql = "INSERT INTO Students 
                        (ClubID, GroupID, ParentID, FullName, BirthDate, ParentPhone, StandardSessions, PackageFee, RemainingSessions, IsActive, CreatedAt) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 1, GETDATE())";
                
                $this->db->prepare($sql)->execute([
                    $clubId, $groupId, $parentId, $fullName, $birthDate, $parentPhone, 
                    $standardSessions, $packageFee
                ]);

                $this->db->commit();
                $_SESSION['success_message'] = "Öğrenci kaydedildi.";
                header("Location: index.php?page=students");
                exit();

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // --- DÜZENLEME SAYFASI (EDIT) - EKSİK OLAN KISIM BUYDU ---
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) die("Geçersiz ID");

        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];

        // Öğrenciyi Çek
        $sql = "SELECT s.*, 
                       u.FullName as ParentName, 
                       u.Phone as ParentPhoneAccount 
                FROM Students s
                LEFT JOIN Users u ON s.ParentID = u.UserID
                WHERE s.StudentID = ? AND s.ClubID = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $clubId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) die("Öğrenci bulunamadı.");

        // Grupları Çek
        $stmtGroups = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ?");
        $stmtGroups->execute([$clubId]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        $this->render('student_edit', ['student' => $student, 'groups' => $groups]);
    }

    // --- GÜNCELLEME İŞLEMİ (UPDATE) ---
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $id = $_POST['student_id'];
                $parentId = $_POST['parent_id'] ?: null;
                $fullName = $_POST['full_name'];
                $birthDate = $_POST['birth_date'];
                $groupId = $_POST['group_id'] ?: null;
                
                // Yeni Sistem Alanları
                $standardSessions = $_POST['standard_sessions'];
                $packageFee = $_POST['package_fee'];
                $remainingSessions = $_POST['remaining_sessions'];

                $parentName = $_POST['parent_name'];
                $parentPhone = trim($_POST['parent_phone']);

                // 1. Öğrenci Güncelle
                $sqlStudent = "UPDATE Students SET 
                                FullName = ?, BirthDate = ?, GroupID = ?, 
                                StandardSessions = ?, PackageFee = ?, RemainingSessions = ? 
                                WHERE StudentID = ?";
                $this->db->prepare($sqlStudent)->execute([
                    $fullName, $birthDate, $groupId, 
                    $standardSessions, $packageFee, $remainingSessions, 
                    $id
                ]);

                // 2. Veli Güncelle
                if (!empty($parentId) && !empty($parentName)) {
                    $sqlParent = "UPDATE Users SET FullName = ?, Phone = ? WHERE UserID = ?";
                    $this->db->prepare($sqlParent)->execute([$parentName, $parentPhone, $parentId]);
                }

                $this->db->commit();
                $_SESSION['success_message'] = "Bilgiler güncellendi.";
                header("Location: index.php?page=students");
                exit();

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Güncelleme Hatası: " . $e->getMessage());
            }
        }
    }

    // --- ARŞİVE GÖNDER (SOFT DELETE) ---
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->db->prepare("UPDATE Students SET IsActive = 0 WHERE StudentID = ?")->execute([$id]);
            $_SESSION['success_message'] = "Öğrenci arşive gönderildi.";
        }
        header("Location: index.php?page=students");
        exit;
    }

    // --- GERİ YÜKLEME (RESTORE) - GRUP SEÇİMLİ ---
    public function restore() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $_POST['student_id'];
                $groupId = $_POST['group_id'];

                if (!$id || !$groupId) {
                    die("Hata: Öğrenci veya Grup seçilmedi.");
                }

                // Hem Aktif Et hem de Grubunu Güncelle
                $sql = "UPDATE Students SET IsActive = 1, GroupID = ? WHERE StudentID = ?";
                $this->db->prepare($sql)->execute([$groupId, $id]);

                $_SESSION['success_message'] = "Öğrenci başarıyla aktif edildi ve seçilen gruba atandı.";
                header("Location: index.php?page=students"); // Aktif listeye dön
                exit;

            } catch (Exception $e) {
                die("Geri Yükleme Hatası: " . $e->getMessage());
            }
        }
    }

    // --- TAMAMEN SİL (DESTROY) ---
    public function destroy() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Önce bağlı verileri silmek gerekebilir (Opsiyonel: İlişkisel veritabanı kuralına göre)
            $this->db->prepare("DELETE FROM Attendance WHERE StudentID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM Payments WHERE StudentID = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM Students WHERE StudentID = ?")->execute([$id]);
            $_SESSION['success_message'] = "Öğrenci tamamen silindi.";
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