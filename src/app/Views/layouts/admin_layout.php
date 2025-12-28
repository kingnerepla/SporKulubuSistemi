<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spor CRM | Sistem Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-bg: #0f172a; --sidebar-hover: #1e293b; --accent-color: #38bdf8; }
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        #wrapper { display: flex; }
        #sidebar-wrapper { min-width: 260px; max-width: 260px; background: var(--sidebar-bg); min-height: 100vh; position: fixed; }
        #page-content-wrapper { width: 100%; margin-left: 260px; }
        .nav-link { color: #94a3b8; padding: 12px 20px; border-radius: 8px; margin: 4px 15px; font-size: 0.9rem; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: var(--sidebar-hover); color: var(--accent-color); }
        .menu-header { color: #475569; font-size: 0.7rem; font-weight: 800; padding: 25px 30px 10px; text-transform: uppercase; letter-spacing: 1px; }
        .navbar { background: #ffffff; border-bottom: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <?php 
        $role = $_SESSION['role'] ?? 'Guest'; 
        $page = $_GET['page'] ?? 'dashboard';
        $selectedClub = $_SESSION['selected_club_id'] ?? null;
    ?>
    <div id="wrapper">
        <div id="sidebar-wrapper">
            <div class="p-4 border-bottom border-secondary mb-3">
                <h4 class="text-white fw-bold mb-0"><i class="fa-solid fa-gauge-high me-2 text-info"></i>Spor CRM</h4>
            </div>
            
            <nav class="nav flex-column">
                <a href="index.php?page=dashboard" class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie me-2"></i> Dashboard
                </a>

                <?php if($role === 'SystemAdmin'): ?>
                    <div class="menu-header">SİSTEM MERKEZİ</div>
                    <a href="index.php?page=clubs" class="nav-link <?php echo $page == 'clubs' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-building-shield me-2"></i> Kulüpler & Lisanslar
                    </a>
                    <a href="index.php?page=club_payments" class="nav-link <?php echo $page == 'club_payments' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i> Kulüp Ödemeleri
                    </a>
                    <a href="index.php?page=system_settings" class="nav-link">
                        <i class="fa-solid fa-gears me-2"></i> Genel Ayarlar
                    </a>
                <?php endif; ?>

                <?php if($role === 'ClubAdmin'): ?>
                    <div class="menu-header">KULÜP OPERASYON</div>
                    <a href="index.php?page=students" class="nav-link"><i class="fa-solid fa-users me-2"></i> Öğrenciler</a>
                    <a href="index.php?page=groups" class="nav-link"><i class="fa-solid fa-layer-group me-2"></i> Gruplar</a>
                    <a href="index.php?page=users" class="nav-link"><i class="fa-solid fa-user-tie me-2"></i> Antrenörler</a>
                    <a href="index.php?page=payments" class="nav-link"><i class="fa-solid fa-lira-sign me-2"></i> Finans / Aidat</a>
                <?php endif; ?>

                <div class="mt-5 pt-3">
                    <a href="index.php?page=logout" class="nav-link text-danger">
                        <i class="fa-solid fa-power-off me-2"></i> Güvenli Çıkış
                    </a>
                </div>
            </nav>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg py-3 px-4">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3 small">Oturum: <strong><?php echo $_SESSION['name']; ?></strong></span>
                        <span class="badge bg-soft-primary text-primary border border-primary px-3"><?php echo $role; ?></span>
                    </div>
                </div>
            </nav>
            <div class="p-4">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</body>
</html>