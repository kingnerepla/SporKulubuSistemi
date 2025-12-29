<div class="container-fluid px-4 mt-4">
    <div class="card shadow mb-4 border-0">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white shadow-sm">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-clipboard-check mr-2"></i>
                <?php echo htmlspecialchars($session['GroupName']); ?> - Yoklama Listesi
            </h6>
            <div class="d-flex align-items-center">
                <?php if (!$isEditable): ?>
                    <span class="badge bg-warning text-dark me-2">
                        <i class="fas fa-lock mr-1"></i> Salt Okunur (Geçmiş)
                    </span>
                <?php endif; ?>
                <span class="badge bg-white text-primary">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <?php echo date('d.m.Y', strtotime($date)); ?>
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <form action="index.php?page=attendance_save" method="POST">
                <input type="hidden" name="group_id" value="<?php echo $session['GroupID']; ?>">
                <input type="hidden" name="club_id" value="<?php echo $session['ClubID']; ?>">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <input type="hidden" name="session_id" value="<?php echo $sessionId; ?>">

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-dark small text-uppercase">
                            <tr>
                                <th class="ps-4">Sporcu Bilgileri</th>
                                <th class="text-center" style="width: 180px;">Yoklama Durumu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $std): ?>
                                    <tr <?php echo (!$isEditable) ? 'class="bg-light-subtle"' : ''; ?>>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="fw-bold text-dark d-flex align-items-center">
                                                        <?php echo htmlspecialchars($std['FullName']); ?>
                                                        
                                                        <?php if (!empty($std['Notes'])): ?>
                                                            <i class="fas fa-exclamation-circle text-danger ms-2 cursor-pointer" 
                                                               data-bs-toggle="tooltip" 
                                                               data-bs-html="true"
                                                               title="<div class='text-start small'><b>Eğitmen Notu:</b><br><?php echo htmlspecialchars($std['Notes']); ?></div>">
                                                            </i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <?php 
                                                            if(!empty($std['BirthDate'])) {
                                                                $age = date_diff(date_create($std['BirthDate']), date_create('today'))->y;
                                                                echo '<i class="fas fa-birthday-cake fa-xs me-1"></i>' . $age . ' Yaş';
                                                            } else {
                                                                echo 'ID: #' . $std['StudentID'];
                                                            }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-control custom-switch custom-switch-lg">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="status_<?php echo $std['StudentID']; ?>" 
                                                       name="status[<?php echo $std['StudentID']; ?>]" 
                                                       value="1" 
                                                       <?php echo ($std['CurrentStatus'] == 1) ? 'checked' : ''; ?>
                                                       <?php echo (!$isEditable) ? 'disabled' : ''; ?>>
                                                <label class="custom-control-label d-block" for="status_<?php echo $std['StudentID']; ?>">
                                                    <span class="status-label-text small fw-bold"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center py-5 text-muted">
                                        <i class="fas fa-users-slash fa-3x mb-3 opacity-25"></i>
                                        <p>Bu grupta kayıtlı aktif öğrenci bulunamadı.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-light d-flex justify-content-between align-items-center border-top">
                    <a href="index.php?page=dashboard" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-arrow-left mr-1"></i> İptal ve Geri Dön
                    </a>

                    <?php if ($isEditable): ?>
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow">
                            <i class="fas fa-save mr-1"></i> Yoklamayı Tamamla
                        </button>
                    <?php else: ?>
                        <div class="text-muted small bg-white p-2 border rounded shadow-sm">
                            <i class="fas fa-info-circle text-warning me-1"></i> Geçmiş tarihli kayıtlar antrenörler tarafından değiştirilemez.
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tooltip'leri aktif et
$(function () {
    $('[data-bs-toggle="tooltip"]').tooltip()
})
</script>

<style>
/* İkon Ayarı */
.cursor-pointer { cursor: help; }

/* Modern ve büyük Switch tasarımı */
.custom-switch-lg .custom-control-label::before {
    height: 2.2rem;
    width: 4rem;
    border-radius: 2rem;
    cursor: pointer;
}
.custom-switch-lg .custom-control-label::after {
    width: calc(2.2rem - 4px);
    height: calc(2.2rem - 4px);
    border-radius: 2rem;
    cursor: pointer;
}
.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(1.8rem);
}
.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #28a745;
    border-color: #28a745;
}

/* Switch içindeki metin durumu (isteğe bağlı) */
.custom-switch-lg .custom-control-label::before { content: ""; }

.bg-light-subtle { background-color: #f8f9fa; }
.table-hover tbody tr:hover { background-color: rgba(0,0,0,.02); }
</style>