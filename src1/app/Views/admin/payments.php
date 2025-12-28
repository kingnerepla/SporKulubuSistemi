<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fa-solid fa-wallet me-2"></i>Kasa ve Tahsilat</h2>
        <span class="text-muted">Bu ayki toplam tahsilat durumu.</span>
    </div>
    <div class="text-end">
        <h3 class="text-success fw-bold m-0"><?php echo number_format($totalIncome ?? 0, 2); ?> ₺</h3>
        <small class="text-muted">Toplam Kasa</small>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12 text-end">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
            <i class="fa-solid fa-hand-holding-dollar"></i> Tahsilat Al (Ödeme Ekle)
        </button>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">İşlem başarıyla kaydedildi.</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="paymentTable">
                <thead class="table-light">
                    <tr>
                        <th>Ödeme Tarihi</th>
                        <th>Dönem (Ay)</th> <th>Öğrenci</th>
                        <th>Grup</th>
                        <th>Ödeme Türü</th>
                        <th class="text-end">Tutar</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $pay): ?>
                    <tr>
                        <td><?php echo date('d.m.Y', strtotime($pay['PaymentDate'])); ?></td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($pay['PaymentMonth'] ?? '-'); ?>
                            </span>
                        </td>
                        <td><strong><?php echo htmlspecialchars($pay['StudentName'] ?? 'Bilinmiyor'); ?></strong></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars($pay['GroupName'] ?? '-'); ?></small></td>
                        <td>
                            <?php $pType = $pay['PaymentType'] ?? 'Nakit'; ?>
                            <?php if($pType == 'Nakit'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Nakit</span>
                            <?php elseif($pType == 'Kredi Kartı'): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Kredi Kartı</span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border"><?php echo htmlspecialchars($pType); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-bold text-dark">
                            <?php echo number_format($pay['Amount'], 2); ?> ₺
                        </td>
                        <td class="text-end">
                            <a href="index.php?page=payment_delete&id=<?php echo $pay['PaymentID']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Bu tahsilat kaydını silmek kasayı etkileyecektir. Emin misiniz?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($payments)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Henüz hiç tahsilat yapılmamış.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=payment_store" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-money-bill-wave me-2"></i>Tahsilat Al</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Öğrenci Seçiniz</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">-- Listeden Seçin --</option>
                            <?php foreach($students as $st): ?>
                                <option value="<?php echo $st['StudentID']; ?>">
                                    <?php echo htmlspecialchars($st['FullName']) . ' (' . htmlspecialchars($st['GroupName']) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Aidat Dönemi (Hangi Ay?)</label>
                        <input type="month" name="payment_month" class="form-control" value="<?php echo date('Y-m'); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tutar (₺)</label>
                            <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ödeme İşlem Tarihi</label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ödeme Yöntemi</label>
                        <select name="payment_type" class="form-select">
                            <option value="Nakit">Nakit</option>
                            <option value="Kredi Kartı">Kredi Kartı / POS</option>
                            <option value="Havale/EFT">Havale / EFT</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Açıklama (Opsiyonel)</label>
                        <input type="text" name="description" class="form-control" placeholder="Ekstra notlar...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Tahsilatı Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>