<?php
class ExpensesController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // Gider Listesi ve Ekleme Formu
    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        $role = $_SESSION['role']; // 'systemadmin' veya 'clubadmin'

        // 1. Giderleri Çek
        // Eğer SystemAdmin ise ve kulüp seçmediyse -> Kendi giderleri (ClubID IS NULL)
        // Eğer Kulüp içindeyse -> O kulübün giderleri (ClubID = ?)
        
        if ($role == 'systemadmin' && !isset($_SESSION['selected_club_id'])) {
            // Sistem Giderleri
            $sql = "SELECT * FROM Expenses WHERE ClubID IS NULL ORDER BY ExpenseDate DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $title = "Merkezi Sistem Giderleri";
        } else {
            // Kulüp Giderleri
            $sql = "SELECT * FROM Expenses WHERE ClubID = ? ORDER BY ExpenseDate DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
            $title = "Kulüp Gider Yönetimi";
        }
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // İstatistikler
        $totalExpense = array_sum(array_column($expenses, 'Amount'));

        $this->render('expenses', [
            'expenses' => $expenses,
            'totalExpense' => $totalExpense,
            'pageTitle' => $title
        ]);
    }

    // Gider Kaydet
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                $role = $_SESSION['role'];

                // Eğer Sistem Admini kendi paneline ekliyorsa ClubID NULL olmalı
                if ($role == 'systemadmin' && !isset($_SESSION['selected_club_id'])) {
                    $clubId = null; 
                }

                $title = $_POST['title'];
                $amount = $_POST['amount'];
                $category = $_POST['category'];
                $date = $_POST['expense_date'];
                $createdBy = $_SESSION['user_id'];

                $stmt = $this->db->prepare("INSERT INTO Expenses (ClubID, Title, Amount, Category, ExpenseDate, CreatedBy) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$clubId, $title, $amount, $category, $date, $createdBy]);

                $_SESSION['success_message'] = "Gider başarıyla kaydedildi.";
                
                // Nereden geldiyse oraya dön (Burası önemli, çünkü rotalar farklı olabilir)
                header("Location: index.php?page=expenses"); 
                exit;

            } catch (Exception $e) {
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // Gider Sil
    public function delete() {
        $id = $_GET['id'];
        $this->db->prepare("DELETE FROM Expenses WHERE ExpenseID = ?")->execute([$id]);
        $_SESSION['success_message'] = "Kayıt silindi.";
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