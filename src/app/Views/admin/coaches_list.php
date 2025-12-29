<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Antrenör Yönetimi</h4>
            <p class="text-muted small">Kulübünüzdeki antrenörleri listeleyebilir ve rapor erişim yetkilerini düzenleyebilirsiniz.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCoachModal">
            <i class="fa-solid fa-user-plus me-2"></i>Yeni Antrenör Ekle
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Ad Soyad</th>
                            <th>E-Posta</th>
                            <th>Rapor Yetkisi</th> 
                            <th>Durum</th>
                            <th class="text-end pe-4">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($coaches)): ?>
                            <?php foreach ($coaches as $coach): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-soft text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #e7f1ff;">
                                                <i class="fa-solid fa-user-tie"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($coach['FullName']) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($coach['Phone'] ?? '-') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($coach['Email']) ?></td>
                                    <td>
                                        <?php if (isset($coach['CanSeeReports']) && $coach['CanSeeReports'] == 1): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="fa-solid fa-eye me-1"></i> Yetkili
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Kısıtlı</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($coach['IsActive']): ?>
                                            <span class="badge bg-success-soft text-success border border-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-soft text-danger border border-danger">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="showLoginDetails('<?= htmlspecialchars($coach['FullName']) ?>', '<?= htmlspecialchars($coach['Email']) ?>')"><i class="fa-solid fa-key"></i></button>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editCoach('<?= $coach['UserID'] ?>', '<?= htmlspecialchars($coach['FullName']) ?>', '<?= htmlspecialchars($coach['Email']) ?>', '<?= htmlspecialchars($coach['Phone'] ?? '') ?>', '<?= $coach['CanSeeReports'] ?? 0 ?>')">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <a href="index.php?page=coach_delete&id=<?= $coach['UserID'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu antrenörü silmek istediğinize emin misiniz?')"><i class="fa-solid fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCoachModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="index.php?page=coach_save" method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Yeni Antrenör Tanımla</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Ad Soyad</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="Ad Soyad giriniz">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">E-Posta</label>
                        <input type="email" name="email" class="form-control" required placeholder="ornek@mail.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Telefon</label>
                        <input type="text" name="phone" id="add_phone" class="form-control">
                    </div>
                </div>
                <div class="mb-3 p-3 bg-light rounded border">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_see_reports" id="add_can_see_reports" value="1">
                        <label class="form-check-label fw-bold small ms-2" for="add_can_see_reports">Yoklama Rapor Yetkisi</label>
                        <div class="form-text mt-1" style="font-size: 0.7rem;">Aktif edilirse antrenör aylık raporları görebilir.</div>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold">Giriş Şifresi</label>
                    <input type="password" name="password" class="form-control" required value="123456">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Antrenörü Kaydet</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editCoachModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="index.php?page=coach_update" method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2"></i>Antrenör Bilgilerini Güncelle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Ad Soyad</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">E-Posta</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Telefon</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
                    </div>
                </div>
                <div class="mb-3 p-3 bg-light rounded border">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_see_reports" id="edit_can_see_reports" value="1">
                        <label class="form-check-label fw-bold small ms-2" for="edit_can_see_reports">Yoklama Rapor Yetkisi</label>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold text-primary">Yeni Şifre</label>
                    <input type="password" name="password" class="form-control" placeholder="Boş bırakırsanız değişmez">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="loginDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white border-0">
                <h6 class="modal-title fw-bold">Giriş Bilgileri</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="fa-solid fa-user-shield fs-1 text-info mb-3"></i>
                <h6 id="displayCoachName" class="fw-bold mb-3"></h6>
                <div class="bg-light p-3 rounded mb-3 text-start">
                    <small class="text-muted d-block">E-Posta:</small>
                    <strong id="displayCoachEmail" class="text-dark"></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/imask"></script>
<script>
// Düzenleme Modalını Doldur ve Aç
function editCoach(id, name, email, phone, canSeeReports) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_full_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    
    // Yetki switch durumunu ayarla
    document.getElementById('edit_can_see_reports').checked = (canSeeReports == 1);
    
    var editModal = new bootstrap.Modal(document.getElementById('editCoachModal'));
    editModal.show();
}

function showLoginDetails(name, email) {
    document.getElementById('displayCoachName').innerText = name;
    document.getElementById('displayCoachEmail').innerText = email;
    var myModal = new bootstrap.Modal(document.getElementById('loginDetailsModal'));
    myModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    var maskOptions = { 
        mask: '0(000) 000 00 00',
        lazy: false 
    };
    
    // Yeni ekleme modalı telefonu için id: add_phone
    var addPhoneEl = document.getElementById('add_phone');
    if(addPhoneEl) IMask(addPhoneEl, maskOptions);
    
    // Düzenleme modalı telefonu için id: edit_phone
    var editPhoneEl = document.getElementById('edit_phone');
    if(editPhoneEl) IMask(editPhoneEl, maskOptions);
});
</script>