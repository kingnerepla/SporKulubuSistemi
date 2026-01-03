<?php
// Hataları görelim
ini_set('display_errors', 1);
error_reporting(E_ALL);

class StudentController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // --- LİSTELEME ---
    public function index() {
        $this->listStudents(1, 'students');
    }

    // --- ARŞİV LİSTESİ ---
    public function archived() {
        $this->listStudents(0, 'students_archived');
    }

    // ORTAK LİSTELEME
    private function listStudents($isActive, $viewPage) {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        $userId = $_SESSION['user_id'];
        $role   = strtolower($_SESSION['role'] ?? '');
        $isCoach = ($role === 'coach' || $role === 'trainer');

        // 1. Gruplar
        if ($isCoach) {
            $stmtGroups = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ? AND CoachID = ? ORDER BY GroupName ASC");
            $stmtGroups->execute([$clubId, $userId]);
        } else {
            $stmtGroups = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ? ORDER BY GroupName ASC");
            $stmtGroups->execute([$clubId]);
        }
        $groups = $stmtGroups->fetchAll(PDO::FETCH_ASSOC);

        // 2. Öğrenciler
        $sql = "SELECT s.*, g.GroupName, u.FullName as ParentName, COALESCE(u.Phone, s.ParentPhone) as DisplayPhone 
                FROM Students s ";
        
        if ($isCoach) $sql .= " JOIN Groups g ON s.GroupID = g.GroupID "; 
        else $sql .= " LEFT JOIN Groups g ON s.GroupID = g.GroupID ";
        
        $sql .= " LEFT JOIN Users u ON s.ParentID = u.UserID 
                  WHERE s.ClubID = ? AND s.IsActive = ? ";
        
        $params = [$clubId, $isActive];
        if ($isCoach) {
            $sql .= " AND g.CoachID = ? ";
            $params[] = $userId;
        }
        $sql .= " ORDER BY g.GroupName ASC, s.FullName ASC"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render($viewPage, ['students' => $students, 'groups' => $groups]);
    }

    // --- YENİ KAYIT ---
    public function store() {
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role == 'coach' || $role == 'trainer') die("Yetkisiz işlem.");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                
                $fullName = trim($_POST['full_name']);
                $birthDate = $_POST['birth_date'];
                $groupId = !empty($_POST['group_id']) ? $_POST['group_id'] : null;
                $standardSessions = !empty($_POST['standard_sessions']) ? $_POST['standard_sessions'] : 8;
                $packageFee = !empty($_POST['package_fee']) ? $_POST['package_fee'] : 0;
                
                // 🔥 DÜZENLENEN KISIM: 
                // İlk kayıtta ders bakiyesi 0 olur. Ödeme yapıldığında PaymentController üzerinden yüklenir.
                $remainingSessions = 0; 
                
                $note = isset($_POST['note']) ? trim($_POST['note']) : null; 
                
                $parentName = trim($_POST['parent_name']); 
                $parentPhone = preg_replace('/[^0-9]/', '', $_POST['parent_phone']); 
                $parentId = null;

                if (!empty($parentPhone)) {
                    $stmtCheck = $this->db->prepare("SELECT UserID FROM Users WHERE Phone = ? AND RoleID = 4");
                    $stmtCheck->execute([$parentPhone]);
                    $existingParent = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if ($existingParent) {
                        $parentId = $existingParent['UserID'];
                    } else {
                        $dummyEmail = $parentPhone . "@veli.sistem";
                        $stmtNewUser = $this->db->prepare("INSERT INTO Users (Email, FullName, PasswordHash, RoleID, Phone, ClubID, IsActive, CreatedAt) VALUES (?, ?, ?, 4, ?, ?, 1, GETDATE())");
                        $stmtNewUser->execute([$dummyEmail, $parentName, password_hash('123456', PASSWORD_DEFAULT), $parentPhone, $clubId]);
                        $parentId = $this->db->lastInsertId();
                    }
                }

                $sql = "INSERT INTO Students 
                        (ClubID, GroupID, ParentID, FullName, BirthDate, ParentPhone, StandardSessions, PackageFee, RemainingSessions, Notes, IsActive, CreatedAt) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, GETDATE())";
                
                $this->db->prepare($sql)->execute([
                    $clubId, $groupId, $parentId, $fullName, $birthDate, $parentPhone, 
                    $standardSessions, $packageFee, $remainingSessions, 
                    $note
                ]);

                $this->db->commit();
                $_SESSION['success_message'] = "Öğrenci başarıyla kaydedildi. Ders kredisi yüklemek için tahsilat yapınız.";
                header("Location: index.php?page=students");
                exit();

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                $_SESSION['error_message'] = "Kayıt Hatası: " . $e->getMessage();
                header("Location: index.php?page=students");
                exit();
            }
        }
    }

    // --- GÜNCELLEME ---
    public function update() {
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role == 'coach' || $role == 'trainer') die("Yetkisiz işlem.");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                $id = $_POST['student_id'];
                $parentId = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
                $fullName = $_POST['full_name'];
                $birthDate = $_POST['birth_date'];
                $groupId = !empty($_POST['group_id']) ? $_POST['group_id'] : null;
                $standardSessions = $_POST['standard_sessions'];
                $packageFee = $_POST['package_fee'];
                $remainingSessions = $_POST['remaining_sessions'];
                $parentName = $_POST['parent_name'];
                $parentPhone = preg_replace('/[^0-9]/', '', $_POST['parent_phone']);

                $sqlStudent = "UPDATE Students SET FullName = ?, BirthDate = ?, GroupID = ?, StandardSessions = ?, PackageFee = ?, RemainingSessions = ?, ParentPhone = ? WHERE StudentID = ?";
                $this->db->prepare($sqlStudent)->execute([$fullName, $birthDate, $groupId, $standardSessions, $packageFee, $remainingSessions, $parentPhone, $id]);

                if (!empty($parentId) && !empty($parentName)) {
                    $this->db->prepare("UPDATE Users SET FullName = ?, Phone = ? WHERE UserID = ?")->execute([$parentName, $parentPhone, $parentId]);
                }

                $this->db->commit();
                $_SESSION['success_message'] = "Güncellendi.";
                header("Location: index.php?page=students");
                exit();
            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // --- ARŞİVLEME & İADE ---
    public function archive_store() {
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role == 'coach' || $role == 'trainer') die("Yetkisiz işlem.");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];

                $studentId = $_POST['student_id'];
                $actionType = $_POST['archive_type'];
                $reason = $_POST['reason'] ?? '';
                $refundAmount = $_POST['refund_amount'] ?? 0;

                $noteUpdate = " [Arşiv: " . $reason . " - " . date('d.m.Y') . "]";
                $sqlArch = "UPDATE Students SET IsActive = 0, Notes = CONCAT(ISNULL(Notes, ''), ?) WHERE StudentID = ?";
                $this->db->prepare($sqlArch)->execute([$noteUpdate, $studentId]);
                
                if ($actionType === 'refund') {
                    $this->db->prepare("UPDATE Students SET RemainingSessions = 0 WHERE StudentID = ?")->execute([$studentId]);
                    if ($refundAmount > 0) {
                        $sqlPay = "INSERT INTO Payments (ClubID, StudentID, Amount, PaymentDate, PaymentType, Method, Description, CreatedAt) 
                                   VALUES (?, ?, ?, GETDATE(), 'Refund', 'cash', ?, GETDATE())";
                        $this->db->prepare($sqlPay)->execute([$clubId, $studentId, -$refundAmount, "İade: " . $reason]);
                    }
                    $_SESSION['success_message'] = "İlişik kesildi, bakiye sıfırlandı ve iade işlendi.";
                } else {
                    $_SESSION['success_message'] = "Öğrenci donduruldu (Hakları saklı).";
                }

                $this->db->commit();
                header("Location: index.php?page=students");
                exit();
            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // --- GERİ YÜKLEME ---
    public function restore() {
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role == 'coach' || $role == 'trainer') die("Yetkisiz işlem.");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['student_id'];
            $groupId = $_POST['group_id'];
            if (!$id || !$groupId) header("Location: index.php?page=students_archived");
            $this->db->prepare("UPDATE Students SET IsActive = 1, GroupID = ? WHERE StudentID = ?")->execute([$groupId, $id]);
            $_SESSION['success_message'] = "Geri yüklendi.";
            header("Location: index.php?page=students"); 
            exit;
        }
    }

    // --- TAM SİLME ---
    public function destroy() {
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role == 'coach' || $role == 'trainer') die("Yetkisiz işlem.");

        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->db->prepare("DELETE FROM Students WHERE StudentID = ?")->execute([id]);
            $_SESSION['success_message'] = "Silindi.";
        }
        header("Location: index.php?page=students_archived");
        exit;
    }

    // --- NOT EKLEME ---
    public function update_note() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'];
            $note = trim($_POST['note']);
            $userId = $_SESSION['user_id'];
            $role   = strtolower($_SESSION['role'] ?? '');

            if ($role == 'coach' || $role == 'trainer') {
                $check = $this->db->prepare("SELECT Count(*) FROM Students s JOIN Groups g ON s.GroupID = g.GroupID WHERE s.StudentID = ? AND g.CoachID = ?");
                $check->execute([$studentId, $userId]);
                if ($check->fetchColumn() == 0) {
                    $_SESSION['error_message'] = "Yetkisiz işlem."; header("Location: index.php?page=students"); exit;
                }
            }
            $this->db->prepare("UPDATE Students SET Notes = ? WHERE StudentID = ?")->execute([$note, $studentId]);
            $_SESSION['success_message'] = "Not kaydedildi.";
            header("Location: index.php?page=students");
            exit;
        }
    }

    // --- ŞİFRE GÜNCELLEME ---
    public function update_password() {
        $role = strtolower($_SESSION['role'] ?? '');
        if ($role == 'coach' || $role == 'trainer') die("Yetkisiz işlem.");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $parentId = $_POST['parent_id'];
            $newPass  = trim($_POST['new_password']);
            if (!empty($parentId) && !empty($newPass)) {
                $this->db->prepare("UPDATE Users SET PasswordHash = ? WHERE UserID = ?")->execute([password_hash($newPass, PASSWORD_DEFAULT), $parentId]);
                $_SESSION['success_message'] = "Veli şifresi güncellendi.";
            }
            header("Location: index.php?page=students");
            exit;
        }
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