<?php

class AuthController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Session'ın açık olduğundan emin ol
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Formdaki name="email" kısmını alıyoruz
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
    
            try {
                // Sorguyu sadece Email ve PasswordHash üzerinden yapıyoruz
                $sql = "SELECT * FROM Users WHERE Email = ? AND IsActive = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    // Veritabanındaki PasswordHash değerini al
                    $dbPass = isset($user['PasswordHash']) ? trim($user['PasswordHash']) : null;
    
                    // Şifre kontrolü
                    if ($dbPass && ($password === $dbPass || password_verify($password, $dbPass))) {
                        
                        // OTURUMU BAŞLAT (index.php'nin tanıması için)
                        $_SESSION['user_id'] = $user['UserID']; 
                        $_SESSION['user_name'] = $user['FullName'];
                        $_SESSION['role'] = $user['Role'];
    
                        // Başarılı! Dashboard'a yönlendir
                        header("Location: index.php?page=dashboard");
                        exit;
                    }
                }
            } catch (Exception $e) {
                die("Sorgu Hatası: " . $e->getMessage());
            }
    
            // Hatalı giriş: Formun olduğu sayfaya geri dön ve hata mesajı gönder
            header("Location: index.php?page=admin_login_form&error=credentials");
            exit;
        }
    }
    public function showSelection() {
        // realpath kullanarak yolun doğruluğunu kontrol altına alıyoruz
        $path = __DIR__ . '/../Views/auth/select.php';
        if (!file_exists($path)) {
            // Eğer yukarıdaki bulamazsa src takılı alternatifi dene
            $path = __DIR__ . '/../../src/app/Views/auth/select.php';
        }
        include $path;
    }
    
  
    public function showAdminLogin() {
        // __DIR__ üzerinden gitmek yerine index.php'nin bulunduğu ana dizini referans alalım
        // index.php /var/www/html/src/ içindeyse, Views şuradadır:
        
        // YOL 1: Doğrudan dosya konumu üzerinden (E    n güvenli yol)
        $path = dirname(__DIR__) . '/Views/auth/login.php';
    
        if (file_exists($path)) {
            include $path;
        } else {
            // YOL 2: Eğer yukarıdaki başarısız olursa manuel zorlama
            $manualPath = '/var/www/html/src/app/Views/admin/login.php';
            if (file_exists($manualPath)) {
                include $manualPath;
            } else {
                die("Hata: Dosya hiçbir yerde bulunamadı.<br>Denenen 1: $path<br>Denenen 2: $manualPath");
            }
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