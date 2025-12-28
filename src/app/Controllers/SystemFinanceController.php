<?php

class SystemFinanceController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        if ($_SESSION['role'] !== 'SystemAdmin') {
            header("Location: index.php?page=dashboard");
            exit;
        }

        try {
            // Kulüplerin toplam ödeme durumlarını çekelim
            // Varsayalım ki 'ClubPayments' diye bir tablon var, yoksa bile hata vermemesi için korumalı yazıyoruz
            $sql = "SELECT c.ClubName, 
                    COALESCE(SUM(p.Amount), 0) as TotalPaid,
                    c.Status 
                    FROM Clubs c 
                    LEFT JOIN ClubPayments p ON c.ClubID = p.ClubID 
                    GROUP BY c.ClubID, c.ClubName, c.Status";
            
            $stmt = $this->db->query($sql);
            $finances = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $finances = []; // Tablo henüz yoksa boş dönsün, sistem patlamasın
        }

        $data = ['finances' => $finances];
        $this->render(__DIR__ . '/../Views/admin/system_finance.php', $data);
    }

    private function render($viewPath, $data = []) {
        extract($data);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}