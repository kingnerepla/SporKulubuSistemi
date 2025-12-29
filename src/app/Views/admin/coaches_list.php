<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Antrenör Yönetimi</h4>
            <p class="text-muted small">Kulübünüzdeki antrenörleri listeleyebilir ve bilgilerini güncelleyebilirsiniz.</p>
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
                            <th>Telefon</th>
                            <th>Durum</th>
                            <th>Kayıt Tarihi</th>
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
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($coach['FullName']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($coach['Email']) ?></td>
                                    <td><?= htmlspecialchars($coach['Phone'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($coach['IsActive']): ?>
                                            <span class="badge bg-success-soft text-success border border-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-soft text-danger border border-danger">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted"><?= date('d.m.Y', strtotime($coach['CreatedAt'])) ?></td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info" 
                                                    onclick="showLoginDetails('<?= htmlspecialchars($coach['FullName']) ?>', '<?= htmlspecialchars($coach['Email']) ?>')"
                                                    title="Giriş Bilgilerini Gör">
                                                <i class="fa-solid fa-key"></i>
                                            </button>

                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary" 
                                                    onclick="editCoach('<?= $coach['UserID'] ?>', '<?= htmlspecialchars($coach['FullName']) ?>', '<?= htmlspecialchars($coach['Email']) ?>', '<?= htmlspecialchars($coach['Phone'] ?? '') ?>')"
                                                    title="Düzenle">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <a href="index.php?page=coach_delete&id=<?= $coach['UserID'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Bu antrenörü silmek istediğinize emin misiniz?')"
                                               title="Sil">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076402.png" width="80" class="opacity-25 mb-3">
                                    <p class="text-muted">Henüz kayıtlı bir antrenör bulunamadı.</p>
                                </td>
                            </tr>
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
                    <input type="text" name="full_name" class="form-control" required placeholder="Örn: Ahmet Yılmaz">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">E-Posta (Giriş İçin)</label>
                    <input type="email" name="email" class="form-control" required placeholder="antrenor@kulup.com">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Telefon</label>
                    <input type="text" name="phone" class="form-control" placeholder="0(5__) ___ __ __">
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold">Giriş Şifresi</label>
                    <input type="password" name="password" class="form-control" required value="123456">
                    <div class="form-text text-muted" style="font-size: 0.7rem;">Varsayılan şifre: 123456</div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
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
                <div class="mb-3">
                    <label class="form-label small fw-bold">E-Posta</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Telefon</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control" placeholder="0(5__) ___ __ __">
                    
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold text-primary">Yeni Şifre</label>
                    <input type="password" name="password" class="form-control" placeholder="Değiştirmek istemiyorsanız boş bırakın">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
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
                <div class="alert alert-warning small mb-0 p-2" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-circle-info me-1"></i> Şifreler güvenlik gereği gizlidir. Şifreyi güncellemek için "Düzenle" butonunu kullanın.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Giriş Bilgilerini Göster
function showLoginDetails(name, email) {
    document.getElementById('displayCoachName').innerText = name;
    document.getElementById('displayCoachEmail').innerText = email;
    var myModal = new bootstrap.Modal(document.getElementById('loginDetailsModal'));
    myModal.show();
}

// Düzenleme Modalını Doldur ve Aç
function editCoach(id, name, email, phone) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_full_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    var editModal = new bootstrap.Modal(document.getElementById('editCoachModal'));
    editModal.show();
}
</script>
<script src="https://unpkg.com/imask"></script>

<script src="https://unpkg.com/imask"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 0(5XX) XXX XX XX Formatı için maske ayarı
    var maskOptions = {
        mask: '0(000) 000 00 00',
        lazy: false // Kullanıcı tıklayınca şablonu (0(___) ___ __ __ şeklinde) gösterir
    };

    // Yeni Antrenör Ekleme Modalındaki Telefon Alanı
    var addPhoneEl = document.querySelector('#addCoachModal input[name="phone"]');
    if(addPhoneEl) IMask(addPhoneEl, maskOptions);

    // Düzenleme Modalındaki Telefon Alanı
    var editPhoneEl = document.getElementById('edit_phone');
    if(editPhoneEl) IMask(editPhoneEl, maskOptions);
});
</script>