<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap - Kulüp Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Kulüp Sistemi</h3>
                    
                    <form action="index.php" method="POST">
                        
                        <div class="mb-3">
                            <label for="emailInput" class="form-label">E-posta Adresi</label>
                            <input type="email" name="email" id="emailInput" class="form-control" placeholder="admin@test.com" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="passwordInput" class="form-label">Şifre</label>
                            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="******" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Giriş Yap</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>