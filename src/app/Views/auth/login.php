<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - KulüpSis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="card shadow login-card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold"><i class="fa-solid fa-medal"></i> KulüpSis</h2>
            <p class="text-muted">Yönetim Paneli Girişi</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="index.php?page=login_submit" method="POST">
            <div class="mb-3">
                <label class="form-label">Email Adresi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="ornek@kulup.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Şifre</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="******" required>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Giriş Yap</button>
            </div>
        </form>
    </div>
    <div class="card-footer text-center bg-white border-0 mt-3">
        <small class="text-muted">Kulüp Yönetim Sistemi v1.0</small>
    </div>
</div>

</body>
</html>