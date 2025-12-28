<?php
require_once __DIR__ . '/../Config/Database.php';
function safe_load($controllerName, $methodName) {
    $path = __DIR__ . "/app/Controllers/{$controllerName}.php";
    // Bu satırı ekle:
    echo "Sistemin aradığı dosya yolu: " . $path . "<br>"; 
    
    if (file_exists($path)) {
        require_once $path;
        // ... geri kalan kodlar
class StudentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $role = strtolower(trim($_SESSION['role'] ?? 'guest')); 
        
        // 2. SONRA KULLAN
        $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);
    
        // ... geri kalan kodlar ...
    }

    // YENİ ÖĞRENCİ EKLEME SAYFASI
    public function create() {
        $role = strtolower(trim($_SESSION['role'] ?? 'guest')); // Burada da tanımlamalıyız
        $clubId = ($role === 'systemadmin') ? ($_SESSION['selected_club_id'] ?? null) : ($_SESSION['club_id'] ?? null);

        $stmt = $this->db->prepare("SELECT * FROM Groups WHERE ClubID = ?");
        $stmt->execute([$clubId]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('student_add', ['groups' => $groups, 'role' => $role]);
    }

    // KRİTİK NOKTA: Render metodu extract($data) içermeli
    private function render($view, $data = []) {
        // Bu satır dizideki 'role' anahtarını $role değişkenine dönüştürür
        extract($data); 
        
        ob_start();
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}