<div class="container-fluid py-4">

    <div class="row justify-content-center mb-4">
        <div class="col-md-6 text-center">
            <div class="d-flex align-items-center justify-content-center bg-white p-2 rounded-pill shadow-sm border">
                <a href="index.php?page=attendance&date=<?= date('Y-m-d', strtotime($selectedDate . ' -1 day')) ?>" class="btn btn-light rounded-circle shadow-sm">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>

                <div class="mx-4 text-center position-relative">
                    <div class="small text-muted fw-bold text-uppercase">YOKLAMA TARİHİ</div>
                    <div class="h4 fw-bold mb-0 text-primary">
                        <i class="fa-regular fa-calendar me-2"></i><?= date('d.m.Y', strtotime($selectedDate)) ?>
                    </div>
                    <input type="date" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" value="<?= $selectedDate ?>" onchange="window.location.href='index.php?page=attendance&date='+this.value" style="cursor: pointer;">
                </div>

                <a href="index.php?page=attendance&date=<?= date('Y-m-d', strtotime($selectedDate . ' +1 day')) ?>" class="btn btn-light rounded-circle shadow-sm">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
            
            <div class="mt-2 badge bg-light text-secondary border fw-normal">
                <?php 
                    $gunler = ['Monday'=>'Pazartesi','Tuesday'=>'Salı','Wednesday'=>'Çarşamba','Thursday'=>'Perşembe','Friday'=>'Cuma','Saturday'=>'Cumartesi','Sunday'=>'Pazar'];
                    echo 'Gün: ' . $gunler[date('l', strtotime($selectedDate))];
                ?>
            </div>
        </div>
    </div>

    <h6 class="fw-bold text-secondary mb-3 ps-2 border-start border-4 border-primary">
        <?= date('d.m.Y', strtotime($selectedDate)) == date('d.m.Y') ? 'BUGÜNKÜ' : 'SEÇİLİ GÜNDEKİ' ?> GRUPLAR
    </h6>

    <div class="row g-3 mb-5">
        <?php foreach($groups as $grp): 
            $isActive = (isset($_GET['group_id']) && $_GET['group_id'] == $grp['GroupID']);
            
            // Varsayılan Stil (Normal Açık)
            $statusIcon = '<i class="fa-regular fa-clock"></i>';
            $statusText = 'Bekliyor';
            $cardStyle = 'bg-white border-0';
            $timeHtml = '';
            $isClickable = true;

            // A. Saat Bilgisi Hazırla
            if (!empty($grp['today_times'])) {
                foreach($grp['today_times'] as $t) {
                    $timeHtml .= '<div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-1 me-1"><i class="fa-regular fa-clock me-1"></i>' . $t . '</div> ';
                }
            } else {
                $timeHtml = '<span class="small text-muted fst-italic">Planlanmış saat yok</span>';
            }

            // B. Kart Durumunu Belirle
            
            // 1. Durum: Yoklama zaten alınmış
            if ($grp['is_taken']) {
                $cardStyle = 'bg-success bg-opacity-10 border border-success';
                $statusIcon = '<i class="fa-solid fa-circle-check text-success"></i>';
                $statusText = 'Tamamlandı';
            }
            // 2. Durum: Erişim Yetkisi Yok (Antrenörün grubu değil)
            elseif (!$grp['can_access']) {
                $cardStyle = 'bg-light border border-dashed opacity-50'; // Silik
                $statusIcon = '<i class="fa-solid fa-lock text-muted"></i>';
                $statusText = 'Yetkiniz Yok';
                $isClickable = false;
            }
            // 3. Durum: Bugün Ders Yok (Ama Yönetici Görüyor)
            elseif (!$grp['is_lesson_day']) {
                // Sadece yönetici buraya düşer (Antrenör için yukarısı geçerli olurdu)
                $cardStyle = 'bg-warning bg-opacity-10 border border-warning';
                $statusIcon = '<i class="fa-solid fa-calendar-plus text-warning"></i>';
                $statusText = 'Ekstra / Telafi';
                $timeHtml = '<div class="badge bg-warning text-dark border border-warning border-opacity-25">Saat Belirsiz</div>';
            }

            if($isActive) $cardStyle .= ' ring-2';
        ?>
        
        <div class="col-6 col-md-4 col-lg-3">
            <?php if($isClickable): ?>
                <a href="index.php?page=attendance&date=<?= $selectedDate ?>&group_id=<?= $grp['GroupID'] ?>" class="text-decoration-none">
            <?php else: ?>
                <div style="cursor: not-allowed;">
            <?php endif; ?>

                <div class="card h-100 shadow-sm <?= $cardStyle ?> hover-scale transition-all">
                    <div class="card-body text-center p-3">
                        <div class="mb-2 fs-4 text-primary">
                            <i class="fa-solid fa-people-group"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2 text-truncate"><?= htmlspecialchars($grp['GroupName']) ?></h6>
                        
                        <div class="mb-3">
                            <?= $timeHtml ?>
                        </div>

                        <div class="d-flex justify-content-center align-items-center small mt-auto">
                            <span class="me-2"><?= $statusIcon ?></span>
                            <span class="fw-bold text-secondary" style="font-size: 0.8rem;">
                                <?= $statusText ?>
                            </span>
                        </div>
                    </div>
                </div>

            <?php if($isClickable): ?></a><?php else: ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if(isset($_GET['group_id']) && !empty($_GET['group_id'])): 
        $groupId = $_GET['group_id'];
        
        // Veritabanı bağlantısı ve öğrenci listesi çekimi
        $db = (new Database())->getConnection();
        $sql = "SELECT s.StudentID, s.FullName, s.RemainingSessions, 
                       (SELECT IsPresent FROM Attendance WHERE StudentID = s.StudentID AND [Date] = ? AND GroupID = ?) as TodayStatus
                FROM Students s 
                WHERE s.GroupID = ? AND s.IsActive = 1 
                ORDER BY s.FullName ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$selectedDate, $groupId, $groupId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $selectedGroupName = "Grup";
        foreach($groups as $g) { if($g['GroupID'] == $groupId) $selectedGroupName = $g['GroupName']; }
    ?>

    <div class="card border-0 shadow rounded-4 mb-5" id="studentListSection">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center rounded-top-4">
             <h6 class="mb-0 fw-bold"><i class="fa-solid fa-list-check me-2"></i><?= htmlspecialchars($selectedGroupName) ?></h6>
             <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="selectAll"><label class="form-check-label text-white small" for="selectAll">Tümü</label></div>
        </div>

        <form action="index.php?page=attendance_save" method="POST">
            <input type="hidden" name="group_id" value="<?= $groupId ?>">
            <input type="hidden" name="date" value="<?= $selectedDate ?>">
            
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="bg-light text-muted small">
                        <tr>
                            <th class="ps-4">Öğrenci Adı</th>
                            <th class="text-center">Kalan</th>
                            <th class="text-center">Durum</th>
                            <th class="text-end pe-4">Katılım</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): 
                            $rem = $s['RemainingSessions']; 
                            $isChecked = ($s['TodayStatus'] == 1) ? 'checked' : ''; 
                            $rowClass = $rem <= 0 ? 'bg-danger bg-opacity-10' : '';
                            $badgeColor = $rem <= 0 ? 'bg-danger' : ($rem <= 2 ? 'bg-warning text-dark' : 'bg-success');
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($s['FullName']) ?></td>
                            
                            <td class="text-center">
                                <span class="badge <?= $badgeColor ?> rounded-pill"><?= $rem ?></span>
                            </td>
                            
                            <td class="text-center small">
                                <?= $isChecked ? '<span class="text-success fw-bold">Geldi</span>' : '<span class="opacity-50">-</span>' ?>
                            </td>
                            
                            <td class="text-end pe-4">
                                <div class="form-check form-switch d-inline-block">
                                    <input type="hidden" name="status[<?= $s['StudentID'] ?>]" value="0">
                                    <input class="form-check-input attendance-checkbox shadow-sm" type="checkbox" name="status[<?= $s['StudentID'] ?>]" value="1" style="width:3em;height:1.5em;cursor:pointer;" <?= $isChecked ?>>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-3 text-end rounded-bottom-4">
                <button type="submit" class="btn btn-success px-5 fw-bold shadow">Kaydet</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('studentListSection').scrollIntoView({ behavior: 'smooth' });
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.attendance-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
    <?php endif; ?>

</div>

<style>
    .hover-scale:hover { transform: translateY(-3px); }
    .transition-all { transition: all 0.3s ease; }
    .ring-2 { box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25) !important; }
</style>