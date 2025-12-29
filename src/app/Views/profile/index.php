<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Profil Ayarları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fa-solid fa-user-gear me-2"></i> Profil Bilgilerim</h5>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                        <div class="alert alert-success">Bilgileriniz başarıyla güncellendi.</div>
                    <?php endif; ?>

                    <form action="index.php?page=profile_update" method="POST">
                        <div class="mb-3 text-center">
                            <div class="display-1 text-muted"><i class="fa-solid fa-circle-user"></i></div>
                            <p class="text-muted small">Kullanıcı Kimliği: <strong><?= $user['Identity'] ?></strong></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ad Soyad</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['Name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Yeni Şifre</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($user['Password']) ?>" required>
                            </div>
                            <div class="form-text">Şifreniz en az 4 karakter olmalıdır.</div>
                        </div>

                        <hr>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold">Değişiklikleri Kaydet</button>
                            <?php
                            // Kullanıcı tipine göre geri dönüş linkini belirle
                            $backLink = isset($_SESSION['parent_logged_in']) ? 'index.php?page=parent_dashboard' : 'index.php?page=dashboard';
                            ?>

                            <a href="<?= $backLink ?>" class="btn btn-outline-secondary">Vazgeç ve Dön</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>