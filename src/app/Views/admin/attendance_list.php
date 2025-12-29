<div class="container-fluid px-4 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list-check me-2 text-primary"></i>Hangi Dersin Yoklamasını Alacaksınız?</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($sessions)): ?>
                <div class="list-group">
                    <?php foreach ($sessions as $s): ?>
                        <a href="index.php?page=attendance&session_id=<?php echo $s['SessionID']; ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div>
                                <span class="badge bg-primary me-2"><?php echo date('H:i', strtotime($s['StartTime'])); ?></span>
                                <strong class="text-dark"><?php echo htmlspecialchars($s['GroupName']); ?></strong>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-calendar-xmark fa-3x text-light mb-3"></i>
                    <p class="text-muted">Bugün için kayıtlı bir dersiniz görünmüyor.</p>
                    <a href="index.php?page=dashboard" class="btn btn-primary btn-sm">Dashboard'a Dön</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>