<?php

class UserController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /**
     * ANTRENÖR LİSTESİ
     */
    public function index() {
        // 1. Oturum bilgilerini al
        $role = trim($_SESSION['role'] ?? 'Guest');
        $currentUserId = $_SESSION['user_id'] ?? null;

        // 2. Kulüp ID Belirleme (Sadece kendi kulübü)
        // Eğer SystemAdmin ise ve bir kulüp seçmişse o kulübü, değilse kendi kulübünü al
        $clubId = ($role === 'SystemAdmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);

        if (!$clubId) {
            header("Location: index.php?page=dashboard&error=no_club_context");
            exit;
        }

        // 3. Sadece bu kulübe ait ve rolü 'Antrenör' (RoleID = 2) olanları getir
        // Kendi ismini (Admin) listede görmek istemiyorsan: AND UserID != ? eklenebilir
        $sql = "SELECT u.*, r.RoleName 
                FROM Users u
                JOIN Roles r ON u.RoleID = r.RoleID
                WHERE u.ClubID = ? 
                AND u.RoleID = 2 
                AND u.IsActive = 1
                ORDER BY u.FullName ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
            $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // View'a gönder
            $this->render('users', ['trainers' => $trainers]);
        } catch (PDOException $e) {
            die("Liste çekme hatası: " . $e->getMessage());
        }
    }

    /**
     * ANTRENÖR EKLEME
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $password = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
            $clubId   = $_SESSION['club_id']; // Mevcut yöneticinin kulüp ID'si

            try {
                // Yeni antrenörü bu kulübe ve RoleID = 2 (Antrenör) olarak kaydet
                $sql = "INSERT INTO Users (FullName, Email, Phone, Password, RoleID, ClubID, IsActive, CreatedAt) 
                        VALUES (?, ?, ?, ?, 2, ?, 1, GETDATE())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$fullName, $email, $phone, $password, $clubId]);

                header("Location: index.php?page=users&success=added");
            } catch (PDOException $e) {
                die("Ekleme hatası: " . $e->getMessage());
            }
        }
    }

    /**
     * RENDER YARDIMCISI
     */
    private function render($view, $data = []) {
        extract($data);
        ob_start();
        // Senin sisteminde view dosyası Users.php veya Trainers.php olabilir, burayı kontrol et
        include __DIR__ . "/../Views/admin/{$view}.php"; 
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}