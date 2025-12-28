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
                // 1. Kullanıcıyı getir
                $sql = "SELECT * FROM Users WHERE Email = ? OR email = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email, $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $userData = array_change_key_case($user, CASE_LOWER);
                    
                    // Şifre sütununu bul
                    $dbPass = null;
                    $passKeys = ['password', 'pwd', 'sifre', 'userpassword'];
                    foreach($passKeys as $key) {
                        if (isset($userData[$key]) && !empty(trim($userData[$key]))) {
                            $dbPass = trim($userData[$key]);
                            break;
                        }
                    }

                    // Şifre Doğrulama (123456 bypass dahil)
                    if (($password === '123456' && (empty($dbPass) || $dbPass === '123456' || password_verify('123456', $dbPass))) || 
                        ($dbPass && ($password === $dbPass || password_verify($password, $dbPass)))) {
                        
                        $this->setSession($userData);
                        header("Location: index.php?page=dashboard");
                        exit;
                    }
                }
            } catch (Exception $e) {
                die("Sorgu Hatası: " . $e->getMessage());
            }

            header("Location: index.php?page=login&error=1");
            exit;
        }
    }

    private function setSession($data) {
        // Oturum verilerini güvenli bir şekilde ata
        $_SESSION['user_id'] = $data['userid'] ?? $data['id'] ?? 0;
        $_SESSION['name']    = $data['fullname'] ?? $data['name'] ?? 'Kullanıcı';
        
        // KRİTİK DÜZELTME: detectRole artık dinamik çalışıyor
        $_SESSION['role']    = $this->detectRole($data);
        
        $_SESSION['club_id'] = $data['clubid'] ?? null;
    }

    private function detectRole($data) {
        // Veritabanındaki RoleID'ye göre rol ismini belirle
        // 1 = SystemAdmin, 2 = ClubAdmin, 4 = Parent (Veli)
        $roleId = intval($data['roleid'] ?? 0);

        switch ($roleId) {
            case 1:
                return 'systemadmin';
            case 2:
                return 'clubadmin';
            case 4:
                return 'parent';
            default:
                return 'trainer'; // Varsayılan olarak antrenör/eğitmen
        }
    }
}