<?php
ob_start();
session_start();

// Hata Raporlama - Sorun varsa ekranda beyaz yazı olarak görünecek
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Veritabanı Bağlantısı (Senin bilgilerini sabitledim)
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
    } catch(PDOException $e) { 
        die("Veritabanı Bağlantı Hatası: " . $e->getMessage()); 
    }
}

$page = $_GET['page'] ?? 'dashboard';

// =================================================================
// 2. GİRİŞ İŞLEMİ (POST GELDİYSE DİREKT ÇALIŞ)
// =================================================================
if ($page === 'login_submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    
    $db = getDB();
    // LEFT JOIN ile Rolü çekiyoruz, kullanıcıyı buluyoruz
    $sql = "SELECT u.*, r.RoleName FROM Users u 
            LEFT JOIN Roles r ON u.RoleID = r.RoleID 
            WHERE u.Email = ?";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($pass, $user['PasswordHash'])) {
        // Oturum Bilgilerini Kaydet
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['name']    = $user['FullName'];
        $_SESSION['role']    = $user['RoleName'] ?? 'Guest';
        $_SESSION['club_id'] = $user['ClubID'];

        // Giriş başarılı, dashboard'a yönlendir
        header("Location: index.php?page=dashboard");
        exit;
    } else {
        echo "<script>alert('Giriş Başarısız! Bilgileri kontrol edin.'); window.location.href='index.php?page=login';</script>";
        exit;
    }
}

// =================================================================
// 3. GÜVENLİK KONTROLÜ
// =================================================================
$publicPages = ['login', 'login_submit'];
if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    // Oturum yoksa zorla login sayfasına gönder
    require_once __DIR__ . '/app/Views/auth/login.php';
    exit;
}

// =================================================================
// 4. ROUTER (DOSYA YOLLARINI KONTROL EDEREK ÇAĞIR)
// =================================================================
require_once __DIR__ . '/app/Config/Database.php';

switch ($page) {
    case 'login':
        require_once __DIR__ . '/app/Views/auth/login.php';
        break;

    case 'dashboard':
        require_once __DIR__ . '/app/Controllers/DashboardController.php';
        (new DashboardController())->index();
        break;

    case 'students':
        require_once __DIR__ . '/app/Controllers/StudentController.php';
        (new StudentController())->index();
        break;

    case 'student_store':
        require_once __DIR__ . '/app/Controllers/StudentController.php';
        (new StudentController())->store();
        break;

    case 'clubs':
        require_once __DIR__ . '/app/Controllers/ClubController.php';
        (new ClubController())->index();
        break;

    case 'club_store':
        require_once __DIR__ . '/app/Controllers/ClubController.php';
        (new ClubController())->store();
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