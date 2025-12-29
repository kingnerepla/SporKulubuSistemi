<?php

class AuthController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Formdaki 'email' inputu artık telefon numarasını da kabul eder
            $loginValue = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
    
            try {
                // KRİTİK DÜZENLEME: Hem Email hem Phone kolonuna bakıyoruz. 
                // Azure SQL ve diğer SQL sistemlerinde performans için IsActive indeksli olmalıdır.
                $sql = "SELECT * FROM Users WHERE (Email = ? OR Phone = ?) AND IsActive = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$loginValue, $loginValue]);
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

                        // 2. Rol Belirleme
                        $detectedRole = $this->detectRole($user);
                        $_SESSION['role'] = $detectedRole;
                        $_SESSION['role_id'] = intval($user['RoleID'] ?? 0);
                        $_SESSION['RoleID']  = intval($user['RoleID'] ?? 0);

                        // 3. Yetkileri Set Etme (Tüm kullanıcı verisini gönderiyoruz)
                        $this->setSessionPermissions($user, $detectedRole);
    
                        session_write_close();
                        header("Location: index.php?page=dashboard");
                        exit;
                    }
                }
            } catch (Exception $e) {
                error_log("Login Hatası: " . $e->getMessage());
                die("Sistemde bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.");
            }
    
            // Giriş başarısızsa hata mesajı ile dön
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
            case 4:  return 'parent';     // Veli
            case 5:  return 'student';
            default: return 'guest';
        }
    }
    
    private function setSessionPermissions($user, $role) {
        // 1. Finansal yetki (Sadece Adminler görebilir)
        if ($role === 'systemadmin' || $role === 'clubadmin') {
            $_SESSION['can_view_finance'] = true;
        } else {
            $_SESSION['can_view_finance'] = false;
        }

        // 2. Antrenör için Rapor Yetkisi
        // Veritabanındaki CanSeeReports kolonunu session'a aktarır
        if ($role === 'coach') {
            $_SESSION['can_see_reports'] = intval($user['CanSeeReports'] ?? 0);
            // Sidebar'daki kontrol için yedek anahtar
            $_SESSION['coach_report_access'] = intval($user['CanSeeReports'] ?? 0);
        }

        // 3. Veli için özel işaretleyici
        if ($role === 'parent') {
            $_SESSION['is_parent'] = true;
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