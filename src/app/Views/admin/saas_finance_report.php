<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark text-uppercase">SaaS Finansal Rapor</h3>
            <p class="text-muted small mb-0">Kulüplerden tahsil edilen lisans ve kullanım bedelleri.</p>
        </div>
        <div class="bg-white p-2 px-3 rounded-pill shadow-sm border fw-bold text-primary">
            Toplam Tahsilat: ₺<?= number_format($totalAmount, 2, ',', '.') ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="system_finance">
                <div class="col-md-4">
                    <label class="form-label x-small fw-bold text-muted">BAŞLANGIÇ TARİHİ</label>
                    <input type="date" name="start_date" class="form-control rounded-pill" value="<?= $startDate ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label x-small fw-bold text-muted">BİTİŞ TARİHİ</label>
                    <input type="date" name="end_date" class="form-control rounded-pill" value="<?= $endDate ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Raporu Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small fw-bold text-uppercase">
                                <tr>
                                    <th class="ps-4">Tarih</th>
                                    <th>Kulüp Adı</th>
                                    <th>Açıklama</th>
                                    <th class="text-end pe-4">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($payments)): ?>
                                    <?php foreach($payments as $p): ?>
                                        <tr>
                                            <td class="ps-4 small text-muted"><?= date('d.m.Y H:i', strtotime($p['PaymentDate'])) ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($p['ClubName']) ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($p['Description'] ?? '-') ?></td>
                                            <td class="text-end pe-4 fw-bold text-success">₺<?= number_format($p['Amount'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">Belirtilen tarihlerde ödeme bulunamadı.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Kulüp Dağılımı</h6>
                </div>
                <div class="card-body">
                    <?php if(!empty($analysis)): ?>
                        <?php foreach($analysis as $name => $amount): 
                            $percent = ($totalAmount > 0) ? ($amount / $totalAmount) * 100 : 0;
                        ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1 small">
                                    <span class="fw-bold"><?= $name ?></span>
                                    <span class="text-muted">₺<?= number_format($amount, 0) ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $percent ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <small class="text-muted">Veri yok.</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.7rem; }
</style>