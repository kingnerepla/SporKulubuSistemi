<div class="container-fluid py-4">
    
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold mb-1 text-danger">
                <i class="fa-solid fa-arrow-trend-down me-2"></i><?= $pageTitle ?>
            </h3>
            <p class="text-muted small mb-0">Harcamaları, faturaları ve diğer gider kalemlerini buradan yönetebilirsiniz.</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <span class="small text-uppercase fw-bold opacity-75">Toplam Gider</span>
                    <span class="h3 fw-bold mb-0"><?= number_format($totalExpense, 2, ',', '.') ?> ₺</span>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fa-solid fa-plus me-2"></i>Yeni Gider Ekle
        </button>
    </div>

    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Tarih</th>
                        <th>Kategori</th>
                        <th>Açıklama / Başlık</th>
                        <th class="text-end pe-4">Tutar</th>
                        <th class="text-end pe-4">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($expenses)): foreach($expenses as $ex): ?>
                    <tr>
                        <td class="ps-4">
                            <i class="fa-regular fa-calendar me-2 text-muted"></i>
                            <?= date('d.m.Y', strtotime($ex['ExpenseDate'])) ?>
                        </td>
                        <td>
                            <?php 
                                $badges = [
                                    'Rent' => ['bg-primary', 'Kira'],
                                    'Salary' => ['bg-info', 'Maaş'],
                                    'Equipment' => ['bg-warning', 'Malzeme'],
                                    'Bill' => ['bg-secondary', 'Fatura'],
                                    'Other' => ['bg-light text-dark border', 'Diğer']
                                ];
                                $b = $badges[$ex['Category']] ?? $badges['Other'];
                            ?>
                            <span class="badge <?= $b[0] ?> bg-opacity-75"><?= $b[1] ?></span>
                        </td>
                        <td class="fw-bold text-dark">
                            <?= htmlspecialchars($ex['Title']) ?>
                        </td>
                        <td class="text-end pe-4 font-monospace text-danger fw-bold">
                            - <?= number_format($ex['Amount'], 2, ',', '.') ?> ₺
                        </td>
                        <td class="text-end pe-4">
                            <a href="index.php?page=expense_delete&id=<?= $ex['ExpenseID'] ?>" 
                               class="btn btn-sm btn-white text-danger border"
                               onclick="return confirm('Bu gider kaydını silmek istiyor musunuz?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-receipt fa-3x mb-3 opacity-25"></i>
                            <p>Henüz kayıtlı bir gider bulunmuyor.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=expense_store" method="POST">
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Yeni Gider Girişi</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Başlık / Açıklama</label>
                        <input type="text" name="title" class="form-control" placeholder="Örn: Saha Kirası" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Tutar (₺)</label>
                            <input type="number" name="amount" class="form-control fw-bold text-danger" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Tarih</label>
                            <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Kategori</label>
                        <select name="category" class="form-select">
                            <option value="Rent">Kira / Tesis</option>
                            <option value="Salary">Personel / Maaş</option>
                            <option value="Equipment">Malzeme / Ekipman</option>
                            <option value="Bill">Fatura (Elektrik/Su/Net)</option>
                            <option value="Other">Diğer Giderler</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger px-4">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>