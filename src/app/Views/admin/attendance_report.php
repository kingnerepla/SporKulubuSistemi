<div class="container-fluid py-4" id="reportContainer">

    <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none bg-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-chart-pie text-primary me-2"></i>Dönemsel Gelişim Raporu
                    </h4>
                    <p class="text-muted small mb-0">Takımların yıllık devamlılık döngülerini ve performanslarını inceleyin.</p>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <i class="fa-regular fa-calendar me-2"></i>Seçili Yıl: <strong><?= $selectedYear ?></strong>
                    </span>
                </div>
            </div>
            
            <hr class="my-4 opacity-10">

            <form action="index.php" method="GET" class="row g-3">
                <input type="hidden" name="page" value="attendance_report">
                
                <div class="col-md-4">
                    <label class="small fw-bold text-muted mb-1">Grup Filtrele</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-users text-muted"></i></span>
                        <select name="group_id" class="form-select border-start-0 ps-0" onchange="this.form.submit()">
                            <option value="">-- Tüm Grupları Göster --</option>
                            <?php foreach($allGroups as $g): ?>
                                <option value="<?= $g['GroupID'] ?>" <?= ($selectedGroupId == $g['GroupID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g['GroupName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">Yıl</label>
                    <select name="year" class="form-select shadow-sm" onchange="this.form.submit()">
                        <?php 
                        $currentYear = date('Y');
                        for($y = $currentYear; $y >= $currentYear - 2; $y--): ?>
                            <option value="<?= $y ?>" <?= ($selectedYear == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-5 text-end d-flex align-items-end justify-content-end gap-2">
                    <?php if(!$isCoach): ?>
                        <button type="button" class="btn btn-dark fw-bold px-4 shadow-sm" onclick="window.print()">
                            <i class="fa-solid fa-print me-2"></i>Raporu Yazdır
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if(!empty($finalReports)): ?>
        
        <div class="accordion" id="groupsAccordion">
            <?php foreach($finalReports as $index => $report): 
                $accordionId = "collapseGroup" . $report['group_info']['GroupID'];
                $headingId = "headingGroup" . $report['group_info']['GroupID'];
                // Tek grup seçildiyse açık gelsin, hepsi seçiliyse kapalı gelsin
                $isShow = ($selectedGroupId) ? 'show' : ''; 
                $isCollapsed = ($selectedGroupId) ? '' : 'collapsed';
            ?>
            
            <div class="card border-0 shadow-sm mb-3 overflow-hidden rounded-3 group-print-container">
                
                <div class="card-header bg-white p-0 border-0" id="<?= $headingId ?>">
                    <button class="accordion-button <?= $isCollapsed ?> p-4 shadow-none bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $accordionId ?>" aria-expanded="<?= $selectedGroupId ? 'true' : 'false' ?>" aria-controls="<?= $accordionId ?>">
                        <div class="d-flex align-items-center w-100">
                            <div class="rounded-pill bg-primary me-3" style="width: 5px; height: 50px;"></div>
                            
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($report['group_info']['GroupName']) ?></h5>
                                <div class="text-muted small">
                                    <i class="fa-solid fa-list-ol me-1"></i> <?= count($report['chunks']) ?> Dönem 
                                    <span class="mx-2">•</span>
                                    <i class="fa-solid fa-user-group me-1"></i> <?= count($report['students']) ?> Sporcu
                                </div>
                            </div>
                            
                            <div class="me-3 text-end d-none d-md-block text-muted small">
                                <span class="d-block">Döngü: <strong><?= $report['cycle_count'] ?> Ders</strong></span>
                            </div>
                        </div>
                    </button>
                </div>

                <div id="<?= $accordionId ?>" class="accordion-collapse collapse <?= $isShow ?> print-show" aria-labelledby="<?= $headingId ?>" data-bs-parent="#groupsAccordion">
                    <div class="card-body bg-light bg-opacity-50 p-4 pt-0">
                        
                        <?php if(!empty($report['chunks'])): ?>
                            <div class="row g-4 mt-2">
                                <?php foreach($report['chunks'] as $chunk): ?>
                                    
                                    <div class="col-12 break-inside-avoid">
                                        <div class="card border shadow-none h-100">
                                            <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 rounded-pill">
                                                    <?= $chunk['period_no'] ?>. DÖNEM
                                                </span>
                                                <small class="text-muted fw-bold" style="font-size: 0.8rem;">
                                                    <?= date('d.m.Y', strtotime($chunk['start'])) ?> — <?= date('d.m.Y', strtotime($chunk['end'])) ?>
                                                </small>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered table-sm text-center align-middle mb-0 print-table" style="font-size: 0.85rem;">
                                                    <thead class="bg-light text-secondary">
                                                        <tr>
                                                            <th class="text-start bg-light ps-3 text-muted text-uppercase x-small" style="width: 180px;">Öğrenci</th>
                                                            
                                                            <?php foreach($chunk['dates'] as $dateStr): 
                                                                $ts = strtotime($dateStr);
                                                                $trDays = [1 => 'Pzt', 2 => 'Sal', 3 => 'Çrş', 4 => 'Prş', 5 => 'Cum', 6 => 'Cmt', 7 => 'Paz'];
                                                            ?>
                                                                <th class="fw-normal" style="min-width: 35px;">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="fw-bold text-dark"><?= date('d', $ts) ?></span>
                                                                        <span class="text-muted x-small"><?= $trDays[date('N', $ts)] ?></span>
                                                                    </div>
                                                                </th>
                                                            <?php endforeach; ?>
                                                            
                                                            <?php for($i = count($chunk['dates']); $i < $report['cycle_count']; $i++): ?>
                                                                <th class="bg-light"></th>
                                                            <?php endfor; ?>
                                                            
                                                            <th class="bg-light text-muted x-small" style="width: 60px;">DURUM</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white">
                                                        <?php foreach($report['students'] as $s): 
                                                            $sid = $s['StudentID'];
                                                            $present = 0;
                                                            foreach($chunk['dates'] as $d) {
                                                                if(($report['attendance'][$sid][$d] ?? 0) == 1) $present++;
                                                            }
                                                        ?>
                                                        <tr>
                                                            <td class="text-start fw-bold text-dark ps-3"><?= htmlspecialchars($s['FullName']) ?></td>
                                                            
                                                            <?php foreach($chunk['dates'] as $d): 
                                                                $status = $report['attendance'][$sid][$d] ?? null;
                                                                $icon = '-'; $color = 'text-muted';
                                                                
                                                                if ($status == 1) { 
                                                                    $icon = '<i class="fa-solid fa-check"></i>'; 
                                                                    $color = 'text-success'; 
                                                                } elseif ($status === 0 || $status === '0') { 
                                                                    $icon = '<i class="fa-solid fa-xmark"></i>'; 
                                                                    $color = 'text-danger opacity-50'; 
                                                                }
                                                            ?>
                                                                <td class="<?= $color ?>"><?= $icon ?></td>
                                                            <?php endforeach; ?>

                                                            <?php for($i = count($chunk['dates']); $i < $report['cycle_count']; $i++): ?>
                                                                <td class="bg-light"></td>
                                                            <?php endfor; ?>

                                                            <td class="fw-bold text-muted small bg-light"><?= $present ?>/<?= count($chunk['dates']) ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border mt-3 text-center text-muted small">
                                <i class="fa-solid fa-calendar-xmark me-2"></i>Bu yıl için kayıtlı veri bulunamadı.
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fa-solid fa-folder-open fa-3x text-muted opacity-25"></i>
            </div>
            <h5 class="text-muted fw-bold">Görüntülenecek veri yok</h5>
            <p class="text-muted small">Lütfen yıl veya grup seçiminizi kontrol edin.</p>
        </div>
    <?php endif; ?>

</div>

<style>
    .x-small { font-size: 0.7rem; }
    
    /* Normal Görünümde Akordiyon Efekti */
    .accordion-button:not(.collapsed) {
        background-color: #fff;
        color: #000;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }

    /* YAZDIRMA MODU (Çok Önemli) */
    @media print {
        @page { size: landscape; margin: 5mm; }
        
        body { visibility: hidden; background: white; }
        .d-print-none, .navbar, #sidebar-wrapper { display: none !important; }
        
        #reportContainer { visibility: visible; position: absolute; left: 0; top: 0; width: 100%; }
        #reportContainer * { visibility: visible; }

        /* Tüm akordiyonları zorla aç */
        .collapse { display: block !important; height: auto !important; opacity: 1 !important; visibility: visible !important; }
        
        /* Kart Stillerini Sadeleştir */
        .card, .card-header, .accordion-button { 
            border: none !important; 
            box-shadow: none !important; 
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        /* Her Grup Yeni Sayfada Başlasın */
        .group-print-container { page-break-before: always; page-break-inside: avoid; }
        .group-print-container:first-child { page-break-before: auto; }
        
        /* Dönemleri Parçalama */
        .break-inside-avoid { page-break-inside: avoid; margin-bottom: 20px; }

        /* Tabloyu Güzelleştir */
        table { width: 100% !important; border: 1px solid #ddd !important; font-size: 10px !important; }
        th, td { padding: 4px !important; border: 1px solid #999 !important; }
        
        /* Renkleri Bas (Chrome ayarı gerektirebilir ama zorlarız) */
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        /* İkonları metne çevir (Yazıcıda ikon çıkmayabilir) */
        .fa-check:before { content: "VAR"; font-size: 8px; }
        .fa-xmark:before { content: "YOK"; font-size: 8px; }
    }
</style>