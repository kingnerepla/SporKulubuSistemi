<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center bg-white p-2 px-3 rounded-4 shadow-sm mb-3 border text-dark">
        <div class="d-flex align-items-center">
            <?php if(isset($selectedClub)): ?>
                <img src="<?= $selectedClub['LogoPath'] ?? 'assets/img/default-club.png' ?>" class="rounded-circle border me-2" width="38" height="38" style="object-fit: cover;" onerror="this.src='https://via.placeholder.com/38'">
                <div>
                    <h6 class="fw-bold mb-0 text-primary small"><?= htmlspecialchars($selectedClub['ClubName']) ?></h6>
                    <span class="badge bg-danger-subtle text-danger p-1 shadow-sm" style="font-size: 0.6rem;">DENETİM MODU</span>
                </div>
            <?php else: ?>
                <div class="bg-light p-2 rounded-circle me-2"><i class="fa-solid fa-building-circle-check text-secondary small"></i></div>
                <h6 class="fw-bold mb-0 small text-dark">Sistem Kulüp Denetim Paneli</h6>
            <?php endif; ?>
        </div>
        <div>
            <?php if(isset($_SESSION['selected_club_id'])): ?>
                <a href="index.php?page=clear_selection" class="btn btn-xs btn-outline-secondary rounded-pill px-2 me-1" style="font-size: 0.7rem;">Kapat</a>
            <?php endif; ?>
            <div class="dropdown d-inline-block text-dark">
                <button class="btn btn-sm btn-primary rounded-pill px-3 dropdown-toggle" style="font-size: 0.75rem;" type="button" data-bs-toggle="dropdown">Kulüp Seç</button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                    <?php foreach($clubs as $c): ?>
                        <li><a class="dropdown-item small" href="index.php?page=select_club&id=<?= $c['ClubID'] ?>"><?= htmlspecialchars($c['ClubName']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['selected_club_id'])): ?>
    <div class="row g-3">
        <div class="col-md-9 text-dark">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-primary border-4">
                        <small class="text-muted d-block mb-1" style="font-size: 0.65rem;">KULÜP TOPLAM TAHSİLAT</small>
                        <h5 class="fw-bold mb-0 text-dark"><?= number_format((float)$stats['revenue'], 0) ?> ₺</h5>
                        <div class="text-muted" style="font-size: 0.6rem;">Öğrenci aidat gelirleri</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-dark text-white border-start border-warning border-4">
                        <small class="text-warning-emphasis d-block mb-1" style="font-size: 0.65rem;">SİSTEM HAKEDİŞİ (SaaS)</small>
                        <h5 class="fw-bold mb-0 text-warning"><?= number_format($stats['system_debt'], 0) ?> ₺</h5>
                        <div class="text-white-50" style="font-size: 0.6rem;">
                            Lisans: <?= number_format($stats['license'], 0) ?>₺ + Birim: <?= $stats['per_student'] ?>₺
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-dark">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-light border-start border-success border-4">
                        <small class="text-muted d-block mb-1" style="font-size: 0.65rem;">KULÜP NET KAZANÇ</small>
                        <h5 class="fw-bold mb-0 text-success"><?= number_format($stats['revenue'] - $stats['system_debt'], 0) ?> ₺</h5>
                        <div class="text-muted" style="font-size: 0.6rem;">Sistem borcu sonrası kalan</div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs border-0 mb-2" style="font-size: 0.8rem;">
                <li class="nav-item"><a class="nav-link border-0 <?= $activeTab == 'students' ? 'active fw-bold text-primary border-bottom border-primary border-2' : 'text-muted' ?>" href="index.php?page=clubs&tab=students">Sporcular (<?= $stats['students'] ?>)</a></li>
                <li class="nav-item"><a class="nav-link border-0 <?= $activeTab == 'finance' ? 'active fw-bold text-primary border-bottom border-primary border-2' : 'text-muted' ?>" href="index.php?page=clubs&tab=finance">Kulüp Aidatları</a></li>
                <li class="nav-item"><a class="nav-link border-0 <?= $activeTab == 'saas_billing' ? 'active fw-bold text-danger border-bottom border-danger border-2' : 'text-muted' ?>" href="index.php?page=clubs&tab=saas_billing"><i class="fa-solid fa-handshake me-1"></i>Sözleşme Ayarı</a></li>
            </ul>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden border">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.75rem;">
                    <thead class="bg-light text-muted text-dark">
                        <tr>
                            <th class="ps-3 py-2">Kalem / İsim</th>
                            <th><?= $activeTab == 'saas_billing' ? 'Anlaşma Tutarı' : 'Detay'; ?></th>
                            <th class="text-end pe-3">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        <?php if($activeTab == 'saas_billing'): ?>
                            <tr>
                                <td class="ps-3 py-2 fw-bold text-dark">Yıllık Sabit Lisans Bedeli</td>
                                <td><?= number_format($stats['license'], 0) ?> ₺</td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-xs btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#editAgreementModal">Düzenle</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3 py-2 fw-bold text-dark">Sporcu Başı Aylık (<?= $stats['per_student'] ?>₺)</td>
                                <td><?= number_format($stats['students'] * $stats['per_student'], 0) ?> ₺</td>
                                <td class="text-end pe-3 text-muted">Dinamik</td>
                            </tr>
                        <?php elseif(!empty($tabData)): foreach($tabData as $row): ?>
                            <tr>
                                <td class="ps-3 py-2 fw-bold text-dark"><?= htmlspecialchars($row['FullName'] ?? $row['StudentName'] ?? 'Bilinmiyor') ?></td>
                                <td><?php if($activeTab == 'students') echo htmlspecialchars($row['GroupName'] ?? 'Grup Yok'); elseif($activeTab == 'finance') echo '<b class="text-success">'.number_format((float)($row['Amount'] ?? 0), 0) . ' ₺</b>'; ?></td>
                                <td class="text-end pe-3 text-muted">Kayıtlı</td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted small italic">Veri bulunamadı.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border mb-3 text-dark text-center">
                <h6 class="fw-bold small mb-3 border-bottom pb-2 text-danger">PLATFORM HAKEDİŞİ</h6>
                <small class="text-muted d-block" style="font-size: 0.6rem;">TOPLAM ALACAĞINIZ</small>
                <h4 class="fw-bold text-dark mb-3"><?= number_format($stats['system_debt'], 0) ?> ₺</h4>
                <button class="btn btn-dark btn-sm w-100 rounded-pill mb-2 shadow-sm" style="font-size: 0.7rem;"><i class="fa-solid fa-file-invoice me-1"></i>Fatura Kes</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAgreementModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm text-dark">
            <form action="index.php?page=update_agreement" method="POST" class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="fw-bold mb-0">Anlaşmayı Güncelle</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-dark">
                    <input type="hidden" name="club_id" value="<?= $_SESSION['selected_club_id'] ?>">
                    <div class="mb-3">
                        <label class="small text-muted mb-1">Yıllık Lisans (₺)</label>
                        <input type="number" name="annual_license" class="form-control form-control-sm" value="<?= $stats['license'] ?>" required>
                    </div>
                    <div class="mb-0">
                        <label class="small text-muted mb-1">Sporcu Başı Ücret (₺)</label>
                        <input type="number" name="per_student" class="form-control form-control-sm" value="<?= $stats['per_student'] ?>" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border text-dark">
        <div class="card-header bg-light fw-bold py-3 ps-4 border-bottom" style="font-size: 0.85rem;">Sistemdeki Kulüpler</div>
        <div class="list-group list-group-flush">
            <?php foreach($clubs as $c): ?>
            <a href="index.php?page=select_club&id=<?= $c['ClubID'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 ps-4">
                <div class="d-flex align-items-center">
                    <img src="<?= $c['LogoPath'] ?? 'assets/img/default-club.png' ?>" class="rounded-circle border me-3" width="32" height="32" onerror="this.src='https://via.placeholder.com/32'">
                    <span class="fw-medium text-dark small"><?= htmlspecialchars($c['ClubName']) ?></span>
                </div>
                <span class="badge bg-light text-primary border rounded-pill" style="font-size: 0.6rem;">Denetle</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .nav-tabs .nav-link { padding: 0.5rem 1rem; border-radius: 0; }
    .nav-tabs .nav-link.active { border-bottom: 2px solid #0d6efd !important; background: transparent; }
    .list-group-item:hover { background-color: #f8fafc; border-left: 3px solid #0d6efd; }
    .btn-xs { padding: 2px 8px; font-size: 0.65rem; }
</style>