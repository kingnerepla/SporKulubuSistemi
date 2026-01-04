<?php

class ProfileController {
    private $db;

    public function __construct() {
        // Hatanın sebebi burasıydı, bağlantı şeklini güncelledik:
        if (file_exists(__DIR__ . '/../Config/Database.php')) {
            require_once __DIR__ . '/../Config/Database.php';
        }
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: index.php?page=login");
            exit;
        }

        // Kullanıcı bilgilerini çek
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE UserID = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->render('profile', ['user' => $user]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId   = $_SESSION['user_id'];
            $fullName = $_POST['full_name'];
            $email    = $_POST['email'];
            $phone    = $_POST['phone'] ?? ''; // Formdaki 'phone' verisi
            $password = $_POST['password'] ?? '';
    
            try {
                if (!empty($password)) {
                    // Şifre güncelleniyorsa: Sütun adı PasswordHash
                    $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ?, PasswordHash = ? WHERE UserID = ?";
                    $params = [$fullName, $email, $phone, $password, $userId];
                } else {
                    // Şifre boşsa sadece diğer bilgiler
                    $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ? WHERE UserID = ?";
                    $params = [$fullName, $email, $phone, $userId];
                }
    
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
    
                $_SESSION['full_name'] = $fullName;
                header("Location: index.php?page=profile&success=1");
                exit;
            } catch (Exception $e) {
                die("Güncelleme Hatası: " . $e->getMessage());
            }
        }
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        $path = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($path)) {
            include $path;
        } else {
            echo "Görünüm bulunamadı: $path";
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}