<?php
// YETKİ KONTROLÜ
$userRole = strtolower($_SESSION['role'] ?? '');
$isAdmin = ($userRole !== 'coach' && $userRole !== 'trainer');
?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">
                <i class="fa-solid fa-layer-group text-primary me-2"></i>Grup Yönetimi
            </h3>
            <p class="text-muted small mb-0">
                <?= $isAdmin ? 'Antrenman gruplarını ve haftalık ders programlarını yönetin.' : 'Sorumlu olduğunuz gruplar ve programlar.' ?>
            </p>
        </div>
        
        <?php if($isAdmin): ?>
            <button type="button" class="btn btn-primary btn-sm shadow-sm px-3 fw-bold rounded-pill" onclick="openModal()">
                <i class="fa-solid fa-plus me-1"></i>Yeni Grup Oluştur
            </button>
        <?php endif; ?>
    </div>

    <?php if(!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm rounded-3">
            <i class="fa-solid fa-circle-check me-2"></i><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm rounded-3">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if(!empty($groups)): foreach($groups as $g): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all rounded-4 overflow-hidden">
                <div class="h-1 w-100 bg-primary position-absolute top-0 start-0" style="height: 4px;"></div>
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($g['GroupName']) ?></h5>
                            <div class="small text-muted">
                                <i class="fa-solid fa-user-tie me-1 text-primary"></i><?= htmlspecialchars($g['CoachName'] ?? 'Antrenör Yok') ?>
                            </div>
                        </div>
                        
                        <?php if($isAdmin): ?>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-circle shadow-sm border" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-vertical text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2">
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 small" href="#" onclick='editGroup(<?= json_encode($g, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="fa-solid fa-pen me-2 text-primary"></i>Düzenle
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 small text-danger" href="index.php?page=group_delete&id=<?= $g['GroupID'] ?>" onclick="return confirm('Bu grubu silmek istediğine emin misin? İçindeki öğrenciler silinmez, grupsuz kalır.')">
                                        <i class="fa-solid fa-trash me-2"></i>Sil
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-light bg-opacity-50 rounded-3 p-3 mb-3 border border-light">
                        <h6 class="x-small text-uppercase fw-bold text-muted mb-2 letter-spacing-1">Ders Programı</h6>
                        <?php if(!empty($g['Schedule'])): ?>
                            <ul class="list-unstyled mb-0 small">
                                <?php 
                                $daysMap = [1=>'Pzt', 2=>'Sal', 3=>'Çrş', 4=>'Prş', 5=>'Cum', 6=>'Cmt', 7=>'Paz'];
                                foreach($g['Schedule'] as $sch): 
                                    $startTime = isset($sch['StartTime']) ? substr($sch['StartTime'], 0, 5) : '??:??';
                                    $endTime = isset($sch['EndTime']) ? substr($sch['EndTime'], 0, 5) : '??:??';
                                ?>
                                <li class="d-flex justify-content-between border-bottom border-white pb-1 mb-1 last-border-none">
                                    <span class="fw-bold text-dark w-25"><?= $daysMap[$sch['DayOfWeek']] ?? '-' ?></span>
                                    <span class="text-muted font-monospace"><i class="fa-regular fa-clock me-1 text-secondary"></i><?= $startTime ?> - <?= $endTime ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted x-small fst-italic"><i class="fa-regular fa-calendar-xmark me-1"></i>Program girilmemiş.</div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="small fw-bold text-secondary mt-2">Sporcu Sayısı</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 mt-2 border border-primary border-opacity-25">
                            <?= $g['StudentCount'] ?> Kişi
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                    <i class="fa-solid fa-layer-group fa-3x text-secondary opacity-25"></i>
                </div>
                <p class="text-muted">Görüntülenecek grup yok.</p>
                <?php if($isAdmin): ?>
                    <button class="btn btn-outline-primary btn-sm rounded-pill fw-bold" onclick="openModal()">İlk Grubu Oluştur</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if($isAdmin): ?>
<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="index.php?page=group_save" method="POST"> <input type="hidden" name="group_id" id="modalGroupId">
                
                <div class="modal-header bg-primary text-white border-0">
                    <h6 class="modal-title fw-bold" id="modalTitle"><i class="fa-solid fa-pen-to-square me-2"></i>Grup İşlemleri</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-1 text-muted">GRUP ADI</label>
                        <input type="text" name="group_name" id="modalGroupName" class="form-control shadow-sm" placeholder="Örn: U12 Basketbol" required>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold mb-1 text-muted">SORUMLU ANTRENÖR</label>
                        <select name="coach_id" id="modalCoachId" class="form-select shadow-sm">
                            <option value="">-- Atanmadı --</option>
                            <?php if(!empty($coaches)): foreach($coaches as $c): ?>
                                <option value="<?= $c['UserID'] ?>"><?= htmlspecialchars($c['FullName']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="small fw-bold mb-0 text-muted">HAFTALIK PROGRAM</label>
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill fw-bold" onclick="addScheduleRow()">
                            <i class="fa-solid fa-plus me-1"></i>Ders Ekle
                        </button>
                    </div>
                    
                    <div id="scheduleContainer" class="bg-light p-2 rounded-3 border" style="max-height: 250px; overflow-y: auto;">
                        </div>
                    <div class="form-text x-small mt-1 text-muted"><i class="fa-solid fa-circle-info me-1"></i>Aynı gün için birden fazla saat ekleyebilirsiniz.</div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    var groupModalObj = null;
    var scheduleContainer = null;

    document.addEventListener('DOMContentLoaded', function() {
        <?php if($isAdmin): ?>
            var modalEl = document.getElementById('groupModal');
            if (modalEl) groupModalObj = new bootstrap.Modal(modalEl);
            scheduleContainer = document.getElementById('scheduleContainer');
        <?php endif; ?>
    });

    function addScheduleRow(day = 1, start = '17:00', end = '18:30') {
        if (!scheduleContainer) return;

        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 align-items-center schedule-row';
        row.innerHTML = `
            <div class="col-4">
                <select name="days[]" class="form-select form-select-sm shadow-sm" required>
                    <option value="1" ${day==1?'selected':''}>Pazartesi</option>
                    <option value="2" ${day==2?'selected':''}>Salı</option>
                    <option value="3" ${day==3?'selected':''}>Çarşamba</option>
                    <option value="4" ${day==4?'selected':''}>Perşembe</option>
                    <option value="5" ${day==5?'selected':''}>Cuma</option>
                    <option value="6" ${day==6?'selected':''}>Cumartesi</option>
                    <option value="7" ${day==7?'selected':''}>Pazar</option>
                </select>
            </div>
            <div class="col-3"><input type="time" name="starts[]" class="form-control form-control-sm shadow-sm" value="${start}" required></div>
            <div class="col-3"><input type="time" name="ends[]" class="form-control form-control-sm shadow-sm" value="${end}" required></div>
            <div class="col-2 text-end"><button type="button" class="btn btn-sm text-danger" onclick="this.closest('.row').remove()"><i class="fa-solid fa-xmark"></i></button></div>
        `;
        scheduleContainer.appendChild(row);
    }

    function openModal() {
        if (!groupModalObj) return;
        document.getElementById('modalGroupId').value = ''; // Boşsa yeni kayıt (INSERT)
        document.getElementById('modalGroupName').value = '';
        document.getElementById('modalCoachId').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-plus me-2"></i>Yeni Grup Oluştur';
        scheduleContainer.innerHTML = '';
        addScheduleRow(); 
        groupModalObj.show();
    }

    function editGroup(group) {
        if (!groupModalObj) return;
        document.getElementById('modalGroupId').value = group.GroupID; // Doluysa güncelleme (UPDATE)
        document.getElementById('modalGroupName').value = group.GroupName;
        document.getElementById('modalCoachId').value = group.CoachID || '';
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Grubu Düzenle';
        scheduleContainer.innerHTML = '';
        
        if (group.Schedule && group.Schedule.length > 0) {
            group.Schedule.forEach(sch => {
                const start = sch.StartTime ? sch.StartTime.substring(0, 5) : '17:00';
                const end = sch.EndTime ? sch.EndTime.substring(0, 5) : '18:30';
                addScheduleRow(sch.DayOfWeek, start, end);
            });
        } else {
            addScheduleRow();
        }
        groupModalObj.show();
    }
</script>

<style>
    .hover-shadow:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .transition-all { transition: all 0.3s ease; }
    .x-small { font-size: 0.7rem; }
    .last-border-none:last-child { border-bottom: 0 !important; margin-bottom: 0 !important; }
    .btn-xs { padding: 0.1rem 0.5rem; font-size: 0.75rem; }
    .letter-spacing-1 { letter-spacing: 1px; }
</style>