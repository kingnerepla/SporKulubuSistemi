<?php
// Ay ve Yıl değişkenleri yoksa bugüne ayarla
$month = $month ?? date('m');
$year = $year ?? date('Y');
$daysInMonth = $daysInMonth ?? date('t', strtotime("$year-$month-01"));

// Hata Ayıklama: Eğer veri boş gelirse örnek veri oluştur (Tasarımı görmek için)
if (!isset($reportMatrix) || empty($reportMatrix)) {
    $reportMatrix = [
        ['FullName' => 'Örnek Sporcu 1', 'presentCount' => 5, 'days' => array_fill(1, $daysInMonth, 1)],
        ['FullName' => 'Örnek Sporcu 2', 'presentCount' => 3, 'days' => array_fill(1, $daysInMonth, 0)],
    ];
}
?>

<style>
    /* Kart ve Genel Yapı */
    .student-card { 
        background: #ffffff; 
        border-radius: 16px; 
        border: 1px solid #eef2f6; 
        margin-bottom: 25px; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
        overflow: hidden;
    }
    
    .card-header-custom { 
        padding: 15px 20px; 
        background: #f8fafc; 
        border-bottom: 1px solid #e2e8f0; 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
    }

    /* Haftalık Gruplandırma (Mobil Uyumlu Flex) */
    .weeks-container { 
        display: flex; 
        flex-wrap: wrap; /* Mobilde alta geçmeyi sağlar */
        gap: 12px; 
        padding: 15px; 
        background: #ffffff;
    }

    .week-box { 
        border: 1px solid #f1f5f9; 
        border-radius: 12px; 
        padding: 12px; 
        flex: 1; 
        min-width: 280px; /* Tablet ve Mobilde genişliği zorlar */
        max-width: 100%;
        background: #fafbfc;
    }

    .week-title {
        font-size: 10px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        margin-bottom: 10px;
        display: block;
        text-align: center;
    }

    /* 7 Günlük Kare Grid */
    .days-grid { 
        display: grid; 
        grid-template-columns: repeat(7, 1fr); 
        gap: 8px; 
    }

    /* Durum Kareleri */
    .status-sq { 
        width: 100%; 
        aspect-ratio: 1 / 1; /* Tam kare olmasını sağlar */
        max-width: 38px;
        margin: 0 auto;
        border-radius: 8px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-size: 12px; 
        color: white; 
        position: relative; 
        transition: transform 0.2s;
    }

    .st-v { background: #22c55e; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.2); } /* Var */
    .st-y { background: #ef4444; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2); } /* Yok */
    .st-n { background: #f1f5f9; color: #cbd5e1; border: 1px dashed #cbd5e1; } /* Kayıt Yok */

    .d-n { 
        position: absolute; 
        top: 2px; 
        right: 3px; 
        font-size: 8px; 
        font-weight: bold;
        opacity: 0.6; 
    }

    .day-name { font-size: 9px; font-weight: 600; color: #64748b; margin-bottom: 4px; }
    .weekend { color: #f87171 !important; }

    /* Mobil İnce Ayar */
    @media (max-width: 576px) {
        .week-box { min-width: 100%; } 
        .status-sq { max-width: 45px; font-size: 14px; }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0"><i class="fas fa-th-large text-primary me-2"></i>Aylık Devam Çizelgesi</h4>
        <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
            <i class="far fa-calendar-alt me-1"></i> <?= $month ?>. Ay / <?= $year ?>
        </div>
    </div>

    <?php foreach($reportMatrix as $student): ?>
    <div class="student-card">
        <div class="card-header-custom">
            <span class="fw-bold text-dark"><i class="fas fa-user-circle me-2 text-secondary"></i><?= htmlspecialchars($student['FullName']) ?></span>
            <span class="badge bg-soft-success text-success border border-success px-3 py-2 rounded-pill" style="background: #f0fdf4;">
                <?php 
                    $totalSessions = count(array_filter($student['days'], function($val) { return $val !== null; }));
                    $rate = ($totalSessions > 0) ? round(($student['presentCount'] / $totalSessions) * 100) : 0;
                ?>
                Katılım: %<?= $rate ?>
            </span>
        </div>
        
        <div class="weeks-container">
            <?php 
            $currentWeek = [];
            for($d = 1; $d <= $daysInMonth; $d++) {
                $ts = strtotime("$year-$month-$d");
                $dow = date('N', $ts); 
                $currentWeek[] = ['d' => $d, 'dow' => $dow, 'ts' => $ts];

                // Pazar günü (7) bittiyse veya ayın son günü ise haftayı kapat
                if ($dow == 7 || $d == $daysInMonth) {
                    echo '<div class="week-box">';
                    echo '<span class="week-title">Hafta ' . date('W', $currentWeek[0]['ts']) . '</span>';
                    echo '<div class="days-grid">';
                    
                    // Ayın ilk haftası için boşluk doldurma (Pazartesi değilse)
                    if ($d <= 7) {
                        $firstDayDow = date('N', strtotime("$year-$month-01"));
                        for($i = 1; $i < $firstDayDow; $i++) echo '<div></div>';
                    }

                    foreach($currentWeek as $day) {
                        $status = $student['days'][$day['d']] ?? null;
                        $isWeekend = ($day['dow'] >= 6);
                        ?>
                        <div class="text-center">
                            <div class="day-name <?= $isWeekend ? 'weekend' : '' ?>"><?= mb_substr(date('D', $day['ts']), 0, 2) ?></div>
                            <div class="status-sq <?= ($status !== null) ? ($status == 1 ? 'st-v' : 'st-y') : 'st-n' ?>">
                                <span class="d-n"><?= $day['d'] ?></span>
                                <?php if($status !== null): ?>
                                    <i class="fas <?= $status == 1 ? 'fa-check' : 'fa-times' ?>"></i>
                                <?php else: ?>
                                    <small style="font-size: 8px;">•</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                    echo '</div></div>';
                    $currentWeek = [];
                }
            }
            ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-center gap-4 mt-2 mb-5 small fw-bold text-muted text-uppercase" style="letter-spacing: 1px;">
        <span><i class="fas fa-square text-success me-1"></i> Var</span>
        <span><i class="fas fa-square text-danger me-1"></i> Yok</span>
        <span><i class="fas fa-square text-light border me-1"></i> Kayıt Yok</span>
    </div>
</div>