<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fa-solid fa-chart-line text-primary me-2"></i>Aylık Yoklama Raporu</h3>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="index.php" method="GET" class="row g-3">
                <input type="hidden" name="page" value="attendance_report">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Grup Seçin</label>
                    <select name="group_id" class="form-select shadow-sm" required>
                        <option value="">-- Grup Seçiniz --</option>
                        <?php foreach($groups as $g): ?>
                            <option value="<?= $g['GroupID'] ?>" <?= ($selectedGroup == $g['GroupID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['GroupName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Ay Seçin</label>
                    <input type="month" name="month" class="form-control shadow-sm" value="<?= $selectedMonth ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Raporu Getir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selectedGroup): ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small fw-bold text-muted">Açıklama: 
                    <i class="fa-solid fa-circle text-success ms-2"></i> Geldi 
                    <i class="fa-solid fa-circle text-danger ms-2"></i> Gelmedi 
                    <span class="ms-2">(-) Yoklama Alınmadı</span>
                </span>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-print me-1"></i> Yazdır / PDF
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0 report-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 border-end" style="min-width: 180px;">Öğrenci Ad Soyad</th>
                            <?php for($i=1; $i<=$daysInMonth; $i++): ?>
                                <th class="text-center p-1" style="width: 30px; font-size: 10px;"><?= $i ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reportData as $name => $days): ?>
                        <tr>
                            <td class="ps-3 fw-bold small border-end"><?= htmlspecialchars($name) ?></td>
                            <?php for($i=1; $i<=$daysInMonth; $i++): ?>
                                <td class="text-center p-0" style="height: 35px;">
                                    <?php 
                                        if (isset($days[$i])) {
                                            if ($days[$i] == 1) 
                                                echo '<i class="fa-solid fa-circle text-success" style="font-size: 12px;"></i>';
                                            else 
                                                echo '<i class="fa-solid fa-circle text-danger" style="font-size: 12px;"></i>';
                                        } else {
                                            echo '<small class="text-muted" style="font-size: 10px;">-</small>';
                                        }
                                    ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-info text-center shadow-sm border-0">
            <i class="fa-solid fa-info-circle me-2"></i> Lütfen raporunu görmek istediğiniz grubu ve ayı seçiniz.
        </div>
    <?php endif; ?>
</div>

<style>
    .report-table th, .report-table td { border-color: #f1f1f1 !important; }
    .report-table tbody tr:hover { background-color: #fbfbfb; }
    @media print {
        .btn, form, .card-header { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table-responsive { overflow: visible !important; }
    }
</style>