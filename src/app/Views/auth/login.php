<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | Spor CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .btn-primary { background: #3b82f6; border: none; padding: 12px; }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="text-center mb-4">
            <i class="fa-solid fa-trophy fa-3x text-primary mb-3"></i>
            <h4 class="fw-bold">Spor CRM Giriş</h4>
            <p class="text-muted small">Lütfen bilgilerinizi giriniz</p>
        </div>

        <form action="index.php?page=auth_check" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">E-posta Adresi</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="admin@example.com" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Şifre</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="••••••••" required>
                </div>
            </div>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger py-2 small mb-3 text-center">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>E-posta veya şifre hatalı!
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Giriş Yap</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php?page=login" class="text-decoration-none small text-muted">
                <i class="fa-solid fa-arrow-left"></i> Giriş Seçimine Dön
            </a>
        </div>
    </div>
</body>
</html>