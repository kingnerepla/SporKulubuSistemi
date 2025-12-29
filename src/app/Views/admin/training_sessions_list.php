<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fa-solid fa-calendar-day text-primary me-2"></i>Antrenman Planı</h3>
        <div class="d-flex gap-2">
            <a href="index.php?page=groups" class="btn btn-outline-primary btn-sm fw-bold"><i class="fa-solid fa-plus me-1"></i>Yeni Program Oluştur</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="sessions">
                <div class="col-md-4">
                    <label class="small fw-bold mb-1 text-muted">Grup Filtrele</label>
                    <select name="group_id" class="form-select border-0 shadow-sm">
                        <option value="">Tüm Gruplar</option>
                        <?php 
                        // Controller'dan gelen grupları buraya basacağız
                        foreach($groups as $g): ?>
                            <option value="<?= $g['GroupID'] ?>" <?= ($_GET['group_id'] ?? '') == $g['GroupID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['GroupName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold mb-1 text-muted">Dönem</label>
                    <select name="range" class="form-select border-0 shadow-sm">
                        <option value="today" <?= ($_GET['range'] ?? '') == 'today' ? 'selected' : '' ?>>Bugünkü Dersler</option>
                        <option value="week" <?= ($_GET['range'] ?? 'week') == 'week' ? 'selected' : '' ?>>Bu Haftaki Program</option>
                        <option value="all" <?= ($_GET['range'] ?? '') == 'all' ? 'selected' : '' ?>>Tüm Takvim</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">Listele</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php if(empty($sessions)): ?>
            <div class="col-12 text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" width="80" class="opacity-25 mb-3">
                <p class="text-muted">Seçili kriterlere uygun antrenman bulunamadı.</p>
            </div>
        <?php else: ?>
            <?php foreach($sessions as $s): 
                $statusColor = [
                    'Scheduled' => 'border-primary',
                    'Cancelled' => 'border-danger bg-danger-subtle',
                    'Completed' => 'border-success'
                ][$s['Status']] ?? 'border-secondary';
            ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 border-0 border-start border-4 <?= $statusColor ?> shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($s['GroupName']) ?></h6>
                                <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= date('H:i', strtotime($s['StartTime'])) ?> - <?= date('H:i', strtotime($s['EndTime'])) ?></small>
                            </div>
                            <span class="badge <?= $s['Status'] == 'Cancelled' ? 'bg-danger' : 'bg-light text-dark border' ?> small">
                                <?= $s['Status'] == 'Scheduled' ? 'Bekliyor' : ($s['Status'] == 'Cancelled' ? 'İptal' : 'Yapıldı') ?>
                            </span>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded p-2 text-center me-3" style="min-width: 50px;">
                                <div class="small fw-bold" style="font-size: 0.7rem;"><?= date('M', strtotime($s['TrainingDate'])) ?></div>
                                <div class="fs-5 fw-bold mt-n1"><?= date('d', strtotime($s['TrainingDate'])) ?></div>
                            </div>
                            <div class="small text-muted">
                                <?= $s['Note'] ? '<i class="fa-solid fa-comment-dots text-warning me-1"></i>' . htmlspecialchars($s['Note']) : 'Not eklenmemiş.' ?>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="index.php?page=attendance&session_id=<?= $s['SessionID'] ?>" class="btn btn-sm btn-success w-100 fw-bold <?= $s['Status'] == 'Cancelled' ? 'disabled' : '' ?>">
                                <i class="fa-solid fa-check-double me-1"></i> Yoklama Al
                            </a>
                            <button class="btn btn-sm btn-outline-dark" onclick="openStatusModal(<?= $s['SessionID'] ?>, '<?= $s['Status'] ?>', '<?= $s['Note'] ?>')">
                                <i class="fa-solid fa-gear"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>