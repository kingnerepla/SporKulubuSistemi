<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - KulüpSis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .logo-icon { width: 70px; height: 70px; background: #0d6efd; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 20px; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-icon">
        <i class="fa-solid fa-trophy"></i>
    </div>
    <h3 class="text-center mb-4 fw-bold text-dark">KulüpSis Giriş</h3>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center">
            <?php 
                if($_GET['error'] == 'invalid') echo '<i class="fa-solid fa-circle-xmark me-1"></i> E-posta veya şifre hatalı!';
                elseif($_GET['error'] == 'inactive') echo '<i class="fa-solid fa-user-lock me-1"></i> Hesabınız pasif durumda.';
                elseif($_GET['error'] == 'empty') echo 'Lütfen tüm alanları doldurun.';
                else echo 'Bir hata oluştu.';
            ?>
        </div>
    <?php endif; ?>

    <form action="index.php?page=login_submit" method="POST">
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">E-POSTA ADRESİ</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-secondary"></i></span>
                <input type="email" name="email" class="form-control" placeholder="ornek@mail.com" required>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label text-muted small fw-bold">ŞİFRE</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-secondary"></i></span>
                <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Giriş Yap</button>
        
        <div class="text-center mt-3">
            <small class="text-muted">Şifrenizi mi unuttunuz? Yöneticinize başvurun.</small>
        </div>
    </form>
</div>

</body>
</html>