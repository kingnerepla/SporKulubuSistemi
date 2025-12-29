<?php 
// Durumları Türkçeleştiren bir dizi (Helper)
$statusLabels = [
    'Scheduled' => 'Planlandı',
    'Cancelled' => 'İptal Edildi',
    'Completed' => 'Tamamlandı'
];

$statusColors = [
    'Scheduled' => 'bg-info text-dark', // Planlananlar Mavi/Açık Mavi
    'Cancelled' => 'bg-danger',         // İptaller Kırmızı
    'Completed' => 'bg-success'         // Tamamlananlar Yeşil
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

    <div class="row">
        <?php if(empty($sessions)): ?>
            <div class="col-12 text-center py-5 bg-white shadow-sm rounded">
                <i class="fa-solid fa-calendar-xmark fs-1 text-muted mb-3"></i>
                <p class="text-muted">Bu grup için henüz oluşturulmuş bir antrenman bulunmuyor.</p>
                <a href="index.php?page=group_schedule&id=<?= $groupId ?>" class="btn btn-primary btn-sm">Program Oluştur</a>
            </div>
        <?php else: ?>
            <?php foreach($sessions as $s): 
                $label = $statusLabels[$s['Status']] ?? $s['Status'];
                $color = $statusColors[$s['Status']] ?? 'bg-secondary';
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow-sm h-100 border-top border-4 <?= $s['Status'] == 'Scheduled' ? 'border-info' : ($s['Status'] == 'Cancelled' ? 'border-danger' : 'border-success') ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fw-bold fs-5"><?= date('d.m.Y', strtotime($s['TrainingDate'])) ?></span>
                                <span class="badge <?= $color ?>"><?= $label ?></span>
                            </div>
                            <div class="text-muted mb-3">
                                <i class="fa-regular fa-clock me-1"></i> 
                                <?= date('H:i', strtotime($s['StartTime'])) ?> - <?= date('H:i', strtotime($s['EndTime'])) ?>
                            </div>
                            
                            <?php if($s['Note']): ?>
                                <div class="alert alert-warning p-2 small mb-3">
                                    <i class="fa-solid fa-circle-info me-1"></i> <?= htmlspecialchars($s['Note']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2">
                                <a href="index.php?page=attendance&session_id=<?= $s['SessionID'] ?>" class="btn btn-sm btn-success <?= $s['Status'] == 'Cancelled' ? 'disabled' : '' ?>">
                                    <i class="fa-solid fa-clipboard-check me-1"></i> Yoklama Al
                                </a>
                                <button onclick="openStatusModal(<?= $s['SessionID'] ?>, '<?= $s['Status'] ?>', '<?= htmlspecialchars($s['Note'] ?? '') ?>')" class="btn btn-sm btn-outline-dark">
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
        <form action="index.php?page=update_session_status" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Antrenman Durumunu Güncelle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="session_id" id="modal_session_id">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Ders Durumu</label>
                    <select name="status" id="modal_status" class="form-select border-2">
                        <option value="Scheduled">Planlandı (Bekliyor)</option>
                        <option value="Cancelled">İptal Edildi</option>
                        <option value="Completed">Tamamlandı</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold">Not / İptal Nedeni (İsteğe Bağlı)</label>
                    <textarea name="note" id="modal_note" class="form-control border-2" rows="3" placeholder="Örn: Kar yağışı nedeniyle salon kapalı..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
// Butona tıklandığında modalı açan ve içindeki bilgileri dolduran fonksiyon
function openStatusModal(id, status, note) {
    document.getElementById('modal_session_id').value = id;
    document.getElementById('modal_status').value = status;
    document.getElementById('modal_note').value = note;
    
    var myModal = new bootstrap.Modal(document.getElementById('statusModal'));
    myModal.show();
}
</script>