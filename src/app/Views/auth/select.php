<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Seçimi | Spor Okulu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; height: 100vh; display: flex; align-items: center; }
        .choice-card { border-radius: 20px; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; }
        .choice-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
        .icon-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Hoş Geldiniz</h2>
        <p class="text-muted">Devam etmek için giriş türünüzü seçiniz</p>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-4 mb-4">
            <a href="index.php?page=parent_login" class="card choice-card border-0 shadow-sm p-4 text-center">
                <div class="icon-circle bg-primary text-white">
                    <i class="fa-solid fa-family-group fs-2"></i>
                </div>
                <h4 class="fw-bold">Veli Girişi</h4>
                <p class="small text-muted">Öğrenci takibi, yoklama ve aidat bilgileri için.</p>
            </a>
        </div>

        <div class="col-md-4 mb-4">
            <a href="index.php?page=admin_login_form" class="card choice-card border-0 shadow-sm p-4 text-center">
                <div class="icon-circle bg-dark text-white">
                    <i class="fa-solid fa-user-shield fs-2"></i>
                </div>
                <h4 class="fw-bold">Yönetici Girişi</h4>
                <p class="small text-muted">Kulüp yönetimi, kayıtlar ve finansal işlemler için.</p>
            </a>
        </div>
    </div>
</div>

</body>
</html>