<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-users-rectangle me-2"></i>Grup ve Takım Yönetimi</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">
        <i class="fa-solid fa-plus"></i> Yeni Grup Oluştur
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <?php if($_GET['success'] == 'created'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check me-2"></i>Yeni grup başarıyla oluşturuldu.</div>
    <?php elseif($_GET['success'] == 'updated'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check me-2"></i>Grup bilgileri güncellendi.</div>
    <?php elseif($_GET['success'] == 'deleted'): ?>
        <div class="alert alert-warning"><i class="fa-solid fa-trash me-2"></i>Grup silindi.</div>
    <?php endif; ?>
<?php elseif(isset($_GET['error']) && $_GET['error'] == 'has_students'): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <strong>Hata:</strong> Bu grupta kayıtlı öğrenciler var! Silmeden önce öğrencilerin grubunu değiştirin veya silin.
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Grup / Takım Adı</th>
                        <th>Sorumlu Antrenör</th>
                        <th class="text-center">Öğrenci Sayısı</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($groups as $group): ?>
                    <tr>
                        <td>
                            <strong class="text-primary"><?php echo htmlspecialchars($group['GroupName']); ?></strong>
                        </td>
                        <td>
                            <?php if(!empty($group['TrainerName'])): ?>
                                <span class="badge bg-info text-dark border">
                                    <i class="fa-solid fa-whistle me-1"></i> <?php echo htmlspecialchars($group['TrainerName']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white-50">Atanmadı</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-users me-1"></i> <?php echo $group['StudentCount'] ?? 0; ?> Öğrenci
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-warning text-dark edit-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editGroupModal"
                                    data-id="<?php echo $group['GroupID']; ?>"
                                    data-name="<?php echo htmlspecialchars($group['GroupName']); ?>"
                                    data-trainer="<?php echo $group['TrainerID'] ?? ''; ?>">
                                <i class="fa-solid fa-pen"></i> Düzenle
                            </button>

                            <a href="index.php?page=group_delete&id=<?php echo $group['GroupID']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('UYARI: \nBu grubu silmek istediğinize emin misiniz?');">
                               <i class="fa-solid fa-trash"></i> Sil
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($groups)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Henüz oluşturulmuş bir grup yok.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=group_store" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Yeni Grup Oluştur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Grup / Takım Adı</label>
                        <input type="text" name="group_name" class="form-control" placeholder="Örn: U14 Futbol A Takımı" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sorumlu Antrenör</label>
                        <select name="trainer_id" class="form-select">
                            <option value="">-- Atama Yapılmadı --</option>
                            <?php foreach($trainers as $trainer): ?>
                                <option value="<?php echo $trainer['UserID']; ?>">
                                    <?php echo htmlspecialchars($trainer['FullName']); ?> (Antrenör)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Listede sadece "Antrenör" rolündeki kullanıcılar görünür.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=group_update" method="POST">
                <input type="hidden" name="group_id" id="edit_group_id">
                
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Grubu Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Grup Adı</label>
                        <input type="text" name="group_name" id="edit_group_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sorumlu Antrenör</label>
                        <select name="trainer_id" id="edit_trainer_id" class="form-select">
                            <option value="">-- Atama Yapılmadı --</option>
                            <?php foreach($trainers as $trainer): ?>
                                <option value="<?php echo $trainer['UserID']; ?>">
                                    <?php echo htmlspecialchars($trainer['FullName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
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
            // Null check yaparak verileri al
            const id = this.dataset.id;
            const name = this.dataset.name;
            const trainerId = this.dataset.trainer || "";

            document.getElementById('edit_group_id').value = id;
            document.getElementById('edit_group_name').value = name;
            document.getElementById('edit_trainer_id').value = trainerId;
        });
    });
});
</script>