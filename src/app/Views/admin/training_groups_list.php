<div class="container-fluid">
    <h3 class="fw-bold mb-4">Antrenman ve Yoklama Yönetimi</h3>
    <div class="row">
        <?php foreach($groups as $g): ?>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm border-bottom border-primary border-3">
                <div class="card-body">
                    <h5 class="fw-bold"><?= htmlspecialchars($g['GroupName']) ?></h5>
                    <p class="small text-muted mb-3">Bu grubun aylık takvimini ve yoklamalarını yönetin.</p>
                    <a href="index.php?page=group_calendar&id=<?= $g['GroupID'] ?>" class="btn btn-primary btn-sm w-100 fw-bold">
                        Takvimi Aç
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>