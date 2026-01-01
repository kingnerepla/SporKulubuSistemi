<div class="container-fluid py-4 text-dark">
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <strong>İşlem Başarısız:</strong> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>
            <strong>Başarılı!</strong> Tahsilat kaydı sisteme işlendi.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>Ödeme Geçmişi</h2>
            <p class="text-muted small">Sporcuların aylık ödeme performansını ve sürekliliğini buradan takip edebilirsiniz.</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm shadow-sm" onchange="location.href='index.php?page=payments&student_id='+this.value">
                <option value="">-- Tüm Sporcuların Dökümü --</option>
                <?php foreach($students as $st): ?>
                    <option value="<?= $st['StudentID'] ?>" <?= (isset($_GET['student_id']) && $_GET['student_id'] == $st['StudentID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st['FullName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-success btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="fa-solid fa-plus me-1"></i> Tahsilat Al
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-success border-4">
                <small class="text-muted d-block fw-bold small">TOPLAM KASA (Filtreye Göre)</small>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($totalIncome ?? 0, 0, ',', '.') ?> ₺</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Ödeme Tarihi</th>
                        <th>Öğrenci / Grup</th>
                        <th>Dönem</th>
                        <th>Tür</th>
                        <th class="text-end">Tutar</th>
                        <th class="text-end pe-4">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($payments)): foreach($payments as $pay): ?>
                    <tr>
                        <td class="ps-4"><?= date('d.m.Y', strtotime($pay['PaymentDate'])) ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($pay['StudentName'] ?? 'Bilinmiyor') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($pay['GroupName'] ?? '-') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3">
                                <?= htmlspecialchars($pay['PaymentMonth']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($pay['PaymentType']) ?></td>
                        <td class="text-end fw-bold"><?= number_format($pay['Amount'], 0, ',', '.') ?> ₺</td>
                        <td class="text-end pe-4">
                            <a href="index.php?page=payment_delete&id=<?= $pay['PaymentID'] ?>" class="text-danger small" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Kayıt bulunamadı.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered text-dark">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=payment_store" method="POST">
                <div class="modal-header border-0 bg-success text-white">
                    <h6 class="modal-title fw-bold">Yeni Tahsilat Girişi</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Sporcu Seçin</label>
                        <select name="student_id" id="main_student_select" class="form-select rounded-3" required>
                            <option value="">-- Seçiniz --</option>
                            <?php foreach($students as $st): ?>
                                <option value="<?= $st['StudentID'] ?>" <?= (isset($_GET['student_id']) && $_GET['student_id'] == $st['StudentID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st['FullName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Dönem (Ay)</label>
                            <input type="month" name="payment_month" class="form-control" value="<?= date('Y-m') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1">İşlem Tarihi</label>
                            <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Tutar (₺)</label>
                            <input type="number" name="amount" class="form-control fw-bold" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1 text-danger">Yeni Vade (Gelecek Ay)</label>
                            <input type="date" name="next_payment_date" class="form-control border-danger" value="<?= date('Y-m-d', strtotime('+1 month')) ?>" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold mb-1">Ödeme Türü</label>
                        <select name="payment_type" class="form-select">
                            <option value="Nakit">Nakit</option>
                            <option value="Kredi Kartı">Kredi Kartı</option>
                            <option value="Havale/EFT">Havale/EFT</option>
                        </select>
                        <input type="hidden" name="description" value="Aidat Ödemesi">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow-sm">Tahsilatı Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Eğer URL'de student_id varsa, modal açıldığında o öğrenciyi otomatik seçmek için
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('student_id');
    if (studentId) {
        const selectBox = document.getElementById('main_student_select');
        if(selectBox) {
            selectBox.value = studentId;
        }
    }
});
</script>