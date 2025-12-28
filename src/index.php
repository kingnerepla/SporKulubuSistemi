<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/Config/Database.php';

$page = $_GET['page'] ?? 'dashboard';
$publicPages = ['login'];

if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header("Location: index.php?page=login");
    exit;
}

// Dosya kontrolü için yardımcı fonksiyon
function safe_load($controllerName, $methodName) {
    $path = __DIR__ . "/app/Controllers/{$controllerName}.php";
    if (file_exists($path)) {
        require_once $path;
        $controller = new $controllerName();
        $controller->$methodName();
    } else {
        die("Hata: {$controllerName} dosyası bulunamadı!");
    }
}

switch ($page) {
    case 'login':           require_once __DIR__ . '/app/Views/auth/login.php'; break;
    case 'dashboard':       safe_load('DashboardController', 'index'); break;
    case 'clubs':           safe_load('ClubController', 'index'); break;
    case 'club_detail':     safe_load('ClubController', 'detail'); break;
    case 'club_store':      safe_load('ClubController', 'store'); break;
    case 'select_club':     
        $_SESSION['selected_club_id'] = $_GET['id'];
        $_SESSION['selected_club_name'] = urldecode($_GET['name']);
        header("Location: index.php?page=students");
        break;
    case 'students':        safe_load('StudentController', 'index'); break;
    case 'logout':          session_destroy(); header("Location: index.php?page=login"); exit;
    default:                header("Location: index.php?page=dashboard"); break;
}
ob_end_flush();