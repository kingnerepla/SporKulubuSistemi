<div class="container-fluid p-0">
    <?php if (isset($club) && $club['IsActive'] == 0): ?>
        <div class="row justify-content-center py-5">
            <div class="col-md-7 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="bg-danger p-5 text-center text-white">
                            <div class="mb-3">
                                <i class="fa-solid fa-lock fa-4x opacity-50"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Hizmetiniz Askıya Alındı</h2>
                            <p class="opacity-75 mb-0 small">SaaS hizmet bedeli ödemeniz geciktiği için sistem fonksiyonları geçici olarak durdurulmuştur.</p>
                        </div>
                        
                        <div class="p-4 p-md-5">
                            <div class="text-center mb-5">
                                <small class="text-muted fw-bold text-uppercase d-block mb-1">Güncel Toplam Borcunuz</small>
                                <h1 class="display-5 fw-bold text-dark">₺<?= number_format($currentDebt ?? 0, 2, ',', '.') ?></h1>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3 border text-center">
                                        <small class="text-muted d-block x-small text-uppercase">Yıllık Lisans</small>
                                        <span class="fw-bold text-dark small">₺<?= number_format($club['AnnualLicenseFee'] ?? 0, 0, ',', '.') ?></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3 border text-center">
                                        <small class="text-muted d-block x-small text-uppercase">Sporcu Kullanım</small>
                                        <span class="fw-bold text-primary small"><?= $stats['totalStudents'] ?? 0 ?> Aktif Sporcu</span>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning border-0 small rounded-3 mb-5">
                                <div class="d-flex align-items-center">
                                    <i class="fa-solid fa-circle-info mt-1 me-2 fs-5 text-warning"></i>
                                    <div>
                                        <strong>Nasıl Aktifleştirilir?</strong><br>
                                        Lütfen ödemenizi gerçekleştirip sistem yöneticisine bilgi veriniz. Tahsilat onaylandığında tüm fonksiyonlar otomatik olarak açılacaktır.
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="index.php?page=logout" class="btn btn-outline-secondary rounded-pill py-2 fw-bold">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i>Güvenli Çıkış Yap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php exit; ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark">Hoş Geldiniz, <?= htmlspecialchars($name); ?> 👋</h3>
            <p class="text-muted small">Kulübünüzdeki son durum ve kritik özetler aşağıdadır. 
                <a href="index.php?page=profile" class="text-decoration-none ms-2"><i class="fa-solid fa-user-gear me-1"></i>Profilim</a>
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark shadow-sm border p-2 px-3">
                <i class="fa-solid fa-calendar-day me-1 text-primary"></i> <?= date('d.m.Y'); ?>
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase x-small">Toplam Öğrenci</small>
                    <h2 class="fw-bold mb-0"><?= $stats['totalStudents'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase x-small">Aylık Beklenen Gelir</small>
                    <h2 class="fw-bold mb-0">₺<?= number_format($stats['expectedRevenue'] ?? 0, 0, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase x-small">Tahsil Edilen</small>
                    <h2 class="fw-bold mb-0">₺<?= number_format($stats['receivedRevenue'] ?? 0, 0, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white bg-primary h-100">
                <div class="card-body">
                    <small class="text-uppercase fw-bold opacity-75 x-small">Aktif Gruplar</small>
                    <h2 class="fw-bold mb-0"><?= $stats['totalGroups'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-danger">
                        <i class="fa-solid fa-hand-holding-dollar me-2"></i>Kredisi Bitenler / Tahsilat Bekleyenler
                    </h6>
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 small"><?= count($debtStudents) ?> Kayıt</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted x-small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-4">Öğrenci Adı</th>
                                    <th class="text-center">Kalan Hak</th>
                                    <th>Paket Ücreti</th>
                                    <th class="text-end pe-4">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($debtStudents)): ?>
                                    <?php foreach ($debtStudents as $stu): 
                                        $isOut = ($stu['RemainingSessions'] <= 0);
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark small"><?= htmlspecialchars($stu['FullName']); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $isOut ? 'bg-danger' : 'bg-warning text-dark' ?> rounded-pill px-3">
                                                    <?= $stu['RemainingSessions']; ?> Ders
                                                </span>
                                            </td>
                                            <td class="fw-bold text-muted small">₺<?= number_format($stu['PackageFee'], 0, ',', '.'); ?></td>
                                            <td class="text-end pe-4">
                                                <a href="index.php?page=payments&student_id=<?= $stu['StudentID'] ?>" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm x-small fw-bold">TAHSİL ET</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted small">Kredisi biten öğrenci yok.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-success">
                        <i class="fa-solid fa-receipt me-2"></i>Son Tahsilatlar (Öğrenciler)
                    </h6>
                    <a href="index.php?page=finance" class="small text-decoration-none text-muted fw-bold x-small">TÜMÜNÜ GÖR</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted x-small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-4">Öğrenci Adı</th>
                                    <th>Tutar</th>
                                    <th>Tarih</th>
                                    <th class="text-end pe-4">Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($criticalClubs)): ?>
                                    <?php foreach ($criticalClubs as $item): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark small"><?= htmlspecialchars($item['FullName']); ?></td>
                                            <td class="fw-bold text-success small">₺<?= number_format($item['Amount'], 0, ',', '.'); ?></td>
                                            <td class="text-muted small"><?= date('d.m.Y', strtotime($item['PaymentDate'])); ?></td>
                                            <td class="text-end pe-4 text-success small fw-bold">OK</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted small">Tahsilat kaydı yok.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>SaaS Kullanım Ödemeleriniz
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted x-small fw-bold text-uppercase">
                                <tr>
                                    <th class="ps-4">Tarih</th>
                                    <th>Açıklama</th>
                                    <th class="text-end pe-4">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($saasHistory)): ?>
                                    <?php foreach($saasHistory as $sh): ?>
                                        <tr>
                                            <td class="ps-4 small text-muted"><?= date('d.m.Y', strtotime($sh['PaymentDate'])) ?></td>
                                            <td class="small fw-bold"><?= htmlspecialchars($sh['Description'] ?? 'Kullanım Bedeli') ?></td>
                                            <td class="text-end pe-4 text-primary fw-bold small">₺<?= number_format($sh['Amount'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted small">Henüz bir kayıt yok.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white p-4 mb-4 rounded-4 shadow-lg">
                <h5 class="fw-bold mb-4 small text-uppercase opacity-75">Hızlı İşlemler</h5>
                <div class="d-grid gap-3">
                    <a href="index.php?page=students" class="btn btn-outline-light text-start border-secondary py-2 small">
                        <i class="fa-solid fa-users me-2 text-info"></i> Öğrenci Listesi
                    </a>
                    <a href="index.php?page=attendance_report" class="btn btn-info text-dark fw-bold text-start border-0 py-2 small">
                        <i class="fa-solid fa-chart-line me-2"></i> Yoklama Raporları
                    </a>
                    <a href="index.php?page=finance" class="btn btn-outline-success text-start border-secondary py-2 small">
                        <i class="fa-solid fa-file-invoice-dollar me-2 text-success"></i> Finans Takibi
                    </a>
                    <hr class="opacity-25 border-secondary my-2">
                    <a href="index.php?page=student_add" class="btn btn-primary text-start shadow-sm fw-bold border-0 py-2 small">
                        <i class="fa-solid fa-plus me-2"></i> Yeni Öğrenci Kaydı
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-light p-4 border-start border-primary border-4 shadow-sm mb-4">
                <h6 class="fw-bold text-dark mb-4 small text-uppercase">SaaS Anlaşma Detayları</h6>
                <div class="mb-3">
                    <small class="text-muted d-block fw-bold x-small">YILLIK LİSANS BEDELİ</small>
                    <span class="fw-bold text-dark small">₺<?= number_format($club['AnnualLicenseFee'] ?? 0, 0, ',', '.') ?></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block fw-bold x-small">SPORCU BAŞI KULLANIM</small>
                    <span class="fw-bold text-primary small">₺<?= number_format($club['MonthlyPerStudentFee'] ?? 0, 2, ',', '.') ?></span>
                </div>
                <div class="mb-0 mt-3">
                    <small class="text-muted d-block fw-bold x-small">LİSANS BİTİŞ</small>
                    <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm small fw-bold mt-1">
                        <?= date('d.m.Y', strtotime($club['LicenseEndDate'] ?? 'now')) ?>
                    </span>
                </div>
                <div class="mt-4 pt-3 border-top border-secondary border-opacity-10">
                    <p class="text-muted mb-0 fst-italic x-small">
                        * Ödemeleriniz sisteme işlendiğinde otomatik olarak güncellenir.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.65rem; }
    .table thead th { letter-spacing: 0.5px; border-bottom: none !important; }
    .card { transition: all 0.2s ease-in-out; }
</style>