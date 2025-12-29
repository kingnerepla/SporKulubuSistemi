<div class="container-fluid px-4 mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
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
                <span class="badge badge-light text-primary">
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
                    <table class="table table-hover mb-0">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th class="ps-4">Öğrenci Adı Soyadı</th>
                                <th class="text-center" style="width: 150px;">Durum (Geldi/Gelmedi)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $std): ?>
                                    <tr <?php echo (!$isEditable) ? 'class="bg-light-subtle"' : ''; ?>>
                                        <td class="ps-4 align-middle font-weight-bold">
                                            <?php echo htmlspecialchars($std['FullName']); ?>
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
                                                <label class="custom-control-label" for="status_<?php echo $std['StudentID']; ?>"></label>
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

                <div class="p-4 bg-light d-flex justify-content-between align-items-center">
                    <a href="index.php?page=dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Geri Dön
                    </a>

                    <?php if ($isEditable): ?>
                        <button type="submit" class="btn btn-success px-5 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Yoklamayı Kaydet
                        </button>
                    <?php else: ?>
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i> Geçmiş tarihli kayıtlar antrenörler tarafından değiştirilemez.
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modern ve büyük Switch tasarımı */
.custom-switch-lg .custom-control-label::before {
    height: 1.8rem;
    width: 3.2rem;
    border-radius: 2rem;
    cursor: pointer;
}
.custom-switch-lg .custom-control-label::after {
    width: calc(1.8rem - 4px);
    height: calc(1.8rem - 4px);
    border-radius: 2rem;
    cursor: pointer;
}
.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(1.4rem);
}
.custom-control-input:disabled ~ .custom-control-label {
    cursor: not-allowed;
    opacity: 0.6;
}
.bg-light-subtle { background-color: #f8f9fa; }
</style>