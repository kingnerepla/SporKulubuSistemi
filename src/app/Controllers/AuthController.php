<?php

class AuthController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
    
            try {
                // Email üzerinden kullanıcıyı bul (PasswordHash sütununu kullanıyoruz)
                $sql = "SELECT * FROM Users WHERE Email = ? AND IsActive = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    $dbPass = isset($user['PasswordHash']) ? trim($user['PasswordHash']) : null;
    
                    // Şifre Doğrulama (Plain text veya Hash kontrolü)
                    if ($dbPass && ($password === $dbPass || password_verify($password, $dbPass))) {
                        
                        // --- OTURUMU BAŞLAT ---
                        // 1. Temel Session verilerini atıyoruz
                        $_SESSION['user_id']   = $user['UserID']; 
                        $_SESSION['user_name'] = $user['FullName'];
                        
                        // 2. Rolü belirliyoruz (Layout/Sidebar buradaki değere göre menü açar)
                        $_SESSION['role'] = $this->detectRole($user); 

                        // 3. Kulüp ID ve diğer finansal yetkiler için detaylı session kurulumu
                        $this->setSession($user);
    
                        session_write_close();
                        header("Location: index.php?page=dashboard");
                        exit;
                    }
                }
            } catch (Exception $e) {
                die("Sorgu Hatası: " . $e->getMessage());
            }
    
            header("Location: index.php?page=admin_login_form&error=credentials");
            exit;
        }
    }

    // Rol belirleme mantığı: Yan menüdeki linklerin görünmesini bu metodun dönüş değeri sağlar
    private function detectRole($data) {
        $roleId = intval($data['RoleID'] ?? 0);

        switch ($roleId) {
            case 1:
                return 'systemadmin'; // 'systemadmin' ise Finans ve Kulüp Denetimi görünür
            case 2:
                return 'clubadmin';   // Sadece kendi kulübünü görür
            case 4:
                return 'parent';      // Veli arayüzü
            default:
                return 'trainer';     // Antrenör arayüzü
        }
    }

    private function setSession($data) {
        // SQL Server'dan gelen verileri küçük harf anahtarlarla yedekliyoruz
        $userData = array_change_key_case($data, CASE_LOWER);
        
        $_SESSION['user_id']   = $userData['userid'] ?? 0;
        $_SESSION['user_name'] = $userData['fullname'] ?? 'Kullanıcı';
        
        // Kulüp Denetimi ve Finans için hayati veri: ClubID
        $_SESSION['club_id']   = $userData['clubid'] ?? null;
        
        // Eğer layout dosyanız direkt 'role' yerine 'RoleID' kontrolü yapıyorsa:
        $_SESSION['RoleID']    = $userData['roleid'] ?? 0;
    }

    public function showSelection() {
        $path = __DIR__ . '/../Views/auth/select.php';
        if (!file_exists($path)) {
            $path = __DIR__ . '/../../src/app/Views/auth/select.php';
        }
        include $path;
    }
    
    public function showAdminLogin() {
        // En güvenli yol: Views klasörüne çıkmak
        $path = dirname(__DIR__) . '/Views/auth/login.php';
    
        if (file_exists($path)) {
            include $path;
        } else {
            $manualPath = dirname(__DIR__) . '/Views/admin/login.php';
            if (file_exists($manualPath)) {
                include $manualPath;
            } else {
                die("Hata: Login dosyası bulunamadı.");
            }
        }
    }
}