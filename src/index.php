<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_dir(__DIR__ . '/src/app')) {
    $basePath = __DIR__ . '/src';
} elseif (is_dir(__DIR__ . '/app')) {
    $basePath = __DIR__;
} else {
    die("Kritik Hata: 'app' klasörü ne ana dizinde ne de 'src' içinde bulunabildi.");
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
        // Hata raporlamasını tam yol vererek yapıyoruz
        die("<b>Dosya Hatası:</b> {$controllerName}.php bulunamadı.<br>Sistemin aradığı tam yol: <code>$path</code>");
    }
}

// 5. ROTA YÖNETİMİ
// index.php içindeki switch ($page) bloğunu bu şekilde güncelle:

    switch ($page) {
        // --- AUTH & GİRİŞ ---
        case 'login':            safe_load('AuthController', 'showSelection'); break;
        case 'admin_login_form': safe_load('AuthController', 'showAdminLogin'); break;
        case 'admin_auth':       safe_load('AuthController', 'login');  break;
        // --- DASHBOARD ---
        case 'dashboard':        safe_load('DashboardController', 'index'); break;

        // --- YOKLAMA VE KATILIM (AttendanceController) ---
        case 'attendance':      safe_load('AttendanceController', 'index'); break;
        case 'attendance_save': safe_load('AttendanceController', 'save'); break;
        case 'delete_sessions': safe_load('GroupScheduleController', 'deleteSessions'); break;
        case 'delete_single_session': safe_load('GroupScheduleController', 'deleteSingleSession'); break;
        // --- KULÜP YÖNETİMİ (ClubController) ---
        case 'club_management':  safe_load('ClubController', 'index'); break;
        case 'club_details':     safe_load('ClubController', 'details'); break;
        case 'club_finance':  safe_load('ClubFinanceController', 'index'); break;
        case 'group_schedule':      safe_load('GroupScheduleController', 'edit'); break;
        case 'group_schedule_save': safe_load('GroupScheduleController', 'save'); break;
        case 'group_schedule_delete': safe_load('GroupScheduleController', 'delete'); break;
        case 'save_schedule':    safe_load('GroupScheduleController', 'store'); break;
        case 'add_schedule':     safe_load('GroupScheduleController', 'create'); break; 
        case 'save_program':     safe_load('GroupScheduleController', 'save'); break;
        // --- FİNANS VE ÖDEMELER (SystemFinance & Payment) ---
        case 'system_finance':  
            safe_load('SystemFinanceController', 'index'); 
            break;  
        case 'payments':         safe_load('PaymentController', 'index'); break;
        case 'payment_add':      safe_load('PaymentController', 'create'); break;
        case 'clubs':           safe_load('ClubController', 'index'); break;

            // --- GRUPLAR VE TAKVİM (Group & GroupSchedule) ---
        case 'groups':           safe_load('GroupController', 'index'); break;
        case 'group_add':        safe_load('GroupController', 'create'); break;
        case 'training_groups':  safe_load('GroupScheduleController', 'trainingGroups'); break;
        case 'group_calendar':   safe_load('GroupScheduleController', 'groupCalendar'); break;
            
        // --- ÖĞRENCİ VE SPORCU İŞLEMLERİ (StudentController) ---
        case 'students':         safe_load('StudentController', 'index'); break;
        case 'student_add':      safe_load('StudentController', 'create'); break;
        case 'student_edit':     safe_load('StudentController', 'edit'); break;
        case 'student_detail':   safe_load('StudentController', 'view'); break;
        case 'student_store': safe_load('StudentController', 'store'); break;
        case 'student_delete': safe_load('StudentController', 'delete'); break;
        // --- VELİ PANELİ ---
        case 'parent_dashboard': safe_load('ParentController', 'dashboard'); break;
    // Antrenmanları toplu oluşturan asıl işlem rotası
        case 'generate_sessions': safe_load('GroupScheduleController', 'generateSessions'); break;
        // --- PROFİL VE KULLANICI YÖNETİMİ ---
        case 'profile':          safe_load('ProfileController', 'index'); break;
        case 'profile_update':   safe_load('ProfileController', 'update'); break;
        case 'user_management':  safe_load('UserController', 'index'); break;
        // --- EKSİK ROTALAR: GRUPLAR, PROGRAM VE YOKLAMA ---
        
        // Gruplar ve Dersler (GroupController)
        case 'groups':           safe_load('GroupController', 'index'); break;
        case 'lessons':          safe_load('GroupController', 'lessons'); break; // Eğer dersler ayrı bir metodsa// index.php içindeki switch($page) bloğuna ekle:
        case 'delete_single_session': safe_load('GroupScheduleController', 'deleteSingleSession'); break;
        case 'parent_login':     safe_load('ParentController', 'loginPage'); break;
        // Program/Takvim Butonu (GroupScheduleController)
        case 'schedule':         safe_load('GroupScheduleController', 'index'); break;
        case 'program':          safe_load('GroupScheduleController', 'trainingGroups'); break;
        
        // Günlük Yoklama (AttendanceController)
  
        case 'daily_attendance': safe_load('AttendanceController', 'index'); break;
        case 'take_attendance':  safe_load('AttendanceController', 'take'); break; // Alternatif link ismi için 
        // --- SİSTEM ÇIKIŞ ---
        case 'logout':           
            session_destroy(); 
            header("Location: index.php?page=login"); 
            exit;
    
        // Tanımsız tüm sayfalar Dashboard'a döner
        default:                
            header("Location: index.php?page=dashboard");
            break;
    }

if (ob_get_level() > 0) ob_end_flush();