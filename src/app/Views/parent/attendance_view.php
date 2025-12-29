<div class="container-fluid px-4 mt-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-white rounded d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-dark">
                    <i class="fa-solid fa-calendar-check me-2 text-success"></i>Yoklama Durumu
                </h4>
                <p class="text-muted small mb-0">Öğrencimizin antrenman katılım geçmişi.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-success px-3 py-2">
                    <i class="fa-solid fa-child me-1"></i> <?= htmlspecialchars($student['FullName']) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase small opacity-75">Toplam Antrenman</h6>
                    <h2 class="fw-bold"><?= count($history) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase small opacity-75">Katılım (Geldi)</h6>
                    <h2 class="fw-bold"><?= $stats['present'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase small opacity-75">Devamsızlık (Gelmedi)</h6>
                    <h2 class="fw-bold"><?= $stats['absent'] ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Tarih</th>
                        <th>Gün</th>
                        <th class="text-center">Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $row): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= date('d.m.Y', strtotime($row['AttendanceDate'])) ?></td>
                        <td class="text-muted small"><?= date('l', strtotime($row['AttendanceDate'])) ?></td>
                        <td class="text-center">
                            <?php if ($row['Status'] == 1): ?>
                                <span class="badge bg-success-subtle text-success border border-success px-3">
                                    <i class="fa-solid fa-check me-1"></i> Geldi
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger px-3">
                                    <i class="fa-solid fa-xmark me-1"></i> Gelmedi
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>