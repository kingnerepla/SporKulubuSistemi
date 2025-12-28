<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark">Hoş Geldiniz, <?php echo htmlspecialchars($name); ?></h3>
            <p class="text-muted small">Sistem genelindeki son durum ve kritik özetler aşağıdadır.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Toplam Kulüp</small>
                    <h2 class="fw-bold mb-0"><?php echo $stats['totalClubs']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Sistem Geliri</small>
                    <h2 class="fw-bold mb-0">₺<?php echo number_format($stats['totalRevenue'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Bekleyen Ödemeler</small>
                    <h2 class="fw-bold mb-0"><?php echo $stats['pendingPayments']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white bg-danger">
                <div class="card-body">
                    <small class="text-uppercase fw-bold opacity-75">Kritik Lisans</small>
                    <h2 class="fw-bold mb-0"><?php echo $stats['expiredLicenses']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-danger">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Dikkat Gerektiren Kulüpler
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Kulüp Adı</th>
                                    <th>Durum</th>
                                    <th>Tutar</th>
                                    <th>Vade Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($criticalClubs)): ?>
                                    <?php foreach ($criticalClubs as $club): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($club['ClubName']); ?></td>
                                            <td><span class="badge bg-danger-subtle text-danger px-3">Ödeme Bekliyor</span></td>
                                            <td>₺<?php echo number_format($club['Amount'], 2); ?></td>
                                            <td class="text-muted small"><?php echo date('d.m.Y', strtotime($club['PaymentDate'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-circle-check text-success fa-2x d-block mb-2"></i>
                                            Şu an dikkat gerektiren bir durum yok.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white p-4 h-100">
                <h5 class="fw-bold mb-3">Hızlı İşlemler</h5>
                <div class="d-grid gap-2">
                    <a href="index.php?page=clubs" class="btn btn-outline-info text-start">
                        <i class="fa-solid fa-list me-2"></i> Kulüp Listesini Gör
                    </a>
                    <a href="index.php?page=system_finance" class="btn btn-outline-success text-start">
                        <i class="fa-solid fa-vault me-2"></i> Finans Paneline Git
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>