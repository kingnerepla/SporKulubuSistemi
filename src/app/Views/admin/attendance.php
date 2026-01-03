<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-user text-info me-2"></i>Yoklama Al</h3>
            <p class="text-muted small mb-0">
                <?= $isAdmin ? 'Tarihler arası geçiş yapabilirsiniz.' : 'Bugünün yoklamasını alabilirsiniz.' ?>
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                
                <?php if($isAdmin): ?>
                    <a href="index.php?page=attendance&date=<?= $prevDate ?>" 
                       class="btn btn-light border rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                       style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-chevron-left text-muted"></i>
                    </a>
                <?php else: ?>
                     <span style="width: 45px;"></span> 
                <?php endif; ?>

                <div class="text-center">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fa-regular fa-calendar me-2 opacity-75"></i><?= $formattedDate ?>
                    </h5>
                </div>

                <?php if($isAdmin): ?>
                    <a href="index.php?page=attendance&date=<?= $nextDate ?>" 
                       class="btn btn-light border rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                       style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-chevron-right text-muted"></i>
                    </a>
                <?php else: ?>
                    <span style="width: 45px;"></span>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="accordion" id="attendanceAccordion">
        <?php if(!empty($groups)): ?>
            <?php foreach($groups as $index => $g): 
                $collapseId = "collapse_" . $g['GroupID'];
                $headingId = "heading_" . $g['GroupID'];
                $hasStudents = !empty($g['students']);
                $colors = ['primary', 'warning', 'info', 'success', 'danger'];
                $color = $colors[$index % 5];
            ?>
            
            <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden group-card">
                
                <div class="card-header bg-white p-0 border-0 border-start border-5 border-<?= $color ?>" id="<?= $headingId ?>">
                    <button class="d-flex align-items-center justify-content-between w-100 p-4 btn btn-link text-decoration-none text-dark" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#<?= $collapseId ?>">
                        
                        <div class="d-flex align-items-center text-start">
                            <div class="fw-bold fs-5"><?= htmlspecialchars($g['GroupName']) ?></div>
                            <?php if(!empty($g['lesson_hours'])): ?>
                                <span class="badge bg-white text-muted border ms-3 fw-normal">
                                    <i class="fa-regular fa-clock me-1"></i><?= $g['lesson_hours'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <i class="fa-solid fa-chevron-down text-muted transition-icon"></i>
                    </button>
                </div>

                <div id="<?= $collapseId ?>" class="accordion-collapse collapse" data-bs-parent="#attendanceAccordion">
                    <div class="card-body bg-white border-top p-0">
                        
                        <?php if(!$g['is_lesson_day'] && !$isAdmin): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-calendar-xmark fa-2x mb-2 opacity-25"></i>
                                <div>Bugün ders yok.</div>
                            </div>
                        <?php elseif(!$hasStudents): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-slash fa-2x mb-2 opacity-25"></i>
                                <div>Öğrenci yok.</div>
                            </div>
                        <?php else: ?>

                            <form action="index.php?page=attendance_store" method="POST">
                                <input type="hidden" name="group_id" value="<?= $g['GroupID'] ?>">
                                <input type="hidden" name="date" value="<?= $selectedDate ?>">
                                
                                <div class="list-group list-group-flush">
                                    <?php foreach($g['students'] as $s): 
                                        $isPresent = isset($g['attendance'][$s['StudentID']]) && $g['attendance'][$s['StudentID']] == 1;
                                        $rem = $s['RemainingSessions'] ?? 0;
                                        
                                        $remClass = 'bg-light text-dark border'; 
                                        if($rem > 5) $remClass = 'bg-success bg-opacity-10 text-success border-success border-opacity-25';
                                        elseif($rem > 0) $remClass = 'bg-warning bg-opacity-10 text-dark border-warning border-opacity-25';
                                        else $remClass = 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
                                    ?>
                                    
                                    <div class="list-group-item p-3 d-flex align-items-center justify-content-between bg-white student-row">
                                        
                                        <div style="width: 40%;" class="d-flex align-items-center">
                                            <span class="fw-bold text-dark fs-6 text-truncate"><?= htmlspecialchars($s['FullName']) ?></span>
                                        </div>

                                        <div style="width: 20%;" class="d-flex justify-content-center">
                                            <div class="rounded-pill py-1 px-3 fw-bold small text-nowrap <?= $remClass ?>">
                                                <?= $rem ?> Hak
                                            </div>
                                        </div>

                                        <div style="width: 40%;" class="d-flex justify-content-end">
                                            <input type="checkbox" 
                                                   name="status[<?= $s['StudentID'] ?>]" 
                                                   id="chk_<?= $s['StudentID'] ?>_<?= $g['GroupID'] ?>" 
                                                   class="attendance-checkbox" 
                                                   <?= $isPresent ? 'checked' : '' ?>>
                                            
                                            <label for="chk_<?= $s['StudentID'] ?>_<?= $g['GroupID'] ?>" class="attendance-label">
                                                <div class="icon-circle shadow-sm">
                                                    <i class="fa-solid fa-check icon-check"></i>
                                                    <i class="fa-solid fa-xmark icon-xmark"></i>
                                                </div>
                                            </label>
                                        </div>

                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="p-3 bg-white text-end border-top">
                                    <button type="submit" class="btn btn-primary px-5 fw-bold rounded-pill shadow-sm">
                                        KAYDET
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center text-muted py-5">Grup bulunamadı.</div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* GİZLİ CHECKBOX */
    .attendance-checkbox { display: none; }

    /* SATIR ÇİZGİSİ */
    .student-row {
        border-bottom: 1px solid #dee2e6; /* Belirgin Gri */
    }
    .student-row:last-child {
        border-bottom: none;
    }

    /* BUTON ALANI */
    .attendance-label {
        cursor: pointer;
        display: inline-block;
        transition: transform 0.1s;
    }
    .attendance-label:active { transform: scale(0.95); }

    /* YUVARLAK İKON ÇERÇEVESİ */
    .icon-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;         
        align-items: center;   
        justify-content: center; 
        font-size: 1.3rem;     
        color: white; 
        transition: all 0.3s ease;
        padding: 0;
        margin: 0;
        line-height: 1; /* Satır yüksekliği sorunu çözer */
    }

    /* İKONLARIN İNCE AYARI */
    .icon-circle i {
        display: block; /* Inline davranışı kır */
        text-align: center;
        width: 100%;
    }

    /* X İkonunu zorla ortala (Hafif sağa iterek) */
    .icon-xmark {
        transform: translateX(1px); /* X ikonunu 1 pixel sağa it */
    }

    /* KIRMIZI DURUM */
    .attendance-checkbox:not(:checked) + .attendance-label .icon-circle {
        background-color: #dc3545;
        border: 2px solid #dc3545;
    }
    .attendance-checkbox:not(:checked) + .attendance-label .icon-check { display: none; }
    .attendance-checkbox:not(:checked) + .attendance-label .icon-xmark { display: block; }

    /* YEŞİL DURUM */
    .attendance-checkbox:checked + .attendance-label .icon-circle {
        background-color: #198754;
        border: 2px solid #198754;
    }
    .attendance-checkbox:checked + .attendance-label .icon-xmark { display: none; }
    .attendance-checkbox:checked + .attendance-label .icon-check { display: block; }

    /* ORTAK STİLLER */
    .text-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .accordion-button:not(.collapsed) .transition-icon { transform: rotate(-180deg); }
    .transition-icon { transition: transform 0.3s ease; }
    .group-card:hover { transform: translateY(-2px); transition: transform 0.2s; }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Controller'dan gelen açık grup ID'sini al
        const openGroupId = "<?= $openGroupId ?? '' ?>"; 
        
        if (openGroupId) {
            // ID yapısı: collapseGroup_10 (Örnek)
            // Eğer View dosyasında collapse ID'lerini nasıl verdiysen ona göre ayarla.
            // Genelde döngüde şöyle veriyoruz: id="collapseGroup_<?= $g['GroupID'] ?>"
            
            // Eğer view dosyasında ID'ler "collapseGroup_GroupID" şeklindeyse:
            const targetId = 'collapseGroup_' + openGroupId;
            
            // Eğer view dosyasında ID'ler sadece sırayla "collapseGroup1, collapseGroup2" gidiyorsa,
            // bunu JavaScript ile bulmak zor olabilir. 
            // EN SAĞLIKLISI View dosyasındaki ID'leri GroupID ile eşleştirmektir.
            
            const collapseElement = document.getElementById(targetId);
            
            if (collapseElement) {
                // Bootstrap 5 ile aç
                if (typeof bootstrap !== 'undefined') {
                    new bootstrap.Collapse(collapseElement, { show: true });
                } else {
                    // Bootstrap yüklü değilse manuel class ekle (Fallback)
                    collapseElement.classList.add('show');
                }
                
                // Oraya kaydır
                collapseElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>