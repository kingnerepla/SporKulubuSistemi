<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark text-uppercase">SİSTEM GENEL MERKEZİ</h3>
            <p class="text-muted small mb-0">Platform genelindeki kulüp performansları ve finansal hakedişler.</p>
        </div>
        <div class="text-end">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-dark shadow-sm border p-2 px-3">
                    <i class="fa-solid fa-calendar-day me-1 text-primary"></i> <?= date('d.m.Y'); ?>
                </span>
                <span class="badge bg-dark text-white shadow-sm p-2 px-3">
                    <i class="fa-solid fa-shield-halved me-2"></i> SÜPER YÖNETİCİ
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white p-3 rounded-4">
                <small class="opacity-75 fw-bold text-uppercase x-small">Toplam Kulüp</small>
                <h2 class="fw-bold mb-0"><?= $stats['totalClubs'] ?? 0 ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white p-3 rounded-4">
                <small class="opacity-75 fw-bold text-uppercase x-small">Aktif Toplam Sporcu</small>
                <h2 class="fw-bold mb-0"><?= $stats['totalStudents'] ?? 0 ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white p-3 rounded-4 border-start border-white border-4">
                <small class="opacity-75 fw-bold text-uppercase x-small">Beklenen Toplam Hakediş</small>
                <h2 class="fw-bold mb-0">₺<?= number_format($stats['totalExpected'] ?? 0, 0, ',', '.') ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-dark text-white p-3 rounded-4 border-start border-warning border-4">
                <small class="opacity-75 fw-bold text-uppercase x-small">Genel Ciro (Öğrenci)</small>
                <h2 class="fw-bold mb-0">₺<?= number_format($stats['totalRevenueAllTime'] ?? 0, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fa-solid fa-building-circle-check me-2 text-primary"></i>Kulüp Denetim ve SaaS Hakedişleri
            </h6>
            <a href="index.php?page=club_add" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Yeni Kulüp Ekle
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small fw-bold text-uppercase">
                        <tr>
                            <th class="ps-4">Kulüp Bilgisi</th>
                            <th>Yıllık Lisans</th>
                            <th>Aylık Hizmet (Sporcu)</th>
                            <th class="text-danger">Kalan Bakiye</th>
                            <th class="text-end pe-4">Yönetim</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($allClubs)): ?>
                            <?php foreach($allClubs as $c): 
                                $today = date('Y-m-d');
                                $isExpired = ($c['LicenseEndDate'] && $c['LicenseEndDate'] < $today);
                                $statusLabel = $c['IsActive'] ? ($isExpired ? 'Süresi Doldu' : 'Aktif') : 'Donduruldu';
                                $statusClass = $c['IsActive'] ? ($isExpired ? 'bg-warning text-dark' : 'bg-success text-white') : 'bg-danger text-white';
                                
                                $licenseFee = (float)($c['license_fee_debt'] ?? 0);
                                $monthlyDebt = (float)($c['monthly_usage_debt'] ?? 0);
                                $currentDebt = (float)($c['current_debt'] ?? 0);
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($c['ClubName']) ?></div>
                                    <div class="x-small text-muted">
                                        <span class="badge <?= $statusClass ?> p-1 px-2 rounded-pill me-1" style="font-size: 0.6rem;"><?= $statusLabel ?></span>
                                        Kayıt: <?= date('d.m.Y', strtotime($c['CreatedAt'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small">₺<?= number_format($licenseFee, 0, ',', '.') ?></div>
                                    <small class="text-muted x-small">Bitiş: <?= $c['LicenseEndDate'] ? date('d.m.Y', strtotime($c['LicenseEndDate'])) : 'Belirsiz' ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary small">₺<?= number_format($monthlyDebt, 0, ',', '.') ?></div>
                                    <small class="text-muted x-small"><?= $c['StudentCount'] ?> Sporcu x ₺<?= number_format($c['MonthlyPerStudentFee'], 0) ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold <?= $currentDebt > 0 ? 'text-danger' : 'text-success' ?>">
                                        ₺<?= number_format($currentDebt, 2, ',', '.') ?>
                                    </div>
                                    <?php if($currentDebt <= 0): ?>
                                        <span class="badge bg-success-light text-success x-small" style="font-size: 0.6rem;">ÖDEME TAMAM</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                        <button onclick="openPaymentModal(<?= $c['ClubID'] ?>, '<?= htmlspecialchars($c['ClubName']) ?>', <?= $currentDebt ?>)" 
                                                class="btn btn-white btn-sm text-success border-end" title="Ödeme İşle">
                                            <i class="fa-solid fa-money-bill-transfer"></i>
                                        </button>
                                        <a href="index.php?page=club_edit&id=<?= $c['ClubID'] ?>" class="btn btn-white btn-sm border-end" title="Düzenle / Uzat">
                                            <i class="fa-solid fa-pen-to-square text-muted"></i>
                                        </a>
                                        <button onclick="confirmStatus(<?= $c['ClubID'] ?>, <?= $c['IsActive'] ? 0 : 1 ?>, '<?= htmlspecialchars($c['ClubName']) ?>')" 
                                                class="btn btn-white btn-sm border-end <?= $c['IsActive'] ? 'text-danger' : 'text-success' ?>" 
                                                title="<?= $c['IsActive'] ? 'Askıya Al' : 'Aktifleştir' ?>">
                                            <i class="fa-solid <?= $c['IsActive'] ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                        </button>
                                        <button onclick="confirmImpersonate(<?= $c['ClubID'] ?>, '<?= htmlspecialchars($c['ClubName']) ?>')" 
                                                class="btn btn-white btn-sm text-primary" 
                                                title="Kulüp Paneline Sız">
                                            <i class="fa-solid fa-right-to-bracket"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Son SaaS Tahsilatları
                    </h6>
                    <a href="index.php?page=system_finance" class="btn btn-link btn-sm text-decoration-none text-muted p-0 x-small fw-bold">
                        TÜMÜNÜ GÖR <i class="fa-solid fa-chevron-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small fw-bold text-uppercase">
                                <tr>
                                    <th class="ps-4">Kulüp</th>
                                    <th>Tutar</th>
                                    <th>Tarih</th>
                                    <th>Açıklama</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($saasHistory)): ?>
                                    <?php foreach($saasHistory as $h): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark small"><?= htmlspecialchars($h['ClubName']) ?></div>
                                            </td>
                                            <td>
                                                <div class="text-success fw-bold small">₺<?= number_format($h['Amount'], 2, ',', '.') ?></div>
                                            </td>
                                            <td>
                                                <div class="small text-muted"><?= date('d.m.Y H:i', strtotime($h['PaymentDate'])) ?></div>
                                            </td>
                                            <td>
                                                <div class="x-small text-muted text-truncate" style="max-width: 150px;"><?= htmlspecialchars($h['Description'] ?? '-') ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">Henüz tahsilat kaydı bulunmuyor.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4 h-100 shadow-lg">
                <h6 class="fw-bold opacity-75 mb-4 text-uppercase small">Alacak Analizi</h6>
                
                <div class="mb-4">
                    <small class="d-block opacity-50 x-small fw-bold mb-1">TOPLAM LİSANS ALACAĞI</small>
                    <h3 class="fw-bold text-warning">₺<?= number_format(array_sum(array_column($allClubs, 'license_fee_debt')), 0, ',', '.') ?></h3>
                </div>

                <div class="mb-4">
                    <small class="d-block opacity-50 x-small fw-bold mb-1">TOPLAM AYLIK HİZMET ALACAĞI</small>
                    <h3 class="fw-bold text-info">₺<?= number_format(array_sum(array_column($allClubs, 'monthly_usage_debt')), 0, ',', '.') ?></h3>
                </div>

                <hr class="opacity-10 my-4">

                <div class="d-grid mb-4">
                    <a href="index.php?page=system_finance" class="btn btn-primary rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-chart-line me-2"></i>Detaylı Finansal Rapor
                    </a>
                </div>

                <div class="bg-primary bg-opacity-10 p-3 rounded-3 border border-primary border-opacity-10 mt-auto">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-circle-info me-2 text-primary mt-1"></i>
                        <small class="small opacity-75">Hakedişler, kulüplerin aktif sporcu sayılarına göre anlık hesaplanmaktadır.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// 1. Ödeme Tahsilat Modalı
function openPaymentModal(clubId, clubName, currentDebt) {
    Swal.fire({
        title: 'SaaS Ödeme Tahsilatı',
        html: `
            <div class="text-start px-2">
                <p class="mb-3 small"><strong>${clubName}</strong> için gelen ödemeyi işleyin.</p>
                <div class="mb-3">
                    <label class="form-label x-small fw-bold text-muted text-uppercase">Tutar (₺)</label>
                    <input type="number" id="swal_amount" class="form-control rounded-pill" value="${currentDebt}">
                </div>
                <div class="mb-3">
                    <label class="form-label x-small fw-bold text-muted text-uppercase">Açıklama</label>
                    <input type="text" id="swal_desc" class="form-control rounded-pill" placeholder="Örn: 2026 Lisans Ödemesi">
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Kaydet',
        confirmButtonColor: '#2ecc71',
        cancelButtonText: 'Vazgeç',
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) {
            const amount = document.getElementById('swal_amount').value;
            const desc = document.getElementById('swal_desc').value;
            if(!amount || amount <= 0) {
                Swal.fire('Hata', 'Geçerli bir tutar girin!', 'error');
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?page=add_saas_payment';
            const params = { club_id: clubId, amount: amount, description: desc };
            for (const key in params) {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = key; input.value = params[key];
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// 2. Kilitleme / Aktifleştirme Onayı
function confirmStatus(id, status, clubName) {
    const isSuspending = (status === 0);
    Swal.fire({
        title: isSuspending ? 'Kulübü Askıya Al?' : 'Kulübü Aktifleştir?',
        text: `${clubName} erişimi ${isSuspending ? 'kapatılacaktır' : 'açılacaktır'}.`,
        icon: isSuspending ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: 'Evet, Onayla',
        confirmButtonColor: isSuspending ? '#e74c3c' : '#2ecc71',
        cancelButtonText: 'Vazgeç',
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=club_status_toggle&id=${id}&status=${status}`;
        }
    });
}

// 3. Sızma Modu Onayı
function confirmImpersonate(id, clubName) {
    Swal.fire({
        title: 'Denetim Modu',
        html: `<strong>${clubName}</strong> paneline sızmak üzeresiniz.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sız',
        confirmButtonColor: '#3498db',
        cancelButtonText: 'Vazgeç',
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?page=club_impersonate&id=${id}`;
        }
    });
}
</script>

<style>
    .bg-success-light { background-color: #d1e7dd; }
    .swal2-popup { font-family: 'Segoe UI', sans-serif !important; font-size: 0.9rem !important; }
    .x-small { font-size: 0.7rem; }
    .btn-white { background: #fff; border: 1px solid #eee; }
    .btn-white:hover { background: #f8f9fa; }
    .table thead th { letter-spacing: 0.5px; white-space: nowrap; }
    .progress { background-color: rgba(255,255,255,0.1); }
</style>