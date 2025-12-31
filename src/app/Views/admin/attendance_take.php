<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">
                <i class="fas fa-clipboard-check text-primary me-2"></i>
                <?php echo htmlspecialchars($session['GroupName']); ?>
            </h4>
            <span class="badge bg-light text-muted border mt-1">Grup Yoklama Listesi</span>
        </div>
        <div class="card border-0 shadow-sm px-3 py-2 bg-white text-center">
            <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Antrenman Tarihi</small>
            <div class="fw-bold text-primary"><?php echo date('d.m.Y', strtotime($date)); ?></div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-body p-0">
            <form action="index.php?page=attendance_save" method="POST" id="attendanceForm">
                <input type="hidden" name="group_id" value="<?php echo $session['GroupID']; ?>">
                <input type="hidden" name="club_id" value="<?php echo $session['ClubID']; ?>">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <input type="hidden" name="session_id" value="<?php echo $sessionId; ?>">

                <div class="attendance-list">
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $index => $std): ?>
                            <?php 
                                // Varsayılan durum: 1 (Var) kabul edelim eğer veri yoksa
                                $currentStatus = ($std['CurrentStatus'] === null) ? 1 : $std['CurrentStatus'];
                                $btnClass = ($currentStatus == 1) ? 'btn-success' : 'btn-danger';
                                $btnText = ($currentStatus == 1) ? 'GELDİ' : 'GELMEDİ';
                                $btnIcon = ($currentStatus == 1) ? 'fa-check' : 'fa-times';
                            ?>
                            <div class="attendance-row d-flex align-items-center justify-content-between p-3 border-bottom <?php echo (!$isEditable) ? 'bg-light' : ''; ?>">
                                
                                <div class="d-flex align-items-center">
                                    <div class="avatar-num me-3"><?php echo $index + 1; ?></div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">
                                            <?php echo htmlspecialchars($std['FullName']); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo !empty($std['BirthDate']) ? date_diff(date_create($std['BirthDate']), date_create('today'))->y . ' Yaş' : 'Sporcu'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="attendance-action">
                                    <input type="hidden" 
                                        name="status[<?php echo $std['StudentID']; ?>]" 
                                        id="input_<?php echo $std['StudentID']; ?>" 
                                        value="<?php echo $currentStatus; ?>">
                                    
                                    <button type="button" 
                                            id="btn_<?php echo $std['StudentID']; ?>"
                                            class="icon-toggle-btn <?php echo ($currentStatus == 1) ? 'is-present' : 'is-absent'; ?>"
                                            onclick="toggleAttendance('<?php echo $std['StudentID']; ?>')"
                                            <?php echo (!$isEditable) ? 'disabled' : ''; ?>>
                                        <div class="toggle-track"></div>
                                        <div class="toggle-thumb">
                                            <i class="fas <?php echo ($currentStatus == 1) ? 'fa-check' : 'fa-times'; ?>"></i>
                                        </div>
                                    </button>
                                </div>  
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users-slash fa-3x text-light mb-3"></i>
                            <p class="text-muted">Öğrenci bulunamadı.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer bg-white p-4 d-flex justify-content-between">
                    <a href="index.php?page=dashboard" class="btn btn-light border px-4">İptal</a>
                    <?php if ($isEditable): ?>
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow fw-bold">
                            <i class="fas fa-save me-2"></i>DEĞİŞİKLİKLERİ KAYDET
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
.icon-toggle-btn {
    position: relative;
    width: 75px; /* Sabit Genişlik */
    height: 38px;
    border-radius: 50px;
    border: none;
    outline: none;
    cursor: pointer;
    background-color: transparent;
    padding: 0;
    transition: all 0.4s ease;
}

/* Arka Plan Kanalı */
.toggle-track {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    border-radius: 50px;
    transition: all 0.4s ease;
    border: 2px solid transparent;
}

/* Hareketli Yuvarlak (Başlık) */
.toggle-thumb {
    position: absolute;
    top: 4px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 2;
}

/* VAR Durumu (Yeşil) */
.icon-toggle-btn.is-present .toggle-track {
    background-color: #f0fdf4;
    border-color: #22c55e;
}
.icon-toggle-btn.is-present .toggle-thumb {
    left: 4px;
    color: #22c55e;
}

/* YOK Durumu (Kırmızı) */
.icon-toggle-btn.is-absent .toggle-track {
    background-color: #fef2f2;
    border-color: #ef4444;
}
.icon-toggle-btn.is-absent .toggle-thumb {
    left: calc(100% - 34px); /* Sağa kaydır */
    color: #ef4444;
}

/* Basma Efekti */
.icon-toggle-btn:active .toggle-thumb {
    transform: scale(0.9);
}
</style>
<script>
$(document).ready(function() {
    $('.btn-toggle-status').on('click', function() {
        const studentId = $(this).data('student-id');
        const input = $('#input_' + studentId);
        const btn = $(this);
        const icon = btn.find('i');
        const text = btn.find('span');

        if (input.val() == "1") {
            // "Var"dan "Yok"a çek
            input.val("0");
            btn.removeClass('btn-success').addClass('btn-danger');
            icon.removeClass('fa-check').addClass('fa-times');
            text.text('GELMEDİ');
        } else {
            // "Yok"tan "Var"a çek
            input.val("1");
            btn.removeClass('btn-danger').addClass('btn-success');
            icon.removeClass('fa-times').addClass('fa-check');
            text.text('GELDİ');
        }
    });
});
</script>

<script>
function toggleAttendance(studentId) {
    const input = document.getElementById('input_' + studentId);
    const btn = document.getElementById('btn_' + studentId);
    const icon = btn.querySelector('.toggle-circle i');

    if (input.value == "1") {
        input.value = "0";
        btn.classList.remove('is-present');
        btn.classList.add('is-absent');
        icon.classList.replace('fa-check', 'fa-times');
    } else {
        input.value = "1";
        btn.classList.remove('is-absent');
        btn.classList.add('is-present');
        icon.classList.replace('fa-times', 'fa-check');
    }
}
</script>