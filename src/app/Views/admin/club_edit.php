<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Kulüp Düzenle: <?= htmlspecialchars($club['ClubName']) ?></h5>
                </div>
                <div class="card-body p-4">
                    <form action="index.php?page=club_update" method="POST">
                        <input type="hidden" name="club_id" value="<?= $club['ClubID'] ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Kulüp Adı</label>
                                <input type="text" name="club_name" class="form-control rounded-pill" value="<?= htmlspecialchars($club['ClubName']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Sistem Durumu</label>
                                <select name="status" class="form-select rounded-pill">
                                    <option value="Active" <?= $club['Status'] == 'Active' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="Suspended" <?= $club['Status'] == 'Suspended' ? 'selected' : '' ?>>Donduruldu (Suspended)</option>
                                </select>
                            </div>
                            
                            <hr class="my-4 opacity-10">
                            <h6 class="fw-bold mb-2 small text-muted text-uppercase">SaaS & Finansal Ayarlar</h6>
                            
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Lisans Bitiş Tarihi</label>
                                <input type="date" name="license_end_date" class="form-control rounded-pill" value="<?= $club['LicenseEndDate'] ? date('Y-m-d', strtotime($club['LicenseEndDate'])) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Öğrenci Başı Ücret (Aylık)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">₺</span>
                                    <input type="number" name="monthly_fee" class="form-control rounded-pill-end" value="<?= $club['MonthlyPerStudentFee'] ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Yıllık Lisans Ücreti</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">₺</span>
                                    <input type="number" name="annual_fee" class="form-control rounded-pill-end" value="<?= $club['AnnualLicenseFee'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                            <a href="index.php?page=dashboard" class="btn btn-light rounded-pill px-4">İptal</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Değişiklikleri Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>