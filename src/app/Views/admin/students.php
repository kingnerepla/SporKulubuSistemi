<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-users text-warning me-2"></i>Öğrenci Yönetimi</h3>
            <p class="text-muted small mb-0">Gruplar bazında paketlenmiş modern liste görünümü.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=students_archived" class="btn btn-outline-secondary btn-sm shadow-sm px-3">
                <i class="fa-solid fa-box-archive me-1"></i>Arşiv
            </a>
            
            <button type="button" class="btn btn-primary btn-sm shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="fa-solid fa-user-plus me-1"></i>Yeni Öğrenci
            </button>
        </div>
    </div>

    <?php if(!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(!empty($students)): ?>
        <?php 
        $currentGroup = null; 
        $groupIndex = 0;
        foreach($students as $s): 
            if ($currentGroup !== $s['GroupName']): 
                if ($currentGroup !== null) echo '</tbody></table></div></div>';
                
                $currentGroup = $s['GroupName'];
                $groupIndex++;
                
                // Tema Seçimi (Turuncu / Gri)
                $isOrange = ($groupIndex % 2 !== 0);
                $themeClass = $isOrange ? 'theme-orange' : 'theme-gray';
                $counter = 1;
        ?>
            <div class="group-package <?= $themeClass ?> mb-5 shadow-sm">
                <div class="group-title-bar px-4 py-3 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fa-solid fa-people-group me-2"></i>
                        <?= htmlspecialchars($currentGroup ?: 'GRUP ATANMAMIŞ ÖĞRENCİLER') ?>
                    </span>
                    <span class="badge bg-white bg-opacity-25 fw-normal small">Sporcu Listesi</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="small text-uppercase text-muted bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0">Öğrenci / Yaş</th>
                                <th class="border-0">Veli Bilgisi</th>
                                <th class="text-center border-0">Aylık Aidat</th>
                                <th class="text-end pe-4 border-0">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
            <?php endif; ?>

            <tr class="student-row">
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <div class="text-muted me-3 small font-monospace fw-bold opacity-50"><?= $counter++ ?>.</div>
                        <div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($s['FullName']) ?></div>
                            <div class="d-flex gap-2 small">
                                <span class="text-muted">ID: #<?= $s['StudentID'] ?></span>
                                <span class="text-muted border-start ps-2">
                                    <i class="fa-regular fa-calendar me-1"></i><?= date('d.m.Y', strtotime($s['BirthDate'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if(!empty($s['ParentName'])): ?>
                        <div class="text-dark small fw-bold"><?= htmlspecialchars($s['ParentName']) ?></div>
                    <?php else: ?>
                        <span class="text-muted small">Veli Kaydı Yok</span>
                    <?php endif; ?>

                    <?php if(!empty($s['DisplayPhone'])): ?>
                        <a href="tel:<?= $s['DisplayPhone'] ?>" class="text-decoration-none small text-secondary d-block mt-1">
                            <i class="fa-solid fa-phone fa-xs me-1"></i><?= $s['DisplayPhone'] ?>
                        </a>
                    <?php else: ?>
                         <span class="badge bg-light text-muted border mt-1">Tel Yok</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="fw-bold text-success font-monospace">
                        <?= number_format($s['MonthlyFee'] ?? 0, 0, ',', '.') ?> ₺
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">
                        Kayıt: <?= date('d.m.Y', strtotime($s['CreatedAt'])) ?>
                    </small>
                </td>
                <td class="text-end pe-4">
                    <div class="btn-group shadow-sm bg-white rounded border overflow-hidden">
                        <button class="btn btn-sm btn-white text-info border-end" 
                                onclick="alert('Veli: <?= htmlspecialchars($s['ParentName'] ?? 'İsimsiz') ?>\nTelefon: <?= $s['DisplayPhone'] ?? 'Yok' ?>')">
                            <i class="fa-solid fa-circle-info"></i>
                        </button>
                        
                        <a href="index.php?page=student_edit&id=<?= $s['StudentID'] ?>" class="btn btn-sm btn-white text-secondary border-end">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        
                        <a href="index.php?page=student_delete&id=<?= $s['StudentID'] ?>" class="btn btn-sm btn-white text-danger" 
                           onclick="return confirm('<?= htmlspecialchars($s['FullName']) ?> kişisini arşive göndermek istediğinize emin misiniz?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div></div> 
    
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="fa-solid fa-graduation-cap fa-3x mb-3 text-muted opacity-50"></i>
            <h5>Henüz kayıtlı öğrenci yok.</h5>
            <p>Yukarıdaki "Yeni Öğrenci" butonunu kullanarak kayıt yapabilirsiniz.</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=student_store" method="POST">
                <div class="modal-header border-0 bg-primary text-white">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Yeni Sporcu Kaydı</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Sporcu Bilgileri</h6>
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Sporcu Adı Soyadı</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Örn: Ali Yılmaz" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Doğum Tarihi</label>
                            <input type="date" name="birth_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Grup / Sınıf</label>
                            <select name="group_id" class="form-select" required>
                                <option value="">-- Seçiniz --</option>
                                <?php if(!empty($groups)): foreach($groups as $grp): ?>
                                    <option value="<?= $grp['GroupID'] ?>"><?= htmlspecialchars($grp['GroupName']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Kayıt Tarihi</label>
                            <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Aylık Aidat (₺)</label>
                            <input type="number" name="monthly_fee" class="form-control" value="0">
                        </div>
                    </div>

                    <div class="mt-4 pt-2 bg-light p-3 rounded border border-warning border-opacity-25">
                        <h6 class="text-warning text-dark fw-bold mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-user-shield me-1"></i>Veli Bilgileri (Giriş İçin)
                        </h6>
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Veli Adı Soyadı</label>
                            <input type="text" name="parent_name" class="form-control" placeholder="Örn: Mehmet Yılmaz" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold mb-1">Cep Telefonu (Kullanıcı Adı)</label>
                            <input type="text" name="parent_phone" class="form-control fw-bold" placeholder="5XXXXXXXXX" required>
                        </div>
                        <div class="d-flex align-items-start mt-2">
                            <i class="fa-solid fa-circle-info text-primary mt-1 me-2" style="font-size: 0.8rem;"></i>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem; line-height: 1.3;">
                                Bu numara sistemde kayıtlıysa, sporcu otomatik olarak mevcut velinin hesabına "Kardeş" olarak eklenir.<br>
                                Yeni kayıtlarda şifre: <strong>123456</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary px-4">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .group-package {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        border-width: 0px; 
        border-left: 6px solid;
    }
    .theme-orange { border-left-color: #ff9800 !important; }
    .theme-orange .group-title-bar { background-color: #ff9800; color: #fff; }
    
    .theme-gray { border-left-color: #607d8b !important; }
    .theme-gray .group-title-bar { background-color: #607d8b; color: #fff; }
    
    .group-title-bar { font-size: 1.1rem; font-weight: 700; letter-spacing: 0.5px; }
    .student-row td { border-bottom: 1px solid #f1f5f9; padding: 15px; }
    .student-row:hover { background-color: #fcfdfe; }
    .btn-white { background: #fff !important; border: 1px solid #eee !important; }
    .btn-white:hover { background: #f8f9fa !important; }
    body { background-color: #f4f7f6; }
</style>