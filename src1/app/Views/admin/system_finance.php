<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fa-solid fa-briefcase me-2"></i>Sistem Muhasebesi</h2>
        <?php if($_SESSION['role'] === 'SystemAdmin'): ?>
            <span class="text-muted">Kulüplerden alınan lisans ve hizmet bedelleri.</span>
        <?php else: ?>
            <span class="text-muted">Sisteme yaptığınız lisans ödemeleri.</span>
        <?php endif; ?>
    </div>
    
    <div class="text-end">
        <h3 class="text-primary fw-bold m-0"><?php echo number_format($totalRevenue ?? 0, 2); ?> ₺</h3>
        <small class="text-muted">Toplam Tahsilat</small>
    </div>
</div>

<?php if($_SESSION['role'] === 'SystemAdmin'): ?>
<div class="card shadow-sm border-start border-primary border-4 mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="m-0 text-primary"><i class="fa-solid fa-calculator me-2"></i>Bu Ayın Hakediş Raporu (Tahmini)</h5>
            <span class="badge bg-primary fs-6">
                Beklenen: <?php echo number_format($totalProjected ?? 0, 2); ?> ₺
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Kulüp Adı</th>
                        <th class="text-center">Aktif Öğrenci</th>
                        <th class="text-center">Anlaşma (Birim)</th>
                        <th class="text-end">Aylık Tutar</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($projectedIncome as $p): 
                        // Verileri güvenli hale getirelim
                        $count = $p['ActiveStudentCount'] ?? 0;
                        $price = $p['PricePerStudent'] ?? 0;
                        $monthlyBill = $count * $price;
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if(!empty($p['LogoPath'])): ?>
                                    <img src="<?php echo htmlspecialchars($p['LogoPath']); ?>" class="rounded-circle border me-2" width="30">
                                <?php endif; ?>
                                <strong><?php echo htmlspecialchars($p['ClubName']); ?></strong>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <?php echo $count; ?> Kişi
                            </span>
                        </td>
                        <td class="text-center text-muted">
                            <?php echo number_format($price, 2); ?> ₺
                        </td>
                        <td class="text-end fw-bold text-success">
                            <?php echo number_format($monthlyBill, 2); ?> ₺
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-success" 
                                    onclick="document.querySelector('[name=club_id]').value='<?php echo $p['ClubID']; ?>';
                                             document.querySelector('[name=amount]').value='<?php echo $monthlyBill; ?>';
                                             document.querySelector('[name=description]').value='<?php echo date('Y F'); ?> Hizmet Bedeli';
                                             new bootstrap.Modal(document.getElementById('addSysPaymentModal')).show();">
                                <i class="fa-solid fa-check"></i> Tahsil Et
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSysPaymentModal">
            <i class="fa-solid fa-plus"></i> Manuel Ödeme Ekle
        </button>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="m-0"><i class="fa-solid fa-list me-2"></i>Geçmiş Tahsilat Hareketleri</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tarih</th>
                        <th>Kulüp</th>
                        <th>Açıklama</th>
                        <th class="text-end">Tutar</th>
                        <?php if($_SESSION['role'] === 'SystemAdmin'): ?>
                            <th class="text-end">İşlem</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $pay): ?>
                    <tr>
                        <td><?php echo date('d.m.Y', strtotime($pay['PaymentDate'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($pay['ClubName']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($pay['Description']); ?></td>
                        <td class="text-end fw-bold text-dark">
                            <?php echo number_format($pay['Amount'] ?? 0, 2); ?> ₺
                        </td>
                        
                        <?php if($_SESSION['role'] === 'SystemAdmin'): ?>
                        <td class="text-end">
                            <a href="index.php?page=sys_payment_delete&id=<?php echo $pay['ID']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Bu kaydı silmek istiyor musunuz?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>

                    <?php if(empty($payments)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Henüz kayıtlı işlem yok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if($_SESSION['role'] === 'SystemAdmin'): ?>
<div class="modal fade" id="addSysPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=sys_payment_store" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Kulüpten Tahsilat Girişi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Hangi Kulüp?</label>
                        <select name="club_id" class="form-select" required>
                            <option value="">-- Seçiniz --</option>
                            <?php foreach($clubs as $club): ?>
                                <option value="<?php echo $club['ClubID']; ?>">
                                    <?php echo htmlspecialchars($club['ClubName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Tutar (₺)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Tarih</label>
                        <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Açıklama</label>
                        <input type="text" name="description" class="form-control" placeholder="Örn: 2025 Hizmet Bedeli">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>