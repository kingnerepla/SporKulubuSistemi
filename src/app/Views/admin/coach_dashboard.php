<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark">Hoş Geldin, <?= htmlspecialchars($name); ?> 👋</h3>
            <p class="text-muted small mb-0">Bugünkü antrenman programın ve öğrenci durumları.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark shadow-sm border p-2">
                <i class="fa-solid fa-calendar-day me-1 text-primary"></i> <?= date('d.m.Y'); ?>
            </span>
        </div>
    </div>

    <?php 
    $missingCount = 0;
    foreach($todayTrainings as $t) {
        if(!(isset($t['AttendanceCount']) && $t['AttendanceCount'] > 0)) {
            $missingCount++;
        }
    }
    ?>

    <?php if ($missingCount > 0): ?>
    <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center animate-pulse">
        <div class="bg-warning bg-opacity-20 p-3 rounded-circle me-3">
            <i class="fa-solid fa-bell-concierge fa-xl text-warning"></i>
        </div>
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-0 text-dark">Yoklama Hatırlatıcı</h6>
            <p class="small mb-0 text-dark opacity-75">Bugün henüz yoklaması alınmamış <strong><?= $missingCount ?></strong> antrenmanınız bulunuyor. Lütfen gecikmeden giriş yapınız.</p>
        </div>
        <div class="ms-3">
            <a href="index.php?page=attendance" class="btn btn-dark btn-sm rounded-pill px-4 fw-bold shadow-sm">
                Yoklamaya Git
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-primary text-white h-100 overflow-hidden position-relative">
                        <div class="card-body p-3 position-relative z-1">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                                    <i class="fa-solid fa-users fa-xl"></i>
                                </div>
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-bold x-small mb-1">Toplam Sporcu</h6>
                                    <h3 class="fw-bold mb-0"><?= $stats['totalStudents'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-white h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 me-3">
                                    <i class="fa-solid fa-layer-group fa-xl"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted text-uppercase fw-bold x-small mb-1">Aktif Grup</h6>
                                    <h3 class="fw-bold mb-0 text-dark"><?= $stats['totalGroups'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-clipboard-list me-2 text-primary"></i>Bugünün Antrenmanları</h6>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($todayTrainings)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($todayTrainings as $t): 
                                $isDone = isset($t['AttendanceCount']) && $t['AttendanceCount'] > 0;
                                $start = substr($t['StartTime'], 0, 5);
                                $end = substr($t['EndTime'], 0, 5);
                            ?>
                            <div class="list-group-item px-4 py-3 border-light <?= !$isDone ? 'bg-warning bg-opacity-10' : '' ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="text-center me-3 px-2 py-1 rounded <?= $isDone ? 'bg-light' : 'bg-white shadow-sm' ?> border border-secondary border-opacity-25" style="min-width: 60px;">
                                            <div class="fw-bold text-dark small"><?= $start ?></div>
                                            <div class="x-small text-muted"><?= $end ?></div>
                                        </div>
                                        
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($t['GroupName']) ?></h6>
                                            <?php if($isDone): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill x-small border border-success border-opacity-25">
                                                    <i class="fa-solid fa-check me-1"></i>Yoklama Alındı
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill x-small border border-danger border-opacity-25 animate-flash">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>Bekliyor
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div>
                                        <?php if(!$isDone): ?>
                                            <a href="index.php?page=attendance&open_group=<?= $t['GroupID'] ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold">
                                                Yoklama Al <i class="fa-solid fa-arrow-right ms-1"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?page=attendance&open_group=<?= $t['GroupID'] ?>" class="btn btn-light btn-sm rounded-pill px-3 border text-muted">
                                                Düzenle
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle d-inline-flex p-3 mb-3">
                                <i class="fa-solid fa-mug-hot fa-2x text-muted opacity-50"></i>
                            </div>
                            <h6 class="text-muted fw-bold">Bugün antrenman programı yok.</h6>
                            <p class="small text-muted mb-0">İyi dinlenmeler!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-bolt me-2 text-warning"></i>Hızlı İşlemler</h5>
                    
                    <div class="d-grid gap-3">
                        <a href="index.php?page=students" class="btn btn-outline-light text-start border-secondary py-2">
                            <i class="fa-solid fa-users me-2"></i> Sporcu Listesi
                        </a>
                        
                        <a href="index.php?page=attendance" class="btn btn-info text-dark fw-bold text-start border-0 py-2">
                            <i class="fa-solid fa-calendar-days me-2"></i> Yoklama Geçmişi
                        </a>
                    </div>

                    <div class="mt-4 pt-4 border-top border-secondary border-opacity-50">
                        <h6 class="small fw-bold text-white-50 mb-3">SİSTEM BİLGİSİ</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa-solid fa-circle-check text-success me-2"></i>
                            <span class="small">Sistem Aktif</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-shield-halved text-warning me-2"></i>
                            <span class="small">Yetki: Antrenör</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.7rem; }
    .btn-primary { background-color: #0d6efd; border-color: #0d6efd; }
    .btn-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; }
    
    /* Hareketli Pulse Efekti */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    .animate-pulse {
        animation: pulse 2s infinite;
    }

    /* Flash Efekti */
    @keyframes flash {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .animate-flash {
        animation: flash 1.5s infinite;
    }
</style>