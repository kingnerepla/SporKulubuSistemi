<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** * 1. YOL TESPİTİ (Hata aldığın yer burası)
 * index.php ana dizindeyse src/app'ye, src içindeyse app'ye bakar.
 */
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
switch ($page) {
    case 'login':            safe_load('AuthController', 'showSelection'); break;
    case 'admin_login_form': safe_load('AuthController', 'showAdminLogin'); break;
    case 'parent_login':     safe_load('ParentController', 'loginPage'); break;
    case 'admin_auth':       safe_load('AuthController', 'login');  break;
    case 'parent_auth':      safe_load('ParentController', 'authenticate'); break;
    
    // --- BU LİNKLER ARTIK ÇALIŞACAK ---
    case 'club_management':  safe_load('AdminController', 'manageClubs'); break;
    case 'system_finance':   safe_load('AdminController', 'systemFinance'); break;
    case 'club_details':     safe_load('AdminController', 'clubDetails'); break;
    
    case 'dashboard':        safe_load('DashboardController', 'index'); break;
    case 'parent_dashboard': safe_load('ParentController', 'dashboard'); break;
    case 'profile':          safe_load('ProfileController', 'index'); break;
    case 'profile_update':   safe_load('ProfileController', 'update'); break;
    case 'training_groups':  safe_load('GroupScheduleController', 'trainingGroups'); break;
    case 'group_calendar':   safe_load('GroupScheduleController', 'groupCalendar'); break;

    case 'logout':           
        session_destroy(); 
        header("Location: index.php?page=login"); 
        exit;

    default:                
        header("Location: index.php?page=dashboard");
        break;
}

if (ob_get_level() > 0) ob_end_flush();