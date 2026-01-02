<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-layer-group text-primary me-2"></i>Grup Yönetimi</h3>
            <p class="text-muted small mb-0">Antrenman gruplarını ve haftalık ders programlarını yönetin.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm shadow-sm px-3" onclick="openModal()">
            <i class="fa-solid fa-plus me-1"></i>Yeni Grup Oluştur
        </button>
    </div>

    <?php if(!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if(!empty($groups)): foreach($groups as $g): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($g['GroupName']) ?></h5>
                            <div class="small text-muted">
                                <i class="fa-solid fa-user-tie me-1"></i><?= htmlspecialchars($g['CoachName'] ?? 'Antrenör Yok') ?>
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li>
                                    <a class="dropdown-item" href="#" onclick='editGroup(<?= json_encode($g, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="fa-solid fa-pen me-2 text-primary"></i>Düzenle
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="index.php?page=group_delete&id=<?= $g['GroupID'] ?>" onclick="return confirm('Silmek istediğine emin misin?')">
                                        <i class="fa-solid fa-trash me-2"></i>Sil
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-light rounded p-3 mb-3">
                        <h6 class="x-small text-uppercase fw-bold text-muted mb-2">Ders Programı</h6>
                        <?php if(!empty($g['Schedule'])): ?>
                            <ul class="list-unstyled mb-0 small">
                                <?php 
                                $daysMap = [1=>'Pzt', 2=>'Sal', 3=>'Çrş', 4=>'Prş', 5=>'Cum', 6=>'Cmt', 7=>'Paz'];
                                foreach($g['Schedule'] as $sch): 
                                    $startTime = isset($sch['StartTime']) ? substr($sch['StartTime'], 0, 5) : '??:??';
                                    $endTime = isset($sch['EndTime']) ? substr($sch['EndTime'], 0, 5) : '??:??';
                                ?>
                                <li class="d-flex justify-content-between border-bottom border-light pb-1 mb-1">
                                    <span class="fw-bold text-dark w-25"><?= $daysMap[$sch['DayOfWeek']] ?? '-' ?></span>
                                    <span class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= $startTime ?> - <?= $endTime ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted x-small fst-italic">Program girilmemiş.</div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="small fw-bold text-secondary">Sporcu Sayısı</span>
                        <span class="badge bg-primary rounded-pill px-3"><?= $g['StudentCount'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-layer-group fa-3x text-muted opacity-25 mb-3"></i>
                <p class="text-muted">Henüz hiç grup oluşturulmamış.</p>
                <button class="btn btn-outline-primary btn-sm" onclick="openModal()">İlk Grubu Oluştur</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=group_store" method="POST">
                <input type="hidden" name="group_id" id="modalGroupId">
                
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold" id="modalTitle">Yeni Grup Oluştur</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Grup Adı</label>
                        <input type="text" name="group_name" id="modalGroupName" class="form-control" placeholder="Örn: U12 Basketbol" required>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold mb-1">Sorumlu Antrenör</label>
                        <select name="coach_id" id="modalCoachId" class="form-select">
                            <option value="">-- Atanmadı --</option>
                            <?php if(!empty($coaches)): foreach($coaches as $c): ?>
                                <option value="<?= $c['UserID'] ?>"><?= htmlspecialchars($c['FullName']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="small fw-bold mb-0">Haftalık Program</label>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="addScheduleRow()">
                            <i class="fa-solid fa-plus me-1"></i>Ders Ekle
                        </button>
                    </div>
                    
                    <div id="scheduleContainer" class="bg-light p-2 rounded border" style="max-height: 250px; overflow-y: auto;">
                        </div>
                    <div class="form-text x-small mt-1">Aynı gün için birden fazla saat ekleyebilirsiniz.</div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Global değişkenler (Tanımsız hatası almamak için)
    var groupModalObj = null;
    var scheduleContainer = null;

    // Sayfa tamamen yüklendiğinde çalıştır
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Modalı Tanımla
        var modalEl = document.getElementById('groupModal');
        if (modalEl) {
            groupModalObj = new bootstrap.Modal(modalEl);
        } else {
            console.error("HATA: groupModal ID'li element bulunamadı!");
        }

        // 2. Container'ı Tanımla
        scheduleContainer = document.getElementById('scheduleContainer');
    });

    // Satır Ekleme Fonksiyonu
    function addScheduleRow(day = 1, start = '17:00', end = '18:30') {
        if (!scheduleContainer) return; // Güvenlik kontrolü

        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 align-items-center schedule-row';
        
        row.innerHTML = `
            <div class="col-4">
                <select name="days[]" class="form-select form-select-sm" required>
                    <option value="1" ${day==1?'selected':''}>Pazartesi</option>
                    <option value="2" ${day==2?'selected':''}>Salı</option>
                    <option value="3" ${day==3?'selected':''}>Çarşamba</option>
                    <option value="4" ${day==4?'selected':''}>Perşembe</option>
                    <option value="5" ${day==5?'selected':''}>Cuma</option>
                    <option value="6" ${day==6?'selected':''}>Cumartesi</option>
                    <option value="7" ${day==7?'selected':''}>Pazar</option>
                </select>
            </div>
            <div class="col-3">
                <input type="time" name="starts[]" class="form-control form-select-sm" value="${start}" required>
            </div>
            <div class="col-3">
                <input type="time" name="ends[]" class="form-control form-select-sm" value="${end}" required>
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('.row').remove()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `;
        scheduleContainer.appendChild(row);
    }

    // Yeni Grup Modalı Aç
    function openModal() {
        if (!groupModalObj) {
            alert("Sayfa tam yüklenmedi veya hata oluştu. Lütfen sayfayı yenileyin.");
            return;
        }

        document.getElementById('modalGroupId').value = '';
        document.getElementById('modalGroupName').value = '';
        document.getElementById('modalCoachId').value = '';
        document.getElementById('modalTitle').innerText = 'Yeni Grup Oluştur';
        
        scheduleContainer.innerHTML = ''; // Temizle
        addScheduleRow(); // Varsayılan 1 satır ekle
        
        groupModalObj.show();
    }

    // Düzenle Modalı Aç
    function editGroup(group) {
        if (!groupModalObj) return;

        document.getElementById('modalGroupId').value = group.GroupID;
        document.getElementById('modalGroupName').value = group.GroupName;
        document.getElementById('modalCoachId').value = group.CoachID || '';
        document.getElementById('modalTitle').innerText = 'Grubu Düzenle';

        scheduleContainer.innerHTML = ''; // Temizle
        
        // Varsa programı yükle
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
    body { background-color: #f4f7f6; }
</style>