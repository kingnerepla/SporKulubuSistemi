<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spor CRM | Yönetim Paneli</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        
        #sidebar-wrapper { min-height: 100vh; width: 260px; background: #2c3e50; transition: all 0.3s; flex-shrink: 0; position: sticky; top: 0; height: 100vh; }
        .sidebar-heading { padding: 20px; font-size: 1.2rem; color: #fff; border-bottom: 1px solid #34495e; font-weight: bold; background: #1a252f; }
        
        .list-group-item { background: #2c3e50; color: #bdc3c7; border: none; padding: 12px 20px; font-size: 0.95rem; transition: 0.2s; border-radius: 0; }
        .list-group-item:hover { background: #34495e; color: #fff; padding-left: 25px; }
        .list-group-item.active { background: #3498db; color: #fff; border-left: 5px solid #2980b9; }
        .list-group-item i { width: 25px; font-size: 1.1rem; }
        
        .menu-header { color: #7f8c8d; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 25px 20px 5px 20px; letter-spacing: 1.5px; background: rgba(0,0,0,0.1); }
        
        #page-content-wrapper { width: 100%; overflow-x: hidden; }
        .main-container { padding: 25px; }
        
        .navbar { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 12px 25px; border-radius: 0; border: none; }
        
        .club-logo-nav { width: 35px; height: 35px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
        
        /* Impersonate Bar */
        .impersonate-bar { background: #f39c12; color: #fff; padding: 10px 25px; font-weight: 600; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }

        @media (max-width: 992px) {
            #sidebar-wrapper { margin-left: -260px; position: fixed; z-index: 1050; }
            #wrapper.toggled #sidebar-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

<?php 
// Oturumdaki rolü al
$currentRole = strtolower(trim($_SESSION['role'] ?? 'guest')); 

// Rol Kontrolleri
$isSystemAdmin = (($_SESSION['role_id'] ?? 0) == 1); // RoleID bazlı kontrol daha güvenlidir
$isClubAdmin   = (($_SESSION['role_id'] ?? 0) == 2);
$isCoach       = (($_SESSION['role_id'] ?? 0) == 3);
$isParent      = (($_SESSION['role_id'] ?? 0) == 4);

// Menü Gösterim Mantığı
$showClubMenu = ($isClubAdmin || $isCoach);

// Üst Bar Bilgileri
$displayClubName = $_SESSION['selected_club_name'] ?? $_SESSION['club_name'] ?? 'Yönetim Merkezi';
$displayClubLogo = $_SESSION['selected_club_logo'] ?? $_SESSION['club_logo'] ?? null;
$activePage = $_GET['page'] ?? 'dashboard';
?>

<div id="wrapper">
    <div id="sidebar-wrapper">
        <div class="sidebar-heading text-center">
            <i class="fa-solid fa-medal me-2 text-warning"></i>SPOR CRM
        </div>
        
        <div class="list-group list-group-flush">
            
            <?php if ($isSystemAdmin): ?>
                <a href="index.php?page=dashboard" class="list-group-item list-group-item-action <?= ($activePage == 'dashboard') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high me-2 text-info"></i> Sistem Özeti
                </a>
                
                <div class="menu-header">Sistem Yönetimi</div>
                <a href="index.php?page=clubs" class="list-group-item list-group-item-action <?= ($activePage == 'clubs') ? 'active' : '' ?>">
                    <i class="fa-solid fa-building-shield me-2 text-primary"></i> Kulüp Denetimi
                </a>
                <a href="index.php?page=system_finance" class="list-group-item list-group-item-action <?= ($activePage == 'system_finance') ? 'active' : '' ?>">
                    <i class="fa-solid fa-sack-dollar me-2 text-success"></i> SaaS Gelirleri
                </a>
                <a href="index.php?page=expenses" class="list-group-item list-group-item-action <?= ($activePage == 'expenses') ? 'active' : '' ?>">
                    <i class="fa-solid fa-receipt me-2 text-danger"></i> Giderler
                </a>
            
            <?php else: ?>
                <a href="index.php?page=dashboard" class="list-group-item list-group-item-action <?= ($activePage == 'dashboard') ? 'active' : '' ?>">
                    <i class="fa-solid fa-house me-2 text-info"></i> Dashboard
                </a>
            <?php endif; ?>

            <?php if ($showClubMenu): ?>
                <div class="menu-header"><?= $isCoach ? 'Eğitim Menüsü' : 'Kulüp İşlemleri'; ?></div>
                
                <?php if ($isClubAdmin): ?>
                    <a href="index.php?page=coach_list" class="list-group-item list-group-item-action <?= ($activePage == 'coach_list') ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-tie me-2"></i> Antrenörler
                    </a>
                <?php endif; ?>

                <a href="index.php?page=students" class="list-group-item list-group-item-action <?= ($activePage == 'students') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-graduate me-2 text-warning"></i> <?= ($isCoach) ? 'Öğrencilerim' : 'Öğrenciler'; ?>
                </a>

                <a href="index.php?page=groups" class="list-group-item list-group-item-action <?= ($activePage == 'groups') ? 'active' : '' ?>">
                    <i class="fa-solid fa-people-group me-2 text-primary"></i> <?= ($isCoach) ? 'Gruplarım' : 'Gruplar'; ?>
                </a>

                <a href="index.php?page=attendance" class="list-group-item list-group-item-action <?= ($activePage == 'attendance') ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-check me-2 text-info"></i> Yoklama Al
                </a>
                
                <a href="index.php?page=attendance_report" class="list-group-item list-group-item-action <?= ($activePage == 'attendance_report') ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-column me-2 text-success"></i> Raporlar
                </a>

                <?php if ($isClubAdmin): ?>
                    <div class="menu-header">FİNANSAL YÖNETİM</div>
                    <a href="index.php?page=club_finance" class="list-group-item list-group-item-action <?= ($activePage == 'club_finance') ? 'active' : '' ?>">
                        <i class="fa-solid fa-cash-register me-2 text-success"></i> Kasa & Tahsilat
                    </a>
                    <a href="index.php?page=expenses" class="list-group-item list-group-item-action <?= ($activePage == 'expenses') ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-invoice-dollar me-2 text-danger"></i> Giderler
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($isParent): ?>
                <div class="menu-header">Veli İşlemleri</div>
                <a href="index.php?page=parent_attendance" class="list-group-item list-group-item-action <?= ($activePage == 'parent_attendance') ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-check me-2 text-success"></i> Yoklama Takibi
                </a>
                <a href="index.php?page=parent_payments" class="list-group-item list-group-item-action <?= ($activePage == 'parent_payments') ? 'active' : '' ?>">
                    <i class="fa-solid fa-receipt me-2 text-warning"></i> Ödemelerim
                </a>
            <?php endif; ?>

            <a href="index.php?page=logout" class="list-group-item list-group-item-action text-danger mt-auto mb-2 border-top border-secondary pt-3">
                <i class="fa-solid fa-power-off me-2"></i> Güvenli Çıkış
            </a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <?php if (isset($_SESSION['impersonator_id'])): ?>
            <div class="impersonate-bar">
                <span>
                    <i class="fa-solid fa-user-secret me-2"></i> 
                    Şu an <strong><?= htmlspecialchars($displayClubName) ?></strong> kulübünü denetliyorsunuz.
                </span>
                <a href="index.php?page=exit_impersonate" class="btn btn-sm btn-light fw-bold px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Süper Adminliğe Dön
                </a>
            </div>
        <?php endif; ?>

        <nav class="navbar d-flex justify-content-between align-items-center sticky-top">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="menu-toggle">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <?php if ($displayClubLogo): ?>
                    <img src="<?= $displayClubLogo ?>" class="club-logo-nav me-2" alt="Logo">
                <?php else: ?>
                    <div class="bg-light p-2 rounded-3 me-2 border d-flex align-items-center justify-content-center" style="width:35px; height:35px;">
                        <i class="fa-solid fa-building text-secondary"></i>
                    </div>
                <?php endif; ?>
                
                <div class="fw-bold text-primary">
                    <span class="text-dark opacity-50 small d-none d-sm-inline">Kulüp:</span>
                    <?= htmlspecialchars($displayClubName); ?>
                </div>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <div class="fw-bold small text-dark"><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['name'] ?? 'Kullanıcı'); ?></div>
                    <div class="badge bg-light text-muted border fw-normal" style="font-size: 0.6rem;">
                        <?php 
                            if ($isSystemAdmin) echo 'SİSTEM SAHİBİ';
                            elseif ($isClubAdmin) echo 'KULÜP YÖNETİCİSİ';
                            elseif ($isCoach) echo 'ANTRENÖR';
                            else echo 'VELİ';
                        ?>
                    </div>
                </div>
                <div class="dropdown">
                    <div class="bg-primary p-2 rounded-circle border shadow-sm text-white cursor-pointer" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                        <li><a class="dropdown-item py-2" href="index.php?page=profile"><i class="fa-solid fa-id-card me-2 text-muted"></i> Profilim</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="index.php?page=logout"><i class="fa-solid fa-power-off me-2"></i> Çıkış Yap</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="main-container">
            <?php echo $content; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function(){
        // Sidebar Toggle
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });

        // Telefon Maskesi
        $('input[name="phone"], input[name="parent_phone"], input[name="parent_phone_account"]').mask('(000) 000 00 00');
    });
</script>

</body>
</html>