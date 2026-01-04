<?php

class AuthController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    /**
     * YÖNETİCİ VE ANTRENÖR GİRİŞİ
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginValue = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
    
            try {
                // 🔥 DEĞİŞİKLİK: Kişinin şahsi hesabı aktif olmalı. 
                // Kulüp pasif olsa bile (IsActive=0) kullanıcıyı içeri alıyoruz ki borcunu görsün.
                $sql = "SELECT * FROM Users WHERE (Email = ? OR Phone = ?) AND IsActive = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$loginValue, $loginValue]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    $dbPass = isset($user['Password']) ? trim($user['Password']) : (isset($user['PasswordHash']) ? trim($user['PasswordHash']) : null);
    
                    if ($dbPass && ($password === $dbPass || password_verify($password, $dbPass))) {
                        // Giriş başarılı, oturumu oluştur
                        $this->createSession($user);
                        header("Location: index.php?page=dashboard");
                        exit;
                    }
                }
            } catch (Exception $e) {
                error_log("Login Hatası: " . $e->getMessage());
                die("Sistemde bir hata oluştu.");
            }
    
            header("Location: index.php?page=admin_login_form&error=credentials");
            exit;
        }
    }

    /**
     * VELİ GİRİŞİ
     */
    public function parentLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
            $password = trim($_POST['password'] ?? '');

            try {
                // Veliler için de aynı mantık: Kişi aktifse içeri al
                $sql = "SELECT * FROM Users WHERE (Phone = ? OR Phone = ?) AND IsActive = 1 AND RoleID = 4";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$phone, trim($_POST['phone'])]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $dbPass = trim($user['Password'] ?? $user['PasswordHash'] ?? '');

                    if ($password === $dbPass || password_verify($password, $dbPass)) {
                        $this->createSession($user);
                        $_SESSION['parent_logged_in'] = true; 
                        header("Location: index.php?page=dashboard");
                        exit;
                    }
                }
            } catch (Exception $e) {
                error_log("Veli Giriş Hatası: " . $e->getMessage());
            }

            header("Location: index.php?page=parent_login&error=invalid");
            exit;
        }
    }

    /**
     * Ortak Oturum Başlatma
     */
    private function createSession($user) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $role = $this->detectRole($user);
        
        $_SESSION['user_id']   = $user['UserID']; 
        $_SESSION['user_name'] = $user['FullName'];
        $_SESSION['name']      = $user['FullName'] ?? 'Kullanıcı';
        $_SESSION['full_name'] = $user['FullName'] ?? 'Kullanıcı';
        $_SESSION['club_id']   = $user['ClubID'] ?? null;
        $_SESSION['role']      = $role;
        $_SESSION['role_id']   = intval($user['RoleID'] ?? 0);
        $_SESSION['RoleID']    = intval($user['RoleID'] ?? 0);

        $this->setSessionPermissions($user, $role);
        session_write_close();
    }

    private function detectRole($data) {
        $roleId = intval($data['RoleID'] ?? 0);
        switch ($roleId) {
            case 1:  return 'systemadmin';
            case 2:  return 'clubadmin';
            case 3:  return 'coach';
            case 4:  return 'parent';
            case 5:  return 'student';
            default: return 'guest';
        }
    }
    
    private function setSessionPermissions($user, $role) {
        $_SESSION['can_view_finance'] = ($role === 'systemadmin' || $role === 'clubadmin');
        
        if ($role === 'coach') {
            $_SESSION['can_see_reports'] = intval($user['CanSeeReports'] ?? 0);
            $_SESSION['coach_report_access'] = intval($user['CanSeeReports'] ?? 0);
        }

        if ($role === 'parent') {
            $_SESSION['is_parent'] = true;
        }
    }

    // Görünüm Yükleyiciler ve Logout (Aynı kalıyor)
    public function showSelection() { include dirname(__DIR__) . '/Views/auth/select.php'; }
    public function showAdminLogin() { include dirname(__DIR__) . '/Views/auth/login.php'; }
    public function showParentLogin() { include dirname(__DIR__) . '/Views/parent/login.php'; }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
        header("Location: index.php?page=login");
        exit;
    }
}