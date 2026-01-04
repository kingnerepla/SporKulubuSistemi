<?php
ob_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

// Veritabanı bağlantısını başlat
$database = new Database();
$db = $database->getConnection();

$page = $_GET['page'] ?? 'login';

// 2. Herkese açık sayfalar
$public_pages = ['login', 'admin_login_form', 'parent_login', 'admin_auth', 'parent_auth'];

// 3. Yetki ve Kulüp Durum Kontrolü (GÜVENLİK KİLİDİ)
if (!in_array($page, $public_pages)) {
    
    // 1. Kural: Giriş yapmamış kimse geçemez
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit;
    }

    // 🔥 2. Kural: YUMUŞAK BORÇ KİLİDİ (Middleware)
    $s_roleId = (string)($_SESSION['role_id'] ?? $_SESSION['RoleID'] ?? '0');
    $s_clubId = $_SESSION['club_id'] ?? null;
    $isImpersonating = isset($_SESSION['impersonator_id']); // Süper Admin sızmış mı?

    // Süper Admin değilse ve bir kulübe bağlıysa kontrol et
    if ($s_roleId !== "1" && $s_clubId && !$isImpersonating) {
        $stmtClub = $db->prepare("SELECT IsActive, LicenseEndDate FROM Clubs WHERE ClubID = ?");
        $stmtClub->execute([$s_clubId]);
        $clubData = $stmtClub->fetch(PDO::FETCH_ASSOC);

        $today = date('Y-m-d');
        $isExpired = ($clubData['LicenseEndDate'] && $clubData['LicenseEndDate'] < $today);

        // Eğer kulüp dondurulmuşsa VEYA lisans süresi bitmişse
        if ($clubData['IsActive'] == 0 || $isExpired) {
            // İzin verilen sayfalar listesi
            $allowedPages = ['dashboard', 'profile', 'logout'];
            
            if (!in_array($page, $allowedPages)) {
                // Kısıtlı sayfaya girmeye çalışırsa Dashboard'a yönlendir ve uyar
                header("Location: index.php?page=dashboard&error=debt_lock");
                exit;
            }
        }
    }

    // 3. Kural: Veli sayfalarına erişim kontrolü
    if (strpos($page, 'parent_') === 0) {
        if (($_SESSION['role'] ?? '') !== 'parent') {
            header("Location: index.php?page=login");
            exit;
        }
    } 

    // 4. Kural: Admin/Veli Rol Ayrıştırması
    if (($_SESSION['role'] ?? '') === 'parent' && strpos($page, 'parent_') !== 0 && !in_array($page, ['dashboard', 'profile', 'logout'])) {
        header("Location: index.php?page=parent_dashboard");
        exit;
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
        die("<b>Dosya Hatası:</b> {$controllerName}.php bulunamadı.");
    }
}

// 5. ROTA YÖNETİMİ
switch ($page) {

    // --- AUTH ---
    case 'login':            safe_load('AuthController', 'showSelection'); break;
    case 'admin_login_form': safe_load('AuthController', 'showAdminLogin'); break;
    case 'parent_login':     safe_load('AuthController', 'showParentLogin'); break; 
    case 'parent_auth':      safe_load('AuthController', 'parentLogin'); break;     
    case 'admin_auth':       safe_load('AuthController', 'login');  break;
    
    // --- DASHBOARD ---
    case 'dashboard':        safe_load('DashboardController', 'index'); break;

    // --- SÜPER ADMİN: KULÜP VE SAAS YÖNETİMİ ---
    case 'clubs':               safe_load('ClubController', 'index'); break; 
    case 'club_add':            safe_load('ClubController', 'create'); break;
    case 'club_store':          safe_load('ClubController', 'store'); break;
    case 'club_edit':           safe_load('ClubController', 'edit'); break;
    case 'club_update':         safe_load('ClubController', 'update'); break;
    case 'club_status_toggle':  safe_load('DashboardController', 'toggleClubStatus'); break;
    case 'select_club':         safe_load('ClubController', 'selectClub'); break;
    case 'clear_selection':     safe_load('ClubController', 'clearSelection'); break;
    case 'club_impersonate':    safe_load('ClubController', 'impersonate'); break;
    case 'exit_impersonate':    safe_load('ClubController', 'exitImpersonate'); break;
    case 'add_saas_payment':    safe_load('ClubController', 'addSaasPayment'); break;

    // --- SÜPER ADMİN: MERKEZİ SİSTEM FİNANS ---
    case 'system_finance':   safe_load('SystemFinanceController', 'index'); break;
    
    case 'store_expense':    safe_load('SystemFinanceController', 'storeExpense'); break;
    case 'delete_expense':   safe_load('SystemFinanceController', 'deleteExpense'); break;

    // --- ÖĞRENCİ İŞLEMLERİ ---
    case 'students':              safe_load('StudentController', 'index'); break;
    case 'student_store':         safe_load('StudentController', 'store'); break;
    case 'student_update':        safe_load('StudentController', 'update'); break;
    case 'student_archive_store': safe_load('StudentController', 'archive_store'); break;
    case 'students_archived':     safe_load('StudentController', 'archived'); break;
    case 'student_restore':       safe_load('StudentController', 'restore'); break;  
    case 'student_destroy':       safe_load('StudentController', 'destroy'); break;  
    case 'student_update_note':   safe_load('StudentController', 'update_note'); break;
    case 'student_update_password': safe_load('StudentController', 'update_password'); break;

    // --- GRUP YÖNETİMİ ---
    case 'groups':      safe_load('GroupController', 'index'); break;
    case 'group_save':  safe_load('GroupController', 'save'); break;  
    case 'group_delete': safe_load('GroupController', 'delete'); break;
    case 'training_groups':  safe_load('GroupScheduleController', 'trainingGroups'); break;
    case 'generate_sessions': safe_load('GroupScheduleController', 'generateSessions'); break;
    case 'delete_sessions':  safe_load('GroupScheduleController', 'deleteSessions'); break;
    case 'delete_single_session': safe_load('GroupScheduleController', 'deleteSingleSession'); break;

    // --- YOKLAMA & RAPORLAR ---
    case 'attendance':       safe_load('AttendanceController', 'index'); break;
    case 'attendance_store':  safe_load('AttendanceController', 'store'); break;
    case 'attendance_report': safe_load('AttendanceReportController', 'index'); break;
    case 'attendance_report_mail': safe_load('AttendanceReportController', 'sendMail'); break;

    // --- ANTRENÖR ---
    case 'coach_list':        safe_load('CoachController', 'index'); break;
    case 'coach_store':       safe_load('CoachController', 'store'); break;
    case 'coach_delete':      safe_load('CoachController', 'delete'); break;      
    case 'coach_restore':     safe_load('CoachController', 'restore'); break;     
    case 'coach_hard_delete': safe_load('CoachController', 'hard_delete'); break; 
    case 'update_coach_permission': safe_load('DashboardController', 'updatePermission'); break;
    
    // --- KULÜP FİNANS & ÖDEMELER ---
    case 'club_finance':     safe_load('ClubFinanceController', 'index'); break;
    case 'payments':         safe_load('PaymentController', 'index'); break;
    case 'payment_store':    safe_load('PaymentController', 'store'); break; 
    case 'payment_delete':   safe_load('PaymentController', 'delete'); break;

    // --- VELİ ÖZEL SAYFALARI ---
    case 'parent_dashboard':  safe_load('DashboardController', 'index'); break; 
    case 'parent_attendance': safe_load('ParentController', 'attendance'); break;
    case 'parent_payments':   safe_load('ParentController', 'payments'); break;

    // --- GİDER YÖNETİMİ ---
    case 'expenses':       safe_load('ExpensesController', 'index'); break;
    case 'expense_store':  safe_load('ExpensesController', 'store'); break;
    case 'expense_delete': safe_load('ExpensesController', 'delete'); break;

    // --- PROFİL & SİSTEM ---
    case 'profile':          safe_load('ProfileController', 'index'); break;
    case 'profile_update':   safe_load('ProfileController', 'index'); break;
    case 'logout':           
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        session_destroy(); 
        header("Location: index.php?page=login"); 
        exit;               
        break;

    default:
        die("Sayfa bulunamadı.");
}

if (ob_get_level() > 0) ob_end_flush();