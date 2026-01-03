<?php
// YETKİ KONTROLÜ
$userRole = strtolower($_SESSION['role'] ?? '');
$isAdmin = ($userRole !== 'coach' && $userRole !== 'trainer');

// Renk Paleti
$themeColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
?>

<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 px-1">
        <div>
            <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">
                <i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Öğrenci Listeleri
            </h3>
            <p class="text-muted small mb-0">
                <?= $isAdmin ? 'Sporcu kayıtları ve grup bazlı listeler.' : 'Takım ve sporcu listesi.' ?>
            </p>
        </div>
        
        <?php if($isAdmin): ?>
        <div class="d-flex gap-2">
            <a href="index.php?page=students_archived" class="btn btn-light border shadow-sm px-3 text-secondary fw-bold">
                <i class="fa-solid fa-box-archive me-2"></i>Arşiv
            </a>
            <button type="button" class="btn btn-primary shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="fa-solid fa-plus me-2"></i>Yeni Kayıt
            </button>
        </div>
        <?php endif; ?>
    </div>

    <?php if(!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm rounded-3">
            <i class="fa-solid fa-circle-check me-2"></i><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(!empty($students)): ?>
        <div class="accordion" id="studentAccordion">
        <?php 
        $currentGroup = null; 
        $groupIndex = 0;
        $counter = 1;

        foreach($students as $s): 
            if ($currentGroup !== $s['GroupName']): 
                // Önceki grubu kapat
                if ($currentGroup !== null) echo '</tbody></table></div></div></div></div>';
                
                $currentGroup = $s['GroupName'];
                $colorName = $themeColors[$groupIndex % count($themeColors)];
                $collapseId = "collapseGroup" . $groupIndex;
                $isFirst = ($groupIndex === 0) ? 'show' : ''; 
                $groupIndex++;
                $counter = 1;
        ?>
            <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden border-top border-4 border-<?= $colorName ?>">
                
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center cursor-pointer" 
                     data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                    
                    <div class="d-flex align-items-center">
                        <div class="bg-<?= $colorName ?> bg-opacity-10 text-<?= $colorName ?> rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($currentGroup ?: 'GRUPSUZ ÖĞRENCİLER') ?></h6>
                        </div>
                    </div>
                    
                    <i class="fa-solid fa-chevron-down text-muted small"></i>
                </div>
                
                <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $isFirst ?>" data-bs-parent="#studentAccordion">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover">
                                <tbody>
            <?php endif; ?>

            <tr class="student-row soft-border">
                <td class="ps-4 text-muted small fw-bold" style="width: 40px;"><?= $counter++ ?></td>
                
                <td style="width: <?= $isAdmin ? '25%' : '40%' ?>;">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['FullName']) ?></div>
                    <div class="text-muted x-small">
                        <i class="fa-regular fa-calendar me-1"></i><?= !empty($s['BirthDate']) ? date('Y', strtotime($s['BirthDate'])) : '-' ?> Doğumlu
                    </div>
                </td>

                <?php if($isAdmin): ?>
                <td style="width: 25%;">
                    <?php if(!empty($s['ParentName'])): ?>
                        <div class="fw-bold text-dark small mb-1">
                            <?= htmlspecialchars($s['ParentName']) ?>
                        </div>
                        <?php if(!empty($s['DisplayPhone'])): 
                             $cleanPhone = preg_replace('/[^0-9]/', '', $s['DisplayPhone']);
                        ?>
                            <div class="d-flex align-items-center">
                                <span class="text-muted x-small font-monospace me-2"><?= $s['DisplayPhone'] ?></span>
                                <a href="tel:<?= $s['DisplayPhone'] ?>" class="text-secondary me-2 hover-scale"><i class="fa-solid fa-phone"></i></a>
                                <a href="https://wa.me/90<?= ltrim($cleanPhone, '0') ?>" target="_blank" class="text-success hover-scale"><i class="fa-brands fa-whatsapp"></i></a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted x-small">-</span>
                    <?php endif; ?>
                </td>
                
                <td style="width: 15%;">
                    <div class="fw-bold text-dark small"><?= $s['StandardSessions'] ?> Ders</div>
                    <div class="text-muted x-small"><?= number_format($s['PackageFee'] ?? 0, 0) ?> ₺</div>
                </td>
                <?php endif; ?>

                <td class="text-center" style="width: <?= $isAdmin ? '10%' : '15%' ?>;">
                    <?php 
                        $rem = (int)($s['RemainingSessions'] ?? 0); 
                        $badgeClass = $rem <= 0 ? 'bg-danger' : ($rem <= 2 ? 'bg-warning text-dark' : 'bg-success');
                    ?>
                    <span class="badge <?= $badgeClass ?> bg-opacity-75 rounded-1 px-2 fw-normal">
                        <?= $rem ?> Hak
                    </span>
                </td>

                <td style="width: <?= $isAdmin ? '15%' : '40%' ?>;">
                    <?php if(!empty($s['Notes'])): ?>
                        <div class="d-flex align-items-center text-muted small cursor-pointer hover-text-dark"
                             onclick="openNoteModal(<?= $s['StudentID'] ?>, '<?= htmlspecialchars($s['FullName']) ?>', `<?= htmlspecialchars($s['Notes']) ?>`)">
                            <i class="fa-solid fa-note-sticky text-warning me-2"></i>
                            <span class="text-truncate" style="max-width: <?= $isAdmin ? '120px' : '250px' ?>;">
                                <?= htmlspecialchars(mb_substr($s['Notes'], 0, 30)) . (mb_strlen($s['Notes']) > 30 ? '...' : '') ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center text-muted opacity-25 small cursor-pointer hover-text-dark"
                             onclick="openNoteModal(<?= $s['StudentID'] ?>, '<?= htmlspecialchars($s['FullName']) ?>', '')">
                            <i class="fa-regular fa-pen-to-square me-2"></i>
                            <span>Not Ekle</span>
                        </div>
                    <?php endif; ?>
                </td>

                <?php if($isAdmin): ?>
                <td class="text-end pe-4" style="width: 10%;">
                    <div class="d-flex justify-content-end gap-2">
                        <?php if(!empty($s['ParentID'])): ?>
                            <i class="fa-solid fa-key text-muted hover-icon cursor-pointer" title="Şifre" 
                               onclick="openPasswordModal(<?= $s['ParentID'] ?>, '<?= htmlspecialchars($s['ParentName']) ?>', '<?= htmlspecialchars($s['DisplayPhone'] ?? '') ?>')"></i>
                        <?php endif; ?>
                        
                        <i class="fa-solid fa-pen text-muted hover-icon cursor-pointer" title="Düzenle"
                           data-bs-toggle="modal" data-bs-target="#editStudentModal" 
                           data-id="<?= $s['StudentID'] ?>" data-parentid="<?= $s['ParentID'] ?? '' ?>" 
                           data-name="<?= htmlspecialchars($s['FullName']) ?>" data-birth="<?= !empty($s['BirthDate']) ? date('Y-m-d', strtotime($s['BirthDate'])) : '' ?>" 
                           data-group="<?= $s['GroupID'] ?>" data-standard="<?= $s['StandardSessions'] ?>" 
                           data-fee="<?= number_format($s['PackageFee'] ?? 0, 0, '.', '') ?>" 
                           data-remaining="<?= $s['RemainingSessions'] ?>" 
                           data-pname="<?= htmlspecialchars($s['ParentName'] ?? '') ?>" data-pphone="<?= $s['DisplayPhone'] ?? '' ?>"></i>
                        
                        <i class="fa-solid fa-box-archive text-muted hover-icon hover-danger cursor-pointer" title="Arşivle"
                           data-bs-toggle="modal" data-bs-target="#archiveModal" 
                           data-id="<?= $s['StudentID'] ?>" data-name="<?= htmlspecialchars($s['FullName']) ?>" 
                           data-remaining="<?= $s['RemainingSessions'] ?>"></i>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div></div></div></div>
        </div> <?php else: ?>
        <div class="alert alert-light text-center py-5 shadow-sm rounded-4">
            <i class="fa-solid fa-clipboard-list fa-3x text-muted opacity-25 mb-3"></i>
            <h5 class="text-muted">Kayıtlı öğrenci bulunamadı.</h5>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background:#fffdf0;"> 
            <form action="index.php?page=student_update_note" method="POST">
                <input type="hidden" name="student_id" id="noteStudentId">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-dark"><i class="fa-solid fa-thumbtack text-warning me-2"></i><span id="noteStudentName"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <textarea class="form-control border-0 shadow-sm" name="note" id="noteText" style="height: 150px; background: rgba(255,255,255,0.7);" placeholder="Antrenör notu..."></textarea>
                </div>
                <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-warning text-dark fw-bold px-4 rounded-pill shadow-sm">Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<?php if($isAdmin): ?>

<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center">
            <form action="index.php?page=student_update_password" method="POST">
                <input type="hidden" name="parent_id" id="passParentId">
                <div class="modal-header border-0 justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body px-4 pb-4 pt-0">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 text-warning mb-3"><i class="fa-solid fa-lock fa-2x"></i></div>
                    <h6 class="fw-bold mb-1" id="passParentName">Veli Adı</h6>
                    <div class="badge bg-light text-dark border mb-3 font-monospace" id="passParentPhone">...</div>
                    <div class="form-floating mb-3">
                        <input type="text" name="new_password" class="form-control text-center fw-bold fs-5" placeholder="Yeni Şifre" required>
                        <label>Yeni Şifre Belirle</label>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 fw-bold rounded-pill">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="index.php?page=student_store" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold">Yeni Kayıt</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3"><label class="small fw-bold text-muted">AD SOYAD</label><input name="full_name" class="form-control shadow-sm" required></div>
                    <div class="row mb-3">
                            <div class="col-6"><label class="small fw-bold text-muted">DOĞUM T.</label><input type="date" name="birth_date" class="form-control shadow-sm" required></div>
                            <div class="col-6"><label class="small fw-bold text-muted">GRUP</label><select name="group_id" class="form-select shadow-sm" required><option value="">Seçiniz</option><?php if(!empty($groups)): foreach($groups as $grp): ?><option value="<?=$grp['GroupID']?>"><?=$grp['GroupName']?></option><?php endforeach; endif; ?></select></div>
                    </div>
                    <div class="row mb-3 bg-light p-3 rounded-3 mx-0 border">
                        <div class="col-12 mb-3">
                            <label class="small fw-bold text-muted">HIZLI PAKET ŞABLONU</label>
                            <select class="form-select form-select-sm shadow-sm" onchange="applyTemplate(this, 'addStandard', 'addFee')">
                                <option value="">-- Şablon Seçiniz --</option>
                                <option value="4" data-fee="1000">Haftada 1 (4 Ders)</option>
                                <option value="8" data-fee="1500">Haftada 2 (8 Ders)</option>
                                <option value="12" data-fee="2000">Haftada 3 (12 Ders)</option>
                                <option value="16" data-fee="2500">Haftada 4 (16 Ders)</option>
                                <option value="20" data-fee="3000">Performans (20 Ders)</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="small fw-bold text-muted">AYLIK DERS</label><input type="number" name="standard_sessions" id="addStandard" class="form-control fw-bold" placeholder="8" required></div>
                        <div class="col-6"><label class="small fw-bold text-muted">ÜCRET (TL)</label><input type="number" name="package_fee" id="addFee" class="form-control fw-bold" placeholder="1500" required></div>
                    </div>
                    <div class="mb-3"><label class="small fw-bold text-muted">VELİ ADI</label><input name="parent_name" class="form-control shadow-sm" required></div>
                    <div class="mb-3"><label class="small fw-bold text-muted">VELİ TEL</label><input name="parent_phone" class="form-control shadow-sm phone_mask" required></div>
                    <div class="form-floating"><textarea class="form-control shadow-sm border-warning" placeholder="Not" name="note" id="addNoteText" style="height: 100px"></textarea><label for="addNoteText" class="text-muted"><i class="fa-solid fa-thumbtack text-warning me-2"></i>İlk Not</label></div>
                </div>
                <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="index.php?page=student_update" method="POST">
                <input type="hidden" name="student_id" id="editId"><input type="hidden" name="parent_id" id="editParentId">
                <div class="modal-header border-0 pb-0"><h5 class="fw-bold">Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="row mb-3"><div class="col-6"><label class="small fw-bold text-muted">AD SOYAD</label><input id="editName" name="full_name" class="form-control"></div><div class="col-6"><label class="small fw-bold text-muted">DOĞUM T.</label><input type="date" id="editBirth" name="birth_date" class="form-control"></div></div>
                    <div class="mb-3"><label class="small fw-bold text-muted">GRUP</label><select id="editGroup" name="group_id" class="form-select"><?php if(!empty($groups)): foreach($groups as $grp): ?><option value="<?=$grp['GroupID']?>"><?=$grp['GroupName']?></option><?php endforeach; endif; ?></select></div>
                    <div class="row mb-3 bg-light p-3 rounded-3 mx-0"><div class="col-4"><label class="small fw-bold text-muted">DERS</label><input id="editStandard" name="standard_sessions" class="form-control"></div><div class="col-4"><label class="small fw-bold text-muted">ÜCRET</label><input id="editFee" name="package_fee" class="form-control"></div><div class="col-4"><label class="small fw-bold text-danger">KALAN</label><input id="editRemaining" name="remaining_sessions" class="form-control border-danger text-danger fw-bold"></div></div>
                    <div class="row"><div class="col-6"><label class="small fw-bold text-muted">VELİ</label><input id="editParentName" name="parent_name" class="form-control"></div><div class="col-6"><label class="small fw-bold text-muted">TEL</label><input id="editParentPhone" name="parent_phone" class="form-control phone_mask"></div></div>
                </div>
                <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-dark w-100 rounded-pill fw-bold">Güncelle</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="index.php?page=student_archive_store" method="POST">
                <input type="hidden" name="student_id" id="archStudentId">
                <div class="modal-header border-0 pb-0"><h5 class="fw-bold">İlişik Kes / Arşivle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <h5 class="text-center fw-bold mb-3" id="archName"></h5>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="archive_type" value="freeze" id="optFreeze" checked onchange="toggleRefund(false)">
                            <label class="btn btn-outline-primary w-100 h-100 p-3 d-flex flex-column align-items-center justify-content-center border-2" for="optFreeze">
                                <i class="fa-regular fa-snowflake fa-2x mb-2"></i><span class="fw-bold">Dondur</span><span class="small opacity-75 mt-1" style="font-size:0.7rem">Haklar Saklı Kalır</span>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="archive_type" value="refund" id="optRefund" onchange="toggleRefund(true)">
                            <label class="btn btn-outline-danger w-100 h-100 p-3 d-flex flex-column align-items-center justify-content-center border-2" for="optRefund">
                                <i class="fa-solid fa-user-xmark fa-2x mb-2"></i><span class="fw-bold">İlişik Kes</span><span class="small opacity-75 mt-1" style="font-size:0.7rem">Haklar Sıfırlanır</span>
                            </label>
                        </div>
                    </div>
                    <div id="refundSection" class="bg-danger bg-opacity-10 p-3 rounded border border-danger border-opacity-25 mb-3" style="display:none;">
                        <label class="small fw-bold mb-1 text-danger">İade Tutarı (TL)</label><input type="number" name="refund_amount" class="form-control fw-bold text-danger border-danger" placeholder="0"><div class="form-text x-small text-danger">Bu tutar kasadan "Gider" olarak düşülecektir.</div>
                    </div>
                    <div class="mb-2"><label class="small fw-bold text-muted">Sebep</label><select name="reason" class="form-select shadow-sm"><option>Sezon Arası</option><option>Sağlık</option><option>Okul</option><option>Diğer</option></select></div>
                </div>
                <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-dark w-100 fw-bold">Onayla</button></div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    function openNoteModal(id, name, note) { document.getElementById('noteStudentId').value = id; document.getElementById('noteStudentName').textContent = name; document.getElementById('noteText').value = note; var myModal = new bootstrap.Modal(document.getElementById('noteModal')); myModal.show(); }
    function openPasswordModal(id, name, phone) { document.getElementById('passParentId').value = id; document.getElementById('passParentName').textContent = name; document.getElementById('passParentPhone').textContent = phone; var myModal = new bootstrap.Modal(document.getElementById('passwordModal')); myModal.show(); }
    function toggleRefund(show) { document.getElementById('refundSection').style.display = show ? 'block' : 'none'; }
    function applyTemplate(selectElement, targetSessionsId, targetFeeId) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const sessions = selectElement.value; const fee = selectedOption.getAttribute('data-fee');
        if(sessions) { document.getElementById(targetSessionsId).value = sessions; if(targetFeeId && fee) document.getElementById(targetFeeId).value = fee; }
    }

    <?php if($isAdmin): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = document.getElementById('editStudentModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('editId').value = btn.getAttribute('data-id');
            document.getElementById('editParentId').value = btn.getAttribute('data-parentid');
            document.getElementById('editName').value = btn.getAttribute('data-name');
            document.getElementById('editBirth').value = btn.getAttribute('data-birth');
            document.getElementById('editGroup').value = btn.getAttribute('data-group');
            document.getElementById('editStandard').value = btn.getAttribute('data-standard');
            document.getElementById('editFee').value = btn.getAttribute('data-fee');
            document.getElementById('editRemaining').value = btn.getAttribute('data-remaining');
            document.getElementById('editParentName').value = btn.getAttribute('data-pname');
            document.getElementById('editParentPhone').value = btn.getAttribute('data-pphone');
        });
        const archiveModal = document.getElementById('archiveModal');
        archiveModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('archStudentId').value = btn.getAttribute('data-id');
            document.getElementById('archName').textContent = btn.getAttribute('data-name');
            document.getElementById('optFreeze').checked = true; toggleRefund(false); 
        });
        if (typeof $ !== 'undefined' && $.fn.mask) { $('.phone_mask').mask('(000) 000 00 00'); }
    });
    <?php endif; ?>
</script>

<style>
    .cursor-pointer { cursor: pointer; }
    .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.75rem; border-radius: 0.2rem; }
    .soft-border td { border-bottom: 1px solid rgba(0,0,0,0.05) !important; }
    .x-small { font-size: 0.7rem; }
    .hover-icon:hover { color: #333 !important; transform: scale(1.1); transition: transform 0.2s; }
    .hover-danger:hover { color: #dc3545 !important; }
    .hover-text-dark:hover { color: #000 !important; }
    .hover-scale:hover { transform: scale(1.1); transition: transform 0.1s; }
</style>