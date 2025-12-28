<?php
ob_start();
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/Config/Database.php';

// Veritabanı bağlantısı
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

// =================================================================
// 1. GİRİŞ İŞLEMİ (GÜVENLİK KONTROLÜNDEN ÖNCE OLMALI)
// =================================================================
if ($page === 'login_submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    
    $db = getDB();
    // LEFT JOIN ile Rolleri de çekiyoruz (Daha önce konuştuğumuz sinsi hata)
    $sql = "SELECT u.*, r.RoleName FROM Users u 
            LEFT JOIN Roles r ON u.RoleID = r.RoleID 
            WHERE u.Email = ?";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($pass, $user['PasswordHash'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['name'] = $user['FullName'];
        $_SESSION['role'] = $user['RoleName'] ?? 'Guest';
        $_SESSION['club_id'] = $user['ClubID'];

        header("Location: index.php?page=dashboard");
        exit;
    } else {
        echo "<script>alert('Giriş Başarısız! E-posta veya şifre yanlış.'); window.location.href='index.php?page=login';</script>";
        exit;
    }
}

// =================================================================
// 2. GÜVENLİK KONTROLÜ (Login ve Submit hariç tutuldu)
// =================================================================
$publicPages = ['login', 'login_submit'];
if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header("Location: index.php?page=login");
    exit;
}

// =================================================================
// 3. ROUTER (SAYFA YÖNLENDİRMELERİ)
// =================================================================
switch ($page) {
    case 'login': require_once __DIR__ . '/app/Views/auth/login.php'; break;
    case 'logout':
        session_destroy();
        header("Location: index.php?page=login");
        exit;
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
    default:
        header("Location: index.php?page=dashboard");
        break;
}

ob_end_flush();