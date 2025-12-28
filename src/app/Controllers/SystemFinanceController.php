<?php
require_once __DIR__ . '/../Config/Database.php';

class SystemFinanceController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?page=login"); exit; }

        $role = $_SESSION['role'];
        $myClubId = $_SESSION['club_id'];

        // 1. GEÇMİŞ ÖDEMELERİ LİSTELE
        if ($role === 'SystemAdmin') {
            // Admin hepsini görür
            $sql = "SELECT ClubPayments.*, Clubs.ClubName, Clubs.LogoPath 
                    FROM ClubPayments 
                    INNER JOIN Clubs ON ClubPayments.ClubID = Clubs.ClubID 
                    ORDER BY PaymentDate DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            // Kulüp sadece kendi ödediğini görür
            $sql = "SELECT ClubPayments.*, Clubs.ClubName, Clubs.LogoPath 
                    FROM ClubPayments 
                    INNER JOIN Clubs ON ClubPayments.ClubID = Clubs.ClubID 
                    WHERE ClubPayments.ClubID = ? 
                    ORDER BY PaymentDate DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$myClubId]);
        }
        $payments = $stmt->fetchAll();

        // Toplam Tahsilat (Kasaya Giren)
        $totalRevenue = 0;
        foreach ($payments as $p) { $totalRevenue += $p['Amount']; }

        // 2. HAKEDİŞ RAPORU (Sadece Admin İçin)
        // Hangi kulüpten ne kadar almalıyım?
        $projectedIncome = []; 
        $totalProjected = 0;

        if ($role === 'SystemAdmin') {
            $sqlReport = "
                SELECT 
                    c.ClubID, c.ClubName, c.LogoPath, c.PricePerStudent, c.LicenseFee,
                    (SELECT COUNT(*) FROM Students s 
                     INNER JOIN Groups g ON s.GroupID = g.GroupID 
                     WHERE g.ClubID = c.ClubID AND s.IsActive = 1) as ActiveStudentCount
                FROM Clubs c
                ORDER BY ActiveStudentCount DESC
            ";
            $projectedIncome = $this->db->query($sqlReport)->fetchAll();

            foreach ($projectedIncome as $item) {
                // Formül: Öğrenci Sayısı x Anlaşılan Fiyat
                $calc = $item['ActiveStudentCount'] * $item['PricePerStudent'];
                $totalProjected += $calc;
            }
        }
        
        // Modal İçin Kulüp Listesi
        $clubs = [];
        if ($role === 'SystemAdmin') {
            $clubs = $this->db->query("SELECT ClubID, ClubName FROM Clubs ORDER BY ClubName ASC")->fetchAll();
        }

        ob_start();
        require_once __DIR__ . '/../Views/admin/system_finance.php'; // Yeni View dosyasını çağırıyor
        $content = ob_get_clean();

        require_once __DIR__ . '/../Views/layouts/admin_layout.php';
    }

    public function store() {
        // Sadece Admin para girişi yapabilir
        if ($_SESSION['role'] !== 'SystemAdmin') { die("Yetkisiz işlem."); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clubId = $_POST['club_id'];
            $amount = $_POST['amount'];
            $date = $_POST['payment_date'];
            $desc = $_POST['description'];

            $stmt = $this->db->prepare("INSERT INTO ClubPayments (ClubID, Amount, PaymentDate, Description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$clubId, $amount, $date, $desc]);

            header("Location: index.php?page=system_finance&success=created");
            exit;
        }
    }
    
    public function delete() {
        if ($_SESSION['role'] !== 'SystemAdmin') { die("Yetkisiz işlem."); }
        
        $id = $_GET['id'];
        $stmt = $this->db->prepare("DELETE FROM ClubPayments WHERE ID = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php?page=system_finance&success=deleted");
    }
}