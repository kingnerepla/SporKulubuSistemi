<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="fw-bold text-dark"><i class="fa-solid fa-medal text-warning me-2"></i><?php echo htmlspecialchars($clubName); ?> Paneli</h3>
            <p class="text-muted small">Kulübünüzdeki öğrencileri, grupları ve antrenör durumlarını buradan takip edin.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-uppercase opacity-75 fw-bold">Toplam Öğrenci</small>
                            <h2 class="fw-bold mb-0"><?php echo $stats['totalStudents']; ?></h2>
                        </div>
                        <i class="fa-solid fa-user-graduate fa-3x opacity-25"></i>
                    </div>
                    <a href="index.php?page=students" class="text-white mt-3 d-block small">Tüm Listeyi Gör <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-uppercase opacity-75 fw-bold">Aktif Gruplar</small>
                            <h2 class="fw-bold mb-0"><?php echo $stats['totalGroups']; ?></h2>
                        </div>
                        <i class="fa-solid fa-people-group fa-3x opacity-25"></i>
                    </div>
                    <a href="index.php?page=groups" class="text-white mt-3 d-block small">Grupları Yönet <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-uppercase opacity-75 fw-bold">Antrenörler</small>
                            <h2 class="fw-bold mb-0"><?php echo $stats['totalCoaches']; ?></h2>
                        </div>
                        <i class="fa-solid fa-user-tie fa-3x opacity-25"></i>
                    </div>
                    <a href="index.php?page=users" class="text-white mt-3 d-block small">Personel Listesi <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Yeni Kayıt Olan Öğrenciler</h6>
                    <button class="btn btn-sm btn-primary" onclick="window.location.href='index.php?page=student_add'">+ Yeni Öğrenci</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr><th>Öğrenci Adı</th><th>Kayıt Tarihi</th></tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($recentActivity)): ?>
                                    <?php foreach($recentActivity as $student): ?>
                                        <tr>
                                            <td class="fw-bold ps-3"><?php echo htmlspecialchars($student['FullName']); ?></td>
                                            <td class="text-muted small"><?php echo date('d.m.Y', strtotime($student['CreatedAt'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="2" class="text-center py-4">Henüz öğrenci kaydı yok.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-4 text-center">
                    <i class="fa-solid fa-calendar-check fa-3x text-success mb-3"></i>
                    <h5>Bugünkü Yoklamalar</h5>
                    <p class="small text-muted">Bugün yapılması gereken derslerin yoklama durumunu kontrol edin.</p>
                    <a href="index.php?page=attendance" class="btn btn-success w-100 fw-bold">Yoklama Ekranına Git</a>
                </div>
            </div>
        </div>
    </div>
</div>