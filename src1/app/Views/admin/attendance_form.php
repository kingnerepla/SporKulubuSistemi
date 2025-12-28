<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><?php echo htmlspecialchars($group['GroupName']); ?></h2>
        <span class="text-muted"><i class="fa-regular fa-calendar me-1"></i> <?php echo date('d.m.Y', strtotime($date)); ?> Tarihli Yoklama</span>
    </div>
    <a href="index.php?page=attendance" class="btn btn-outline-secondary">Geri Dön</a>
</div>

<form action="index.php?page=attendance_store" method="POST">
    <input type="hidden" name="group_id" value="<?php echo $groupId; ?>">
    <input type="hidden" name="date" value="<?php echo $date; ?>">

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Öğrenci Adı</th>
                        <th class="text-end pe-4">Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $index => $student): ?>
                        <?php 
                            // Daha önce kaydedilmiş veri var mı? Yoksa varsayılan olarak "Geldi" (checked) yapabiliriz veya boş bırakabiliriz.
                            // Burada: Kayıt varsa ona göre, yoksa BOŞ (Gelmedi) varsayalım.
                            $isChecked = isset($existingRecords[$student['StudentID']]) && $existingRecords[$student['StudentID']] == 1 ? 'checked' : '';
                        ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($student['FullName']); ?></strong></td>
                        <td class="text-end pe-4">
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input" type="checkbox" style="transform: scale(1.4);" 
                                       name="attendance[<?php echo $student['StudentID']; ?>]" <?php echo $isChecked; ?>>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            <button type="submit" class="btn btn-success float-end">
                <i class="fa-solid fa-save me-2"></i> Yoklamayı Kaydet
            </button>
        </div>
    </div>
</form>