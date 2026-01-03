<div class="container-fluid px-4 mt-4">
    <h4 class="mb-4 fw-bold text-dark">
        <i class="fa-solid fa-calendar-check me-2 text-success"></i>Yoklama Geçmişi
    </h4>

    <?php foreach ($students as $stu): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <span class="badge bg-success px-3 py-2 float-end">
                    <i class="fa-solid fa-child me-1"></i> <?= htmlspecialchars($stu['FullName']) ?>
                </span>
                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($stu['FullName']) ?> Verileri</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-primary text-white rounded shadow-sm text-center">
                            <small class="opacity-75">Toplam</small>
                            <h3 class="mb-0 fw-bold"><?= count($stu['history']) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-success text-white rounded shadow-sm text-center">
                            <small class="opacity-75">Geldi</small>
                            <h3 class="mb-0 fw-bold"><?= $stu['stats']['present'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-danger text-white rounded shadow-sm text-center">
                            <small class="opacity-75">Gelmedi</small>
                            <h3 class="mb-0 fw-bold"><?= $stu['stats']['absent'] ?></h3>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Tarih</th>
                                <th class="text-center">Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stu['history'] as $row): ?>
                            <tr>
                                <td class="fw-bold"><?= date('d.m.Y', strtotime($row['AttendanceDate'])) ?></td>
                                <td class="text-center">
                                    <?php if ($row['Status'] == 1): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-3">Geldi</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-3">Gelmedi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>