<?php 
// Durumları Türkçeleştiren yardımcı diziler
$statusLabels = [
    'Scheduled' => 'Planlandı',
    'Cancelled' => 'İptal Edildi',
    'Completed' => 'Tamamlandı'
];

$statusColors = [
    'Scheduled' => 'bg-info text-dark', 
    'Cancelled' => 'bg-danger text-white',         
    'Completed' => 'bg-success text-white'         
];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">
            <i class="fa-solid fa-calendar-day me-2"></i><?= htmlspecialchars($group['GroupName']) ?> Antrenman Takvimi
        </h4>
        <a href="index.php?page=training_groups" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Gruplara Dön
        </a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-danger py-2 small shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
            <i class="fa-solid fa-trash-can me-2"></i> Antrenman kaydı başarıyla silindi.
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if(empty($sessions)): ?>
            <div class="col-12 text-center py-5 bg-white shadow-sm rounded border">
                <i class="fa-solid fa-calendar-xmark fs-1 text-muted mb-3"></i>
                <p class="text-muted">Bu grup için henüz oluşturulmuş bir antrenman bulunmuyor.</p>
                <a href="index.php?page=group_schedule&id=<?= $groupId ?>" class="btn btn-primary btn-sm">Program Oluştur</a>
            </div>
        <?php else: ?>
            <?php foreach($sessions as $s): 
                $currentID = $s['SessionID'];
                $label = $statusLabels[$s['Status']] ?? $s['Status'];
                $color = $statusColors[$s['Status']] ?? 'bg-secondary';
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow-sm h-100 border-top border-4 <?= $s['Status'] == 'Scheduled' ? 'border-info' : ($s['Status'] == 'Cancelled' ? 'border-danger' : 'border-success') ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fw-bold fs-5 text-dark"><?= date('d.m.Y', strtotime($s['TrainingDate'])) ?></span>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge <?= $color ?>"><?= $label ?></span>
                                    
                                    <a href="index.php?page=delete_single_session&id=<?= $currentID ?>&group_id=<?= $groupId ?>" 
                                       class="text-danger text-decoration-none" 
                                       onclick="return confirm('Bu antrenmanı kalıcı olarak silmek istediğinize emin misiniz?')"
                                       title="Antrenmanı Sil">
                                        <i class="fa-solid fa-trash-can" style="padding: 6px; background: #fff5f5; border-radius: 6px; font-size: 0.85rem;"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="text-muted small mb-3">
                                <i class="fa-regular fa-clock me-1 text-primary"></i> 
                                <?= date('H:i', strtotime($s['StartTime'])) ?> - <?= date('H:i', strtotime($s['EndTime'])) ?>
                            </div>
                            
                            <?php if(!empty($s['Note'])): ?>
                                <div class="alert alert-warning p-2 small mb-3 border-0 shadow-sm" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle-info me-1"></i> <?= htmlspecialchars($s['Note']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2 mt-auto">
                                <a href="index.php?page=attendance&session_id=<?= $currentID ?>" class="btn btn-sm btn-success <?= $s['Status'] == 'Cancelled' ? 'disabled' : '' ?>">
                                    <i class="fa-solid fa-clipboard-check me-1"></i> Yoklama Al
                                </a>
                                <button type="button" onclick="openStatusModal('<?= $currentID ?>', '<?= $s['Status'] ?>', '<?= htmlspecialchars($s['Note'] ?? '', ENT_QUOTES) ?>')" class="btn btn-sm btn-outline-dark">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Durumu Yönet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="index.php?page=update_session_status&group_id=<?= $groupId ?>" method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold">Antrenman Durumu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="session_id" id="modal_session_id">
                <input type="hidden" name="group_id" value="<?= $groupId ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Ders Durumu</label>
                    <select name="status" id="modal_status" class="form-select border-2">
                        <option value="Scheduled">Planlandı (Bekliyor)</option>
                        <option value="Cancelled">İptal Edildi</option>
                        <option value="Completed">Tamamlandı</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold">Not / İptal Nedeni</label>
                    <textarea name="note" id="modal_note" class="form-control border-2" rows="3" placeholder="Gerekirse bir açıklama ekleyin..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light text-end">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
// Sayfa içinde tek bir modal instance'ı kullanmak daha sağlıklıdır
let myStatusModal = null;

function openStatusModal(id, status, note) {
    // Değerleri form içine doldur
    document.getElementById('modal_session_id').value = id;
    document.getElementById('modal_status').value = status;
    document.getElementById('modal_note').value = note;
    
    // Modalı aç
    if (!myStatusModal) {
        myStatusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    }
    myStatusModal.show();
}
</script>