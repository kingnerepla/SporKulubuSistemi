<?php
// index.php 
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata vermemesi için kontrol ekleyelim
$userId = $_SESSION['user_id'] ?? null; 
// echo "ID: " . $userId; // Testten sonra bunu kapatabilirsin
$page = $_GET['page'] ?? 'login';

// Eğer giriş yapılmamışsa ve gidilmek istenen sayfa "kamu" sayfası değilse...
if (!in_array($page, ['login', 'admin_login_form', 'parent_login', 'admin_auth', 'parent_auth'])) {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['parent_logged_in'])) {
        // ...onu seçim sayfasına zorla gönder!
        header("Location: index.php?page=login"); 
        exit;
    }
}

// 1. Veritabanı Yolu: index.php ile aynı yerdeki app/config/Database.php
$dbPath = __DIR__ . '/app/config/Database.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    die("Kritik Hata: Database.php bulunamadı. <br> Aranan yol: " . $dbPath);
}

$page = $_GET['page'] ?? 'login';

// 2. Herkese açık sayfalar
$public_pages = ['login', 'admin_login_form', 'parent_login', 'admin_auth', 'parent_auth'];

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

// 3. Dinamik Controller Yükleyici
function safe_load($controllerName, $methodName) {
    // index.php zaten src içinde olduğu için yol: app/Controllers/...
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
        die("Hata: " . $controllerName . " dosyası bulunamadı. <br> Aranan Yol: " . $path);
    }
}

// 4. Rota Yönetimi
switch ($page) {
    case 'login':            safe_load('AuthController', 'showSelection'); break;
    case 'admin_login_form': safe_load('AuthController', 'showAdminLogin'); break;
    case 'parent_login':     safe_load('ParentController', 'loginPage'); break;
    case 'admin_auth':       safe_load('AuthController', 'login');  break;
    case 'parent_auth':      safe_load('ParentController', 'authenticate'); break;
    case 'parent_dashboard': safe_load('ParentController', 'dashboard'); break;
    case 'logout':           session_destroy(); header("Location: index.php?page=login"); exit;
    case 'profile':        safe_load('ProfileController', 'index'); break;
    case 'profile_update': safe_load('ProfileController', 'update'); break;
    case 'dashboard':        safe_load('DashboardController', 'index'); break;
    case 'training_groups':  safe_load('GroupScheduleController', 'trainingGroups'); break;
    case 'group_calendar':   safe_load('GroupScheduleController', 'groupCalendar'); break;

    default:                
        header("Location: index.php?page=dashboard"); 
        break;
}

if (ob_get_level() > 0) ob_end_flush();