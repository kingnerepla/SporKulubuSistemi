<div class="container-fluid py-4 text-dark">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Platform Finans & Lisans Denetimi</h4>
            <p class="text-muted small mb-0">SaaS hakedişleri ve merkezi gider yönetimi</p>
        </div>
        <div class="d-flex gap-2">
            <?php if($critical_licenses > 0): ?>
                <span class="badge bg-danger rounded-pill p-2 px-3 shadow-sm">
                    <i class="fa-solid fa-bell me-2 pulse"></i><?= $critical_licenses ?> Kulübün Süresi Azaldı!
                </span>
            <?php endif; ?>
            <button class="btn btn-danger btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fa-solid fa-plus me-2"></i>Gider Ekle
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-dark text-white border-start border-warning border-4">
                <small class="text-warning d-block mb-1 fw-bold small">TOPLAM SAAS GELİRİ</small>
                <h3 class="fw-bold mb-0"><?= number_format($total_saas_revenue, 0, ',', '.') ?> ₺</h3>
            </div>
        </div>
        <div class="col-md-4 text-dark">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-danger border-4">
                <small class="text-muted d-block mb-1 fw-bold small">SİSTEM GİDERLERİ</small>
                <h3 class="fw-bold mb-0 text-danger"><?= number_format($total_expenses, 0, ',', '.') ?> ₺</h3>
            </div>
        </div>
        <div class="col-md-4 text-dark">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-success border-4">
                <small class="text-muted d-block mb-1 fw-bold small">NET PLATFORM KARI</small>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($total_saas_revenue - $total_expenses, 0, ',', '.') ?> ₺</h3>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4 border overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Kulüp Lisans & Hakediş Takibi</span>
                </div>
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Kulüp Adı</th>
                            <th>Aktif Sporcu</th>
                            <th>Lisans Bitiş</th>
                            <th>Kalan Süre</th>
                            <th class="text-end pe-4">Sistem Alacağı</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark bg-white">
                        <?php foreach($club_breakdown as $cb): ?>
                        <tr class="<?= $cb['status'] == 'Expired' ? 'table-danger' : ($cb['status'] == 'Warning' ? 'table-warning' : '') ?>">
                            <td class="ps-4 py-3 fw-bold"><?= $cb['name'] ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $cb['students'] ?> Öğrenci</span></td>
                            <td><?= $cb['expiry_date'] ?></td>
                            <td>
                                <?php if($cb['days_left'] <= 0): ?>
                                    <span class="badge bg-danger">Süre Doldu</span>
                                <?php elseif($cb['days_left'] <= 30): ?>
                                    <span class="text-danger fw-bold"><i class="fa-solid fa-clock me-1"></i><?= $cb['days_left'] ?> Gün</span>
                                <?php else: ?>
                                    <span class="text-success"><?= $cb['days_left'] ?> Gün</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 fw-bold text-primary"><?= number_format($cb['total'], 0) ?> ₺</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-12 mt-3">
            <div class="card border-0 shadow-sm rounded-4 border overflow-hidden">
                <div class="card-header bg-white py-3 fw-bold border-bottom text-dark">Şirket Gider Kayıtları</div>
                <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                    <thead class="bg-light text-muted text-dark">
                        <tr><th class="ps-4">Gider</th><th>Kategori</th><th>Tarih</th><th class="text-end pe-4">Tutar</th></tr>
                    </thead>
                    <tbody class="text-dark bg-white">
                        <?php foreach($real_expenses as $re): ?>
                        <tr>
                            <td class="ps-4 py-2"><?= $re['Title'] ?></td>
                            <td><span class="badge bg-light text-muted border"><?= $re['Category'] ?></span></td>
                            <td><?= date('d.m.Y', strtotime($re['ExpenseDate'])) ?></td>
                            <td class="text-end pe-4 fw-bold text-danger"><?= number_format($re['Amount'], 0) ?> ₺</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>