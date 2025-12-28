<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col">
        <h3>Genel Bakış</h3>
        <p class="text-muted">Kulübünüzdeki son durum ve istatistikler.</p>
    </div>
</div>

<div class="row g-4">
    <?php if($_SESSION['role'] === 'SystemAdmin'): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Kayıtlı Kulüpler</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $stats['total_clubs'] ?? 0; ?></h2>
                    </div>
                    <i class="fa-solid fa-building fa-3x opacity-25"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 opacity-75">Aktif Öğrenciler</h6>
                    <h2 class="mb-0 fw-bold">
                        <?php echo ($_SESSION['role'] === 'SystemAdmin') ? ($stats['total_students'] ?? 0) : ($stats['my_students'] ?? 0); ?>
                    </h2>
                </div>
                <i class="fa-solid fa-users fa-3x opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning text-dark p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1 opacity-75">Grup Sayısı</h6>
                    <h2 class="mb-0 fw-bold">
                        <?php echo ($_SESSION['role'] === 'SystemAdmin') ? 'Tüm Sistem' : ($stats['my_groups'] ?? 0); ?>
                    </h2>
                </div>
                <i class="fa-solid fa-layer-group fa-3x opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm p-4">
            <h5><i class="fa-solid fa-circle-info text-primary me-2"></i>Bilgilendirme</h5>
            <hr>
            <p><strong>Kulüp ID:</strong> <?php echo $_SESSION['club_id'] ?: 'Sistem Yöneticisi (Tümü)'; ?></p>
            <p>Hoşgeldiniz. Sol menüyü kullanarak işlemlerinizi gerçekleştirebilirsiniz.</p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php'; 
?>