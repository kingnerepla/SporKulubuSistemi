<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kulüp Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-bg: #2c3e50; --sidebar-hover: #34495e; }
        body { background-color: #f8f9fa; overflow-x: hidden; }
        #wrapper { display: flex; }
        #sidebar-wrapper { min-width: 250px; max-width: 250px; background: var(--sidebar-bg); min-height: 100vh; transition: all 0.3s; }
        #sidebar-wrapper .list-group-item { background: transparent; color: rgba(255,255,255,0.7); border: none; padding: 15px 20px; }
        #sidebar-wrapper .list-group-item:hover { background: var(--sidebar-hover); color: #fff; }
        #sidebar-wrapper .list-group-item.active { background: #3498db; color: #fff; }
        #page-content-wrapper { width: 100%; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div id="wrapper">
        <div id="sidebar-wrapper">
            <div class="p-4 text-white">
                <h4><i class="fa-solid fa-trophy me-2"></i>Spor CRM</h4>
                <small class="text-muted text-uppercase">Yönetim Paneli</small>
            </div>
            <div class="list-group list-group-flush">
                <a href="index.php?page=dashboard" class="list-group-item list-group-item-action active"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
                
                <?php if($_SESSION['role'] === 'SystemAdmin'): ?>
                    <div class="px-4 py-2 mt-3 text-muted small fw-bold">SİSTEM</div>
                    <a href="index.php?page=clubs" class="list-group-item list-group-item-action"><i class="fa-solid fa-building me-2"></i> Kulüpler</a>
                    <a href="index.php?page=users" class="list-group-item list-group-item-action"><i class="fa-solid fa-users-gear me-2"></i> Yöneticiler</a>
                <?php endif; ?>

                <div class="px-4 py-2 mt-3 text-muted small fw-bold">KULÜP İŞLEMLERİ</div>
                <a href="index.php?page=students" class="list-group-item list-group-item-action"><i class="fa-solid fa-user-graduate me-2"></i> Öğrenciler</a>
                <a href="index.php?page=groups" class="list-group-item list-group-item-action"><i class="fa-solid fa-people-group me-2"></i> Gruplar</a>
                <a href="index.php?page=attendance" class="list-group-item list-group-item-action"><i class="fa-solid fa-calendar-check me-2"></i> Yoklama</a>
                <a href="index.php?page=payments" class="list-group-item list-group-item-action"><i class="fa-solid fa-credit-card me-2"></i> Aidatlar</a>

                <a href="index.php?page=logout" class="list-group-item list-group-item-action text-danger mt-5"><i class="fa-solid fa-power-off me-2"></i> Güvenli Çıkış</a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white p-3">
                <div class="container-fluid">
                    <span class="navbar-text">
                        <i class="fa-solid fa-circle-user text-success me-1"></i> 
                        Oturum Açan: <strong><?php echo $_SESSION['name']; ?></strong> 
                        <span class="badge bg-secondary ms-2"><?php echo $_SESSION['role']; ?></span>
                    </span>
                </div>
            </nav>
            <div class="container-fluid p-4">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</body>
</html>