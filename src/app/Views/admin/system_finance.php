<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark text-uppercase">SAAS FİNANS MERKEZİ</h3>
            <p class="text-muted small mb-0">Lisans süreleri, tahsilatlar ve sistem giderlerinin merkezi yönetimi.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="bg-white p-2 px-3 rounded-4 shadow-sm border border-success border-2">
                <small class="text-muted d-block x-small fw-bold text-uppercase">Toplam Tahsilat</small>
                <span class="fw-bold text-success">₺<?= number_format($actual_collections, 2, ',', '.') ?></span>
            </div>
            <div class="bg-white p-2 px-3 rounded-4 shadow-sm border border-danger border-2">
                <small class="text-muted d-block x-small fw-bold text-uppercase">Toplam Gider</small>
                <span class="fw-bold text-danger">₺<?= number_format($total_expenses, 2, ',', '.') ?></span>
            </div>
            <div class="bg-dark p-2 px-4 rounded-4 shadow-sm text-white">
                <small class="opacity-50 d-block x-small fw-bold text-uppercase">Net Kar (Dönemlik)</small>
                <span class="fw-bold fs-5">₺<?= number_format($actual_collections - $total_expenses, 2, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="system_finance">
                <div class="col-md-4">
                    <label class="form-label x-small fw-bold text-muted">RAPOR BAŞLANGIÇ</label>
                    <input type="date" name="start_date" class="form-control rounded-pill" value="<?= $startDate ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label x-small fw-bold text-muted">RAPOR BİTİŞ</label>
                    <input type="date" name="end_date" class="form-control rounded-pill" value="<?= $endDate ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">
                        <i class="fa-solid fa-filter me-2"></i>Verileri Filtrele
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-key me-2 text-warning"></i>Kulüp Lisans ve Hakediş Durumları</h6>
                    <?php if($critical_licenses > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $critical_licenses ?> Kritik Kulüp</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted x-small fw-bold text-uppercase">
                                <tr>
                                    <th class="ps-4">Kulüp Adı</th>
                                    <th>Kayıt Tarihi</th>
                                    <th>Lisans Bitiş</th>
                                    <th>Kalan Gün</th>
                                    <th>Durum</th>
                                    <th class="text-end pe-4">Yıllık + Aylık Hakediş</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($club_breakdown as $cb): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= $cb['name'] ?></td>
                                        <td class="small text-muted"><?= date('d.m.Y', strtotime($cb['expiry_date'] . ' -1 year')) ?></td>
                                        <td class="small fw-bold"><?= $cb['expiry_date'] ?></td>
                                        <td>
                                            <span class="fw-bold <?= $cb['days_left'] <= 30 ? 'text-danger' : 'text-success' ?>">
                                                <?= $cb['days_left'] ?> Gün
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($cb['status'] == 'Expired'): ?>
                                                <span class="badge bg-danger rounded-pill">Süresi Doldu</span>
                                            <?php elseif($cb['status'] == 'Warning'): ?>
                                                <span class="badge bg-warning text-dark rounded-pill">Yaklaşıyor</span>
                                            <?php else: ?>
                                                <span class="badge bg-success rounded-pill">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-primary">₺<?= number_format($cb['total'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-success py-3 border-0">
                    <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-money-bill-trend-up me-2"></i>Dönemlik SaaS Tahsilatları</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted x-small fw-bold text-uppercase">
                                <tr>
                                    <th class="ps-4">Tarih</th>
                                    <th>Kulüp</th>
                                    <th class="text-end pe-4">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($saas_payments)): ?>
                                    <?php foreach($saas_payments as $sp): ?>
                                        <tr>
                                            <td class="ps-4 small text-muted"><?= date('d.m.Y', strtotime($sp['PaymentDate'])) ?></td>
                                            <td class="small fw-bold"><?= $sp['ClubName'] ?></td>
                                            <td class="text-end pe-4 text-success fw-bold">₺<?= number_format($sp['Amount'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">Tahsilat kaydı yok.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-dark py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-receipt me-2 text-danger"></i>Sistem Giderleri</h6>
                    <button class="btn btn-outline-light btn-sm rounded-pill x-small fw-bold" data-bs-toggle="modal" data-bs-target="#expenseModal">
                        <i class="fa-solid fa-plus me-1"></i> GİDER EKLE
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted x-small fw-bold text