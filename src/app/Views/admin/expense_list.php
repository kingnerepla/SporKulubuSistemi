<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fa-solid fa-money-bill-transfer text-danger me-2"></i>Gider Yönetimi</h3>
        <button class="btn btn-danger shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#expenseModal">
            <i class="fa-solid fa-plus me-2"></i>Yeni Gider Ekle
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Tarih</th>
                            <th>Kategori</th>
                            <th>Açıklama</th>
                            <th class="text-end pe-4">Tutar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($expenses)): ?>
                            <?php foreach($expenses as $e): ?>
                            <tr>
                                <td class="ps-4"><?= date('d.m.Y', strtotime($e['ExpenseDate'])) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($e['Category']) ?></span></td>
                                <td class="text-muted small"><?= htmlspecialchars($e['Description']) ?></td>
                                <td class="text-end pe-4 fw-bold text-danger"><?= number_format($e['Amount'], 2) ?> ₺</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted">Henüz kaydedilmiş bir gider bulunmuyor.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="index.php?page=expense_add" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-receipt me-2"></i>Yeni Gider Girişi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Kategori</label>
                    <select name="category" class="form-select border-2" required>
                        <option value="Kira">Kira</option>
                        <option value="Personel / Maaş">Personel / Maaş</option>
                        <option value="Elektrik / Su / Gaz">Elektrik / Su / Gaz</option>
                        <option value="Malzeme Alımı">Malzeme Alımı</option>
                        <option value="Reklam / Tanıtım">Reklam / Tanıtım</option>
                        <option value="Diğer">Diğer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tutar (₺)</label>
                    <input type="number" name="amount" step="0.01" class="form-control form-control-lg text-danger fw-bold border-2" required>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold">Açıklama</label>
                    <input type="text" name="description" class="form-control border-2" placeholder="Örn: Aralık ayı dükkan kirası" required>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-danger px-5 fw-bold shadow-sm">Gideri Kaydet</button>
            </div>
        </form>
    </div>
</div>