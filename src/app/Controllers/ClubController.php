<?php

class ClubController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /**
     * SÜPER ADMİN KULÜP YÖNETİM MERKEZİ
     */
    public function index() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') {
            header("Location: index.php?page=dashboard");
            exit;
        }

        $selectedClubId = $_SESSION['selected_club_id'] ?? null;
        $tab = $_GET['tab'] ?? 'students';
        
        $data = [
            'clubs' => [],
            'tabData' => [],
            'stats' => ['students' => 0, 'revenue' => 0, 'system_debt' => 0, 'per_student' => 0, 'license' => 0],
            'selectedClub' => null,
            'activeTab' => $tab
        ];

        try {
            // Tüm kulüplerin listesi
            $data['clubs'] = $this->db->query("SELECT * FROM Clubs ORDER BY ClubName ASC")->fetchAll(PDO::FETCH_ASSOC);

            if ($selectedClubId) {
                $stmtClub = $this->db->prepare("SELECT * FROM Clubs WHERE ClubID = ?");
                $stmtClub->execute([$selectedClubId]);
                $data['selectedClub'] = $stmtClub->fetch(PDO::FETCH_ASSOC);

                // Finansal parametreler
                $data['stats']['per_student'] = $data['selectedClub']['MonthlyPerStudentFee'] ?? 100;
                $data['stats']['license']     = $data['selectedClub']['AnnualLicenseFee'] ?? 5000;

                // KPI İstatistikleri
                $data['stats']['students'] = $this->getScalar("SELECT COUNT(*) FROM Students WHERE ClubID = ? AND IsActive = 1", [$selectedClubId]);
                $data['stats']['coaches']  = $this->getScalar("SELECT COUNT(*) FROM Users WHERE ClubID = ? AND RoleID = 3", [$selectedClubId]);
                $data['stats']['revenue']  = $this->getScalar("SELECT SUM(Amount) FROM Payments WHERE ClubID = ?", [$selectedClubId]);

                // Hakediş Hesabı
                $data['stats']['system_debt'] = $data['stats']['license'] + ($data['stats']['students'] * $data['stats']['per_student']);

                switch ($tab) {
                    case 'students':
                        $stmt = $this->db->prepare("SELECT s.*, g.GroupName FROM Students s LEFT JOIN Groups g ON s.GroupID = g.GroupID WHERE s.ClubID = ? AND s.IsActive = 1 ORDER BY s.FullName ASC");
                        $stmt->execute([$selectedClubId]);
                        $data['tabData'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;
                    case 'finance':
                        $stmt = $this->db->prepare("SELECT p.*, s.FullName as StudentName FROM Payments p JOIN Students s ON p.StudentID = s.StudentID WHERE p.ClubID = ? ORDER BY p.PaymentDate DESC");
                        $stmt->execute([$selectedClubId]);
                        $data['tabData'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;
                }
            }
        } catch (Exception $e) { 
            die("SQL Hatası: " . $e->getMessage()); 
        }

        $this->render('clubs', $data);
    }

    /**
     * KULÜP EKLEME FORMU
     */
    public function create() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') exit;
        $this->render('club_add');
    }

    /**
     * KULÜP VE ADMİN KAYDI (TRANSACTION)
     */
    public function store() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') exit;

        $clubName   = $_POST['club_name'] ?? null;
        $adminName  = $_POST['admin_name'] ?? null;
        $adminPhone = $_POST['admin_phone'] ?? null;
        $adminEmail = $_POST['admin_email'] ?? null;

        if (!$clubName || !$adminName || !$adminPhone) {
            die("Hata: Gerekli alanlar doldurulmadı.");
        }

        $hashedPassword = password_hash($adminPhone, PASSWORD_DEFAULT); 

        try {
            $this->db->beginTransaction();

            // 1. Kulüp Ekle
            $sqlClub = "INSERT INTO Clubs (ClubName, IsActive, CreatedAt, MonthlyPerStudentFee, AnnualLicenseFee, [Status]) 
                        OUTPUT INSERTED.ClubID
                        VALUES (?, 1, SYSDATETIME(), 100, 5000, 'Active')";
            $stmt = $this->db->prepare($sqlClub);
            $stmt->execute([$clubName]);
            $newClubId = $stmt->fetchColumn();

            // 2. Kulüp Yöneticisi Ekle
            $sqlUser = "INSERT INTO Users (FullName, Phone, Email, PasswordHash, RoleID, ClubID, IsActive, CreatedAt) 
                        VALUES (?, ?, ?, ?, 2, ?, 1, SYSDATETIME())";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([$adminName, $adminPhone, $adminEmail, $hashedPassword, $newClubId]);

            $this->db->commit();
            header("Location: index.php?page=dashboard&msg=success");
            exit;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            die("Kayıt Hatası: " . $e->getMessage());
        }
    }

    /**
     * DÜZENLEME FORMU
     */
    public function edit() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') exit;
        
        $id = $_GET['id'] ?? null;
        if (!$id) { header("Location: index.php?page=dashboard"); exit; }

        $stmt = $this->db->prepare("SELECT * FROM Clubs WHERE ClubID = ?");
        $stmt->execute([$id]);
        $club = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$club) die("Kulüp bulunamadı.");

        $this->render('club_edit', ['club' => $club]);
    }

    /**
     * BİLGİ GÜNCELLEME
     */
    public function update() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') exit;

        $clubId   = $_POST['club_id'] ?? null;
        $clubName = $_POST['club_name'] ?? null;
        $status   = $_POST['status'] ?? 'Active';
        $licenseEndDate = $_POST['license_end_date'] ?? null;
        $monthlyFee = $_POST['monthly_fee'] ?? 0;
        $annualFee  = $_POST['annual_fee'] ?? 0;

        try {
            $isActive = ($status === 'Active') ? 1 : 0;
            $sql = "UPDATE Clubs SET 
                    ClubName = ?, [Status] = ?, LicenseEndDate = ?, 
                    MonthlyPerStudentFee = ?, AnnualLicenseFee = ?, IsActive = ?
                    WHERE ClubID = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubName, $status, $licenseEndDate, $monthlyFee, $annualFee, $isActive, $clubId]);

            header("Location: index.php?page=dashboard&msg=updated");
            exit;
        } catch (Exception $e) {
            die("Güncelleme Hatası: " . $e->getMessage());
        }
    }

    /**
     * 🔥 IMPERSONATE (KULÜP OLARAK GÖR)
     */
    public function impersonate() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') exit;

        $clubId = $_GET['id'] ?? null;
        if (!$clubId) die("ID eksik.");

        try {
            // Kulübün ana yöneticisini bul
            $stmt = $this->db->prepare("SELECT TOP 1 * FROM Users WHERE ClubID = ? AND RoleID = 2 AND IsActive = 1");
            $stmt->execute([$clubId]);
            $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$targetUser) die("Aktif yönetici hesabı bulunamadı.");

            // Mevcut Admin bilgilerini yedekle
            $_SESSION['impersonator_id'] = $_SESSION['user_id'];
            $_SESSION['impersonator_name'] = $_SESSION['full_name'] ?? $_SESSION['name'];

            // Hedef kulüp kimliğine bürün
            $_SESSION['user_id']   = $targetUser['UserID'];
            $_SESSION['role']      = 'clubadmin';
            $_SESSION['role_id']   = 2;
            $_SESSION['club_id']   = $targetUser['ClubID'];
            $_SESSION['full_name'] = $targetUser['FullName'];

            header("Location: index.php?page=dashboard");
            exit;
        } catch (Exception $e) { die("Hata: " . $e->getMessage()); }
    }

    /**
     * SIZMA MODUNDAN ÇIK
     */
    public function exitImpersonate() {
        if (!isset($_SESSION['impersonator_id'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }

        $_SESSION['user_id'] = $_SESSION['impersonator_id'];
        $_SESSION['role']    = 'systemadmin';
        $_SESSION['role_id'] = 1;
        unset($_SESSION['club_id'], $_SESSION['impersonator_id'], $_SESSION['impersonator_name']);

        header("Location: index.php?page=dashboard");
        exit;
    }
    public function addSaasPayment() {
        // 1. Yetki Kontrolü
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') exit;
    
        // 2. Verileri Al
        $clubId  = $_POST['club_id'] ?? null;
        $amount  = $_POST['amount'] ?? null;
        $desc    = $_POST['description'] ?? 'SaaS Hizmet Bedeli Ödemesi';
        $adminId = $_SESSION['user_id'] ?? 0;
    
        // 3. Eksik Veri Kontrolü
        if (empty($clubId) || empty($amount)) {
            header("Location: index.php?page=dashboard&msg=missing_data");
            exit;
        }
    
        try {
            $this->db->beginTransaction();
    
            // 4. İŞLEM: Merkezi Kasa Kaydı (Süper Admin Paneli İçin)
            $sqlSaas = "INSERT INTO SaasPayments (ClubID, Amount, PaymentDate, Description) VALUES (?, ?, GETDATE(), ?)";
            $stmtSaas = $this->db->prepare($sqlSaas);
            $stmtSaas->execute([$clubId, $amount, $desc]);
    
            // 5. İŞLEM: Kulüp Gider Kaydı (Kulüp Muhasebe Sayfası İçin)
            // Kategori kısmını 'Sistem Ödemesi' olarak güncelledik
            $sqlExpense = "INSERT INTO Expenses (ClubID, Category, Title, Amount, ExpenseDate, CreatedBy, CreatedAt) 
                           VALUES (?, ?, ?, ?, GETDATE(), ?, GETDATE())";
            $stmtExpense = $this->db->prepare($sqlExpense);
            
            $category = 'Sistem Ödemesi'; // İstediğin kategori ismi
            $fullTitle = "SaaS Kullanım Bedeli: " . $desc;
            
            $stmtExpense->execute([
                $clubId, 
                $category,   // 'Sistem Ödemesi' buraya yazılıyor
                $fullTitle, 
                $amount, 
                $adminId
            ]);
    
            $this->db->commit();
    
            header("Location: index.php?page=dashboard&msg=payment_saved");
            exit;
    
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("SaaS Ödeme Hatası: " . $e->getMessage());
            die("Hata: " . $e->getMessage());
        }
    }
    private function getScalar($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetchColumn();
        return $res !== false ? $res : 0;
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        $path = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($path)) {
            include $path;
        } else {
            echo "Görünüm bulunamadı: $view";
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}