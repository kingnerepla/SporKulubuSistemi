<div class="container-fluid py-4">

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-chart-column me-2"></i>Aylık Yoklama Raporu</h5>
            
            <form action="index.php" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="attendance_report">
                
                <div class="col-md-4">
                    <label class="small fw-bold text-muted mb-1">Grup / Takım</label>
                    <select name="group_id" class="form-select shadow-sm" onchange="this.form.submit()">
                        <?php if(empty($groups)): ?>
                            <option value="">Grup Bulunamadı</option>
                        <?php else: ?>
                            <?php foreach($groups as $g): ?>
                                <option value="<?= $g['GroupID'] ?>" <?= ($selectedGroupId == $g['GroupID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g['GroupName']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">Ay</label>
                    <select name="month" class="form-select shadow-sm" onchange="this.form.submit()">
                        <?php 
                        $months = [
                            1=>'Ocak', 2=>'Şubat', 3=>'Mart', 4=>'Nisan', 5=>'Mayıs', 6=>'Haziran',
                            7=>'Temmuz', 8=>'Ağustos', 9=>'Eylül', 10=>'Ekim', 11=>'Kasım', 12=>'Aralık'
                        ];
                        foreach($months as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($selectedMonth == $k) ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
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
                
                <div class="col-md-2 text-end">
                     <button type="button" class="btn btn-outline-success w-100" onclick="window.print()">
                        <i class="fa-solid fa-print me-1"></i>Yazdır
                     </button>
                </div>
            </form>
        </div>
    </div>

    <?php if(!empty($students)): 
        // Seçilen ayın kaç gün çektiğini bul
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
    ?>
    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-3 text-start bg-white position-sticky start-0" style="min-width: 200px; z-index:10;">Öğrenci Adı</th>
                        <th class="bg-white text-muted" style="min-width: 60px;">Katılım</th>
                        
                        <?php for($d=1; $d<=$daysInMonth; $d++): 
                            // O gün ders var mıydı? (Varsa sütun başlığını koyu yap)
                            $isLessonDay = isset($lessonDays[$d]);
                            $bgClass = $isLessonDay ? 'bg-primary text-white bg-opacity-75' : '';
                        ?>
                            <th class="<?= $bgClass ?>" style="width: 35px; min-width: 35px;"><?= $d ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $s): 
                        $sid = $s['StudentID'];
                        $presentCount = 0; // O ay kaç kere gelmiş?
                        
                        // Önce sayalım
                        for($d=1; $d<=$daysInMonth; $d++) {
                            if(isset($attendanceData[$sid][$d]) && $attendanceData[$sid][$d] == 1) {
                                $presentCount++;
                            }
                        }
                    ?>
                    <tr>
                        <td class="ps-3 text-start fw-bold text-dark position-sticky start-0 bg-white" style="z-index:10; border-right: 2px solid #eee;">
                            <?= htmlspecialchars($s['FullName']) ?>
                        </td>

                        <td>
                            <span class="badge bg-info text-dark bg-opacity-10 border border-info border-opacity-25 rounded-pill">
                                <?= $presentCount ?> Ders
                            </span>
                        </td>

                        <?php for($d=1; $d<=$daysInMonth; $d++): 
                            $status = $attendanceData[$sid][$d] ?? null; // 1: Geldi, 0: Gelmedi, null: Kayıt Yok
                            $cellContent = '';
                            $cellClass = '';

                            if ($status === 1) { // GELDİ
                                $cellContent = '<i class="fa-solid fa-check"></i>';
                                $cellClass = 'text-success bg-success bg-opacity-10 fw-bold';
                            } elseif ($status === 0) { // GELMEDİ (Ama ders varmış)
                                $cellContent = '<i class="fa-solid fa-xmark"></i>';
                                $cellClass = 'text-danger bg-danger bg-opacity-10';
                            } else {
                                // Kayıt yok. Eğer o gün başkalarına yoklama alınmışsa (ders günüyse), bu öğrenci yok yazılmalı veya boş
                                if (isset($lessonDays[$d])) {
                                    $cellContent = '<span class="text-muted opacity-25">-</span>';
                                }
                            }
                        ?>
                            <td class="<?= $cellClass ?> p-0"><?= $cellContent ?></td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-3 text-muted small">
        <i class="fa-solid fa-check text-success me-1"></i>: Derse Katıldı &nbsp;|&nbsp; 
        <i class="fa-solid fa-xmark text-danger me-1"></i>: Devamsız
    </div>

    <?php else: ?>
        <div class="alert alert-warning shadow-sm rounded-3 mt-3 border-0">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            Bu grupta henüz öğrenci yok veya seçim yapılmadı.
        </div>
    <?php endif; ?>

</div>

<style>
    /* Yazdırma Ayarları */
    @media print {
        body * { visibility: hidden; }
        .card-body, .card-body * { visibility: visible; }
        .card-body { position: absolute; left: 0; top: 0; width: 100%; }
        .position-sticky { position: static !important; } /* Yazıcıda sticky sorun çıkarır */
        .btn { display: none; } /* Butonları gizle */
    }
</style>