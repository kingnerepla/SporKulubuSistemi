<?php

class SystemFinanceController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') {
            header("Location: index.php?page=dashboard"); exit;
        }

        $data = [
            'total_saas_revenue' => 0,
            'club_breakdown' => [],
            'active_clubs_count' => 0,
            'real_expenses' => [],
            'total_expenses' => 0,
            'critical_licenses' => 0 // Süresi az kalan kulüp sayısı
        ];

        try {
            // 1. Kulüp Hakedişlerini ve Lisans Sürelerini Çek (Dinamik SQL)
            // LicenseStartDate: Kulübün sisteme kayıt tarihi
            $sqlClubs = "SELECT c.ClubID, c.ClubName, 
                         COALESCE(c.AnnualLicenseFee, 5000) as License, 
                         COALESCE(c.MonthlyPerStudentFee, 100) as PerStudent,
                         COALESCE(c.LicenseStartDate, c.CreatedAt) as StartDate,
                         (SELECT COUNT(*) FROM Students s WHERE s.ClubID = c.ClubID AND s.IsActive = 1) as Students
                         FROM Clubs c";
            
            $clubs = $this->db->query($sqlClubs)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clubs as $c) {
                // LİSANS SÜRE HESAPLAMA (1 Yıllık Döngü)
                $startDate = new DateTime($c['StartDate']);
                $endDate = clone $startDate;
                $endDate->modify('+1 year'); // 1 yıl sonraki tarih
                $today = new DateTime();
                
                // Kalan gün farkı (Bugün ile Bitiş Tarihi arası)
                $interval = $today->diff($endDate);
                $daysLeft = (int)$interval->format('%r%a');

                // Finansal Hakediş (Yıllık Lisans + Aktif Sporcu Başı)
                $debt = (float)$c['License'] + ((int)$c['Students'] * (float)$c['PerStudent']);
                
                $data['club_breakdown'][] = [
                    'id' => $c['ClubID'],
                    'name' => $c['ClubName'],
                    'students' => $c['Students'],
                    'total' => $debt,
                    'expiry_date' => $endDate->format('d.m.Y'),
                    'days_left' => $daysLeft,
                    'status' => ($daysLeft <= 0) ? 'Expired' : (($daysLeft <= 30) ? 'Warning' : 'Active')
                ];

                if ($daysLeft <= 30) $data['critical_licenses']++;
                $data['total_saas_revenue'] += $debt;
            }
            $data['active_clubs_count'] = count($clubs);

            // 2. Gerçek Giderleri Çek
            $data['real_expenses'] = $this->db->query("SELECT * FROM SystemExpenses ORDER BY ExpenseDate DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data['real_expenses'] as $exp) {
                $data['total_expenses'] += (float)$exp['Amount'];
            }

        } catch (Exception $e) { die("Hata: " . $e->getMessage()); }

        $this->render('system_finance', $data);
    }

    // Gider Kaydetme
    public function storeExpense() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->db->prepare("INSERT INTO SystemExpenses (Title, Category, Amount, Description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['title'], $_POST['category'], $_POST['amount'], $_POST['description']]);
            header("Location: index.php?page=system_finance&success=1");
        }
    }

    // Gider Silme
    public function deleteExpense() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM SystemExpenses WHERE ExpenseID = ?");
            $stmt->execute([$id]);
        }
        header("Location: index.php?page=system_finance&deleted=1");
    }

    private function render($view, $data = []) {
        extract($data); ob_start();
        $path = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($path)) {
            include $path;
        } else {
            die("View bulunamadı: $path");
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}