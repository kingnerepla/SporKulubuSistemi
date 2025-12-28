<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-building me-2"></i>Kulüp Yönetimi</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClubModal">
        <i class="fa-solid fa-plus"></i> Yeni Kulüp Ekle
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <?php if($_GET['success'] == 'created'): ?>
        <div class="alert alert-success">Yeni kulüp ve anlaşma şartları oluşturuldu.</div>
    <?php elseif($_GET['success'] == 'updated'): ?>
        <div class="alert alert-success">Kulüp bilgileri ve fiyatlandırma güncellendi.</div>
    <?php elseif($_GET['success'] == 'deleted'): ?>
        <div class="alert alert-warning">Kulüp silindi.</div>
    <?php endif; ?>
<?php elseif(isset($_GET['error'])): ?>
    <div class="alert alert-danger">Bu işlem yapılamadı (Bağlı kayıtlar olabilir).</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">Logo</th>
                        <th>Kulüp Adı</th>
                        <th>Yönetici</th>
                        <th>Anlaşma (Tarife)</th>
                        <th>Kayıt Tarihi</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clubs as $club): ?>
                    <tr>
                        <td>
                            <?php if(!empty($club['LogoPath'])): ?>
                                <img src="<?php echo htmlspecialchars($club['LogoPath']); ?>" 
                                     alt="Logo" 
                                     class="rounded-circle border"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fa-solid fa-shield-halved text-muted fa-lg"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($club['ClubName']); ?></strong><br>
                            <small class="text-muted">ID: <?php echo $club['ClubID']; ?></small>
                        </td>
                        <td>
                            <?php if(!empty($club['ManagerName'])): ?>
                                <span class="badge bg-primary text-white">
                                    <i class="fa-solid fa-user-tie me-1"></i> <?php echo htmlspecialchars($club['ManagerName']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i> Atanmadı
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success mb-1">
                                    <i class="fa-solid fa-user me-1"></i> <?php echo number_format($club['PricePerStudent'] ?? 0, 2); ?> ₺ / Öğrenci
                                </span>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    Lisans: <?php echo number_format($club['LicenseFee'] ?? 0, 2); ?> ₺/Yıl
                                </small>
                            </div>
                        </td>
                        <td>
                            <?php echo date('d.m.Y', strtotime($club['CreatedAt'])); ?>
                        </td>
                        <td class="text-end">
                             <a href="index.php?page=club_detail&id=<?php echo $club['ClubID']; ?>" 
                               class="btn btn-sm btn-info text-white me-1" 
                               title="Detaylı İncele">
                               <i class="fa-solid fa-eye"></i>
                            </a>

                            <button class="btn btn-sm btn-outline-warning text-dark edit-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editClubModal"
                                    data-id="<?php echo $club['ClubID']; ?>"
                                    data-name="<?php echo htmlspecialchars($club['ClubName']); ?>"
                                    data-price="<?php echo $club['PricePerStudent'] ?? 0; ?>"
                                    data-license="<?php echo $club['LicenseFee'] ?? 0; ?>">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <a href="index.php?page=club_delete&id=<?php echo $club['ClubID']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('DİKKAT: Bu kulübü silerseniz tüm verileri silinebilir!\n\nEmin misiniz?');">
                               <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if(empty($clubs)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Sistemde kayıtlı kulüp bulunamadı.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addClubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=club_store" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Yeni Kulüp Kaydı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kulüp Adı</label>
                        <input type="text" name="club_name" class="form-control" placeholder="Örn: Gençlik Spor Kulübü" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kulüp Logosu</label>
                        <input type="file" name="club_logo" class="form-control" accept="image/*">
                        <small class="text-muted">JPG, PNG formatları önerilir.</small>
                    </div>

                    <hr>
                    <h6 class="text-primary"><i class="fa-solid fa-tags me-1"></i>Fiyatlandırma Anlaşması</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Öğrenci Başı Ücret (Aylık)</label>
                            <div class="input-group">
                                <input type="number" name="price_per_student" class="form-control" value="0" step="0.50" required>
                                <span class="input-group-text">₺</span>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Aktif öğrenci başına alınacak tutar.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Yıllık Lisans Bedeli</label>
                            <div class="input-group">
                                <input type="number" name="license_fee" class="form-control" value="0" step="100">
                                <span class="input-group-text">₺</span>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">İlk yıl ücretsizse 0 girin.</small>
                        </div>
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

<div class="modal fade" id="editClubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=club_update" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="club_id" id="edit_club_id">
                
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Kulüp Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kulüp Adı</label>
                        <input type="text" name="club_name" id="edit_club_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Logo Güncelle</label>
                        <input type="file" name="club_logo" class="form-control" accept="image/*">
                        <small class="text-muted">Logoyu değiştirmek istemiyorsanız boş bırakın.</small>
                    </div>

                    <hr>
                    <h6 class="text-warning"><i class="fa-solid fa-tags me-1"></i>Fiyatlandırma Güncelle</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Öğrenci Başı Ücret</label>
                            <div class="input-group">
                                <input type="number" name="price_per_student" id="edit_price_per_student" class="form-control" step="0.50">
                                <span class="input-group-text">₺</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Yıllık Lisans Bedeli</label>
                            <div class="input-group">
                                <input type="number" name="license_fee" id="edit_license_fee" class="form-control" step="100">
                                <span class="input-group-text">₺</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Verileri butonun datasından al
            document.getElementById('edit_club_id').value = this.dataset.id;
            document.getElementById('edit_club_name').value = this.dataset.name;
            // Fiyatları doldur
            document.getElementById('edit_price_per_student').value = this.dataset.price;
            document.getElementById('edit_license_fee').value = this.dataset.license;
        });
    });
});
</script>