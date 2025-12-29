<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fa-solid fa-user-plus me-2"></i>Yeni Öğrenci ve Veli Kaydı
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="index.php?page=student_store" method="POST" id="studentForm">
                        
                        <h6 class="text-secondary fw-bold mb-3 border-bottom pb-2">Öğrenci Bilgileri</h6>
                        <div class="row mb-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Öğrenci Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="student_name" class="form-control" placeholder="Örn: Ali Yılmaz" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Doğum Tarihi <span class="text-danger">*</span></label>
                                <input type="text" name="birth_date" id="birth_date_mask" class="form-control" placeholder="GG.AA.YYYY" required>
                                <small class="text-muted" style="font-size: 0.7rem;">Yaş hesabı için gereklidir.</small>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Dahil Olacağı Grup (Opsiyonel)</label>
                                <select name="group_id" class="form-select">
                                    <option value="">Grup Atanmadı (Sonra Seçilebilir)</option>
                                    <?php foreach($groups as $g): ?>
                                        <option value="<?= $g['GroupID'] ?>"><?= $g['GroupName'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Eğitmen Notu / Sağlık Notu</label>
                                <textarea name="notes" class="form-control" rows="1" placeholder="Alerji, sakatlık veya özel notlar..."></textarea>
                            </div>
                        </div>

                        <h6 class="text-secondary fw-bold mb-3 border-bottom pb-2">Veli ve Hesap Bilgileri</h6>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Veli Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="parent_name" class="form-control" placeholder="Örn: Mehmet Yılmaz" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Veli Telefon (Giriş ID) <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone_mask" class="form-control" placeholder="0(5xx) xxx xx xx" required>
                                <div class="form-text text-info" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle-info me-1"></i> Bu numara velinin giriş adı olacaktır.
                                </div>
                            </div>
                        </div>

                        <h6 class="text-success fw-bold mb-3 border-bottom pb-2">Finansal Bilgiler</h6>
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label class="form-label small fw-bold">Aylık Sabit Aidat Tutarı</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">₺</span>
                                    <input type="number" name="monthly_fee" class="form-control fw-bold text-success" value="1500" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                            <a href="index.php?page=students" class="btn btn-light px-4 border">İptal</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="fa-solid fa-check me-2"></i>Kaydı Tamamla
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
    // Türkiye Telefon Formatı Maskesi
    $('#phone_mask').mask('0(500) 000 00 00');
    
    // Doğum Tarihi Maskesi (GG.AA.YYYY)
    $('#birth_date_mask').mask('00.00.0000');
});
</script>