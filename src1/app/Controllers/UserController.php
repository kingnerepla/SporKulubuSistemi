<?php
require_once __DIR__ . '/../Config/Database.php';

class UserController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // KULLANICILARI LİSTELE
    public function index() {
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?page=login"); exit; }

        $clubId = $_SESSION['club_id'];
        $role = $_SESSION['role'];

        // ------------------------------------------------------------------
        // 1. KULLANICI LİSTESİNİ ÇEK
        // ------------------------------------------------------------------
        
        if ($role === 'SystemAdmin') {
            // SÜPER YÖNETİCİ: 
            // Veli (Parent) ve Öğrenci (Student) HARİÇ herkesi görsün.
            $sql = "SELECT Users.*, Roles.RoleName, Clubs.ClubName 
                    FROM Users 
                    INNER JOIN Roles ON Users.RoleID = Roles.RoleID 
                    LEFT JOIN Clubs ON Users.ClubID = Clubs.ClubID
                    WHERE Roles.RoleName NOT IN ('Parent', 'Student')
                    ORDER BY Users.CreatedAt DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            // KULÜP YÖNETİCİSİ: 
            // Sadece kendi kulübündeki Yönetici ve Antrenörleri görsün.
            $sql = "SELECT Users.*, Roles.RoleName, Clubs.ClubName 
                    FROM Users 
                    INNER JOIN Roles ON Users.RoleID = Roles.RoleID 
                    LEFT JOIN Clubs ON Users.ClubID = Clubs.ClubID
                    WHERE Users.ClubID = ? 
                    AND Roles.RoleName IN ('ClubAdmin', 'Trainer')
                    ORDER BY Users.CreatedAt DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
        }
        
        $users = $stmt->fetchAll();

        // ------------------------------------------------------------------
        // 2. KULÜP LİSTESİNİ ÇEK (Yeni Kullanıcı Ekleme Modalı İçin)
        // ------------------------------------------------------------------
        $clubs = [];
        if ($role === 'SystemAdmin') {
            $clubs = $this->db->query("SELECT ClubID, ClubName FROM Clubs ORDER BY ClubName ASC")->fetchAll();
        }

        ob_start();
        require_once __DIR__ . '/../Views/admin/users.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    // YENİ KULLANICI EKLE
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $roleId = $_POST['role_id'];
            
            // Eğer Süper Admin ise formdan gelen kulübü al, değilse kendi kulübünü kullan
            $clubId = null;
            if ($_SESSION['role'] == 'SystemAdmin') {
                $clubId = !empty($_POST['club_id']) ? $_POST['club_id'] : null;
            } else {
                $clubId = $_SESSION['club_id'];
            }

            // E-posta kontrolü
            $check = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE Email = ?");
            $check->execute([$email]);
            if ($check->fetchColumn() > 0) {
                header("Location: index.php?page=users&error=email_exists");
                exit;
            }

            $stmt = $this->db->prepare("INSERT INTO Users (FullName, Email, PasswordHash, RoleID, ClubID, IsActive) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$fullName, $email, $password, $roleId, $clubId]);

            header("Location: index.php?page=users&success=created");
            exit;
        }
    }

    // KULLANICI SİLME VEYA PASİFE ALMA
    public function delete() {
        $id = $_GET['id'];
        
        // Kendi kendini silmeyi engelle
        if ($id == $_SESSION['user_id']) {
            header("Location: index.php?page=users&error=self_delete");
            exit;
        }

        // --- BAĞIMLILIK KONTROLÜ ---
        $reasons = [];

        // A. Yönettiği Gruplar Var mı?
        $stmtGroups = $this->db->prepare("SELECT GroupName FROM Groups WHERE TrainerID = ?");
        $stmtGroups->execute([$id]);
        $groups = $stmtGroups->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($groups)) {
            $reasons[] = "Yönettiği Gruplar: <b>" . implode(', ', $groups) . "</b>";
        }

        // B. Velisi Olduğu Öğrenciler
        try {
            $stmtStudents = $this->db->prepare("SELECT FullName FROM Students WHERE ParentID = ?");
            $stmtStudents->execute([$id]);
            $students = $stmtStudents->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($students)) {
                $reasons[] = "Velisi Olduğu Öğrenciler: <b>" . implode(', ', $students) . "</b>";
            }
        } catch (Exception $e) { /* Tablo yoksa geç */ }

        // --- KARAR ANI ---
        try {
            if (empty($reasons)) {
                // Engel yoksa tamamen sil
                $stmt = $this->db->prepare("DELETE FROM Users WHERE UserID = ?");
                $stmt->execute([$id]);
                header("Location: index.php?page=users&success=deleted");
            } else {
                // Engel varsa hata fırlat (catch bloğuna düşsün)
                throw new Exception("Dependencies found");
            }
        } catch (Exception $e) {
            // Silemedik, Pasife Alıyoruz
            $stmtPassive = $this->db->prepare("UPDATE Users SET IsActive = 0 WHERE UserID = ?");
            $stmtPassive->execute([$id]);

            // Sebebi Session'a kaydet
            if (empty($reasons)) {
                $errorDetails = "Geçmiş veri bütünlüğü.";
            } else {
                $errorDetails = implode('<br>', $reasons);
            }
            $_SESSION['passivate_reason'] = $errorDetails;

            header("Location: index.php?page=users&warning=passived");
        }
        exit;
    }

    // KULLANICIYI GERİ AL (AKTİF ET)
    public function restore() {
        if (!isset($_GET['id'])) { header("Location: index.php?page=users"); exit; }
        $id = $_GET['id'];
        
        $stmt = $this->db->prepare("UPDATE Users SET IsActive = 1 WHERE UserID = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php?page=users&success=restored");
        exit;
    }
}