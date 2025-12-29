<div class="container-fluid px-4 mt-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-white rounded d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-dark">
                    <i class="fa-solid fa-user-graduate me-2 text-primary"></i>Öğrencilerim
                </h4>
                <p class="text-muted small mb-0">Antrenörü olduğunuz gruplardaki aktif öğrenci listesi.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary px-3 py-2 shadow-sm">
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
                            <th class="ps-4 py-3" style="min-width: 250px;">Öğrenci Bilgisi</th>
                            <th>Grup</th>
                            <th>Yaş</th>
                            <th style="max-width: 300px;">Eğitmen Notları / Uyarılar</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $std): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary bg-opacity-10 p-2 rounded-circle me-3 text-center" style="width: 40px; height: 40px;">
                                                <i class="fa-solid fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($std['FullName']); ?></div>
                                                <div class="text-muted small">ID: #<?php echo $std['StudentID']; ?></div>
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
                                                $birthDate = new DateTime($std['BirthDate']);
                                                $today = new DateTime('today');
                                                echo '<span class="text-dark fw-medium">' . $birthDate->diff($today)->y . ' Yaş</span>';
                                            } else {
                                                echo '<span class="text-muted small italic">Belirtilmedi</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;">
                                            <?php if (!empty($std['Notes'])): ?>
                                                <span class="small text-dark" title="<?php echo htmlspecialchars($std['Notes']); ?>">
                                                    <i class="fa-solid fa-note-sticky text-warning me-1"></i>
                                                    <?php echo htmlspecialchars($std['Notes']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small italic opacity-50">- Not eklenmemiş -</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-outline-primary btn-sm" title="Gelişim Notu Yaz">
                                                <i class="fa-solid fa-comment-dots"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm" title="Detay">
                                                <i class="fa-solid fa-circle-info"></i>
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
                                        <h5 class="text-muted">Henüz kayıtlı öğrencisiniz bulunmuyor.</h5>
                                        <p class="text-muted small">Atandığınız gruplarda öğrenci olduğunda burada listelenecektir.</p>
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
    .table thead th { font-size: 0.75rem; letter-spacing: 0.05em; border-top: none; }
    .table tbody tr:hover { background-color: #f8fafc; transition: background-color 0.2s ease; }
    .btn-group .btn { border-radius: 8px; margin-left: 4px; }
    .italic { font-style: italic; }
</style>