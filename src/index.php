<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/Config/Database.php';

$page = $_GET['page'] ?? 'dashboard';
$publicPages = ['login', 'auth_check'];

// 1. Giriş kontrolü
if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    header("Location: index.php?page=login");
    exit;
}

// 2. Dinamik Controller Yükleyici
function safe_load($controllerName, $methodName) {
    // 1. Yol Tanımlama
    $path = __DIR__ . "/app/Controllers/{$controllerName}.php";
    if (file_exists($path)) {
        require_once $path;
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
            } else {
                die("Hata: " . $controllerName . " sınıfı içinde " . $methodName . " metodu bulunamadı.");
            }
        } else {
            die("Hata: " . $controllerName . " sınıfı bulunamadı.");
        }
    } else {
        die("Hata: " . $controllerName . " dosyası bulunamadı. Yol: " . $path);
    }
}

// 3. Rota Yönetimi (Switch)
switch ($page) {
    // --- AUTH ROTALARI ---
    case 'login':           
        require_once __DIR__ . '/app/Views/auth/login.php'; 
        break;
        
    case 'auth_check':      
        safe_load('AuthController', 'login'); 
        break;

    case 'logout':          
        session_destroy(); 
        header("Location: index.php?page=login"); 
        exit;

    // --- GENEL PANEL ---
    case 'dashboard':       
        safe_load('DashboardController', 'index'); 
        break;

    // --- SİSTEM ADMİN (SÜPER ADMİN) ROTALARI ---
    case 'clubs':           
        safe_load('ClubController', 'index'); 
        break;

    case 'club_detail':     
        safe_load('ClubController', 'detail'); 
        break;

    case 'system_finance':  
        safe_load('SystemFinanceController', 'index'); 
        break;

    case 'select_club':     
        if (isset($_GET['id'])) {
            $_SESSION['selected_club_id'] = $_GET['id'];
            $_SESSION['selected_club_name'] = urldecode($_GET['name'] ?? 'Kulüp');
            header("Location: index.php?page=dashboard");
        } else {
            header("Location: index.php?page=clubs");
        }
        exit;

    // --- KULÜP ADMİN OPERASYONLARI ---
    case 'students':      
        safe_load('StudentController', 'index'); 
        break;

    case 'student_add':   
        safe_load('StudentController', 'create'); 
        break;

    case 'student_store': 
        safe_load('StudentController', 'store'); 
        break;

    case 'groups':        
        safe_load('GroupController', 'index'); 
        break;

    case 'group_store':   
        safe_load('GroupController', 'store'); 
        break;

    case 'club_finance':  
        safe_load('ClubFinanceController', 'index'); 
        break;
    case 'student_edit':   safe_load('StudentController', 'edit'); break;
    case 'student_update': safe_load('StudentController', 'update'); break;
    case 'student_delete': safe_load('StudentController', 'delete'); break;
    case 'finance':         safe_load('FinanceController', 'index'); break;
    case 'finance_collect': safe_load('FinanceController', 'collect'); break;
    case 'finance_bulk':    safe_load('FinanceController', 'bulkDebt'); break;
    case 'expenses':        safe_load('FinanceController', 'expenses'); break;
    case 'expense_add':     safe_load('FinanceController', 'addExpense'); break;
    case 'attendance':      safe_load('AttendanceController', 'index'); break;
    case 'attendance_save': safe_load('AttendanceController', 'save'); break;
    case 'group_schedule':      safe_load('GroupScheduleController', 'edit'); break;
    case 'group_schedule_save': safe_load('GroupScheduleController', 'save'); break;
    case 'generate_sessions':   safe_load('GroupScheduleController', 'generateSessions'); break;
    // --- VARSAYILAN ---
    default:                
        header("Location: index.php?page=dashboard"); 
        break;
}

ob_end_flush();