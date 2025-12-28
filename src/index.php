<?php
ob_start();
session_start();

// Hataları ekrana bas ki ne olduğunu görelim
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/Config/Database.php';

// Veritabanı Bağlantısı
function getDB() {
    $host = 'host.docker.internal'; 
    $db_name = 'ClubSystemDB';
    $username = 'sa'; 
    $password = 'Ab_kulup_248'; 
    try {
        $dsn = "sqlsrv:Server=$host;Database=$db_name;TrustServerCertificate=yes;Encrypt=no;LoginTimeout=30";
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) { die("Bağlantı Hatası: " . $e->getMessage()); }
}

$page = $_GET['page'] ?? 'dashboard';

// ---------------------------------------------------------
// 1. ADIM: POST VERİSİ VAR MI? (Giriş Denemesi)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    
    $db = getDB();
    $sql = "SELECT u.*, r.RoleName FROM Users u 
            LEFT JOIN Roles r ON u.RoleID = r.RoleID 
            WHERE LTRIM(RTRIM(u.Email)) = ?";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($pass, trim($user['PasswordHash']))) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['name']    = $user['FullName'];
        $_SESSION['role']    = $user['RoleName'] ?? 'Guest';
        
        header("Location: index.php?page=dashboard");
        exit;
    } else {
        // Şifre yanlışsa hata verip login'de tut
        echo "<script>alert('Giriş Başarısız! Email veya Şifre hatalı.'); window.location.href='index.php?page=login';</script>";
        exit;
    }
}

// ---------------------------------------------------------
// 2. ADIM: GÜVENLİK KONTROLÜ (Kritik Nokta!)
// ---------------------------------------------------------
// Eğer giriş yapmamışsa ve zaten login sayfasında değilse login'e at
if (!isset($_SESSION['user_id']) && $page !== 'login') {
    header("Location: index.php?page=login");
    exit;
}

// ---------------------------------------------------------
// 3. ADIM: ROUTER (Sayfaları Çağırma)
// ---------------------------------------------------------
switch ($page) {
    case 'login':
        require_once __DIR__ . '/app/Views/auth/login.php';
        break;
    case 'dashboard':
        require_once __DIR__ . '/app/Controllers/DashboardController.php';
        (new DashboardController())->index();
        break;
    case 'logout':
        session_destroy();
        header("Location: index.php?page=login");
        exit;
    default:
        header("Location: index.php?page=dashboard");
        break;
}

ob_end_flush();