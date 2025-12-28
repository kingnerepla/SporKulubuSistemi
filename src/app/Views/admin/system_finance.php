<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="fa-solid fa-vault text-success me-2"></i>Sistem Gelir Denetimi</h3>
            <p class="text-muted small">Kulüplerin sisteme olan ödemelerini ve lisans durumlarını buradan takip edin.</p>
        </div>
        <div class="bg-white p-3 rounded shadow-sm border-start border-4 border-success">
            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Toplam Tahsilat</small>
            <span class="fs-4 fw-bold text-success">
                <?php 
                $total = array_sum(array_column($finances, 'TotalPaid'));
                echo number_format($total, 2, ',', '.'); 
                ?> ₺
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Kulüp Adı</th>
                            <th>Durum</th>
                            <th>Lisans Bitiş</th>
                            <th>Toplam Ödeme</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($finances)): ?>
                            <?php foreach($finances as $f): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($f['ClubName']); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($f['Status'] == 'Active') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                            <?php echo $f['Status']; ?>
                                        </span>
                                    </td>
                                    <td><span class="text-muted small">01.01.2026</span></td>
                                    <td class="fw-bold"><?php echo number_format($f['TotalPaid'], 2, ',', '.'); ?> ₺</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary">Detayları Gör</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Henüz finansal veri bulunmuyor.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>