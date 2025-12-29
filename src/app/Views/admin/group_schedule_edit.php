<div class="container-fluid">
    <?php if (isset($_GET['success']) && $_GET['success'] == 'template_saved'): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success d-flex align-items-center shadow-sm border-0 mb-4" role="alert" style="background-color: #d1e7dd; color: #0f5132;">
                    <i class="fa-solid fa-circle-check fs-4 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Haftalık Şablon Kaydedildi!</h6>
                        <small>Şimdi sağ taraftaki panelden tarih aralığı seçerek <strong>"Antrenmanları Oluştur"</strong> butonuna basabilirsiniz.</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fa-solid fa-calendar-week text-primary me-2"></i>Haftalık Program Düzenle</h3>
        <a href="index.php?page=groups" class="btn btn-secondary btn-sm">Geri Dön</a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">Haftalık Sabit Günler</h5>
                    <small class="text-muted">Grubun her hafta standart yaptığı antrenman saatleri.</small>
                </div>
                <form action="index.php?page=group_schedule_save" method="POST">
                    <input type="hidden" name="group_id" value="<?= $groupId ?>">
                    <div class="card-body">
                        <?php 
                        $gunler = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
                        foreach($gunler as $num => $ad): 
                            $data = null;
                            foreach($schedules as $s) { if($s['DayOfWeek'] == $num) $data = $s; }
                        ?>
                        <div class="row mb-3 align-items-center border-bottom pb-3">
                            <div class="col-md-3 fw-bold"><?= $ad ?></div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">Başla</span>
                                    <input type="time" name="days[<?= $num ?>][start]" class="form-control" 
                                           value="<?= $data ? date('H:i', strtotime($data['StartTime'])) : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">Bitir</span>
                                    <input type="time" name="days[<?= $num ?>][end]" class="form-control" 
                                           value="<?= $data ? date('H:i', strtotime($data['EndTime'])) : '' ?>">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer bg-light text-end">
                        <button type="submit" class="btn btn-primary fw-bold px-4">Şablonu Kaydet</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary">Dersleri Takvime İşle</h5>
                    <small class="text-muted">Şablona göre toplu antrenman kaydı oluşturur.</small>
                </div>
                
                <form action="index.php?page=generate_sessions" method="POST" class="card-body">
                    <input type="hidden" name="group_id" value="<?= $groupId ?>">
                    
                    <div class="alert alert-warning small">
                        <i class="fa-solid fa-circle-info me-1"></i> 
                        Bu işlem, seçtiğiniz tarih aralığındaki şablona uyan tüm günleri <strong>TrainingSessions</strong> tablosuna ekler.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bitiş Tarihi</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 fw-bold">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Antrenmanları Oluştur
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>