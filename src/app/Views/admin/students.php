<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fa-solid fa-users text-warning me-2"></i>Öğrenci Yönetimi</h3>
        <a href="index.php?page=student_add" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-user-plus me-2"></i>Yeni Öğrenci Ekle
        </a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> İşlem başarıyla tamamlandı.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Öğrenci</th>
                            <th>Veli / Telefon</th>
                            <th>Grup</th>
                            <th>Aylık Aidat</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($students)): ?>
                            <?php foreach($students as $s): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['FullName']) ?></div>
                                    <small class="text-muted">ID: #<?= $s['StudentID'] ?></small>
                                </td>
                                <td>
                                    <div class="text-dark"><?= htmlspecialchars($s['ParentName'] ?? 'Belirtilmemiş') ?></div>
                                    <small class="text-primary fw-medium">
                                        <i class="fa-solid fa-phone-flip fa-xs me-1"></i><?= $s['ParentPhone'] ?? '-' ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if(!empty($s['GroupName'])): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2"><?= $s['GroupName'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">Grup Atanmadı</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-success font-monospace"><?= number_format($s['MonthlyFee'], 2) ?> ₺</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light text-info border me-1" 
                                            onclick="showParentInfo('<?= htmlspecialchars($s['FullName']) ?>', '<?= $s['ParentPhone'] ?>')"
                                            title="Veli Giriş Bilgileri">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                    
                                    <a href="index.php?page=student_edit&id=<?= $s['StudentID'] ?>" 
                                       class="btn btn-sm btn-light text-secondary border me-1" title="Düzenle">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    
                                    <a href="index.php?page=student_delete&id=<?= $s['StudentID'] ?>" 
                                       class="btn btn-sm btn-light text-danger border" 
                                       onclick="return confirm('Bu öğrenciyi silmek istediğinize emin misiniz?')" title="Sil">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="fa-solid fa-user-slash fa-3x"></i></div>
                                    <div class="text-muted">Henüz öğrenci kaydı bulunmuyor.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="parentInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-id-card me-2"></i>Veli Giriş Bilgileri</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="text-muted mb-4"><strong id="m_student_name" class="text-dark"></strong> isimli öğrencinin velisi için giriş bilgileri:</p>
                
                <div class="bg-light p-3 rounded-3 mb-3 border">
                    <small class="text-uppercase fw-bold text-muted d-block mb-1">Kullanıcı Adı (Telefon)</small>
                    <span class="fs-4 fw-bold text-dark font-monospace" id="m_phone"></span>
                </div>
                
                <div class="bg-light p-3 rounded-3 border">
                    <small class="text-uppercase fw-bold text-muted d-block mb-1">Geçici Şifre</small>
                    <span class="fs-4 fw-bold text-success font-monospace">123456</span>
                </div>

                <div class="alert alert-info mt-4 mb-0 text-start" style="font-size: 0.85rem;">
                    <i class="fa-solid fa-info-circle me-2"></i> Veli bu bilgilerle sisteme giriş yaparak aidat takibi yapabilir ve yoklama durumunu görebilir.
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
function showParentInfo(student, phone) {
    // Modal içeriğini doldur
    document.getElementById('m_student_name').innerText = student;
    document.getElementById('m_phone').innerText = phone;
    
    // Modalı göster
    var myModal = new bootstrap.Modal(document.getElementById('parentInfoModal'));
    myModal.show();
}
</script>