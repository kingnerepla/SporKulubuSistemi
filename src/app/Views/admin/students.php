<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-users text-warning me-2"></i>Öğrenci Yönetimi</h3>
            <p class="text-muted small mb-0">Gruplar bazında paketlenmiş modern liste görünümü.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=students_archived" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-box-archive me-1"></i>Arşiv
            </a>
            <a href="index.php?page=student_add" class="btn btn-primary btn-sm shadow-sm">
                <i class="fa-solid fa-user-plus me-1"></i>Yeni Öğrenci
            </a>
        </div>
    </div>

    <?php if(!empty($students)): ?>
        <?php 
        $currentGroup = null; 
        $groupIndex = 0;
        foreach($students as $s): 
            if ($currentGroup !== $s['GroupName']): 
                if ($currentGroup !== null) echo '</tbody></table></div></div>'; // Önceki kartı ve tabloyu kapat
                
                $currentGroup = $s['GroupName'];
                $groupIndex++;
                // Renk ve Stil Seçimi
                $isOrange = ($groupIndex % 2 !== 0);
                $themeClass = $isOrange ? 'theme-orange' : 'theme-gray';
                $counter = 1;
        ?>
            <div class="group-package <?= $themeClass ?> mb-5 shadow-sm">
                <div class="group-title-bar px-4 py-3">
                    <i class="fa-solid fa-people-group me-2"></i>
                    <?= htmlspecialchars($currentGroup ?: 'GRUP ATANMAMIŞ ÖĞRENCİLER') ?>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="small text-uppercase text-muted">
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
                        <div class="text-muted me-3 small font-monospace"><?= $counter++ ?>.</div>
                        <div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($s['FullName']) ?></div>
                            <small class="text-muted">ID: #<?= $s['StudentID'] ?></small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="text-dark small fw-medium"><?= htmlspecialchars($s['ParentName'] ?? '-') ?></div>
                    <small class="text-primary"><i class="fa-solid fa-phone-flip fa-xs me-1"></i><?= $s['ParentPhone'] ?? '-' ?></small>
                </td>
                <td class="text-center fw-bold text-success font-monospace">
                    <?= number_format($s['MonthlyFee'] ?? 0, 2, ',', '.') ?> ₺
                </td>
                <td class="text-end pe-4">
                    <div class="btn-group shadow-sm bg-white rounded border overflow-hidden">
                        <button class="btn btn-sm btn-white text-info border-end" onclick="showParentInfo('<?= htmlspecialchars($s['FullName']) ?>', '<?= $s['ParentPhone'] ?>')">
                            <i class="fa-solid fa-key"></i>
                        </button>
                        <a href="index.php?page=student_edit&id=<?= $s['StudentID'] ?>" class="btn btn-sm btn-white text-secondary border-end"><i class="fa-solid fa-pen"></i></a>
                        <a href="index.php?page=student_delete&id=<?= $s['StudentID'] ?>" class="btn btn-sm btn-white text-danger" onclick="return confirm('Silinsin mi?')"><i class="fa-solid fa-trash"></i></a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div></div> <?php else: ?>
        <div class="alert alert-info text-center">Öğrenci bulunamadı.</div>
    <?php endif; ?>
</div>

<style>
    /* --- GENEL PAKET TASARIMI --- */
    .group-package {
        background: #fff;
        border-radius: 20px; /* Köşeleri iyice yumuşattık */
        overflow: hidden;
        border-width: 4px; /* Borderı kalınlaştırdık */
        border-style: solid;
    }

    /* TURUNCU TEMA */
    .theme-orange {
        border-color: #ff9800 !important;
    }
    .theme-orange .group-title-bar {
        background-color: #ff9800;
        color: #fff;
    }

    /* GRİ TEMA */
    .theme-gray {
        border-color: #6c757d !important;
    }
    .theme-gray .group-title-bar {
        background-color: #6c757d;
        color: #fff;
    }

    /* --- İÇ TASARIM --- */
    .group-title-bar {
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .student-row {
        transition: background 0.2s;
    }
    .student-row td {
        border-bottom: 1px solid #f1f5f9;
        padding-top: 15px;
        padding-bottom: 15px;
    }
    .student-row:last-child td {
        border-bottom: none;
    }
    .student-row:hover {
        background-color: #fcfdfe;
    }

    .btn-white { background: #fff !important; border: none; }
    body { background-color: #f4f7f6; }
</style>