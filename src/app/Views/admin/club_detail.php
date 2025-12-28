<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="index.php?page=clubs" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fa-solid fa-arrow-left"></i> Kulüp Listesine Dön
        </a>
        <h2 class="d-flex align-items-center">
            <?php if(!empty($club['LogoPath'])): ?>
                <img src="<?php echo htmlspecialchars($club['LogoPath']); ?>" class="rounded-circle border me-3" style="width: 60px; height: 60px; object-fit: cover;">
            <?php endif; ?>
            <?php echo htmlspecialchars($club['ClubName']); ?>
            <span class="badge bg-dark ms-3 fs-6">ID: <?php echo $club['ClubID']; ?></span>
        </h2>
    </div>
    
    <div>
        <button class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editClubModal">
             <i class="fa-solid fa-pen"></i> Bilgileri Düzenle
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-success border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-bold">TOPLAM KASA GELİRİ</small>
                <h3 class="text-success mt-2"><?php echo number_format($totalIncome, 2); ?> ₺</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-primary border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-bold">AKTİF ÖĞRENCİ</small>
                <h3 class="text-primary mt-2"><?php echo $studentCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-warning border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-bold">ANTRENÖR</small>
                <h3 class="text-dark mt-2"><?php echo $trainerCount; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-info border-4 h-100">
            <div class="card-body">
                <small class="text-muted fw-bold">GRUP SAYISI</small>
                <h3 class="text-info mt-2"><?php echo $groupCount; ?></h3>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="clubTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">Genel Bakış</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="groups-tab" data-bs-toggle="tab" data-bs-target="#groups" type="button">Gruplar</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button">Öğrenci Listesi</button>
    </li>
</ul>

<div class="tab-content" id="clubTabsContent">
    
    <div class="tab-pane fade show active" id="general">
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light fw-bold">Kulüp Yöneticisi</div>
                    <div class="card-body">
                        <?php if($manager): ?>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fa-solid fa-user fa-lg"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($manager['FullName']); ?></h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($manager['Email']); ?></small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">Yönetici atanmamış.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-bold">Antrenör Kadrosu</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach($trainers as $trainer): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?php echo htmlspecialchars($trainer['FullName']); ?></span>
                            <?php if($trainer['IsActive']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                        <?php if(empty($trainers)): ?>
                            <li class="list-group-item text-center text-muted">Kayıtlı antrenör yok.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="groups">
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Grup Adı</th>
                            <th>Sorumlu Hoca</th>
                            <th class="text-center">Öğrenci Sayısı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($groups as $grp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grp['GroupName']); ?></td>
                            <td><?php echo htmlspecialchars($grp['TrainerName'] ?? '-'); ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?php echo $grp['StudentCount']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($groups)): echo '<tr><td colspan="3" class="text-center text-muted">Grup yok.</td></tr>'; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="students">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Grup</th>
                                <th>Doğum Tarihi</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $st): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($st['FullName']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($st['GroupName']); ?></span></td>
                                <td><?php echo date('d.m.Y', strtotime($st['BirthDate'])); ?></td>
                                <td>
                                    <?php if($st['IsActive']): ?>
                                        <span class="badge bg-success text-white" style="font-size: 0.7em;">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white" style="font-size: 0.7em;">Pasif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($students)): echo '<tr><td colspan="4" class="text-center text-muted">Öğrenci yok.</td></tr>'; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="editClubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=club_update" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="club_id" value="<?php echo $club['ClubID']; ?>">
                
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Kulüp Bilgilerini Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kulüp Adı</label>
                        <input type="text" name="club_name" class="form-control" 
                               value="<?php echo htmlspecialchars($club['ClubName']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fiyatlandırma</label>
                        <div class="row">
                            <div class="col-6">
                                <label class="small">Öğrenci Başı</label>
                                <input type="number" name="price_per_student" class="form-control" step="0.5" value="<?php echo $club['PricePerStudent']; ?>">
                            </div>
                            <div class="col-6">
                                <label class="small">Yıllık Lisans</label>
                                <input type="number" name="license_fee" class="form-control" step="100" value="<?php echo $club['LicenseFee']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Logo</label>
                        <input type="file" name="club_logo" class="form-control" accept="image/*">
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>