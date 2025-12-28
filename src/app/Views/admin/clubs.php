<?php foreach($clubs as $c): 
    // Sütun isimlerini küçük harfe normalize et
    $c = array_change_key_case($c, CASE_LOWER);
    $clubId = $c['clubid'] ?? $c['id'];
    $clubName = $c['clubname'] ?? $c['name'];
?>
<div class="col-xl-4 col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
            <div class="mb-3">
                <i class="fa-solid fa-building-columns fa-3x text-primary opacity-25"></i>
            </div>
            <h5 class="fw-bold text-dark"><?php echo htmlspecialchars($clubName); ?></h5>
            <hr>
            <div class="d-grid gap-2">
                <a href="index.php?page=select_club&id=<?php echo $clubId; ?>&name=<?php echo urlencode($clubName); ?>" 
                   class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Kulübü Denetle
                </a>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>