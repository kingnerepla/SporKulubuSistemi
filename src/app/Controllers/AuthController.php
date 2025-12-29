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
                $sql = "SELECT * FROM Users WHERE Email = ? AND IsActive = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    $dbPass = isset($user['PasswordHash']) ? trim($user['PasswordHash']) : null;
    
                    if ($dbPass && ($password === $dbPass || password_verify($password, $dbPass))) {
                        
                        // Önce tüm session'ı temizle
                        if (session_status() === PHP_SESSION_NONE) session_start();
                        
                        // --- OTURUMU BAŞLAT ---
                        // 1. Ana veriler (Layout'un en çok kullandığı anahtarlar)
                        $_SESSION['user_id']   = $user['UserID']; 
                        $_SESSION['user_name'] = $user['FullName']; // Ekranda ismi bu basar
                        
                        // 2. Rol belirleme (Sidebar menüleri için)
                        $_SESSION['role'] = $this->detectRole($user); 

                        // 3. Detaylı session verileri (Bypass edilen yerleri burası doldurur)
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

    private function detectRole($data) {
        $roleId = intval($data['RoleID'] ?? 0);
        switch ($roleId) {
            case 1:  return 'systemadmin';
            case 2:  return 'clubadmin';
            case 4:  return 'parent';
            default: return 'trainer';
        }
    }

    private function setSession($data) {
        // SQL Server anahtarlarını normalize et
        $userData = array_change_key_case($data, CASE_LOWER);
        
        // Eğer layout 'user_name' değil de 'name' veya 'fullname' bekliyorsa bunları da ekleyelim
        $_SESSION['name']      = $data['FullName'] ?? 'Kullanıcı';
        $_SESSION['club_id']   = $data['ClubID'] ?? null;
        $_SESSION['RoleID']    = $data['RoleID'] ?? 0;
        
        // Finansal yetkiler için ek kontroller
        if ($_SESSION['role'] === 'systemadmin') {
            $_SESSION['can_view_finance'] = true;
        }
    }

    public function showSelection() {
        // SRC yapısına duyarlı yol tespiti
        $path = dirname(__DIR__) . '/Views/auth/select.php';
        if (!file_exists($path)) {
            $path = dirname(__DIR__) . '/Views/admin/select.php'; // Alternatif
        }
        include $path;
    }
    
    public function showAdminLogin() {
        // En sağlıklı yol dirname(__DIR__) kullanımıdır
        $path = dirname(__DIR__) . '/Views/auth/login.php';
    
        if (file_exists($path)) {
            include $path;
        } else {
            // Eğer dosya auth içinde değilse admin klasörüne bak
            $manualPath = dirname(__DIR__) . '/Views/admin/login.php';
            if (file_exists($manualPath)) {
                include $manualPath;
            } else {
                die("<b>Dosya Hatası:</b> Login formu bulunamadı.<br>Yol: $path");
            }
        }
    }
}