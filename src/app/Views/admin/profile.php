<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark">Profil Ayarları</h3>
            <p class="text-muted small">Kişisel bilgilerinizi ve giriş şifrenizi buradan güncelleyebilirsiniz.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark shadow-sm border p-2 px-3">
                <i class="fa-solid fa-shield-halved me-1 text-primary"></i> Güvenli Alan
            </span>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    
                    <?php if(isset($_GET['success'])): ?>
                        <div class="alert alert-success border-0 rounded-3 mb-4 d-flex align-items-center">
                            <i class="fa-solid fa-circle-check fs-4 me-3"></i>
                            <div>Bilgileriniz başarıyla güncellendi.</div>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?page=profile_update" method="POST">
                        
                        <div class="text-center mb-5">
                            <div class="position-relative d-inline-block">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px;">
                                    <i class="fa-solid fa-user-gear fa-3x"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['FullName'] ?? 'Kullanıcı'); ?></h5>
                            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">
                                ID: #<?= $user['UserID'] ?? '0' ?>
                            </span>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Ad Soyad</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-signature text-muted"></i></span>
                                    <input type="text" name="full_name" class="form-control bg-light border-start-0" value="<?= htmlspecialchars($user['FullName'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">E-Posta Adresi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control bg-light border-start-0" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Telefon Numarası</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" class="form-control bg-light border-start-0" value="<?= htmlspecialchars($user['Phone'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="p-3 bg-warning bg-opacity-10 rounded-4 border border-warning border-opacity-25">
                                    <label class="form-label fw-bold text-dark small mb-2"><i class="fa-solid fa-lock me-1"></i> Şifre Güncelleme</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                        <input type="text" name="password" class="form-control bg-white border-start-0" placeholder="Yeni şifre (Değiştirmek istemiyorsanız boş bırakın)">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">* Şifreniz en az 4 karakterden oluşmalıdır. Boş bırakırsanız mevcut şifreniz korunur.</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="row g-2">
                            <div class="col-8">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Değişiklikleri Kaydet
                                </button>
                            </div>
                            <div class="col-4">
                                <?php
                                $backLink = isset($_SESSION['parent_logged_in']) ? 'index.php?page=parent_dashboard' : 'index.php?page=dashboard';
                                ?>
                                <a href="<?= $backLink ?>" class="btn btn-light w-100 py-2 border rounded-3 fw-bold text-muted text-decoration-none text-center">
                                    Vazgeç
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus {
        box-shadow: none !important;
        border-color: #0d6efd !important;
    }
    .input-group-text {
        color: #6c757d;
    }
    /* Telefon ve isim alanlarının arka plan rengi için ekleme */
    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>