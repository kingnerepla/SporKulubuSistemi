<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/Config/Database.php';

$page = $_GET['page'] ?? 'dashboard';
$publicPages = ['login', 'auth_check'];

// Giriş kontrolü
if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header("Location: index.php?page=login");
    exit;
}

function safe_load($controllerName, $methodName) {
    $path = __DIR__ . "/app/Controllers/{$controllerName}.php";
    if (file_exists($path)) {
        require_once $path;
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            $controller->$methodName();
        } else {
            die("Hata: " . $controllerName . " sınıfı bulunamadı.");
        }
    } else {
        die("Hata: " . $controllerName . " dosyası bulunamadı.");
    }
}

switch ($page) {
    case 'login':           
        require_once __DIR__ . '/app/Views/auth/login.php'; 
        break;
        
    case 'auth_check':      
        safe_load('AuthController', 'login'); 
        break;
        
    case 'dashboard':       
        safe_load('DashboardController', 'index'); 
        break;

    case 'clubs':           
        safe_load('ClubController', 'index'); 
        break;
    case 'system_finance':  
        safe_load('SystemFinanceController', 'index'); 
        break;  
    case 'club_detail':     
        safe_load('ClubController', 'detail'); 
        break;
        
    case 'system_finance':  
        safe_load('SystemFinanceController', 'index'); 
        break;

    // KULÜP SEÇİM SİHİRBAZI (Süper Admin'i Kulüp Admin moduna sokar)
    case 'select_club':     
        if (isset($_GET['id'])) {
            $_SESSION['selected_club_id'] = $_GET['id'];
            $_SESSION['selected_club_name'] = urldecode($_GET['name'] ?? 'Kulüp');
            header("Location: index.php?page=dashboard");
        } else {
            header("Location: index.php?page=clubs");
        }
        exit;
        break;

    // KULÜP OPERASYON ROTALARI
    case 'students':        
        safe_load('StudentController', 'index'); 
        break;
        
    case 'student_store':   
        safe_load('StudentController', 'store'); 
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