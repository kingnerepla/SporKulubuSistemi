<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-plus-circle me-2"></i>Yeni Kulüp / Spor Okulu Kaydı</h5>
                </div>
                <div class="card-body">
                    <form action="index.php?page=club_store" method="POST">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Kulüp Tam Adı</label>
                                <input type="text" name="club_name" class="form-control rounded-pill" placeholder="Örn: Yıldız Basketbol Akademi" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Kulüp Yetkilisi Ad Soyad</label>
                                <input type="text" name="admin_name" class="form-control rounded-pill" placeholder="Admin hesabı için" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Yetkili Telefon (Giriş için)</label>
                                <input type="text" name="admin_phone" class="form-control rounded-pill" placeholder="5xx xxx xx xx" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Yönetici E-posta</label>
                                <input type="email" name="admin_email" class="form-control rounded-pill" placeholder="admin@kulupadi.com" required>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                            <a href="index.php?page=dashboard" class="btn btn-light rounded-pill px-4 text-muted">İptal</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Kulübü Tanımla ve Aktivasyon Gönder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>