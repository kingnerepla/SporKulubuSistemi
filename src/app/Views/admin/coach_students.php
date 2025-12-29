<div class="container-fluid px-4 mt-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-white rounded d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-dark">
                    <i class="fa-solid fa-user-graduate me-2 text-primary"></i>Öğrencilerim
                </h4>
                <p class="text-muted small mb-0">Sorumlu olduğunuz gruplardaki aktif öğrenci listesi.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary px-3 py-2 shadow-sm rounded-pill">
                    <i class="fa-solid fa-users me-1"></i> Toplam: <?php echo count($students); ?> Öğrenci
                </span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3" style="min-width: 250px;">Sporcu Bilgisi</th>
                            <th>Grup</th>
                            <th>Yaş</th>
                            <th style="min-width: 250px;">Yönetici / Sağlık Notları</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $std): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary bg-opacity-10 p-2 rounded-circle me-3 text-center d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fa-solid fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($std['FullName']); ?></div>
                                                <div class="text-muted small" style="font-size: 0.7rem;">ID: #<?php echo $std['StudentID']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            <i class="fa-solid fa-layer-group me-1 small"></i>
                                            <?php echo htmlspecialchars($std['GroupName']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            if(!empty($std['BirthDate'])) {
                                                // Yaş hesaplama
                                                $birthDate = new DateTime($std['BirthDate']);
                                                $today = new DateTime('today');
                                                $age = $birthDate->diff($today)->y;
                                                echo '<div class="d-flex align-items-center">';
                                                echo '<i class="fa-solid fa-cake-candles text-primary me-2 opacity-50"></i>';
                                                echo '<span class="text-dark fw-bold">' . $age . ' Yaş</span>';
                                                echo '</div>';
                                            } else {
                                                echo '<span class="text-muted small italic">Girilmemiş</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($std['Notes'])): ?>
                                            <div class="p-2 rounded bg-warning bg-opacity-10 border-start border-3 border-warning" style="max-width: 300px;">
                                                <small class="text-dark d-block" style="line-height: 1.2;">
                                                    <i class="fa-solid fa-circle-exclamation text-warning me-1"></i>
                                                    <?php echo htmlspecialchars($std['Notes']); ?>
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small italic opacity-50">- Not yok -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary border-0" title="Gelişim Takibi">
                                                <i class="fa-solid fa-chart-line"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary border-0" title="İletişim Bilgisi">
                                                <i class="fa-solid fa-address-book"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fa-solid fa-users-slash fa-4x text-light mb-3"></i>
                                        <h5 class="text-muted">Size atanmış öğrenci bulunmuyor.</h5>
                                        <p class="text-muted small">Eğitmeni olduğunuz gruplara öğrenci eklendiğinde burada listelenecektir.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Tasarım İyileştirmeleri */
    .bg-info-subtle { background-color: #e0f2fe !important; color: #0369a1 !important; }
    .table thead th { font-size: 0.7rem; letter-spacing: 0.05em; border-top: none; font-weight: 700; }
    .table tbody tr:hover { background-color: #f8fafc; transition: background-color 0.2s ease; }
    .btn-group .btn { border-radius: 6px; padding: 0.25rem 0.5rem; transition: all 0.2s; }
    .btn-group .btn:hover { background-color: #e2e8f0; }
    .italic { font-style: italic; }
</style>