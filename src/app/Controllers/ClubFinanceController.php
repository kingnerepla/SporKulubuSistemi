<?php

class ClubFinanceController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // --- GENEL FİNANS LİSTESİ VE ÖZET ---
    public function index() {
        $role = strtolower(trim($_SESSION['role'] ?? 'guest'));
        $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);

        if (!$clubId) {
            die("Hata: İşlem yapmak için bir kulüp seçmelisiniz.");
        }

        // 1. Öğrenci Bakiyelerini Getir (Borçlar - Tahsilatlar)
        // SQL Server için CASE WHEN yapısı kullanılmıştır
        $sql = "SELECT s.StudentID, s.FullName, s.MonthlyFee, g.GroupName,
                (SELECT SUM(CASE WHEN Type = 'Debt' THEN Amount ELSE -Amount END) 
                 FROM StudentPayments WHERE StudentID = s.StudentID) as Balance
                FROM Students s
                LEFT JOIN Groups g ON s.GroupID = g.GroupID
                WHERE s.ClubID = ? AND s.IsActive = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Kasa Özet İstatistikleri (Toplam Gelir ve Toplam Gider)
        $sqlIncome = "SELECT SUM(Amount) as Total FROM StudentPayments WHERE ClubID = ? AND Type = 'Collection'";
        $sqlExpense = "SELECT SUM(Amount) as Total FROM ClubExpenses WHERE ClubID = ?";
        
        $stmtInc = $this->db->prepare($sqlIncome); 
        $stmtInc->execute([$clubId]);
        $stmtExp = $this->db->prepare($sqlExpense); 
        $stmtExp->execute([$clubId]);

        $stats = [
            'income' => $stmtInc->fetch()['Total'] ?? 0,
            'expense' => $stmtExp->fetch()['Total'] ?? 0
        ];

        $this->render('finance_list', ['summary' => $summary, 'stats' => $stats]);
    }
    public function sessions() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        $groupId = $_GET['group_id'] ?? null;
        $range = $_GET['range'] ?? 'week';
    
        $sql = "SELECT ts.*, g.GroupName 
                FROM TrainingSessions ts 
                JOIN Groups g ON ts.GroupID = g.GroupID 
                WHERE ts.ClubID = ?";
        
        $params = [$clubId];
    
        if ($groupId) {
            $sql .= " AND ts.GroupID = ?";
            $params[] = $groupId;
        }
    
        if ($range == 'today') {
            $sql .= " AND ts.TrainingDate = CAST(GETDATE() AS DATE)";
        } elseif ($range == 'week') {
            // Bu haftaki dersler
            $sql .= " AND ts.TrainingDate BETWEEN CAST(GETDATE() AS DATE) AND CAST(DATEADD(day, 7, GETDATE()) AS DATE)";
        }
    
        $sql .= " ORDER BY ts.TrainingDate ASC, ts.StartTime ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Grupları filtreleme için çek
        $stmtG = $this->db->prepare("SELECT GroupID, GroupName FROM Groups WHERE ClubID = ?");
        $stmtG->execute([$clubId]);
        $groups = $stmtG->fetchAll(PDO::FETCH_ASSOC);
    
        $this->render('training_sessions_list', ['sessions' => $sessions, 'groups' => $groups]);
    }
    // --- TAHSİLAT İŞLEMİ (ÖDEME ALMA) ---
    public function collect() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'];
            $amount = $_POST['amount'];
            $method = $_POST['method'];
            $desc = $_POST['description'];
            $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
            $adminId = $_SESSION['user_id'];

            try {
                $sql = "INSERT INTO StudentPayments (StudentID, ClubID, Amount, Type, PaymentMethod, Description, CreatedBy) 
                        VALUES (?, ?, ?, 'Collection', ?, ?, ?)";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$studentId, $clubId, $amount, $method, $desc, $adminId]);

                header("Location: index.php?page=finance&success=1");
                exit;
            } catch (Exception $e) {
                die("Tahsilat Hatası: " . $e->getMessage());
            }
        }
    }

    // --- TOPLU AİDAT BORÇLANDIRMA ---
    public function bulkDebt() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        $adminId = $_SESSION['user_id'];
        $monthName = date('m/Y');

        try {
            // Aktif öğrencilerin MonthlyFee miktarını 'Debt' olarak kaydet
            $sql = "INSERT INTO StudentPayments (StudentID, ClubID, Amount, Type, Description, CreatedBy)
                    SELECT StudentID, ClubID, MonthlyFee, 'Debt', ?, ?
                    FROM Students 
                    WHERE ClubID = ? AND IsActive = 1 AND MonthlyFee > 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$monthName . " Aidat Tahakkuku", $adminId, $clubId]);

            header("Location: index.php?page=finance&success=bulk");
            exit;
        } catch (Exception $e) {
            die("Toplu Borçlandırma Hatası: " . $e->getMessage());
        }
    }

    // --- GİDER YÖNETİMİ ---
    public function expenses() {
        $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
        
        $stmt = $this->db->prepare("SELECT * FROM ClubExpenses WHERE ClubID = ? ORDER BY ExpenseDate DESC");
        $stmt->execute([$clubId]);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('expense_list', ['expenses' => $expenses]);
    }

    public function addExpense() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clubId = $_SESSION['club_id'] ?? $_SESSION['selected_club_id'];
            $category = $_POST['category'];
            $amount = $_POST['amount'];
            $desc = $_POST['description'];
            $adminId = $_SESSION['user_id'];

            try {
                $sql = "INSERT INTO ClubExpenses (ClubID, Category, Amount, Description, CreatedBy) 
                        VALUES (?, ?, ?, ?, ?)";
                $this->db->prepare($sql)->execute([$clubId, $category, $amount, $desc, $adminId]);

                header("Location: index.php?page=expenses&success=1");
                exit;
            } catch (Exception $e) {
                die("Gider Kayıt Hatası: " . $e->getMessage());
            }
        }
    }

    // Render Yardımcı Fonksiyonu
    private function render($view, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}