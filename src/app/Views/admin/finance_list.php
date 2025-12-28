<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>Aidat ve Tahsilat</h3>
        <a href="index.php?page=finance_bulk" class="btn btn-outline-danger shadow-sm fw-bold" 
           onclick="return confirm('Tüm aktif öğrencilere bu ayki aidat borçları yansıtılacak. Emin misiniz?')">
            <i class="fa-solid fa-calendar-plus me-2"></i>Toplu Aidat Borçlandır
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3">
                    <div class="small opacity-75">Toplam Tahsilat (Gelir)</div>
                    <div class="fs-3 fw-bold"><?= number_format($stats['income'], 2) ?> ₺</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-3">
                    <div class="small opacity-75">Toplam Harcama (Gider)</div>
                    <div class="fs-3 fw-bold"><?= number_format($stats['expense'], 2) ?> ₺</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3">
                    <div class="small opacity-75">Net Kasa Durumu</div>
                    <div class="fs-3 fw-bold"><?= number_format($stats['income'] - $stats['expense'], 2) ?> ₺</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Öğrenci / Grup</th>
                            <th>Aylık Aidat</th>
                            <th>Güncel Bakiye</th>
                            <th>Durum</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($summary as $row): 
                            $balance = $row['Balance'] ?? 0;
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?= htmlspecialchars($row['FullName']) ?></div>
                                <small class="text-muted"><?= $row['GroupName'] ?? 'Grup Atanmadı' ?></small>
                            </td>
                            <td><?= number_format($row['MonthlyFee'], 0) ?> ₺</td>
                            <td class="fw-bold <?= $balance > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($balance, 2) ?> ₺
                            </td>
                            <td>
                                <?php if($balance > 0): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3">Borçlu</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3">Ödemiş</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-success px-3 shadow-sm" 
                                        onclick="openCollectModal(<?= $row['StudentID'] ?>, '<?= htmlspecialchars($row['FullName']) ?>', <?= $balance ?>)">
                                    <i class="fa-solid fa-hand-holding-dollar me-1"></i>Ödeme Al
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="collectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="index.php?page=finance_collect" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-cash-register me-2"></i>Tahsilat Girişi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="student_id" id="modal_student_id">
                <div class="mb-4">
                    <small class="text-muted d-block mb-1">Öğrenci:</small>
                    <h5 id="modal_student_name" class="fw-bold"></h5>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Alınan Tutar (₺)</label>
                    <input type="number" name="amount" id="modal_amount" step="0.01" class="form-control form-control-lg fw-bold text-success border-2" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-secondary">Ödeme Yöntemi</label>
                        <select name="method" class="form-select border-2">
                            <option value="Nakit">Nakit</option>
                            <option value="Kredi Kartı">Kredi Kartı</option>
                            <option value="Havale/EFT">Havale/EFT</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-secondary">Tarih</label>
                        <input type="text" class="form-control" value="<?= date('d.m.Y') ?>" disabled>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold text-secondary">Açıklama</label>
                    <input type="text" name="description" class="form-control border-2" placeholder="Örn: Ocak ayı aidat ödemesi">
                </div>
            </div>
            <div class="modal-footer border-0 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-success px-5 fw-bold shadow-sm">Tahsilatı Onayla</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCollectModal(id, name, balance) {
    document.getElementById('modal_student_id').value = id;
    document.getElementById('modal_student_name').innerText = name;
    // Eğer borcu varsa tutarı otomatik doldur, yoksa 0 getir
    document.getElementById('modal_amount').value = balance > 0 ? balance : 0;
    new bootstrap.Modal(document.getElementById('collectModal')).show();
}
</script>