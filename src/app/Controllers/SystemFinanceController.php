<?php

class SystemFinanceController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') {
            header("Location: index.php?page=dashboard"); exit;
        }

        // Filtreleme için tarihleri al (Yeni eklendi)
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate   = $_GET['end_date'] ?? date('Y-m-d');

        $data = [
            'total_saas_revenue' => 0, // Beklenen Hakediş
            'actual_collections' => 0, // 🔥 Gerçek Tahsilat (Yeni)
            'club_breakdown' => [],
            'active_clubs_count' => 0,
            'real_expenses' => [],
            'total_expenses' => 0,
            'critical_licenses' => 0,
            'saas_payments' => [], // 🔥 Filtrelenmiş Tahsilat Listesi (Yeni)
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        try {
            // 1. Kulüp Hakedişlerini ve Lisans Sürelerini Çek
            $sqlClubs = "SELECT c.ClubID, c.ClubName, 
                         COALESCE(c.AnnualLicenseFee, 5000) as License, 
                         COALESCE(c.MonthlyPerStudentFee, 100) as PerStudent,
                         COALESCE(c.LicenseStartDate, c.CreatedAt) as StartDate,
                         (SELECT COUNT(*) FROM Students s WHERE s.ClubID = c.ClubID AND s.IsActive = 1) as Students
                         FROM Clubs c";
            
            $clubs = $this->db->query($sqlClubs)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clubs as $c) {
                $startDateObj = new DateTime($c['StartDate']);
                $endDateObj = clone $startDateObj;
                $endDateObj->modify('+1 year');
                $today = new DateTime();
                
                $interval = $today->diff($endDateObj);
                $daysLeft = (int)$interval->format('%r%a');

                $debt = (float)$c['License'] + ((int)$c['Students'] * (float)$c['PerStudent']);
                
                $data['club_breakdown'][] = [
                    'id' => $c['ClubID'],
                    'name' => $c['ClubName'],
                    'students' => $c['Students'],
                    'total' => $debt,
                    'expiry_date' => $endDateObj->format('d.m.Y'),
                    'days_left' => $daysLeft,
                    'status' => ($daysLeft <= 0) ? 'Expired' : (($daysLeft <= 30) ? 'Warning' : 'Active')
                ];

                if ($daysLeft <= 30) $data['critical_licenses']++;
                $data['total_saas_revenue'] += $debt;
            }
            $data['active_clubs_count'] = count($clubs);

            // 🔥 2. GERÇEK SAAS TAHSİLATLARINI FİLTRELEYEREK ÇEK (Yeni)
            $sqlPayments = "SELECT sp.*, c.ClubName 
                            FROM SaasPayments sp 
                            JOIN Clubs c ON sp.ClubID = c.ClubID 
                            WHERE CAST(sp.PaymentDate AS DATE) BETWEEN ? AND ?
                            ORDER BY sp.PaymentDate DESC";
            $stmtPay = $this->db->prepare($sqlPayments);
            $stmtPay->execute([$startDate, $endDate]);
            $data['saas_payments'] = $stmtPay->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($data['saas_payments'] as $pay) {
                $data['actual_collections'] += (float)$pay['Amount'];
            }

            // 3. Gerçek Giderleri Çek (SystemExpenses)
            $data['real_expenses'] = $this->db->query("SELECT * FROM SystemExpenses ORDER BY ExpenseDate DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data['real_expenses'] as $exp) {
                $data['total_expenses'] += (float)$exp['Amount'];
            }

        } catch (Exception $e) { die("Hata: " . $e->getMessage()); }

        $this->render('system_finance', $data);
    }
    public function addSaasPayment() {
        // Yetki Kontrolü
        if (($_SESSION['role_id'] ?? 0) != 1) { die("Yetkisiz erişim."); }
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clubId      = $_POST['club_id'];
            $amount      = $_POST['amount'];
            $description = $_POST['description'] ?? 'SaaS Hizmet Bedeli Tahsilatı';
            $paymentDate = date('Y-m-d H:i:s');
    
            try {
                $this->db->beginTransaction();
    
                // 1. ADIM: SaasPayments tablosuna ekle (Süper Admin'in Kazancı)
                $sqlSaas = "INSERT INTO SaasPayments (ClubID, Amount, PaymentDate, Description) VALUES (?, ?, ?, ?)";
                $this->db->prepare($sqlSaas)->execute([$clubId, $amount, $paymentDate, $description]);
    
                // 2. ADIM: Kulübün kendi giderlerine işle (Otomatik Gider Kaydı)
                // Bu ödeme kulüp için bir 'Dış Gider'dir.
                // Not: Eğer giderleriniz 'Payments' tablosunda eksi tutar veya farklı bir 'Expenses' tablosundaysa oraya yönlendirin.
                // Örnek: 'Expenses' tablosuna kayıt atıyoruz:
                $sqlExpense = "INSERT INTO Expenses (ClubID, Category, Title, Amount, ExpenseDate, Description) 
                               VALUES (?, 'Sistem', 'SaaS Kullanım Bedeli', ?, ?, ?)";
                $this->db->prepare($sqlExpense)->execute([$clubId, $amount, $paymentDate, $description]);
    
                $this->db->commit();
                $_SESSION['success_message'] = "Tahsilat başarıyla yapıldı ve kulüp giderlerine işlendi.";
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error_message'] = "Hata oluştu: " . $e->getMessage();
            }
            
            header("Location: index.php?page=dashboard");
            exit;
        }
    }
    public function storeExpense() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->db->prepare("INSERT INTO SystemExpenses (Title, Category, Amount, Description, ExpenseDate) VALUES (?, ?, ?, ?, GETDATE())");
            $stmt->execute([$_POST['title'], $_POST['category'], $_POST['amount'], $_POST['description']]);
            header("Location: index.php?page=system_finance&success=1");
            exit;
        }
    }

    public function deleteExpense() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM SystemExpenses WHERE ExpenseID = ?");
            $stmt->execute([$id]);
        }
        header("Location: index.php?page=system_finance&deleted=1");
        exit;
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