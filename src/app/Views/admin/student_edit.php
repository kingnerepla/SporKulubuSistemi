<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fa-solid fa-user-pen me-2"></i>Öğrenci Kartını Düzenle
                    </h5>
                    <a href="index.php?page=students" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i>Listeye Dön
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="index.php?page=student_update" method="POST">
                        <input type="hidden" name="student_id" value="<?= $student['StudentID'] ?>">
                        <input type="hidden" name="parent_id" value="<?= $student['ParentID'] ?>">
                        
                        <h6 class="text-secondary fw-bold mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-child me-1"></i>Kimlik & Grup Bilgileri
                        </h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Adı Soyadı</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($student['FullName']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Doğum Tarihi</label>
                                <input type="date" name="birth_date" class="form-control" 
                                       value="<?= date('Y-m-d', strtotime($student['BirthDate'])) ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Dahil Olduğu Grup</label>
                            <select name="group_id" class="form-select text-dark fw-bold">
                                <option value="">-- Grup Seçiniz --</option>
                                <?php foreach($groups as $g): ?>
                                    <option value="<?= $g['GroupID'] ?>" <?= ($g['GroupID'] == $student['GroupID']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['GroupName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="p-3 bg-light rounded-3 border mb-4">
                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                <i class="fa-solid fa-ticket me-1"></i>Paket & Bakiye Ayarları
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small fw-bold">Standart Paket</label>
                                    <select name="standard_sessions" class="form-select">
                                        <option value="8" <?= ($student['StandardSessions'] == 8) ? 'selected' : '' ?>>Haftada 2 (8 Ders)</option>
                                        <option value="12" <?= ($student['StandardSessions'] == 12) ? 'selected' : '' ?>>Haftada 3 (12 Ders)</option>
                                        <option value="4" <?= ($student['StandardSessions'] == 4) ? 'selected' : '' ?>>Haftada 1 (4 Ders)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small fw-bold">Paket Ücreti (₺)</label>
                                    <input type="number" name="package_fee" class="form-control" 
                                           value="<?= number_format($student['PackageFee'], 0, '.', '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small fw-bold text-danger">Kalan Ders (Bakiye)</label>
                                    <input type="number" name="remaining_sessions" class="form-control fw-bold text-danger border-danger" 
                                           value="<?= $student['RemainingSessions'] ?>">
                                    <div class="form-text x-small">Manuel düzeltme yapabilirsiniz.</div>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-secondary fw-bold mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-user-shield me-1"></i>Veli İletişim
                        </h6>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Veli Ad Soyad</label>
                                <input type="text" name="parent_name" class="form-control" value="<?= htmlspecialchars($student['ParentName'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Telefon</label>
                                <input type="text" name="parent_phone" id="phone_mask" class="form-control fw-bold" 
                                       value="<?= $student['ParentPhoneAccount'] ?? $student['ParentPhone'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-4">
                            <a href="index.php?page=students" class="btn btn-light px-4 border">İptal</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Değişiklikleri Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function(){
        $('#phone_mask').mask('(000) 000 00 00');
    });
</script>