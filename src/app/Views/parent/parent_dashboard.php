<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Hoş Geldiniz, <?= htmlspecialchars($_SESSION['name']); ?></h4>
            <p class="text-muted small">Çocuğunuzun gelişimini ve kulüp bilgilerini buradan takip edebilirsiniz.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-primary shadow-sm border p-2 px-3 rounded-pill">
                <i class="fa-solid fa-shield-heart me-1"></i> Velisi Olduğum Öğrenciler: <?= count($students); ?>
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-primary text-white p-2">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 bg-white bg-opacity-25 rounded-circle p-3 me-3">
                        <i class="fa-solid fa-calendar-check fa-2x"></i>
                    </div>
                    <div>
                        <small class="opacity-75 d-block">Bu Ayki Katılım Oranı</small>
                        <h3 class="fw-bold mb-0">%<?= $attendanceRate; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-success text-white p-2">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 bg-white bg-opacity-25 rounded-circle p-3 me-3">
                        <i class="fa-solid fa-file-invoice-dollar fa-2x"></i>
                    </div>
                    <div>
                        <small class="opacity-75 d-block">Ödeme Durumu</small>
                        <h3 class="fw-bold mb-0"><?= $paymentStatus; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <?php foreach ($students as $student): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fa-solid fa-child-reaching me-2 text-warning"></i><?= htmlspecialchars($student['FullName']); ?> - Son Katılım Durumu
                        </h6>
                        <a href="index.php?page=parent_attendance" class="btn btn-sm btn-outline-primary rounded-pill">Tüm Geçmiş</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light small">
                                    <tr>
                                        <th class="ps-4">Tarih</th>
                                        <th>Grup / Ders</th>
                                        <th class="text-center">Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($student['last_attendance'] as $att): ?>
                                    <tr>
                                        <td class="ps-4 small fw-bold"><?= date('d.m.Y', strtotime($att['AttendanceDate'])); ?></td>
                                        <td class="small"><?= htmlspecialchars($student['GroupName']); ?></td>
                                        <td class="text-center">
                                            <?php if ($att['Status'] == 1): ?>
                                                <span class="badge bg-success-subtle text-success rounded-pill px-3">Geldi</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Gelmedi</span>
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

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Hızlı Menü</h6>
                    <div class="list-group list-group-flush">
                        <a href="index.php?page=parent_attendance" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                            <i class="fa-solid fa-calendar-days me-3 text-primary"></i> Devamsızlık Takibi
                        </a>
                        <a href="index.php?page=parent_payments" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                            <i class="fa-solid fa-credit-card me-3 text-success"></i> Ödemeler ve Aidatlar
                        </a>
                        <a href="index.php?page=club_info" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                            <i class="fa-solid fa-circle-info me-3 text-info"></i> Kulüp Bilgileri
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center p-4">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3">Sorumlu Antrenör</h6>
                    <div class="avatar-md bg-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-user-tie fa-2x text-primary"></i>
                    </div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($coach['FullName'] ?? 'Atanmadı'); ?></h6>
                    <p class="text-muted small mb-0"><?= htmlspecialchars($coach['Phone'] ?? '-'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>