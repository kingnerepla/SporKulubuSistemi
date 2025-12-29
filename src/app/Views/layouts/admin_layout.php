<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spor CRM | Yönetim Paneli</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar-wrapper { min-height: 100vh; width: 250px; background: #2c3e50; transition: all 0.3s; }
        .sidebar-heading { padding: 20px; font-size: 1.2rem; color: #fff; border-bottom: 1px solid #34495e; font-weight: bold; }
        .list-group-item { background: #2c3e50; color: #bdc3c7; border: none; padding: 12px 20px; font-size: 0.95rem; }
        .list-group-item:hover { background: #34495e; color: #fff; }
        .list-group-item.active { background: #34495e; color: #fff; border-left: 4px solid #3498db; }
        .list-group-item i { width: 25px; }
        .menu-header { color: #7f8c8d; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; padding: 20px 20px 5px 20px; letter-spacing: 1px; }
        #page-content-wrapper { width: 100%; padding: 20px; }
        .navbar { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 15px; border-radius: 10px; }
    </style>
</head>
<body>

<?php 
// 1. Rol ve ID tespiti
$currentRole = strtolower(trim($_SESSION['role'] ?? 'guest')); 
$roleId = intval($_SESSION['role_id'] ?? $_SESSION['RoleID'] ?? 0);

// 2. Yetki Tanımları
$isSystemAdmin = ($roleId === 1 || $currentRole === 'systemadmin');
$isClubAdmin   = ($roleId === 2 || $currentRole === 'clubadmin' || $currentRole === 'admin');
$isCoach       = ($roleId === 3 || $currentRole === 'coach' || $currentRole === 'trainer');
$isParent      = ($roleId === 4 || $currentRole === 'parent' || $currentRole === 'veli');

// Kulüp içeriği gösterilsin mi? (Admin veya Antrenörse)
$showClubMenu = ($isClubAdmin || $isCoach || ($isSystemAdmin && isset($_SESSION['selected_club_id'])));

// Navbar Başlığı
$displayClubName = $_SESSION['selected_club_name'] ?? $_SESSION['club_name'] ?? 'Sistem Paneli';
$activePage = $_GET['page'] ?? 'dashboard';
?>

<div id="wrapper">
    <div id="sidebar-wrapper">
        <div class="sidebar-heading border-bottom text-center">
            <i class="fa-solid fa-medal me-2 text-warning"></i>SPOR CRM
        </div>
        
        <div class="list-group list-group-flush">
            <a href="index.php?page=dashboard" class="list-group-item list-group-item-action <?= ($activePage == 'dashboard') ? 'active' : '' ?>">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>

            <?php if ($isSystemAdmin): ?>
                <div class="menu-header">Sistem Sahibi</div>
                <a href="index.php?page=clubs" class="list-group-item list-group-item-action <?= ($activePage == 'clubs') ? 'active' : '' ?>">
                    <i class="fa-solid fa-building me-2 text-info"></i> Kulüp Denetimi
                </a>
                <a href="index.php?page=system_finance" class="list-group-item list-group-item-action <?= ($activePage == 'system_finance') ? 'active' : '' ?>">
                    <i class="fa-solid fa-wallet me-2 text-success"></i> Sistem Finans
                </a>
            <?php endif; ?>

            <?php if ($showClubMenu): ?>
                <div class="menu-header"><?php echo $isCoach ? 'Eğitim Menüsü' : 'Kulüp İşlemleri'; ?></div>
                
                <?php if ($isClubAdmin): ?>
                    <a href="index.php?page=coaches" class="list-group-item list-group-item-action <?= ($activePage == 'coaches') ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-tie me-2"></i> Antrenörler
                    </a>
                <?php endif; ?>

                <a href="index.php?page=students" class="list-group-item list-group-item-action <?= ($activePage == 'students') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-graduate me-2 text-warning"></i> 
                    <?php echo $isCoach ? 'Öğrencilerim' : 'Öğrenciler'; ?>
                </a>

                <?php 
                    $canSeeReport = false;
                    if ($isClubAdmin) $canSeeReport = true;
                    elseif ($isCoach && isset($_SESSION['can_see_reports']) && $_SESSION['can_see_reports'] == 1) $canSeeReport = true;

                    if ($canSeeReport): 
                ?>
                <a href="index.php?page=attendance_report" class="list-group-item list-group-item-action <?= ($activePage == 'attendance_report') ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-column me-2 text-success"></i> Yoklama Raporları
                </a>
                <?php endif; ?>

                <a href="index.php?page=groups" class="list-group-item list-group-item-action <?= ($activePage == 'groups') ? 'active' : '' ?>">
                    <i class="fa-solid fa-people-group me-2 text-primary"></i> 
                    <?php echo $isCoach ? 'Gruplarım' : 'Gruplar / Dersler'; ?>
                </a>

                <a href="index.php?page=attendance" class="list-group-item list-group-item-action <?= ($activePage == 'attendance') ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-check me-2 text-info"></i> Yoklama Al
                </a>
                           
                <a href="index.php?page=training_groups" class="list-group-item list-group-item-action <?= ($activePage == 'training_groups') ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-days me-2"></i> Çalışma Takvimi
                </a>
          
                <?php if ($isClubAdmin): ?>
                    <div class="menu-header">Finansal Takip</div>
                    <a href="index.php?page=club_finance" class="list-group-item list-group-item-action <?= ($activePage == 'club_finance') ? 'active' : '' ?>">
                        <i class="fa-solid fa-money-bill-transfer me-2 text-success"></i> Aidat Takibi
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($isParent): ?>
                <div class="menu-header">Veli İşlemleri</div>
                <a href="index.php?page=parent_attendance" class="list-group-item list-group-item-action <?= ($activePage == 'parent_attendance') ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-check me-2 text-success"></i> Çocuklarımın Yoklaması
                </a>
                <a href="index.php?page=parent_payments" class="list-group-item list-group-item-action <?= ($activePage == 'parent_payments') ? 'active' : '' ?>">
                    <i class="fa-solid fa-credit-card me-2 text-warning"></i> Ödeme Geçmişi
                </a>
            <?php endif; ?>

            <a href="index.php?page=logout" class="list-group-item list-group-item-action text-danger mt-5 border-top border-secondary">
                <i class="fa-solid fa-power-off me-2"></i> Güvenli Çıkış
            </a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar d-flex justify-content-between">
            <div class="fw-bold text-primary">
                <i class="fa-solid fa-location-dot me-2 text-danger"></i><?php echo htmlspecialchars($displayClubName); ?>
            </div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <div class="fw-bold small text-dark"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Kullanıcı'); ?></div>
                    <div class="text-muted small" style="font-size: 0.7rem;">
                        <?php 
                            if ($isSystemAdmin) echo 'Sistem Yöneticisi';
                            elseif ($isClubAdmin) echo 'Kulüp Yöneticisi';
                            elseif ($isCoach) echo 'Antrenör';
                            elseif ($isParent) echo 'Veli';
                        ?>
                    </div>
                </div>
                <div class="bg-light p-2 rounded-circle border">
                    <i class="fa-solid fa-user-tie fa-lg text-secondary"></i>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <?php echo $content; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>