<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">Merhaba, <?php echo htmlspecialchars($name); ?></h4>
            <p class="text-muted small mb-0">Öğrencilerinizin gelişimini ve finansal durumunu buradan takip edebilirsiniz.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark border px-3 py-2 shadow-sm">
                <?= date('d.m.Y') ?>
            </span>
        </div>
    </div>

    <?php if (empty($students)): ?>
        <div class="alert alert-warning shadow-sm rounded-3 border-0 py-4 text-center">
            <i class="fa-solid fa-triangle-exclamation fa-2x mb-3 text-warning"></i><br>
            <strong>Sisteme kayıtlı bir öğrenciniz bulunamadı.</strong><br>
            <span class="small">Lütfen kulüp yetkilisi ile görüşerek öğrenci kaydının yapıldığından emin olun.</span>
        </div>
    <?php else: ?>

        <ul class="nav nav-pills mb-4 gap-2" id="pills-tab" role="tablist">
            <?php foreach($students as $index => $stu): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold shadow-sm <?= $index === 0 ? 'active' : 'bg-white text-dark' ?>" 
                            id="pills-<?= $stu['StudentID'] ?>-tab" 
                            data-bs-toggle="pill" 
                            data-bs-target="#pills-<?= $stu['StudentID'] ?>" 
                            type="button" role="tab">
                        <i class="fa-solid fa-user-graduate me-2"></i><?= htmlspecialchars($stu['FullName']) ?>
                        <?php if($stu['RemainingSessions'] <= 2): ?>
                            <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">!</span>
                        <?php endif; ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <?php foreach($students as $index => $stu): ?>
                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="pills-<?= $stu['StudentID'] ?>" role="tabpanel">
                    
                    <?php if($stu['RemainingSessions'] <= 2): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                            <i class="fa-solid fa-circle-exclamation fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Ders Hakkı Azaldı!</h6>
                                <p class="small mb-0">Öğrencimizin sadece <strong><?= $stu['RemainingSessions'] ?></strong> ders hakkı kalmıştır. Antrenmanlara kesintisiz devam edebilmesi için yeni paket almanız önerilir.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 mb-4 text-center overflow-hidden">
                                <div class="card-header bg-primary bg-gradient text-white border-0 py-4">
                                    <div class="bg-white rounded-circle d-inline-flex p-3 shadow-sm mb-2" style="width: 80px; height: 80px; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-user fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($stu['FullName']) ?></h5>
                                    <small class="opacity-75"><?= htmlspecialchars($stu['GroupName']) ?></small>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-6 border-end">
                                            <small class="text-muted d-block text-uppercase x-small fw-bold">Kalan Hak</small>
                                            <span class="fs-4 fw-bold <?= $stu['RemainingSessions'] <= 2 ? 'text-danger' : 'text-dark' ?>">
                                                <?= $stu['RemainingSessions'] ?>
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block text-uppercase x-small fw-bold">Devamlılık</small>
                                            <span class="fs-4 fw-bold text-success">%<?= $stu['attendance_rate'] ?? 0 ?></span>
                                        </div>
                                    </div>

                                    <?php 
                                        $total = ($stu['StandardSessions'] > 0) ? $stu['StandardSessions'] : 8;
                                        $percent = ($stu['RemainingSessions'] / $total) * 100;
                                        $barColor = $percent <= 25 ? 'bg-danger' : 'bg-success';
                                    ?>
                                    <div class="px-2 mb-3">
                                        <div class="d-flex justify-content-between x-small fw-bold mb-1">
                                            <span class="text-muted">DOLULUK ORANI</span>
                                            <span class="<?= $stu['RemainingSessions'] <= 2 ? 'text-danger' : 'text-success' ?>">%<?= round($percent) ?></span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar <?= $barColor ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $percent ?>%"></div>
                                        </div>
                                    </div>

                                    <hr class="my-3 opacity-10">
                                    <div class="d-flex justify-content-between px-2">
                                        <small class="text-muted">Antrenör:</small>
                                        <small class="fw-bold text-dark"><?= $stu['CoachName'] ?? 'Belirtilmemiş' ?></small>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-wallet me-2 text-warning"></i>Finansal Durum</h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted small">Paket Ücreti</span>
                                        <span class="fw-bold">₺<?= number_format($stu['PackageFee'], 0, ',', '.') ?></span>
                                    </div>
                                    
                                    <h6 class="small fw-bold text-muted mb-2 ps-1">Son Ödemeler</h6>
                                    <?php if(!empty($stu['payment_log'])): ?>
                                        <ul class="list-group list-group-flush small">
                                            <?php foreach($stu['payment_log'] as $pay): ?>
                                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center bg-transparent border-light">
                                                    <span class="text-muted"><i class="fa-regular fa-calendar me-1"></i><?= date('d.m.Y', strtotime($pay['PaymentDate'])) ?></span>
                                                    <span class="text-success fw-bold">+ ₺<?= number_format($pay['Amount'], 0, ',', '.') ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="text-muted small fst-italic mb-0 ps-1">Henüz kayıtlı ödeme bulunamadı.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-0 py-3">
                                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Son 5 Antrenman</h6>
                                </div>
                                <div class="card-body p-0">
                                    <?php if(!empty($stu['attendance_log'])): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach($stu['attendance_log'] as $att): 
                                                $dateTS = strtotime($att['Date']);
                                                $trDays = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'];
                                                $dayIndex = date('N', $dateTS);
                                                $dayName = $trDays[$dayIndex - 1];
                                                
                                                $isPresent = ($att['IsPresent'] == 1);
                                            ?>
                                                <div class="list-group-item px-4 py-3 border-light">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <div class="text-center me-3 px-3 py-1 rounded-3 <?= $isPresent ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' ?>">
                                                                <div class="fw-bold h5 mb-0"><?= date('d', $dateTS) ?></div>
                                                                <small class="text-uppercase fw-bold" style="font-size: 0.65rem;"><?= date('M', $dateTS) ?></small>
                                                            </div>
                                                            <div>
                                                                <span class="fw-bold d-block text-dark"><?= $dayName ?></span>
                                                                <small class="text-muted"><?= date('d.m.Y', $dateTS) ?></small>
                                                            </div>
                                                        </div>

                                                        <?php if($isPresent): ?>
                                                            <span class="badge bg-success rounded-pill px-3"><i class="fa-solid fa-check me-1"></i>Katıldı</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger rounded-pill px-3"><i class="fa-solid fa-xmark me-1"></i>Gelmedi</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fa-solid fa-clipboard-list fa-3x text-muted opacity-25 mb-3"></i>
                                            <p class="text-muted">Henüz yoklama kaydı bulunmuyor.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>