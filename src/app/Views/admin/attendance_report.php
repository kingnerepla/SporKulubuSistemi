<div class="container-fluid py-4" id="reportContainer">

    <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">
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
                
                <div class="col-md-2 text-end d-flex gap-2">
                    <button type="button" class="btn btn-outline-success w-100" onclick="window.print()">
                        <i class="fa-solid fa-print"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="sendQuickMail()">
                        <i class="fa-solid fa-envelope"></i>
                    </button>
        
                </div>
            </form>
        </div>
    </div>

    <div class="d-none d-print-block text-center mb-3">
        <h3>SPOR CRM - YOKLAMA ÇİZELGESİ</h3>
        <p>
            <strong>Grup:</strong> 
            <?php foreach($groups as $g) { if($g['GroupID'] == $selectedGroupId) echo $g['GroupName']; } ?> 
            &nbsp;|&nbsp; 
            <strong>Tarih:</strong> <?= $months[$selectedMonth] ?> <?= $selectedYear ?>
        </p>
    </div>

    <?php if(!empty($students)): 
        $daysInMonth = date('t', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
    ?>
    <div class="card border-0 shadow rounded-4 overflow-visible-print">
        <div class="table-responsive-print">
            <table class="table table-bordered table-sm text-center align-middle mb-0 print-table">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="text-start bg-white" style="min-width: 150px;">Öğrenci Adı</th>
                        <th class="bg-white text-muted">Top.</th>
                        
                        <?php for($d=1; $d<=$daysInMonth; $d++): 
                            $isLessonDay = isset($lessonDays[$d]);
                            $bgClass = $isLessonDay ? 'bg-primary text-white' : '';
                            $style = $isLessonDay ? 'background-color: #0d6efd !important; color: white !important; -webkit-print-color-adjust: exact;' : '';
                        ?>
                            <th class="<?= $bgClass ?>" style="width: 25px; <?= $style ?>"><?= $d ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $s): 
                        $sid = $s['StudentID'];
                        $presentCount = 0;
                        // Toplamı Hesapla (Gevşek karşılaştırma == kullanılır)
                        for($d=1; $d<=$daysInMonth; $d++) {
                            if(isset($attendanceData[$sid][$d]) && $attendanceData[$sid][$d] == 1) $presentCount++;
                        }
                    ?>
                    <tr>
                        <td class="text-start fw-bold text-dark text-nowrap">
                            <?= htmlspecialchars($s['FullName']) ?>
                        </td>

                        <td class="fw-bold bg-light"><?= $presentCount ?></td>

                        <?php for($d=1; $d<=$daysInMonth; $d++): 
                            $status = $attendanceData[$sid][$d] ?? null;
                            $cellContent = '';
                            $cellStyle = '';

                            // DÜZELTME BURADA YAPILDI: === yerine == kullanıldı
                            if ($status == 1) { // GELDİ ("1" veya 1)
                                $cellContent = '&#10003;'; // Tik İşareti
                                $cellStyle = 'background-color: #d1e7dd !important; color: #0f5132 !important; font-weight:bold;';
                            } elseif ($status !== null && $status == 0) { // GELMEDİ ("0" veya 0)
                                $cellContent = '&#10007;'; // Çarpı İşareti
                                $cellStyle = 'background-color: #f8d7da !important; color: #842029 !important;';
                            } else {
                                if (isset($lessonDays[$d])) {
                                    $cellContent = '-';
                                    $cellStyle = 'color: #ccc;';
                                }
                            }
                        ?>
                            <td style="<?= $cellStyle ?> -webkit-print-color-adjust: exact; font-size: 10px; padding: 2px;"><?= $cellContent ?></td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-3 text-muted small d-print-none">
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
    .overflow-visible-print { overflow: hidden; }
    .table-responsive-print { overflow-x: auto; }

    @media print {
        @page { size: landscape; margin: 5mm; }
        body { visibility: hidden; background-color: white !important; }
        #sidebar-wrapper, .navbar, .btn, .d-print-none, form { display: none !important; }
        #reportContainer { visibility: visible; position: absolute; left: 0; top: 0; width: 100%; margin: 0 !important; padding: 0 !important; }
        #reportContainer * { visibility: visible; }
        .card { border: none !important; box-shadow: none !important; }
        .table-responsive-print { overflow: visible !important; }
        table { width: 100% !important; font-size: 10px !important; border-collapse: collapse !important; }
        th, td { padding: 2px !important; border: 1px solid #999 !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function sendQuickMail() {
    Swal.fire({
        title: 'Rapor Gönderiliyor...',
        text: 'Lütfen bekleyin, rapor kayıtlı e-posta adresinize iletiliyor.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            const groupId = '<?= $selectedGroupId ?>';
            const month = '<?= $selectedMonth ?>';
            const year = '<?= $selectedYear ?>';
            
            // Kullanıcıya sormadan direkt kayıtlı mailine gönderiyoruz
            window.location.href = `index.php?page=attendance_report_mail&group_id=${groupId}&month=${month}&year=${year}`;
        }
    });
}
</script>