<?php
require_once __DIR__ . '/../Config/Database.php';

class ClubController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // LİSTELEME SAYFASI
    public function index() {
        // Sadece Süper Yönetici görebilir
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SystemAdmin') {
            header("Location: index.php?page=dashboard");
            exit;
        }

        // SQL: Kulüpleri, Yöneticilerini ve Fiyat Bilgilerini Çek
        $sql = "SELECT Clubs.*, 
                (SELECT TOP 1 Users.FullName 
                 FROM Users 
                 INNER JOIN Roles ON Users.RoleID = Roles.RoleID 
                 WHERE Users.ClubID = Clubs.ClubID AND Roles.RoleName = 'ClubAdmin') as ManagerName
                FROM Clubs 
                ORDER BY CreatedAt DESC";

        $clubs = $this->db->query($sql)->fetchAll();

        // Görünümü Yükle (Layout ile beraber)
        ob_start(); 
        require_once __DIR__ . '/../Views/admin/clubs.php';
        $content = ob_get_clean(); 
        
        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    // KULÜP DETAY SAYFASI
    public function detail() {
        if ($_SESSION['role'] !== 'SystemAdmin') { header("Location: index.php?page=dashboard"); exit; }

        $clubId = $_GET['id'] ?? null;
        if (!$clubId) { header("Location: index.php?page=clubs"); exit; }

        // 1. Temel Bilgiler
        $stmtClub = $this->db->prepare("SELECT * FROM Clubs WHERE ClubID = ?");
        $stmtClub->execute([$clubId]);
        $club = $stmtClub->fetch();

        // 2. Yönetici
        $stmtManager = $this->db->prepare("SELECT Users.* FROM Users INNER JOIN Roles ON Users.RoleID = Roles.RoleID WHERE Users.ClubID = ? AND Roles.RoleName = 'ClubAdmin'");
        $stmtManager->execute([$clubId]);
        $manager = $stmtManager->fetch();

        // 3. İstatistikler
        $stmtTrainers = $this->db->prepare("SELECT COUNT(*) FROM Users INNER JOIN Roles ON Users.RoleID = Roles.RoleID WHERE Users.ClubID = ? AND Roles.RoleName = 'Trainer' AND Users.IsActive = 1");
        $stmtTrainers->execute([$clubId]);
        $trainerCount = $stmtTrainers->fetchColumn();

        $stmtGroups = $this->db->prepare("SELECT COUNT(*) FROM Groups WHERE ClubID = ?");
        $stmtGroups->execute([$clubId]);
        $groupCount = $stmtGroups->fetchColumn();

        $stmtStudents = $this->db->prepare("SELECT COUNT(*) FROM Students INNER JOIN Groups ON Students.GroupID = Groups.GroupID WHERE Groups.ClubID = ? AND Students.IsActive = 1");
        $stmtStudents->execute([$clubId]);
        $studentCount = $stmtStudents->fetchColumn();

        $stmtFinance = $this->db->prepare("SELECT SUM(Amount) FROM Payments INNER JOIN Students ON Payments.StudentID = Students.StudentID INNER JOIN Groups ON Students.GroupID = Groups.GroupID WHERE Groups.ClubID = ?");
        $stmtFinance->execute([$clubId]);
        $totalIncome = $stmtFinance->fetchColumn() ?: 0;

        // 4. LİSTELER (DETAYLI ANALİZ İÇİN)
        
        // A. Antrenörler
        $stmtTrainerList = $this->db->prepare("SELECT * FROM Users INNER JOIN Roles ON Users.RoleID = Roles.RoleID WHERE Users.ClubID = ? AND Roles.RoleName = 'Trainer'");
        $stmtTrainerList->execute([$clubId]);
        $trainers = $stmtTrainerList->fetchAll();

        // B. Gruplar
        $stmtGroupList = $this->db->prepare("
            SELECT Groups.GroupName, Users.FullName as TrainerName,
            (SELECT COUNT(*) FROM Students WHERE Students.GroupID = Groups.GroupID AND Students.IsActive=1) as StudentCount
            FROM Groups
            LEFT JOIN Users ON Groups.TrainerID = Users.UserID
            WHERE Groups.ClubID = ?
        ");
        $stmtGroupList->execute([$clubId]);
        $groups = $stmtGroupList->fetchAll();

        // C. YENİ EKLENEN: ÖĞRENCİ LİSTESİ (Süper Admin buradan takip edecek)
        $stmtStudentList = $this->db->prepare("
            SELECT Students.*, Groups.GroupName 
            FROM Students 
            INNER JOIN Groups ON Students.GroupID = Groups.GroupID 
            WHERE Groups.ClubID = ? 
            ORDER BY Students.FullName ASC
        ");
        $stmtStudentList->execute([$clubId]);
        $students = $stmtStudentList->fetchAll();

        ob_start();
        require_once __DIR__ . '/../Views/admin/club_detail.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    // YENİ KULÜP KAYDETME
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['club_name'];
            $pricePerStudent = $_POST['price_per_student'] ?? 0;
            $licenseFee = $_POST['license_fee'] ?? 0;
            
            $logoPath = null;

            // Logo Yükleme İşlemi
            if (isset($_FILES['club_logo']) && $_FILES['club_logo']['error'] == 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = time() . '_' . basename($_FILES['club_logo']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['club_logo']['tmp_name'], $targetPath)) {
                    $logoPath = 'public/uploads/' . $fileName;
                }
            }

            // Veritabanına Ekle (Fiyatlar Dahil)
            $stmt = $this->db->prepare("INSERT INTO Clubs (ClubName, LogoPath, PricePerStudent, LicenseFee, CreatedAt) VALUES (?, ?, ?, ?, GETDATE())");
            $stmt->execute([$name, $logoPath, $pricePerStudent, $licenseFee]);
            
            header("Location: index.php?page=clubs&success=created");
            exit;
        }
    }

    // KULÜP GÜNCELLEME
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['club_id'];
            $name = $_POST['club_name'];
            $pricePerStudent = $_POST['price_per_student'];
            $licenseFee = $_POST['license_fee'];
            
            // Temel güncelleme sorgusu (İsim ve Fiyatlar)
            $sql = "UPDATE Clubs SET ClubName = ?, PricePerStudent = ?, LicenseFee = ?";
            $params = [$name, $pricePerStudent, $licenseFee];

            // Yeni logo yüklenmiş mi?
            if (isset($_FILES['club_logo']) && $_FILES['club_logo']['error'] == 0) {
                
                // Eski resmi sil
                $stmtOld = $this->db->prepare("SELECT LogoPath FROM Clubs WHERE ClubID = ?");
                $stmtOld->execute([$id]);
                $oldLogoPath = $stmtOld->fetchColumn();

                if ($oldLogoPath && file_exists(__DIR__ . '/../../' . $oldLogoPath)) {
                    unlink(__DIR__ . '/../../' . $oldLogoPath);
                }

                // Yeni resmi yükle
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = time() . '_' . basename($_FILES['club_logo']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['club_logo']['tmp_name'], $targetPath)) {
                    $sql .= ", LogoPath = ?";
                    $params[] = 'public/uploads/' . $fileName;
                }
            }

            $sql .= " WHERE ClubID = ?";
            $params[] = $id;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            header("Location: index.php?page=clubs&success=updated");
            exit;
        }
    }

    // SİLME İŞLEMİ
    public function delete() {
        $id = $_GET['id'];
        try {
            $stmt = $this->db->prepare("DELETE FROM Clubs WHERE ClubID = ?");
            $stmt->execute([$id]);
            header("Location: index.php?page=clubs&success=deleted");
        } catch (PDOException $e) {
            header("Location: index.php?page=clubs&error=dependency");
        }
        exit;
    }
}