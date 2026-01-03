<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold mb-1 text-secondary"><i class="fa-solid fa-box-archive me-2"></i>Arşivlenmiş Öğrenciler</h3>
            <p class="text-muted small mb-0">Pasif durumdaki öğrenci kayıtları.</p>
        </div>
        <div>
            <a href="index.php?page=students" class="btn btn-outline-primary btn-sm shadow-sm px-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Aktif Listeye Dön
            </a>
        </div>
    </div>

    <?php if(!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(!empty($students)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-4 py-3">Öğrenci Bilgisi</th>
                            <th>Son Durum</th>
                            <th class="text-center">Kalan Hak</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): 
                            $rem = (int)$s['RemainingSessions'];
                        ?>
                        <tr class="<?= $rem > 0 ? 'bg-warning bg-opacity-10' : '' ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['FullName']) ?></div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-layer-group me-1"></i><?= htmlspecialchars($s['GroupName'] ?? 'Grup Yok') ?>
                                </div>
                                <?php if(!empty($s['Notes'])): ?>
                                    <div class="x-small text-danger fst-italic mt-1"><?= htmlspecialchars($s['Notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php if($rem > 0): ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-regular fa-snowflake me-1"></i>DONDURULDU</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">İLİŞİK KESİLDİ</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if($rem > 0): ?>
                                    <h5 class="fw-bold text-dark mb-0"><?= $rem ?></h5>
                                <?php else: ?>
                                    <span class="text-muted opacity-50">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-success shadow-sm px-3 fw-bold me-1"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#restoreModal"
                                        data-id="<?= $s['StudentID'] ?>"
                                        data-name="<?= htmlspecialchars($s['FullName']) ?>"
                                        data-group="<?= $s['GroupID'] ?>"
                                        data-remaining="<?= $s['RemainingSessions'] ?>">
                                    <i class="fa-solid fa-rotate-left me-1"></i>Aktif Et
                                </button>

                                <a href="index.php?page=student_destroy&id=<?= $s['StudentID'] ?>" 
                                   class="btn btn-sm btn-outline-danger shadow-sm px-2"
                                   onclick="return confirm('DİKKAT: Tamamen silinecek. Emin misiniz?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    
    <?php else: ?>
        <div class="text-center py-5">
            <h5 class="text-muted">Arşiv boş.</h5>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=student_restore" method="POST">
                <input type="hidden" name="student_id" id="restStudentId">
                
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold">Öğrenciyi Aktif Et</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold text-dark" id="restName">Öğrenci Adı</h5>
                        <p class="text-muted small">Bu öğrenci aktif listeye taşınacaktır.</p>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold mb-1 text-dark">Dahil Olacağı Grup</label>
                        <select name="group_id" id="restGroup" class="form-select border-success" required>
                            <option value="">-- Grup Seçiniz --</option>
                            <?php if(!empty($groups)): foreach($groups as $g): ?>
                                <option value="<?= $g['GroupID'] ?>"><?= htmlspecialchars($g['GroupName']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const restoreModal = document.getElementById('restoreModal');
        restoreModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('restStudentId').value = btn.getAttribute('data-id');
            document.getElementById('restName').textContent = btn.getAttribute('data-name');
            
            const oldGroup = btn.getAttribute('data-group');
            const selectBox = document.getElementById('restGroup');
            if(oldGroup) selectBox.value = oldGroup; 
            else selectBox.value = "";
        });
    });
</script>