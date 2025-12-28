<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #cfd8dc; text-decoration: none; display: block; padding: 10px 15px; }
        .sidebar a:hover { background-color: #495057; color: white; }
        .sidebar .active { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar p-3" style="width: 250px; flex-shrink: 0;">
        <h4 class="mb-4 text-white">🏆 KulüpSis</h4>
        <ul class="list-unstyled">
            <li><a href="index.php?page=dashboard"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a></li>
            <li><a href="index.php?page=clubs"><i class="fa-solid fa-building me-2"></i> Kulüpler</a></li>
            <li><a href="#"><i class="fa-solid fa-users me-2"></i> Kullanıcılar</a></li>
            <li class="mt-4 border-top pt-2"><a href="index.php?page=logout" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Çıkış Yap</a></li>

        </ul>
    </div>

    <div class="flex-grow-1 bg-light">
        <nav class="navbar navbar-light bg-white shadow-sm px-4 mb-4">
            <span class="navbar-brand mb-0 h1">Yönetim Paneli</span>
            <span class="text-muted">Hoşgeldin, <?php echo $_SESSION['name'] ?? 'Admin'; ?></span>
        </nav>

        <div class="container-fluid px-4">
            <?php 
                // $content değişkeni Controller'dan gelecek
                if(isset($content)) echo $content; 
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>