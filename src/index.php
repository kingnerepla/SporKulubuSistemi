<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

if ($page === 'login_submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    
    $db = getDB();
    // En temel sorgu: JOIN falan yok, sadece kullanıcıyı bulalım
    $sql = "SELECT * FROM Users WHERE Email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("DEDEKTİF RAPORU: Veritabanında '$email' adresiyle bir kullanıcı YOK. Yazım hatası veya gizli boşluk olabilir.");
    }

    if (password_verify($pass, $user['PasswordHash'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['name']    = $user['FullName'];
        $_SESSION['role']    = 'SystemAdmin'; // Rolü şimdilik manuel verdik ki girsin
        header("Location: index.php?page=dashboard");
        exit;
    } else {
        die("DEDEKTİF RAPORU: Kullanıcı bulundu ama şifre (Hash) uyuşmuyor. Girilen: $pass, DB'deki Hash: " . $user['PasswordHash']);
    }
}

// Güvenlik: Giriş yapmamışsa login'e at
if (!isset($_SESSION['user_id']) && !in_array($page, ['login', 'login_submit'])) {
    require_once __DIR__ . '/app/Views/auth/login.php';
    exit;
}

// Router
switch ($page) {
    case 'login': require_once __DIR__ . '/app/Views/auth/login.php'; break;
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