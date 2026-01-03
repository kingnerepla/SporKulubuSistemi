<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4"><i class="fa-solid fa-wallet text-warning me-2"></i>Ödeme ve Finansal Geçmiş</h4>

    <?php if (empty($students)): ?>
        <div class="alert alert-info">Kayıtlı öğrenci bilgisi bulunamadı.</div>
    <?php else: ?>
        <?php foreach ($students as $stu): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($stu['FullName']) ?> - Ödeme Geçmişi</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Dönem / Ay</th>
                                    <th>Tarih</th>
                                    <th>Ödeme Türü</th>
                                    <th class="text-end pe-4">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stu['payments'])): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Henüz kayıtlı ödeme bulunmuyor.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($stu['payments'] as $pay): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($pay['PaymentMonth'] ?? '-') ?></td>
                                            <td class="small text-muted"><?= date('d.m.Y', strtotime($pay['PaymentDate'])) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($pay['PaymentType']) ?></span></td>
                                            <td class="text-end pe-4 fw-bold text-success">₺<?= number_format($pay['Amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>