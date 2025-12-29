<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veli Paneli - <?= htmlspecialchars($student['FullName'] ?? 'Öğrenci') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .navbar { background: #2c3e50; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-graduation-cap me-2"></i> Sporcu Veli Paneli</span>
        <div class="d-flex gap-2">
            <a href="index.php?page=profile" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-user-gear"></i> Profilim
            </a>
            <a href="index.php?page=parent_logout" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-right-from-bracket"></i> Çıkış
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary text-white"><i class="fa-solid fa-user"></i></div>
                    <div class="ms-3">
                        <small class="text-muted d-block">Öğrenci</small>
                        <h5 class="mb-0"><?= htmlspecialchars($student['FullName']) ?></h5>
                        <small class="badge bg-light text-primary"><?= htmlspecialchars($student['GroupName'] ?? 'Grup Yok') ?></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success text-white"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="ms-3">
                        <small class="text-muted d-block">Katılım Oranı</small>
                        <h5 class="mb-0">%<?= ($stats['attended'] + $stats['missed']) > 0 ? round(($stats['attended'] / ($stats['attended'] + $stats['missed'])) * 100) : 0 ?></h5>
                        <small class="text-success"><?= $stats['attended'] ?> Antrenman</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning text-white"><i class="fa-solid fa-lira-sign"></i></div>
                    <div class="ms-3">
                        <small class="text-muted d-block">Toplam Ödeme</small>
                        <h5 class="mb-0"><?= number_format($totalPaid, 2) ?> TL</h5>
                        <small class="text-muted">Ödeme Geçmişi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-list-check me-2 text-primary"></i>Antrenman Yoklamaları</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Tarih</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($attendanceHistory as $row): ?>
                            <tr>
                                <td class="ps-4"><?= date('d.m.Y', strtotime($row['AttendanceDate'])) ?></td>
                                <td>
                                    <?php if($row['Status'] == 1): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success">GELDI</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">GELMEDI</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm bg-primary text-white p-4 mb-4">
                <h5><i class="fa-solid fa-circle-info me-2"></i>Bilgi</h5>
                <p class="small mb-0">Çocuğunuzun gelişimi ve antrenman saatleri hakkında bilgi almak için lütfen kulüp yönetimiyle iletişime geçiniz.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>