<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spor CRM | Yönetim Paneli</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php 
$currentRole = strtolower(trim($_SESSION['role'] ?? 'guest')); 
$roleId = intval($_SESSION['role_id'] ?? $_SESSION['RoleID'] ?? 0);
$isSystemAdmin = ($roleId === 1 || $currentRole === 'systemadmin');
$isClubAdmin   = ($roleId === 2 || $currentRole === 'clubadmin' || $currentRole === 'admin');
$isCoach       = ($roleId === 3 || $currentRole === 'coach' || $currentRole === 'trainer');
$isParent      = ($roleId === 4 || $currentRole === 'parent' || $currentRole === 'veli');
$showClubMenu  = ($isClubAdmin || $isCoach || ($isSystemAdmin && isset($_SESSION['selected_club_id'])));
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

                <a href="index.php?page=attendance_report" class="list-group-item list-group-item-action <?= ($activePage == 'attendance_report') ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-column me-2 text-success"></i> Yoklama Raporu
                </a>

                <a href="index.php?page=attendance" class="list-group-item list-group-item-action <?= ($activePage == 'attendance') ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-check me-2 text-info"></i> Yoklama Al
                </a>
            <?php endif; ?>

            <a href="index.php?page=logout" class="list-group-item list-group-item-action text-danger mt-5">
                <i class="fa-solid fa-power-off me-2"></i> Çıkış
            </a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-light me-3" id="menu-toggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="fw-bold text-primary d-none d-sm-block">
                    <i class="fa-solid fa-location-dot me-2 text-danger"></i><?php echo htmlspecialchars($displayClubName); ?>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <div class="text-end me-2 d-none d-md-block">
                    <div class="fw-bold small"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Kullanıcı'); ?></div>
                    <div class="text-muted small" style="font-size: 0.7rem;">Panel</div>
                </div>
                <div class="bg-light p-2 rounded-circle border">
                    <i class="fa-solid fa-user fa-lg text-secondary"></i>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <?php echo $content; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mobil Menü Toggle Fonksiyonu
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
</script>

</body>
</html>