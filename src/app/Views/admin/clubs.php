<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold">Kulüp Yönetimi</h3>
            <p class="text-muted small">Sistemdeki tüm spor kulüplerini buradan yönetebilirsiniz.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addClubModal">
            <i class="fa-solid fa-plus me-2"></i>Yeni Kulüp Ekle
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Kulüp Adı</th>
                            <th>Kayıt Tarihi</th>
                            <th class="text-end pe-4">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($clubs)): ?>
                            <?php foreach($clubs as $c): ?>
                                <tr>
                                    <td class="ps-4 text-muted">#<?php echo $c['ClubID']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($c['ClubName'] ?? ''); ?></td>
                                    <td><small class="text-muted"><?php echo date('d.m.Y', strtotime($c['CreatedAt'] ?? 'now')); ?></small></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <a href="index.php?page=club_detail&id=<?php echo $c['ClubID']; ?>" class="btn btn-primary">
                                                <i class="fa-solid fa-eye me-1"></i> Detay
                                            </a>
                                            <button class="btn btn-outline-secondary"><i class="fa-solid fa-pen"></i></button>
                                            <button class="btn btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Kayıtlı kulüp bulunamadı.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addClubModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Yeni Kulüp Kaydı</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?page=club_store" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kulüp Adı</label>
                        <input type="text" name="club_name" class="form-control" placeholder="Örn: Yıldız Spor Kulübü" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary">Kulübü Oluştur</button>
                </div>
            </form>
        </div>
    </div>
</div>