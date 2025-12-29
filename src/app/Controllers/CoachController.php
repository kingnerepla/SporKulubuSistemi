<?php
class CoachController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Antrenörleri Listele
    public function index() {
        $role = strtolower(trim($_SESSION['role'] ?? 'guest'));
        $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);
    
        if (!$clubId && $role !== 'systemadmin') {
            die("Hata: Kulüp seçilmedi.");
        }
    
        // SORGULAMA: CanSeeReports kolonunu da çekiyoruz
        $sql = "SELECT UserID, FullName, Email, Phone, IsActive, CreatedAt, CanSeeReports 
                FROM Users 
                WHERE ClubID = ? AND RoleID = 3
                ORDER BY FullName ASC";
    
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $coaches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $this->render('coaches_list', ['coaches' => $coaches, 'role' => $role]);
    }

    // Antrenör Bilgilerini Güncelle
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId   = $_POST['user_id'] ?? null;
            $fullName = $_POST['full_name'] ?? '';
            $email    = $_POST['email'] ?? '';
            $phone    = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // --- YETKİ VERİSİNİ YAKALA ---
            $canSeeReports = isset($_POST['can_see_reports']) ? 1 : 0;
    
            try {
                // Not: RoleID = 3 (Antrenör) kontrolü yapıyoruz
                if ($password) {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ?, CanSeeReports = ?, PasswordHash = ? 
                            WHERE UserID = ? AND RoleID = 3";
                    $params = [$fullName, $email, $phone, $canSeeReports, $passwordHash, $userId];
                } else {
                    $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ?, CanSeeReports = ? 
                            WHERE UserID = ? AND RoleID = 3";
                    $params = [$fullName, $email, $phone, $canSeeReports, $userId];
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

    // Yeni Antrenör Kaydet
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'] ?? '';
            $email    = $_POST['email'] ?? '';
            $phone    = preg_replace('/\D/', '', $_POST['phone'] ?? ''); // Telefonu temizle
            $password = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
            $clubId   = $_SESSION['club_id'] ?? null;
            
            // --- YETKİ VERİSİNİ YAKALA ---
            $canSeeReports = isset($_POST['can_see_reports']) ? 1 : 0;

            try {
                // INSERT sorgusuna CanSeeReports eklendi
                $sql = "INSERT INTO Users (FullName, Email, Phone, PasswordHash, RoleID, ClubID, IsActive, CanSeeReports, CreatedAt) 
                        VALUES (?, ?, ?, ?, 3, ?, 1, ?, GETDATE())";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $fullName, 
                    $email, 
                    $phone, 
                    $password, 
                    $clubId,
                    $canSeeReports
                ]);

                header("Location: index.php?page=coaches&status=success");
                exit;
            } catch (Exception $e) {
                die("Kayıt Hatası: " . $e->getMessage());
            }
        }
    }

    // Silme Metodu
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Güvenlik için RoleID = 3 (Antrenör) olduğundan emin oluyoruz
            $stmt = $this->db->prepare("DELETE FROM Users WHERE UserID = ? AND RoleID = 3");
            $stmt->execute([$id]);
        }
        header("Location: index.php?page=coaches&msg=deleted");
        exit;
    }

    // Görünüm Yükleyici
    private function render($view, $data = []) {
        extract($data); 
        ob_start();
        $viewPath = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($viewPath)) { 
            include $viewPath; 
        } else {
            echo "Görünüm dosyası bulunamadı: " . htmlspecialchars($viewPath);
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}