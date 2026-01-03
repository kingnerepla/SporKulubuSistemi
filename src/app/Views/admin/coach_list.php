<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-user-tie text-info me-2"></i>Antrenör Yönetimi</h3>
            <p class="text-muted small mb-0">Teknik ekip listesi, atanan gruplar ve yetkilendirmeler.</p>
        </div>
        
        <?php if($currentStatus == 1): ?>
        <button type="button" class="btn btn-info text-white btn-sm shadow-sm px-3 fw-bold" onclick="openModal()">
            <i class="fa-solid fa-user-plus me-1"></i>Yeni Antrenör Ekle
        </button>
        <?php endif; ?>
    </div>

    <?php if(!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3 border-0 shadow-sm rounded-3">
            <i class="fa-solid fa-circle-check me-2"></i><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3 border-0 shadow-sm rounded-3">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link <?= ($currentStatus == 1) ? 'active bg-info' : 'text-secondary bg-white border' ?>" href="index.php?page=coach_list&show=active">
                    <i class="fa-solid fa-user-check me-2"></i>Aktif Kadro
                </a>
            </li>
            <li class="nav-item ms-2">
                <a class="nav-link <?= ($currentStatus == 0) ? 'active bg-secondary' : 'text-secondary bg-white border' ?>" href="index.php?page=coach_list&show=passive">
                    <i class="fa-solid fa-box-archive me-2"></i>Arşiv (Pasifler)
                </a>
            </li>
        </ul>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="bg-light text-uppercase small text-muted">
                    <tr>
                        <th class="ps-4 py-3 border-0">Adı Soyadı</th>
                        <th class="border-0">İletişim</th>
                        <th class="border-0">Sorumlu Olduğu Gruplar</th>
                        <th class="text-center border-0">Durum</th>
                        <th class="text-end pe-4 border-0">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($coaches)): foreach($coaches as $c): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-<?= ($currentStatus==1)?'info':'secondary' ?> bg-opacity-10 text-<?= ($currentStatus==1)?'info':'secondary' ?> rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold"><?= strtoupper(mb_substr($c['FullName'], 0, 1)) ?></span>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($c['FullName']) ?></div>
                                    <div class="x-small text-muted">Antrenör</div>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <div class="small text-dark"><i class="fa-solid fa-phone fa-xs me-1 text-muted"></i><?= htmlspecialchars($c['Phone'] ?? '-') ?></div>
                            <div class="small text-muted"><i class="fa-solid fa-envelope fa-xs me-1"></i><?= htmlspecialchars($c['Email']) ?></div>
                        </td>

                        <td>
                            <?php if($c['GroupCount'] > 0): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-1 rounded-pill px-2">
                                    <?= $c['GroupCount'] ?> Grup
                                </span>
                                <div class="small text-muted text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($c['GroupNames']) ?>">
                                    <?= htmlspecialchars($c['GroupNames']) ?>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border fw-normal">Grup Atanmamış</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <?php if($currentStatus == 1): ?>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 border border-success border-opacity-25">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 border border-secondary border-opacity-25">Pasif</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm bg-white rounded border overflow-hidden">
                                
                                <?php if($currentStatus == 1): ?>
                                    <button class="btn btn-sm btn-white text-info border-end"
                                            onclick='editCoach(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                            title="Düzenle">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    
                                    <button class="btn btn-sm btn-white text-warning"
                                            onclick="confirmDelete(<?= $c['UserID'] ?>, '<?= htmlspecialchars($c['FullName']) ?>', <?= $c['GroupCount'] ?>)"
                                            title="Pasife Al (Arşivle)">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                
                                <?php else: ?>
                                    <a href="index.php?page=coach_restore&id=<?= $c['UserID'] ?>" 
                                       class="btn btn-sm btn-white text-success border-end"
                                       title="Geri Yükle (Aktif Et)">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </a>
                                    
                                    <button class="btn btn-sm btn-white text-danger"
                                            onclick="confirmHardDelete(<?= $c['UserID'] ?>, '<?= htmlspecialchars($c['FullName']) ?>')"
                                            title="Tamamen Sil (Kalıcı)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                                
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fa-solid fa-inbox fa-3x opacity-25 mb-3 d-block"></i>Bu listede kayıtlı veri yok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="coachModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=coach_store" method="POST">
                <input type="hidden" name="coach_id" id="modalId">
                
                <div class="modal-header bg-info text-white border-0">
                    <h6 class="modal-title fw-bold" id="modalTitle">Yeni Antrenör</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-1 text-muted">ADI SOYADI</label>
                        <input type="text" name="full_name" id="modalName" class="form-control shadow-sm" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small fw-bold mb-1 text-muted">TELEFON</label>
                            <input type="text" name="phone" id="modalPhone" class="form-control shadow-sm phone_mask" placeholder="(5XX) ...">
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1 text-muted">E-POSTA</label>
                            <input type="email" name="email" id="modalEmail" class="form-control shadow-sm" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small fw-bold mb-1 text-muted">ŞİFRE</label>
                        <input type="password" name="password" id="modalPassword" class="form-control shadow-sm" placeholder="***">
                        <div class="form-text x-small text-muted" id="passHelp">Yeni kayıtta zorunlu (Varsayılan: 123456).</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="small fw-bold mb-1 d-flex justify-content-between text-muted">
                            <span>ATANACAĞI GRUPLAR</span>
                            <span class="text-info fw-normal x-small">Tıklayarak seçiniz</span>
                        </label>
                        
                        <div class="bg-white border rounded shadow-sm p-2" style="height: 150px; overflow-y: auto;">
                            <?php if(!empty($allGroups)): foreach($allGroups as $g): ?>
                                <div class="form-check form-check-sm mb-1 px-3 py-1 hover-bg rounded">
                                    <input class="form-check-input group-checkbox" type="checkbox" name="group_ids[]" value="<?= $g['GroupID'] ?>" id="grp_<?= $g['GroupID'] ?>" style="cursor: pointer;">
                                    <label class="form-check-label w-100 cursor-pointer" for="grp_<?= $g['GroupID'] ?>">
                                        <?= htmlspecialchars($g['GroupName']) ?>
                                    </label>
                                </div>
                            <?php endforeach; else: ?>
                                <div class="text-muted small text-center pt-4">Tanımlı grup yok.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="bg-warning bg-opacity-10 p-3 rounded border border-warning border-opacity-25">
                        <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-shield-halved me-1 text-warning"></i>Yetkilendirme</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="can_view_reports" id="modalReports" value="1">
                            <label class="form-check-label small" for="modalReports">
                                <strong>Raporları Görebilsin</strong>
                                <div class="text-muted x-small">Tüm kulübün finansal raporlarını görebilir.</div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-info text-white px-4 fw-bold rounded-pill">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var coachModalObj = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('coachModal');
        if (modalEl) coachModalObj = new bootstrap.Modal(modalEl);
        if(typeof $ !== 'undefined' && $.fn.mask) $('.phone_mask').mask('(000) 000 00 00');
    });

    function openModal() {
        if (!coachModalObj) return;
        document.getElementById('modalId').value = ''; 
        document.getElementById('modalName').value = ''; 
        document.getElementById('modalPhone').value = ''; 
        document.getElementById('modalEmail').value = ''; 
        document.getElementById('modalPassword').value = ''; 
        document.getElementById('modalReports').checked = false;
        
        // Checkboxları Temizle
        document.querySelectorAll('.group-checkbox').forEach(chk => chk.checked = false);
        
        document.getElementById('modalTitle').innerText = 'Yeni Antrenör Ekle'; 
        document.getElementById('passHelp').innerText = 'Yeni kayıt için zorunludur.';
        coachModalObj.show();
    }

    function editCoach(data) {
        if (!coachModalObj) return;
        document.getElementById('modalId').value = data.UserID; 
        document.getElementById('modalName').value = data.FullName; 
        document.getElementById('modalPhone').value = data.Phone; 
        document.getElementById('modalEmail').value = data.Email; 
        document.getElementById('modalPassword').value = ''; 
        document.getElementById('modalReports').checked = (data.CanViewReports == 1);
        
        // 🔥 GRUPLARI İŞARETLE (JS GÜNCELLENDİ) 🔥
        // Önce hepsini temizle
        document.querySelectorAll('.group-checkbox').forEach(chk => chk.checked = false);
        
        // Veritabanından gelen virgüllü ID'leri kontrol et ve işaretle
        if (data.GroupIDs) { 
            const groupIds = String(data.GroupIDs).split(','); 
            groupIds.forEach(id => {
                const chk = document.getElementById('grp_' + id);
                if(chk) chk.checked = true;
            });
        }
        
        document.getElementById('modalTitle').innerText = 'Antrenör Düzenle'; 
        document.getElementById('passHelp').innerText = 'Değişmeyecekse boş bırakın.';
        coachModalObj.show();
    }

    function confirmDelete(id, name, groupCount) {
        if (groupCount > 0) {
            Swal.fire({ icon: 'warning', title: 'Pasife Alınamaz!', html: `<strong>${name}</strong> adlı antrenörün <strong>${groupCount} grubu</strong> var.<br>Önce düzenle diyerek grupları boşa çıkarın.`, confirmButtonText: 'Tamam' });
            return;
        }
        Swal.fire({
            title: 'Pasife Alınsın mı?', text: `${name} arşive gönderilecek.`, icon: 'question', showCancelButton: true, confirmButtonColor: '#f39c12', cancelButtonColor: '#3085d6', confirmButtonText: 'Evet, Arşivle', cancelButtonText: 'İptal'
        }).then((result) => { if (result.isConfirmed) window.location.href = `index.php?page=coach_delete&id=${id}`; });
    }

    function confirmHardDelete(id, name) {
        Swal.fire({
            title: 'DİKKAT: Kalıcı Silme!', html: `<strong>${name}</strong> tamamen silinecek.`, icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Evet, Sil', cancelButtonText: 'Vazgeç'
        }).then((result) => { if (result.isConfirmed) window.location.href = `index.php?page=coach_hard_delete&id=${id}`; });
    }

    // SWEET ALERT TETİKLEYİCİ
    <?php if(isset($_SESSION['sweet_alert'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= $_SESSION['sweet_alert']['icon'] ?>',
                title: '<?= $_SESSION['sweet_alert']['title'] ?>',
                html: '<?= $_SESSION['sweet_alert']['html'] ?? $_SESSION['sweet_alert']['text'] ?>',
                confirmButtonText: 'Tamam',
                confirmButtonColor: '#3085d6'
            });
        });
        <?php unset($_SESSION['sweet_alert']); ?>
    <?php endif; ?>

</script>

<style>
    .btn-white { background: #fff !important; border: 1px solid #eee !important; }
    .btn-white:hover { background: #f8f9fa !important; }
    .x-small { font-size: 0.7rem; }
    body { background-color: #f4f7f6; }
    .hover-bg:hover { background-color: #f8f9fa; }
    .cursor-pointer { cursor: pointer; }
</style>