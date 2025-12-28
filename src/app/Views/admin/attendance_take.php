<div class="container-fluid">
    <h3 class="fw-bold mb-4"><i class="fa-solid fa-clipboard-user text-primary me-2"></i>Günlük Yoklama</h3>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="page" value="attendance">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Grup Seçin</label>
                    <select name="group_id" class="form-select" onchange="this.form.submit()">
                        <option value="">--- Grup Seçiniz ---</option>
                        <?php foreach($groups as $g): ?>
                            <option value="<?= $g['GroupID'] ?>" <?= $selectedGroup == $g['GroupID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['GroupName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Tarih</label>
                    <input type="date" name="date" class="form-control" value="<?= $selectedDate ?>" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <?php if($selectedGroup && !empty($students)): ?>
    <form action="index.php?page=attendance_save" method="POST">
        <input type="hidden" name="group_id" value="<?= $selectedGroup ?>">
        <input type="hidden" name="date" value="<?= $selectedDate ?>">
        
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Öğrenci Adı Soyadı</th>
                            <th class="text-center">Katılım Durumu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($s['FullName']) ?></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" name="status[<?= $s['StudentID'] ?>]" 
                                           style="width: 3em; height: 1.5em; cursor:pointer;"
                                           <?= ($s['AttendanceStatus'] === null || $s['AttendanceStatus'] == 1) ? 'checked' : '' ?>>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 p-3">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i>Yoklamayı Kaydet
                </button>
            </div>
        </div>
    </form>
    <?php elseif($selectedGroup): ?>
        <div class="alert alert-info border-0 shadow-sm text-center py-5">
            <i class="fa-solid fa-users-slash fs-1 mb-3"></i>
            <p class="mb-0">Bu grupta henüz kayıtlı öğrenci bulunamadı.</p>
        </div>
    <?php endif; ?>
</div>