<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="fa-solid fa-user-graduate text-primary me-2"></i>Öğrenci Yönetimi</h3>
            <p class="text-muted small mb-0"><?php echo htmlspecialchars($clubName); ?> bünyesindeki tüm aktif öğrenciler.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fa-solid fa-user-plus me-2"></i>Yeni Öğrenci Kaydet
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Öğrenci Adı Soyadı</th>
                            <th>Grup</th>
                            <th>TC Kimlik</th>
                            <th>Veli Telefon</th>
                            <th>Durum</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($students)): ?>
                            <?php foreach($students as $s): ?>
                                <tr>
                                    <td class="ps-4 text-muted small">#<?php echo $s['StudentID'] ?? $s['id']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($s['FullName'] ?? $s['fullname']); ?></td>
                                    <td><span class="badge bg-info-subtle text-info"><?php echo htmlspecialchars($s['GroupName'] ?? 'Grup Atanmadı'); ?></span></td>
                                    <td><?php echo $s['TCNo'] ?? '-'; ?></td>
                                    <td><?php echo $s['ParentPhone'] ?? '-'; ?></td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">Aktif</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/5087/5087579.png" width="80" class="opacity-25 mb-3 d-block mx-auto">
                                    <span class="text-muted">Henüz öğrenci kaydı bulunmuyor.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Yeni Öğrenci Kayıt Formu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?page=student_store" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TC Kimlik No</label>
                            <input type="text" name="tc_no" class="form-control" maxlength="11">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Doğum Tarihi</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Veli Telefon</label>
                            <input type="text" name="parent_phone" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary px-4">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>