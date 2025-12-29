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
                        
                        if (session_status() === PHP_SESSION_NONE) session_start();
                        
                        // 1. Ana veriler
                        $_SESSION['user_id']   = $user['UserID']; 
                        $_SESSION['user_name'] = $user['FullName'];
                        
                        // 2. Rol belirleme (Azure: 3 = Coach)
                        $_SESSION['role'] = $this->detectRole($user); 

                        // 3. Session set etme
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
            case 3:  return 'coach';      // Azure Trainer -> Coach
            case 4:  return 'parent';
            case 5:  return 'student';
            default: return 'guest';
        }
    }
    
    // TEK VE BİRLEŞTİRİLMİŞ FONKSİYON
    private function setSession($data) {
        // Layout'un beklediği tüm varyasyonları ekliyoruz
        $_SESSION['name']      = $data['FullName'] ?? 'Kullanıcı';
        $_SESSION['club_id']   = $data['ClubID'] ?? null;
        $_SESSION['RoleID']    = intval($data['RoleID'] ?? 0);
        $_SESSION['role_id']   = intval($data['RoleID'] ?? 0);
        
        // Finansal yetki kontrolü
        if ($_SESSION['role'] === 'systemadmin' || $_SESSION['role'] === 'clubadmin') {
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
}