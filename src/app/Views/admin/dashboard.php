<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark">Hoş Geldiniz, <?php echo htmlspecialchars($name); ?></h3>
            <p class="text-muted small">Kulübünüzdeki son durum ve kritik özetler aşağıdadır. 
                <a href="index.php?page=profile" class="text-decoration-none ms-2"><i class="fa-solid fa-user-gear me-1"></i>Profilim</a>
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark shadow-sm border p-2">
                <i class="fa-solid fa-calendar-day me-1 text-primary"></i> <?php echo date('d.m.Y'); ?>
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Toplam Öğrenci</small>
                    <h2 class="fw-bold mb-0"><?php echo $stats['totalStudents'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Aylık Beklenen Gelir</small>
                    <h2 class="fw-bold mb-0">₺<?php echo number_format($stats['expectedRevenue'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Tahsil Edilen</small>
                    <h2 class="fw-bold mb-0">₺<?php echo number_format($stats['receivedRevenue'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white bg-primary">
                <div class="card-body">
                    <small class="text-uppercase fw-bold opacity-75">Aktif Gruplar</small>
                    <h2 class="fw-bold mb-0"><?php echo $stats['totalGroups'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-danger">
                        <i class="fa-solid fa-bell me-2"></i>Son Ödeme Hareketleri
                    </h6>
                    <a href="index.php?page=finance" class="small text-decoration-none">Tümünü Gör</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Öğrenci Adı</th>
                                    <th>Durum</th>
                                    <th>Tutar</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($criticalClubs)): ?>
                                    <?php foreach ($criticalClubs as $club): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($club['FullName'] ?? $club['ClubName']); ?></td>
                                            <td><span class="badge bg-danger-subtle text-danger px-3 small">Ödeme Bekliyor</span></td>
                                            <td>₺<?php echo number_format($club['Amount'], 2); ?></td>
                                            <td class="text-muted small"><?php echo date('d.m.Y', strtotime($club['PaymentDate'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-circle-check text-success fa-2x d-block mb-2"></i>
                                            Şu an dikkat gerektiren bir ödeme yok.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-shield-halved me-2 text-warning"></i>Antrenör Yetki Ayarları</h6>
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded border">
                        <div>
                            <span class="d-block fw-bold small">Aylık Yoklama Raporu Erişimi</span>
                            <small class="text-muted">Antrenörler kendi gruplarının devam çizelgesini görebilsin mi?</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="coachAccessSwitch" 
                                   style="width: 2.8rem; height: 1.5rem; cursor: pointer;"
                                   <?php echo (isset($club['CoachReportAccess']) && $club['CoachReportAccess'] == 1) ? 'checked' : ''; ?>
                                   onchange="updateCoachAccess(this.checked)">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-bolt me-2 text-warning"></i>Hızlı İşlemler</h5>
                <div class="d-grid gap-3">
                    <a href="index.php?page=students" class="btn btn-outline-light text-start border-secondary">
                        <i class="fa-solid fa-users me-2"></i> Öğrenci Listesi
                    </a>
                    
                    <a href="index.php?page=attendance_report" class="btn btn-info text-dark fw-bold text-start">
                        <i class="fa-solid fa-chart-line me-2"></i> Yoklama Raporlarını Gör
                    </a>

                    <a href="index.php?page=finance" class="btn btn-outline-success text-start border-secondary">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i> Aidat ve Finans Takibi
                    </a>

                    <hr class="opacity-20">
                    
                    <a href="index.php?page=student_add" class="btn btn-primary text-start shadow-sm">
                        <i class="fa-solid fa-plus me-2"></i> Yeni Öğrenci Kaydı
                    </a>
                </div>

                <div class="mt-4 p-3 bg-secondary bg-opacity-10 rounded border border-secondary border-opacity-25">
                    <small class="text-info d-block mb-1"><i class="fa-solid fa-info-circle me-1"></i> İpucu</small>
                    <small class="text-muted small" style="font-size: 0.75rem;">
                        Antrenör yetkisini kapattığınızda, antrenörler menüsünden rapor butonu gizlenir ve erişim engellenir.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateCoachAccess(status) {
    const isVisible = status ? 1 : 0;
    
    // Basit bir görsel geri bildirim
    const switchEl = document.getElementById('coachAccessSwitch');
    switchEl.disabled = true;

    const formData = new URLSearchParams();
    formData.append('status', isVisible);

    fetch('index.php?page=update_coach_permission', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Ağ hatası');
        return response.text();
    })
    .then(data => {
        // İşlem başarılı
        console.log('Yetki Güncellendi:', data);
    })
    .catch(error => {
        alert('Hata: Yetki güncellenemedi!');
        switchEl.checked = !status; // Hata durumunda switch'i eski haline çek
    })
    .finally(() => {
        switchEl.disabled = false;
    });
}
</script>

<style>
    .card { transition: transform 0.2s ease; }
    .table-hover tbody tr:hover { background-color: rgba(0,0,0,.02); }
    .form-switch .form-check-input:focus { border-color: rgba(255,193,7, 0.5); box-shadow: 0 0 0 0.25rem rgba(255,193,7, 0.25); }
    .form-switch .form-check-input:checked { background-color: #ffc107; border-color: #ffc107; }
</style>