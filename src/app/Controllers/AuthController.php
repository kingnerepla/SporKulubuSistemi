<?php
// Database sınıfı index.php tarafından yüklendiği için burada tekrar require etmiyoruz.

class AuthController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            // 1. Kullanıcıyı ve ROL ADINI çek (JOIN işlemi kritik!)
            $sql = "SELECT Users.*, Roles.RoleName 
                    FROM Users 
                    INNER JOIN Roles ON Users.RoleID = Roles.RoleID 
                    WHERE Users.Email = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Doğrulama
            if ($user && password_verify($password, $user['PasswordHash'])) {
                
                // Pasif Kontrolü
                if ($user['IsActive'] == 0) {
                    echo "<script>alert('Hesabınız Pasif Durumda!'); window.location.href='index.php?page=login';</script>";
                    exit;
                }

                // 3. Session Başlat (EKSİKSİZ)
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['name'] = $user['FullName'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['role'] = $user['RoleName']; // <-- Bu satır çok önemli!
                $_SESSION['club_id'] = $user['ClubID'];

                // 4. GARANTİ YÖNLENDİRME (JS)
                echo "<script>window.location.href = 'index.php?page=dashboard';</script>";
                exit;

            } else {
                // Hatalı Giriş
                echo "<script>alert('E-posta veya Şifre Hatalı!'); window.location.href='index.php?page=login';</script>";
                exit;
            }
        }
    }
    
    public function logout() {
        session_destroy();
        echo "<script>window.location.href = 'index.php?page=login';</script>";
        exit;
    }
}