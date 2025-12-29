<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-secondary mb-1">
                <i class="fa-solid fa-box-archive me-2"></i>Eski Öğrenci Arşivi
            </h3>
            <p class="text-muted small mb-0">Kaydı dondurulmuş veya ayrılmış öğrenciler.</p>
        </div>
        <a href="index.php?page=students" class="btn btn-outline-primary shadow-sm">
            <i class="fa-solid fa-arrow-left me-2"></i>Aktif Öğrencilere Dön
        </a>
    </div>

    <div class="card border-0 shadow-sm border-top border-secondary border-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Öğrenci / Kimlik</th>
                            <th>Veli / İletişim</th>
                            <th>Ayrıldığı Grup</th>
                            <th class="text-end pe-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($students)): ?>
                            <?php foreach($students as $s): ?>
                            <tr class="opacity-75">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['FullName']) ?></div>
                                    <small class="text-muted">ID: #<?= $s['StudentID'] ?></small>
                                </td>
                                <td>
                                    <div class="text-dark small"><?= htmlspecialchars($s['ParentName'] ?? '-') ?></div>
                                    <div class="text-muted extra-small"><?= $s['ParentPhone'] ?? '-' ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        <?= $s['GroupName'] ?? 'Grup Yok' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="index.php?page=student_restore&id=<?= $s['StudentID'] ?>" 
                                       class="btn btn-sm btn-success shadow-sm" 
                                       onclick="return confirm('Bu öğrenciyi tekrar aktif listeye taşımak istiyor musunuz?')"
                                       title="Geri Yükle">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Aktif Yap
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="fa-solid fa-folder-open fa-3x opacity-25"></i></div>
                                    <div class="text-muted">Arşivde hiç öğrenci bulunmuyor.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>