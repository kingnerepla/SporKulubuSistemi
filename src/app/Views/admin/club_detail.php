<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-4 bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($club['ClubName']); ?></h2>
                        <p class="mb-0 opacity-75">Kulüp Kimliği: #<?php echo $club['ClubID']; ?> | Kayıt Tarihi: <?php echo date('d.m.Y', strtotime($club['CreatedAt'])); ?></p>
                    </div>
                    <a href="index.php?page=clubs" class="btn btn-light btn-sm px-4">Listeye Dön</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <ul class="nav nav-tabs card-header-tabs" id="clubTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active fw-bold" id="finance-tab" data-bs-toggle="tab" href="#finance" role="tab">Sistem Ödemeleri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" id="students-tab" data-bs-toggle="tab" href="#students" role="tab">Öğrenci Listesi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" id="coaches-tab" data-bs-toggle="tab" href="#coaches" role="tab">Antrenörler</a>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="clubTabContent">
                
                <div class="tab-pane fade show active" id="finance" role="tabpanel">
                    <div class="d-flex justify-content-between mb-3">
                        <h6>Lisans ve Yazılım Ödemeleri</h6>
                        <span class="badge bg-success">Aktif Lisans</span>
                    </div>
                    <table class="table table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Dönem</th><th>Tutar</th><th>Durum</th><th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Ocak 2024</td><td>1.500 TL</td><td><span class="badge bg-success">Ödendi</span></td><td>02.01.2024</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="students" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Öğrenci Adı</th><th>Grup</th><th>Durum</th></tr></thead>
                            <tbody>
                                <?php foreach($students as $s): ?>
                                    <tr>
                                        <td><?php echo $s['FullName']; ?></td>
                                        <td><?php echo $s['GroupName']; ?></td>
                                        <td><span class="badge bg-info">Aktif</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="coaches" role="tabpanel">
                    <div class="row">
                        <?php foreach($coaches as $c): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border p-3">
                                    <h6 class="mb-1 fw-bold"><?php echo $c['FullName']; ?></h6>
                                    <small class="text-muted"><?php echo $c['Specialty'] ?? 'Antrenör'; ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>