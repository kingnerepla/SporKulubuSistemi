<?php
class CoachController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId   = $_POST['user_id'] ?? null;
            $fullName = $_POST['full_name'] ?? '';
            $email    = $_POST['email'] ?? '';
            $phone    = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
    
            try {
                if ($password) {
                    // Şifre değiştirilmek isteniyorsa
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ?, PasswordHash = ? WHERE UserID = ? AND RoleID = 2";
                    $params = [$fullName, $email, $phone, $passwordHash, $userId];
                } else {
                    // Şifreye dokunulmuyorsa
                    $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ? WHERE UserID = ? AND RoleID = 2";
                    $params = [$fullName, $email, $phone, $userId];
                }
    
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
    
                header("Location: index.php?page=coaches&status=updated");
                exit;
            } catch (Exception $e) {
                die("Güncelleme Hatası: " . $e->getMessage());
            }
        }
    }
    // Silme Metodu
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM Users WHERE UserID = ? AND RoleID = 2");
            $stmt->execute([$id]);
        }
        header("Location: index.php?page=coaches&msg=deleted");
        exit;
    }
    // Antrenörleri Listele
    public function index() {
        // 1. Rol ve Kulüp ID kontrolü
        $role = strtolower(trim($_SESSION['role'] ?? 'guest'));
        $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);
    
        if (!$clubId && $role !== 'systemadmin') {
            die("Hata: Kulüp seçilmedi.");
        }
    
        // 2. SORGUNUN DÜZELTİLMESİ
        // Artık Coaches tablosu yerine Users tablosundan RoleID = 3 olanları çekiyoruz
        $sql = "SELECT UserID, FullName, Email, Phone, IsActive, CreatedAt 
                FROM Users 
                WHERE ClubID = ? AND RoleID = 3
                ORDER BY FullName ASC";
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $coaches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // 3. Veriyi View'a gönder
        $this->render('coaches_list', ['coaches' => $coaches, 'role' => $role]);
    }

    // Yeni Antrenör Kaydet
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'] ?? '';
            $email    = $_POST['email'] ?? '';
            $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
            $phone    = $_POST['phone'] ?? '';
            $password = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
            $clubId   = $_SESSION['club_id'] ?? null;

            try {
                // Teyit ettiğimiz kolonlar: FullName, PasswordHash, RoleID
                // RoleID'yi 2 (Antrenör) olarak gönderiyoruz
                // CoachController.php -> store() metodu içinde
                $sql = "INSERT INTO Users (FullName, Email, Phone, PasswordHash, RoleID, ClubID, IsActive) 
                        VALUES (?, ?, ?, ?, 3, ?, 1)"; // Buradaki '3' artık Trainer/Coach oldu.
        
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $fullName, 
                    $email, 
                    $phone, 
                    $password, 
                    $clubId
                ]);

                header("Location: index.php?page=coaches&status=success");
                exit;
            } catch (Exception $e) {
                die("Kayıt Hatası: " . $e->getMessage());
            }
        }
    }

    // Görünüm Yükleyici
    private function render($view, $data = []) {
        extract($data); 
        ob_start();
        $viewPath = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($viewPath)) { 
            include $viewPath; 
        } else {
            echo "Görünüm dosyası bulunamadı: " . htmlspecialchars($view);
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}