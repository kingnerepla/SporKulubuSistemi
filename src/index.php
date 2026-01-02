<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Klasör Yapısı Tespiti
if (is_dir(__DIR__ . '/src/app')) {
    $basePath = __DIR__ . '/src';
} elseif (is_dir(__DIR__ . '/app')) {
    $basePath = __DIR__;
} else {
    die("Kritik Hata: 'app' klasörü bulunamadı.");
}

require_once $basePath . '/app/config/Database.php';

$page = $_GET['page'] ?? 'login';

// 2. Herkese açık sayfalar
$public_pages = ['login', 'admin_login_form', 'parent_login', 'admin_auth', 'parent_auth'];

// 3. Yetki Kontrolü
if (!in_array($page, $public_pages)) {
    if (strpos($page, 'parent_') === 0) {
        if (!isset($_SESSION['parent_logged_in'])) {
            header("Location: index.php?page=parent_login");
            exit;
        }
    } else {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit;
        }
    }
}

/**
 * 4. DİNAMİK YÜKLEYİCİ
 */
function safe_load($controllerName, $methodName) {
    global $basePath;
    $path = $basePath . "/app/Controllers/{$controllerName}.php";
    
    if (file_exists($path)) {
        require_once $path;
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
            } else {
                die("<b>Metot Hatası:</b> {$controllerName} -> {$methodName} bulunamadı.");
            }
        } else {
            die("<b>Sınıf Hatası:</b> {$controllerName} sınıfı bulunamadı.");
        }
    } else {
        die("<b>Dosya Hatası:</b> {$controllerName}.php bulunamadı.<br>Yol: <code>$path</code>");
    }
}

// 5. ROTA YÖNETİMİ
switch ($page) {
    // --- AUTH & DASHBOARD ---
    case 'login':            safe_load('AuthController', 'showSelection'); break;
    case 'admin_login_form': safe_load('AuthController', 'showAdminLogin'); break;
    case 'admin_auth':       safe_load('AuthController', 'login');  break;
    case 'dashboard':        safe_load('DashboardController', 'index'); break;

    // --- SÜPER ADMİN: KULÜP VE SAAS YÖNETİMİ ---
    case 'clubs':            safe_load('ClubController', 'index'); break; 
    case 'select_club':      safe_load('ClubController', 'selectClub'); break;
    case 'clear_selection':  safe_load('ClubController', 'clearSelection'); break;
    case 'club_store':       safe_load('ClubController', 'store'); break;
    case 'update_agreement': safe_load('ClubController', 'updateAgreement'); break;
    case 'packages':         safe_load('ClubController', 'packages'); break; 

    // --- SÜPER ADMİN: MERKEZİ SİSTEM FİNANS ---
    case 'system_finance':   safe_load('SystemFinanceController', 'index'); break;
    case 'store_expense':    safe_load('SystemFinanceController', 'storeExpense'); break;
    case 'delete_expense':   safe_load('SystemFinanceController', 'deleteExpense'); break;

    // --- ÖĞRENCİ İŞLEMLERİ ---
    case 'students':         safe_load('StudentController', 'index'); break;
    case 'student_add':      safe_load('StudentController', 'create'); break;
    case 'student_edit':     safe_load('StudentController', 'edit'); break;
    case 'student_update':   safe_load('StudentController', 'update'); break;
    case 'student_store':    safe_load('StudentController', 'store'); break;
    case 'student_delete':   safe_load('StudentController', 'delete'); break; // Arşive atar (Soft Delete)
    case 'students_archived': safe_load('StudentController', 'archived'); break;
    case 'student_restore':  safe_load('StudentController', 'restore'); break;
    case 'student_destroy':  safe_load('StudentController', 'destroy'); break; // [YENİ EKLENDİ] Tamamen siler

    // --- GRUPLAR VE TAKVİM ---
    case 'groups':           safe_load('GroupController', 'index'); break;
    case 'group_add':        safe_load('GroupController', 'create'); break;
    case 'group_update':     safe_load('GroupController', 'update'); break;
    case 'training_groups':  safe_load('GroupScheduleController', 'trainingGroups'); break;
    case 'generate_sessions': safe_load('GroupScheduleController', 'generateSessions'); break;
    case 'delete_sessions':  safe_load('GroupScheduleController', 'deleteSessions'); break;
    case 'delete_single_session': safe_load('GroupScheduleController', 'deleteSingleSession'); break;

    // --- YOKLAMA ---
    case 'attendance':       safe_load('AttendanceController', 'index'); break;
    case 'attendance_save':  safe_load('AttendanceController', 'save'); break;
    case 'attendance_report': safe_load('AttendanceController', 'report'); break;

    // --- ANTRENÖR ---
    case 'coaches':          safe_load('CoachController', 'index'); break;
    
    // --- FİNANS & ÖDEMELER ---
    case 'club_finance':     safe_load('ClubFinanceController', 'index'); break;
    
    // Payments için senin eklediğin özel debug bloğu
    case 'payments':
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        require_once $basePath . '/app/Controllers/PaymentController.php';
        $controller = new PaymentController();
        $controller->index();
        break;
        
    case 'payment_store':    safe_load('PaymentController', 'store'); break; 
    case 'payment_delete':   safe_load('PaymentController', 'delete'); break;

    // --- VELİ ---
    case 'parent_dashboard':  safe_load('ParentController', 'dashboard'); break;
    case 'parent_attendance': safe_load('ParentController', 'attendance'); break;
    case 'parent_payments':   safe_load('ParentController', 'payments'); break;
    
    case 'expenses':       safe_load('ExpensesController', 'index'); break;
    case 'expense_store':  safe_load('ExpensesController', 'store'); break;
    case 'expense_delete': safe_load('ExpensesController', 'delete'); break;
    // --- PROFİL & SİSTEM ---
    case 'profile':          safe_load('ProfileController', 'index'); break;
    case 'logout':           
        session_destroy(); 
        header("Location: index.php?page=login"); 
        exit;
    
    default:                
        header("Location: index.php?page=dashboard");
        break;
}

if (ob_get_level() > 0) ob_end_flush();