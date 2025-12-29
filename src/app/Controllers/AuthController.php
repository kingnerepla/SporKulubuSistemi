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
                // Azure SQL üzerinde büyük/küçük harf duyarlılığına karşı IsActive kontrolü ile çekiyoruz
                $sql = "SELECT * FROM Users WHERE Email = ? AND IsActive = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    $dbPass = isset($user['PasswordHash']) ? trim($user['PasswordHash']) : null;
    
                    // Şifre doğrulama (Hem düz metin hem hash desteği)
                    if ($dbPass && ($password === $dbPass || password_verify($password, $dbPass))) {
                        
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        // 1. Temel Kullanıcı Bilgileri
                        $_SESSION['user_id']   = $user['UserID']; 
                        $_SESSION['user_name'] = $user['FullName'];
                        $_SESSION['name']      = $user['FullName'] ?? 'Kullanıcı';
                        $_SESSION['club_id']   = $user['ClubID'] ?? null;

                        // 2. Rol Belirleme (Kritik: Hem string hem ID olarak kaydediyoruz)
                        $detectedRole = $this->detectRole($user);
                        $_SESSION['role'] = $detectedRole;
                        $_SESSION['role_id'] = intval($user['RoleID'] ?? 0);
                        $_SESSION['RoleID']  = intval($user['RoleID'] ?? 0); // Yedek (Büyük harf kullanan yerler için)

                        // 3. Yetkileri Set Etme
                        $this->setSessionPermissions($detectedRole);
    
                        session_write_close();
                        header("Location: index.php?page=dashboard");
                        exit;
                    }
                }
            } catch (Exception $e) {
                error_log("Login Hatası: " . $e->getMessage());
                die("Sistemde bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.");
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
            case 3:  return 'coach';      // Antrenör
            case 4:  return 'parent';
            case 5:  return 'student';
            default: return 'guest';
        }
    }
    
    private function setSessionPermissions($role) {
        // Finansal yetki veya diğer modül izinleri
        if ($role === 'systemadmin' || $role === 'clubadmin') {
            $_SESSION['can_view_finance'] = true;
        } else {
            $_SESSION['can_view_finance'] = false;
        }
    }

    public function showSelection() {
        $path = dirname(__DIR__) . '/Views/auth/select.php';
        if (!file_exists($path)) {
            $path = dirname(__DIR__) . '/Views/admin/select.php';
        }
        include $path;
    }
    
    public function showAdminLogin() {
        $path = dirname(__DIR__) . '/Views/auth/login.php';
        if (file_exists($path)) {
            include $path;
        } else {
            $manualPath = dirname(__DIR__) . '/Views/admin/login.php';
            if (file_exists($manualPath)) {
                include $manualPath;
            } else {
                die("<b>Dosya Hatası:</b> Login formu bulunamadı.");
            }
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
        header("Location: index.php?page=admin_login_form");
        exit;
    }
}