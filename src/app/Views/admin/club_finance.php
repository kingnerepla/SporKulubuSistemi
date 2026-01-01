<div class="container-fluid py-4 text-dark">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-primary">
                <i class="fa-solid fa-calendar-check me-2"></i>Aidat ve Hizmet Süresi Takibi
            </h4>
            <p class="text-muted small mb-0">Öğrencilerin peşin ödeme periyotları ve hizmet bitiş vadeleri.</p>
        </div>
        <a href="index.php?page=payments" class="btn btn-primary rounded-pill px-4 shadow-sm border-0" style="background: linear-gradient(45deg, #0d6efd, #0099ff);">
            <i class="fa-solid fa-plus me-1"></i> Yeni Tahsilat Girişi
        </a>
    </div>

    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm d-inline-flex border" id="financeTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active rounded-pill position-relative me-2" data-bs-toggle="pill" data-bs-target="#tab-overdue">
                Süresi Dolanlar
                <?php 
                $overdueCount = count(array_filter($students, function($s) { return $s['is_overdue']; }));
                if($overdueCount > 0): ?>
                    <span class="ms-1 badge rounded-pill bg-danger"><?= $overdueCount ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill me-2" data-bs-toggle="pill" data-bs-target="#tab-upcoming">Yaklaşanlar (7 Gün)</button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill" data-bs-toggle="pill" data-bs-target="#tab-all">Tüm Öğrenciler</button>
        </li>
    </ul>

    <div class="tab-content" id="financeTabsContent">
        
        <div class="tab-pane fade show active" id="tab-overdue">
            <div class="row g-3">
                <?php 
                $hasOverdue = false;
                foreach($students as $st): if($st['is_overdue']): $hasOverdue = true; ?>
                    <?php renderStudentCard($st, 'danger', 'Süresi Doldu'); ?>
                <?php endif; endforeach; 
                if(!$hasOverdue): echo '<div class="col-12"><div class="alert alert-success border-0 rounded-4 shadow-sm"><i class="fa-solid fa-circle-check me-2"></i>Tüm öğrencilerin ödemeleri güncel.</div></div>'; endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-upcoming">
            <div class="row g-3">
                <?php 
                $hasUpcoming = false;
                foreach($students as $st): if($st['is_upcoming'] && !$st['is_overdue']): $hasUpcoming = true; ?>
                    <?php renderStudentCard($st, 'warning', 'Süre Azaldı'); ?>
                <?php endif; endforeach; 
                if(!$hasUpcoming): echo '<div class="col-12 text-muted text-center py-5">Önümüzdeki 7 gün içinde süresi dolacak öğrenci yok.</div>'; endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-all">
            <div class="row g-3">
                <?php foreach($students as $st): ?>
                    <?php 
                        $color = $st['is_overdue'] ? 'danger' : ($st['is_upcoming'] ? 'warning' : 'success');
                        $label = $st['is_overdue'] ? 'Süresi Doldu' : ($st['is_upcoming'] ? 'Yaklaşıyor' : 'Aktif Hizmet');
                        renderStudentCard($st, $color, $label); 
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php 
/**
 * Öğrenci Kartı Render Fonksiyonu
 * Tasarımın her yerde aynı kalması ve kodun temiz görünmesi için.
 */
function renderStudentCard($st, $color, $label) { ?>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 border-top border-4 border-<?= $color ?> h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($st['FullName']) ?></h6>
                        <small class="text-muted small"><?= htmlspecialchars($st['GroupName']) ?></small>
                    </div>
                    <span class="badge rounded-pill bg-<?= $color ?> <?= $color == 'warning' ? 'text-dark' : '' ?>"><?= $label ?></span>
                </div>

                <div class="p-2 bg-light rounded-3 mb-3 border text-center">
                    <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.65rem;">
                        <i class="fa-solid fa-hourglass-half me-1"></i>HİZMET BİTİŞ / YENİ VADE:
                    </small>
                    <span class="fw-bold fs-6 <?= $color == 'danger' ? 'text-danger' : 'text-dark' ?>">
                        <?= $st['NextPaymentDate'] ? date('d.m.Y', strtotime($st['NextPaymentDate'])) : 'Tarih Belirlenmemiş' ?>
                    </span>
                </div>

                <div class="row g-2 mb-3 text-center small">
                    <div class="col-6 border-end">
                        <div class="text-muted x-small">Toplam Tahsilat</div>
                        <div class="fw-bold text-dark"><?= number_format($st['TotalPaid'] ?? 0, 0, ',', '.') ?> ₺</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted x-small">Ödenen Dönem</div>
                        <div class="fw-bold text-dark"><?= $st['PaidMonths'] ?> Ay</div>
                    </div>
                </div>

                <div class="mt-auto">
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm w-100 rounded-pill border fw-bold" 
                                type="button" data-bs-toggle="collapse" 
                                data-bs-target="#hist_<?= $st['StudentID'] ?>">
                            <i class="fa-solid fa-list-ul me-1"></i> Geçmiş
                        </button>
                        <a href="index.php?page=payments&student_id=<?= $st['StudentID'] ?>" 
                           class="btn btn-<?= $color == 'danger' ? 'danger' : 'dark' ?> btn-sm w-100 rounded-pill fw-bold shadow-sm">
                           <i class="fa-solid fa-cash-register me-1"></i> Tahsil Et
                        </a>
                    </div>

                    <div class="collapse mt-3" id="hist_<?= $st['StudentID'] ?>">
                        <div class="p-2 bg-white rounded border small">
                            <ul class="list-unstyled mb-0">
                                <?php if(!empty($st['payment_history'])): ?>
                                    <?php foreach($st['payment_history'] as $h): ?>
                                    <li class="d-flex justify-content-between py-1 border-bottom border-light last-child-0">
                                        <span class="text-muted"><?= $h['PaymentMonth'] ?> Dönemi</span>
                                        <span class="fw-bold text-success">+<?= number_format($h['Amount'], 0) ?> ₺</span>
                                    </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="text-center text-muted small py-1 italic">Kayıt bulunamadı.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<style>
    .nav-pills .nav-link { color: #555; font-weight: 500; font-size: 0.85rem; padding: 8px 20px; }
    .nav-pills .nav-link.active { background-color: #0d6efd; color: white; box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2); }
    .x-small { font-size: 0.7rem; }
    .last-child-0:last-child { border-bottom: 0 !important; }
</style>