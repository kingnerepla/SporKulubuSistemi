<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold mb-1 text-secondary"><i class="fa-solid fa-box-archive me-2"></i>Arşivlenmiş Öğrenciler</h3>
            <p class="text-muted small mb-0">Silinmiş (pasif) kayıtlar burada tutulur.</p>
        </div>
        <div>
            <a href="index.php?page=students" class="btn btn-outline-primary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Aktif Listeye Dön
            </a>
        </div>
    </div>

    <?php if(!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if(!empty($students)): ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Öğrenci Bilgisi</th>
                            <th>Eski Grubu</th>
                            <th>Silinme / Kayıt Tarihi</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($students as $s): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark opacity-75"><?= htmlspecialchars($s['FullName']) ?></div>
                                <div class="small text-muted">ID: #<?= $s['StudentID'] ?></div>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                    <?= htmlspecialchars($s['GroupName'] ?? 'Grup Yok') ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-muted small">
                                    <i class="fa-regular fa-calendar me-1"></i><?= date('d.m.Y', strtotime($s['CreatedAt'])) ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <button type="button" 
                                            class="btn btn-sm btn-success restore-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#restoreModal"
                                            data-id="<?= $s['StudentID'] ?>"
                                            data-name="<?= htmlspecialchars($s['FullName']) ?>"
                                            data-group="<?= $s['GroupID'] ?>">
                                        <i class="fa-solid fa-rotate-left me-1"></i>Geri Yükle
                                    </button>

                                    <a href="index.php?page=student_destroy&id=<?= $s['StudentID'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('DİKKAT! \n<?= htmlspecialchars($s['FullName']) ?> öğrencisi VERİTABANINDAN TAMAMEN SİLİNECEK.\n\nBu işlem geri alınamaz! Onaylıyor musunuz?');">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center py-5">
            <i class="fa-solid fa-box-open fa-3x mb-3 opacity-50"></i>
            <h5>Arşiv Boş</h5>
            <p class="mb-0">Şu anda silinmiş veya pasif durumda öğrenci bulunmuyor.</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="index.php?page=student_restore" method="POST">
                <input type="hidden" name="student_id" id="restoreStudentId">

                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="fa-solid fa-rotate-left me-2"></i>Öğrenciyi Geri Yükle
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="alert alert-light border border-success mb-3">
                        <strong id="restoreStudentName" class="text-success"></strong><br>
                        adlı öğrenci arşivden çıkarılarak tekrar aktif edilecek.
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Hangi Gruba Eklensin?</label>
                        <select name="group_id" id="restoreGroupId" class="form-select" required>
                            <option value="">-- Grup Seçiniz --</option>
                            <?php if(!empty($groups)): foreach($groups as $grp): ?>
                                <option value="<?= $grp['GroupID'] ?>"><?= htmlspecialchars($grp['GroupName']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <div class="form-text small">Öğrenciyi geri yüklerken sınıfını değiştirebilirsiniz.</div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success px-4">Onayla ve Yükle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const restoreModal = document.getElementById('restoreModal');
        if (restoreModal) {
            restoreModal.addEventListener('show.bs.modal', function(event) {
                // Butonu tetikleyen öğe
                const button = event.relatedTarget;
                
                // Verileri al
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const groupId = button.getAttribute('data-group');
                
                // Modal içindeki inputlara yerleştir
                document.getElementById('restoreStudentId').value = id;
                document.getElementById('restoreStudentName').textContent = name;
                
                // Eski grubunu otomatik seç (Varsa)
                const groupSelect = document.getElementById('restoreGroupId');
                groupSelect.value = groupId; 
            });
        }
    });
</script>