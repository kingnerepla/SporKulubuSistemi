<?php
class ExpensesController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;
        $role = $_SESSION['role'] ?? 'guest';

        // 1. Giderleri Çek (Yetkiye Göre Ayrıştırma)
        if ($role == 'systemadmin' && !$clubId) {
            // Sistem Yöneticisi (Kulüp seçmemişse) -> MERKEZİ GİDERLER (ClubID IS NULL)
            $sql = "SELECT * FROM Expenses WHERE ClubID IS NULL ORDER BY ExpenseDate DESC";
            $params = [];
            $pageTitle = "Merkezi Sistem Giderleri";
        } else {
            // Kulüp Yöneticisi veya Kulüp Seçmiş Admin -> KULÜP GİDERLERİ
            $sql = "SELECT * FROM Expenses WHERE ClubID = ? ORDER BY ExpenseDate DESC";
            $params = [$clubId];
            $pageTitle = "Kulüp Gider Yönetimi";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. İstatistik (Toplam Gider)
        $totalExpense = 0;
        foreach ($expenses as $ex) {
            $totalExpense += $ex['Amount'];
        }

        $this->render('expenses', [
            'expenses' => $expenses,
            'totalExpense' => $totalExpense,
            'pageTitle' => $pageTitle
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;
                $role = $_SESSION['role'] ?? 'guest';

                // Eğer Sistem Admini ve Kulüp seçili değilse -> ClubID NULL gider (Sistem Gideri)
                if ($role == 'systemadmin' && empty($clubId)) {
                    $clubId = null; 
                }

                $title = $_POST['title'];
                $amount = $_POST['amount'];
                $category = $_POST['category'];
                $date = $_POST['expense_date'];
                $createdBy = $_SESSION['user_id'];

                $stmt = $this->db->prepare("INSERT INTO Expenses (ClubID, Title, Amount, Category, ExpenseDate, CreatedBy) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$clubId, $title, $amount, $category, $date, $createdBy]);

                $_SESSION['success_message'] = "Gider kalemi eklendi.";
                header("Location: index.php?page=expenses");
                exit;

            } catch (Exception $e) {
                die("Kayıt Hatası: " . $e->getMessage());
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->db->prepare("DELETE FROM Expenses WHERE ExpenseID = ?")->execute([$id]);
            $_SESSION['success_message'] = "Gider silindi.";
        }
        header("Location: index.php?page=expenses");
        exit;
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