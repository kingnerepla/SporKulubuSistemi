<?php
class ClubFinanceController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];

        // 1. ÖĞRENCİLERİ ÇEK
        // RemainingSessions (Kalan Ders) sütununa göre sırala
        $sql = "SELECT 
                    s.StudentID, s.FullName, s.StandardSessions, s.PackageFee, s.RemainingSessions, s.LastPaymentDate,
                    g.GroupName,
                    (SELECT SUM(Amount) FROM Payments WHERE StudentID = s.StudentID) as TotalPaid
                FROM Students s
                LEFT JOIN Groups g ON s.GroupID = g.GroupID
                WHERE s.ClubID = ? AND s.IsActive = 1
                ORDER BY s.RemainingSessions ASC"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. DURUM ANALİZİ (Kontör Bazlı)
        foreach ($students as &$st) {
            $rem = (int)$st['RemainingSessions'];

            if ($rem <= 0) {
                // HAKKI BİTMİŞ -> KIRMIZI
                $st['status'] = 'overdue'; 
                $st['status_text'] = 'Bakiye Bitti';
                $st['color'] = 'danger';
            } elseif ($rem <= 2) {
                // AZ KALMIŞ -> SARI
                $st['status'] = 'upcoming'; 
                $st['status_text'] = 'Hak Azaldı (' . $rem . ')';
                $st['color'] = 'warning';
            } else {
                // DURUM İYİ -> YEŞİL
                $st['status'] = 'active'; 
                $st['status_text'] = 'Aktif (' . $rem . ' Ders)';
                $st['color'] = 'success';
            }
        }

        // 3. GENEL KASA DURUMU (Kulüp Gelir/Gider Özeti)
        // Gelirler (Aidat + Diğer)
        $incStmt = $this->db->prepare("SELECT SUM(Amount) FROM Payments WHERE ClubID = ?");
        $incStmt->execute([$clubId]);
        $totalIncome = $incStmt->fetchColumn() ?: 0;

        // Giderler (Expenses Tablosundan)
        $expStmt = $this->db->prepare("SELECT SUM(Amount) FROM Expenses WHERE ClubID = ?");
        $expStmt->execute([$clubId]);
        $totalExpense = $expStmt->fetchColumn() ?: 0;

        // SaaS Maliyeti (Sisteme Ödenecek - Bilgi Amaçlı)
        // Örn: Aktif Öğrenci * 100 TL
        $perStudentFee = 100; // Veritabanından da çekilebilir
        $systemCost = count($students) * $perStudentFee;

        $balance = $totalIncome - $totalExpense;

        $this->render('club_finance', [
            'students' => $students,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'systemCost' => $systemCost
        ]);
    }

    private function render($view, $data = []) {
        if(isset($_SESSION)) $data = array_merge($_SESSION, $data);
        extract($data);
        ob_start();
        $baseDir = __DIR__ . '/../';
        $viewsFolder = is_dir($baseDir . 'Views') ? 'Views' : 'views';
        $viewFile = $baseDir . $viewsFolder . "/admin/{$view}.php";
        if (file_exists($viewFile)) include $viewFile;
        $content = ob_get_clean();
        $layoutPath = $baseDir . $viewsFolder . '/layouts/admin_layout.php';
        if (file_exists($layoutPath)) include $layoutPath; else echo $content;
    }
}