<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/Config/Database.php';

$page = $_GET['page'] ?? 'dashboard';
$publicPages = ['login', 'login_submit'];

if (!isset($_SESSION['user_id']) && !in_array($page, $publicPages)) {
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit;
}

switch ($page) {
    case 'login': require_once __DIR__ . '/app/Views/auth/login.php'; break;
    case 'dashboard':
        require_once __DIR__ . '/app/Controllers/DashboardController.php';
        (new DashboardController())->index();
        break;
    // --- ÖĞRENCİ ---
    case 'students':
        require_once __DIR__ . '/app/Controllers/StudentController.php';
        (new StudentController())->index();
        break;
    case 'student_store':
        require_once __DIR__ . '/app/Controllers/StudentController.php';
        (new StudentController())->store();
        break;
    // --- KULÜP ---
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
        echo "<script>window.location.href = 'index.php?page=login';</script>";
        break;
    default:
        header("Location: index.php?page=dashboard");
        break;
}
ob_end_flush();