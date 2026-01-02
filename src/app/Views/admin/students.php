<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-users text-warning me-2"></i>Öğrenci Yönetimi</h3>
            <p class="text-muted small mb-0">Öğrencilerin paketleri ve kalan ders hakları.</p>
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
                                <th class="border-0">Paket Detayı</th>
                                <th class="text-center border-0">Kalan Hak</th>
                                <th class="border-0">Veli İletişim</th>
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
                                <span class="text-muted">
                                    <i class="fa-regular fa-calendar me-1"></i><?= date('d.m.Y', strtotime($s['BirthDate'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
                
                <td>
                    <div class="fw-bold text-dark small">
                        <?php 
                            $std = $s['StandardSessions'];
                            if($std == 8) echo 'Standart (Haftada 2)';
                            elseif($std == 12) echo 'Standart (Haftada 3)';
                            elseif($std == 4) echo 'Başlangıç (Haftada 1)';
                            elseif($std >= 20) echo '<span class="text-danger"><i class="fa-solid fa-medal me-1"></i>Performans (' . $std . ')</span>';
                            else echo $std . ' Derslik Özel Paket';
                        ?>
                    </div>
                    <div class="text-muted small">
                        <?= number_format($s['PackageFee'] ?? 0, 0, '', '') ?> ₺ / Dönem
                    </div>
                </td>

                <td class="text-center">
                    <?php 
                        $rem = (int)($s['RemainingSessions'] ?? 0);
                        $badgeColor = 'bg-success';
                        if($rem <= 0) $badgeColor = 'bg-danger';
                        elseif($rem <= 2) $badgeColor = 'bg-warning text-dark';
                    ?>
                    <span class="badge <?= $badgeColor ?> fs-6 px-3 py-2 rounded-pill">
                        <?= $rem ?> Ders
                    </span>
                    <?php if($rem <= 0): ?>
                        <div class="text-danger x-small fw-bold mt-1">ÖDEME BEKLENİYOR</div>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if(!empty($s['ParentName'])): ?>
                        <div class="text-dark small fw-bold"><?= htmlspecialchars($s['ParentName']) ?></div>
                    <?php else: ?>
                        <span class="text-muted small">Veli Yok</span>
                    <?php endif; ?>

                    <?php if(!empty($s['DisplayPhone'])): ?>
                        <a href="tel:<?= $s['DisplayPhone'] ?>" class="text-decoration-none small text-secondary d-block mt-1">
                            <i class="fa-solid fa-phone fa-xs me-1"></i><?= $s['DisplayPhone'] ?>
                        </a>
                    <?php endif; ?>
                </td>

                <td class="text-end pe-4">
                    <div class="btn-group shadow-sm bg-white rounded border overflow-hidden">
                        <button class="btn btn-sm btn-white text-info border-end" 
                                data-bs-toggle="modal" 
                                data-bs-target="#infoStudentModal"
                                data-name="<?= htmlspecialchars($s['FullName']) ?>"
                                data-parent="<?= htmlspecialchars($s['ParentName'] ?? 'Tanımsız') ?>"
                                data-phone="<?= htmlspecialchars($s['DisplayPhone'] ?? '-') ?>">
                            <i class="fa-solid fa-circle-info"></i>
                        </button>
                        
                        <button class="btn btn-sm btn-white text-secondary border-end"
                                data-bs-toggle="modal" 
                                data-bs-target="#editStudentModal"
                                data-id="<?= $s['StudentID'] ?>"
                                data-name="<?= htmlspecialchars($s['FullName']) ?>"
                                data-birth="<?= date('Y-m-d', strtotime($s['BirthDate'])) ?>"
                                data-group="<?= $s['GroupID'] ?>"
                                data-standard="<?= $s['StandardSessions'] ?>"
                                data-fee="<?= number_format($s['PackageFee'] ?? 0, 0, '.', '') ?>"
                                data-remaining="<?= $s['RemainingSessions'] ?>"
                                data-pname="<?= htmlspecialchars($s['ParentName'] ?? '') ?>"
                                data-pphone="<?= $s['DisplayPhone'] ?? '' ?>">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        
                        <button class="btn btn-sm btn-white text-danger" 
                           data-bs-toggle="modal" 
                           data-bs-target="#archiveModal"
                           data-id="<?= $s['StudentID'] ?>"
                           data-name="<?= htmlspecialchars($s['FullName']) ?>"
                           data-remaining="<?= $s['RemainingSessions'] ?>"
                           title="İlişik Kes / Arşivle">
                            <i class="fa-solid fa-box-archive"></i>
                        </button>
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

<div class="modal fade" id="infoStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4 text-center">
            <div class="modal-body p-4">
                <div class="mb-3">
                    <i class="fa-solid fa-circle-info fa-3x text-info opacity-50"></i>
                </div>
                <h5 class="fw-bold mb-1" id="infoName">Öğrenci Adı</h5>
                <p class="text-muted small mb-4">Hızlı İletişim Bilgileri</p>
                <div class="bg-light p-3 rounded-3 mb-3 border">
                    <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.65rem;">VELİ ADI SOYADI</small>
                    <div class="fw-bold text-dark" id="infoParent">-</div>
                </div>
                <div class="bg-light p-3 rounded-3 mb-3 border">
                    <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.65rem;">TELEFON NUMARASI</small>
                    <div class="fw-bold text-dark" id="infoPhone">-</div>
                </div>
                <button type="button" class="btn btn-dark w-100 rounded-pill" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=student_update" method="POST">
                <input type="hidden" name="student_id" id="editId">
                <input type="hidden" name="parent_id" id="editParentId" value="">
                
                <div class="modal-header bg-secondary text-white">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2"></i>Öğrenci Düzenle</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="small fw-bold mb-1">Adı Soyadı</label>
                            <input type="text" name="full_name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold mb-1">Doğum Tarihi</label>
                            <input type="date" name="birth_date" id="editBirth" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold mb-1">Grup / Sınıf</label>
                        <select name="group_id" id="editGroup" class="form-select">
                            <option value="">-- Seçiniz --</option>
                            <?php if(!empty($groups)): foreach($groups as $grp): ?>
                                <option value="<?= $grp['GroupID'] ?>"><?= htmlspecialchars($grp['GroupName']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 small">Paket & Performans Ayarları</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold mb-1 text-muted">Hızlı Paket Şablonu</label>
                                <select id="editTemplate" class="form-select form-select-sm text-secondary" onchange="applyTemplate(this, 'editStandard')">
                                    <option value="">-- Seçiniz --</option>
                                    <option value="4">Haftada 1 (4 Ders)</option>
                                    <option value="8">Haftada 2 (8 Ders)</option>
                                    <option value="12">Haftada 3 (12 Ders)</option>
                                    <option value="16">Haftada 4 (16 Ders)</option>
                                    <option value="20">Performans (20 Ders)</option>
                                    <option value="24">Yarışmacı (24+ Ders)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold mb-1">Aylık Toplam Ders</label>
                                <input type="number" name="standard_sessions" id="editStandard" class="form-control fw-bold text-dark" required>
                                <div class="form-text x-small">Şablondan seçin veya elle yazın.</div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small fw-bold mb-1">Paket Ücreti</label>
                                <input type="number" name="package_fee" id="editFee" class="form-control" required>
                            </div>
                            
                            <div class="col-12 mt-2 border-top pt-2">
                                <label class="small fw-bold mb-1 text-danger">Kalan Hak (Manuel Müdahale)</label>
                                <input type="number" name="remaining_sessions" id="editRemaining" class="form-control border-danger text-danger fw-bold w-50">
                                <div class="form-text x-small text-danger">Dikkat: Burayı sadece hatayı düzeltmek için kullanın.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="small fw-bold mb-1">Veli Adı</label>
                            <input type="text" name="parent_name" id="editParentName" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold mb-1">Veli Telefon</label>
                            <input type="text" name="parent_phone" id="editParentPhone" class="form-control phone_mask">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-secondary px-4">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
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

                    <div class="row mb-3 bg-light p-2 rounded mx-1 border">
                        <div class="col-12 mb-2">
                            <label class="small fw-bold mb-1 text-muted">Hızlı Paket Şablonu</label>
                            <select class="form-select form-select-sm" onchange="applyTemplate(this, 'addStandard')">
                                <option value="">-- Şablon Seçiniz --</option>
                                <option value="4">Haftada 1 (4 Ders)</option>
                                <option value="8">Haftada 2 (8 Ders)</option>
                                <option value="12">Haftada 3 (12 Ders)</option>
                                <option value="16">Haftada 4 (16 Ders)</option>
                                <option value="20">Performans (20 Ders)</option>
                                <option value="30">Profesyonel / Sınırsız (30 Ders)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Aylık Ders Hakkı</label>
                            <input type="number" name="standard_sessions" id="addStandard" class="form-control fw-bold" placeholder="Örn: 8" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Paket Ücreti (₺)</label>
                            <input type="number" name="package_fee" class="form-control" placeholder="1500" required>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 bg-light p-3 rounded border border-warning border-opacity-25">
                        <h6 class="text-warning text-dark fw-bold mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-user-shield me-1"></i>Veli Bilgileri
                        </h6>
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Veli Adı Soyadı</label>
                            <input type="text" name="parent_name" class="form-control" placeholder="Örn: Mehmet Yılmaz" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold mb-1">Cep Telefonu</label>
                            <input type="text" name="parent_phone" class="form-control phone_mask" placeholder="5XXXXXXXXX" required>
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

<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=student_archive_store" method="POST">
                <input type="hidden" name="student_id" id="archStudentId">
                
                <div class="modal-header bg-secondary text-white">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-user-xmark me-2"></i>Öğrenci İlişiğini Kes</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold text-dark" id="archName">Öğrenci Adı</h5>
                        <div class="badge bg-warning text-dark fs-6 mt-2">
                            Mevcut Hak: <span id="archRemaining">0</span> Ders
                        </div>
                    </div>

                    <p class="small text-muted mb-3 fw-bold">Lütfen yapılacak işlemi seçiniz:</p>

                    <div class="form-check card p-3 mb-3 border hover-shadow" onclick="document.getElementById('optFreeze').checked=true; toggleRefund(false);">
                        <div class="d-flex align-items-center">
                            <input class="form-check-input mt-0 me-3" type="radio" name="archive_type" id="optFreeze" value="freeze" checked style="transform: scale(1.3);">
                            <div>
                                <label class="form-check-label fw-bold text-primary mb-0" for="optFreeze">
                                    Hesabı Dondur (Hakları Kalsın)
                                </label>
                                <div class="x-small text-muted">Öğrenci arşive gider ama ders hakkı silinmez. Geri döndüğünde kaldığı yerden devam eder.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-check card p-3 mb-3 border hover-shadow" onclick="document.getElementById('optRefund').checked=true; toggleRefund(true);">
                        <div class="d-flex align-items-center">
                            <input class="form-check-input mt-0 me-3" type="radio" name="archive_type" id="optRefund" value="refund" style="transform: scale(1.3);">
                            <div>
                                <label class="form-check-label fw-bold text-danger mb-0" for="optRefund">
                                    İade Yap / Hakkı Sıfırla
                                </label>
                                <div class="x-small text-muted">Öğrencinin kalan ders hakkı silinir. İstenirse para iadesi yapılır.</div>
                            </div>
                        </div>
                    </div>

                    <div id="refundInputSection" class="bg-light p-3 rounded border border-danger mb-3" style="display:none;">
                        <label class="small fw-bold mb-1 text-danger">İade Edilecek Tutar (TL)</label>
                        <input type="number" name="refund_amount" class="form-control fw-bold text-danger" placeholder="0" value="0">
                        <div class="form-text x-small">Para iadesi yapılmayacaksa 0 bırakın. Kasa bakiyesinden düşülecektir.</div>
                    </div>

                    <div class="mb-2">
                        <label class="small fw-bold mb-1">Ayrılma / Dondurma Sebebi</label>
                        <select name="reason" class="form-select">
                            <option value="Sezon Arası">Sezon Arası / Tatil</option>
                            <option value="Sağlık">Sağlık Sorunu</option>
                            <option value="Okul">Okul / Sınav Dönemi</option>
                            <option value="Maddi">Maddi Sebepler</option>
                            <option value="Taşınma">Taşınma / Tayin</option>
                            <option value="Memnuniyetsizlik">Memnuniyetsizlik</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                    </div>

                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-dark w-100 py-2">İşlemi Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Şablon Seçilince Input'u Dolduran Fonksiyon
    function applyTemplate(selectElement, targetInputId) {
        const val = selectElement.value;
        if(val) {
            document.getElementById(targetInputId).value = val;
        }
    }

    // İade Alanını Aç/Kapa
    function toggleRefund(show) {
        const section = document.getElementById('refundInputSection');
        section.style.display = show ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. INFO MODAL
        const infoModal = document.getElementById('infoStudentModal');
        infoModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('infoName').textContent = btn.getAttribute('data-name');
            document.getElementById('infoParent').textContent = btn.getAttribute('data-parent');
            document.getElementById('infoPhone').textContent = btn.getAttribute('data-phone');
        });

        // 2. EDIT MODAL
        const editModal = document.getElementById('editStudentModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            
            document.getElementById('editId').value = btn.getAttribute('data-id');
            document.getElementById('editName').value = btn.getAttribute('data-name');
            document.getElementById('editBirth').value = btn.getAttribute('data-birth');
            
            document.getElementById('editGroup').value = btn.getAttribute('data-group');
            document.getElementById('editStandard').value = btn.getAttribute('data-standard');
            // Template select'i sıfırla, çünkü özel bir rakam olabilir
            document.getElementById('editTemplate').value = ""; 
            
            document.getElementById('editFee').value = btn.getAttribute('data-fee');
            document.getElementById('editRemaining').value = btn.getAttribute('data-remaining');
            
            document.getElementById('editParentName').value = btn.getAttribute('data-pname');
            document.getElementById('editParentPhone').value = btn.getAttribute('data-pphone');
        });

        // 3. ARCHIVE MODAL (YENİ)
        const archiveModal = document.getElementById('archiveModal');
        archiveModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('archStudentId').value = btn.getAttribute('data-id');
            document.getElementById('archName').textContent = btn.getAttribute('data-name');
            document.getElementById('archRemaining').textContent = btn.getAttribute('data-remaining');
            
            // Sıfırla: Varsayılan "Dondur" seçili gelsin
            document.getElementById('optFreeze').checked = true;
            toggleRefund(false);
        });

        $('.phone_mask').mask('(000) 000 00 00');
    });
</script>

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
    .x-small { font-size: 0.65rem; }
    .hover-shadow:hover { background-color: #f8f9fa; cursor: pointer; border-color: #ced4da !important; }
    body { background-color: #f4f7f6; }
</style>