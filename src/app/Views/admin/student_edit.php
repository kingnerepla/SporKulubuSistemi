<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Öğrenci Düzenle</h2>
    <a href="index.php?page=students" class="btn btn-outline-secondary">İptal</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="index.php?page=student_update" method="POST">
            <input type="hidden" name="student_id" value="<?php echo $student['StudentID']; ?>">

            <div class="mb-3">
                <label>Öğrenci Adı Soyadı</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['FullName']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label>Grubu / Takımı</label>
                <select name="group_id" class="form-select" required>
                    <?php foreach($groups as $group): ?>
                        <option value="<?php echo $group['GroupID']; ?>" <?php echo $group['GroupID'] == $student['GroupID'] ? 'selected' : ''; ?>>
                            <?php echo $group['GroupName']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Doğum Tarihi</label>
                    <input type="date" name="birth_date" class="form-control" value="<?php echo $student['BirthDate']; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Velisi</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- Yok --</option>
                        <?php foreach($parents as $parent): ?>
                            <option value="<?php echo $parent['UserID']; ?>" <?php echo $parent['UserID'] == $student['ParentID'] ? 'selected' : ''; ?>>
                                <?php echo $parent['FullName']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
        </form>
    </div>
</div>