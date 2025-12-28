<tr>
    <td>
        <?php if(!empty($club['LogoPath'])): ?>
            <img src="<?php echo $club['LogoPath']; ?>" alt="Logo" width="50" height="50" class="rounded-circle border">
        <?php else: ?>
            <i class="fa-solid fa-shield-halved fa-2x text-muted"></i>
        <?php endif; ?>
    </td>
    <td><strong><?php echo htmlspecialchars($club['ClubName']); ?></strong></td>
    </tr>

<form action="index.php?page=club_store" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label>Kulüp Logosu</label>
        <input type="file" name="club_logo" class="form-control" accept="image/*">
    </div>
    </form>

<form action="index.php?page=club_update" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label>Yeni Logo (Opsiyonel)</label>
        <input type="file" name="club_logo" class="form-control" accept="image/*">
        <small class="text-muted">Değiştirmek istemiyorsanız boş bırakın.</small>
    </div>
    </form>