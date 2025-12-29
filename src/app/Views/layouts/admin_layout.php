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
        .list-group-item i { width: 25px; }
        .menu-header { color: #7f8c8d; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; padding: 20px 20px 5px 20px; letter-spacing: 1px; }
        #page-content-wrapper { width: 100%; padding: 20px; }
        .navbar { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 15px; border-radius: 10px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<?php 
// 1. Rolü normalize et (Küçük harf ve temiz veri)
$currentRole = strtolower(trim($_SESSION['role'] ?? 'guest')); 

// 2. Yetki Kontrolleri
$isSystemAdmin = ($currentRole === 'systemadmin' || $currentRole === 'admin');
$isClubAdmin   = ($currentRole === 'clubadmin' || $currentRole === 'manager');
$selectedClubID = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'] ?? null;

// Kulüp içeriği gösterilsin mi? (Kulüp Adminiyse VEYA Süper Admin bir kulüp seçmişse)
$showClubMenu = ($isClubAdmin || ($isSystemAdmin && isset($_SESSION['selected_club_id'])));

// Navbar Başlığı
$displayClubName = $_SESSION['selected_club_name'] ?? $_SESSION['club_name'] ?? 'Sistem Paneli';
?>

<div id="wrapper">
    <div id="sidebar-wrapper">
        <div class="sidebar-heading border-bottom">
            <i class="fa-solid fa-medal me-2 text-warning"></i>SPOR CRM
        </div>
        
        <div class="list-group list-group-flush">
            <a href="index.php?page=dashboard" class="list-group-item list-group-item-action">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>

            <?php if ($isSystemAdmin): ?>
                <div class="menu-header">Sistem Sahibi</div>
                <a href="index.php?page=clubs" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-building me-2 text-info"></i> Kulüp Denetimi
                </a>
                <a href="index.php?page=system_finance" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-wallet me-2 text-success"></i> Sistem Finans
                </a>
            <?php endif; ?>

            <?php if ($showClubMenu): ?>
                <div class="menu-header">Kulüp İşlemleri</div>
                
                <a href="index.php?page=students" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-user-graduate me-2 text-warning"></i> Öğrenciler
                </a>

                <a href="index.php?page=groups" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-people-group me-2 text-primary"></i> Gruplar / Dersler
                </a>

                <a href="index.php?page=attendance" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-calendar-check me-2 text-info"></i> Günlük Yoklama
                </a>
                           
                <li class="nav-item">
                    <a class="list-group-item list-group-item-action" href="index.php?page=training_groups">
                        <i class="fa-solid fa-calendar-check me-2"></i> Antrenman Takvimi
                    </a>
                </li>
          
                <div class="menu-header">Finansal Takip</div>

                <a href="index.php?page=club_finance" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-money-bill-transfer me-2 text-success"></i> Aidat Takibi
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
                    <div class="fw-bold small text-dark"><?php echo $_SESSION['name'] ?? 'Kullanıcı'; ?></div>
                    <div class="text-muted small" style="font-size: 0.7rem;"><?php echo $_SESSION['role'] ?? ''; ?></div>
                </div>
                <i class="fa-solid fa-circle-user fa-2xl text-secondary"></i>
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