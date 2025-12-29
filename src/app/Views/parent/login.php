<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veli Giriş Sistemi | Spor Okulu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; border-radius: 20px; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .btn-login { border-radius: 10px; padding: 12px; font-weight: bold; background: #764ba2; border: none; }
        .btn-login:hover { background: #5a377d; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark">Veli Bilgi Sistemi</h3>
        <p class="text-muted small">Çocuğunuzun gelişimini buradan takip edin.</p>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger small py-2 text-center">Giriş bilgileri hatalı!</div>
    <?php endif; ?>
    <form action="index.php?page=parent_auth" method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">Kayıtlı Telefon Numarası</label>
            <input type="text" name="phone" class="form-control form-control-lg" placeholder="5XXXXXXXXX" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Şifre</label>
            <input type="password" name="password" class="form-control form-control-lg" placeholder="******" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-login shadow">Giriş Yap</button>
    </form>
    <div class="text-center mt-3">
        <a href="index.php?page=login" class="text-decoration-none small text-muted">
            <i class="fa-solid fa-arrow-left"></i> Giriş Seçimine Dön
        </a>
    </div>
    <div class="text-center mt-4 text-muted small">
        Şifrenizi unuttuysanız antrenörünüzle iletişime geçin.
    </div>
</div>

</body>
</html>