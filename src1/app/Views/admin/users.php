<?php
// ROL ÇEVİRİ LİSTESİ (Ekranda güzel görünsün diye)
$roleTr = [
    'SystemAdmin' => '<span class="badge bg-dark">Süper Yönetici</span>',
    'ClubAdmin'   => '<span class="badge bg-primary">Kulüp Yöneticisi</span>',
    'Trainer'     => '<span class="badge bg-warning text-dark">Antrenör</span>',
    'Parent'      => '<span class="badge bg-info text-dark">Veli</span>',
    'Student'     => '<span class="badge bg-light text-dark border">Öğrenci</span>'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-users-gear me-2"></i>Sistem Yöneticileri ve Personel</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fa-solid fa-user-plus"></i> Yeni Kullanıcı Ekle
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <?php if($_GET['success'] == 'created'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check me-2"></i>Yeni kullanıcı başarıyla oluşturuldu.</div>
    <?php elseif($_GET['success'] == 'deleted'): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-trash me-2"></i>Kullanıcı tamamen silindi.</div>
    <?php elseif($_GET['success'] == 'restored'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-rotate-left me-2"></i>Kullanıcı tekrar aktif hale getirildi.</div>
    <?php else: ?>
        <div class="alert alert-success">İşlem başarıyla tamamlandı.</div>
    <?php endif; ?>
<?php endif; ?>

<?php if(isset($_GET['warning']) && $_GET['warning'] == 'passived'): ?>
    <div class="alert alert-warning border-start border-warning border-4">
        <h5 class="alert-heading"><i class="fa-solid fa-triangle-exclamation me-2"></i>Kullanıcı Silinemedi, Pasife Alındı!</h5>
        <p class="mb-1">Bu kullanıcının sistemde bağlı olduğu aktif kayıtlar (Grup, Öğrenci vb.) bulunmaktadır:</p>
        
        <div class="bg-white p-2 rounded text-dark border mt-2">
            <?php 
                if(isset($_SESSION['passivate_reason'])) {
                    echo $_SESSION['passivate_reason'];
                    unset($_SESSION['passivate_reason']); // Mesajı gösterdikten sonra temizle
                } else {
                    echo "Sistemsel veri bütünlüğü sebebiyle silinemedi.";
                }
            ?>
        </div>
        <p class="mb-0 mt-2 small text-muted">Veri kaybını önlemek için kullanıcı silinmek yerine sisteme girişi engellendi.</p>
    </div>
<?php elseif(isset($_GET['error']) && $_GET['error'] == 'self_delete'): ?>
    <div class="alert alert-danger">Kendi hesabınızı silemezsiniz!</div>
<?php elseif(isset($_GET['error']) && $_GET['error'] == 'email_exists'): ?>
    <div class="alert alert-danger">Bu E-posta adresi ile kayıtlı bir kullanıcı zaten var.</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Email</th>
                        <th>Rolü</th>
                        <th>Bağlı Olduğu Kulüp</th>
                        <th>Durum</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($users as $user): ?>
                
                <tr class="<?php echo ($user['IsActive'] == 0) ? 'table-secondary text-muted' : ''; ?>">
                    
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($user['FullName'] ?? ''); ?></div>
                        <small class="text-muted" style="font-size: 0.75rem;">Kayıt: <?php echo date('d.m.Y', strtotime($user['CreatedAt'])); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($user['Email'] ?? ''); ?></td>
                    <td>
                        <?php 
                            $roleKey = $user['RoleName'] ?? '';
                            echo $roleTr[$roleKey] ?? $roleKey; 
                        ?>
                    </td>
                    <td>
                        <?php if(!empty($user['ClubName'])): ?>
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-building me-1"></i> <?php echo htmlspecialchars($user['ClubName']); ?>
                            </span>
                        <?php else: ?>
                            <?php if($user['RoleName'] == 'SystemAdmin'): ?>
                                <span class="badge bg-dark">Sistem Geneli</span>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(isset($user['IsActive']) && $user['IsActive'] == 1): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Pasif / Engelli</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if($user['IsActive'] == 1): ?>
                            <a href="index.php?page=user_delete&id=<?php echo $user['UserID']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('İŞLEM ONAYI:\n\nBu kullanıcıyı listeden kaldırmak üzeresiniz.\n\n- Eğer yönettiği grup/öğrenci YOKSA tamamen silinir.\n- Eğer geçmiş kayıtları VARSA veri kaybını önlemek için sadece PASİFE alınır.\n\nDevam edilsin mi?');"
                               title="Sil veya Pasife Al">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        
                        <?php else: ?>
                            <a href="index.php?page=user_restore&id=<?php echo $user['UserID']; ?>" 
                               class="btn btn-sm btn-success"
                               onclick="return confirm('Bu kullanıcıyı tekrar aktif etmek ve sisteme giriş yetkisi vermek istiyor musunuz?');"
                               title="Tekrar Aktif Et / Geri Al">
                                <i class="fa-solid fa-rotate-left"></i> Geri Al
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($users)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Görüntülenecek kullanıcı bulunamadı.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=user_store" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Yeni Yönetici / Personel Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Adresi</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Şifre</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kullanıcı Rolü</label>
                        <select name="role_id" class="form-select" id="roleSelect" required onchange="toggleClubSelect()">
                             <?php if($_SESSION['role'] == 'SystemAdmin'): ?>
                                <option value="1">Süper Yönetici (System Admin)</option>
                                <option value="2" selected>Kulüp Yöneticisi</option>
                             <?php endif; ?>
                             
                             <option value="3">Antrenör</option>
                             </select>
                    </div>

                    <?php if($_SESSION['role'] == 'SystemAdmin'): ?>
                    <div class="mb-3" id="clubSelectDiv">
                        <label class="form-label fw-bold text-primary">Hangi Kulübe Atanacak?</label>
                        <select name="club_id" class="form-select">
                            <option value="">-- Bir Kulüp Seçiniz --</option>
                            <?php 
                                // Controller'dan gelen $clubs değişkenini kullanıyoruz
                                if(isset($clubs) && !empty($clubs)) {
                                    foreach($clubs as $c) {
                                        echo '<option value="'.$c['ClubID'].'">'.htmlspecialchars($c['ClubName']).'</option>';
                                    }
                                }
                            ?>
                        </select>
                        <small class="text-muted d-block mt-1">
                            <i class="fa-solid fa-info-circle"></i> Eğer "Süper Yönetici" rolü seçerseniz bu alanı boş bırakın. 
                            Diğer roller için zorunludur.
                        </small>
                    </div>
                    <?php endif; ?>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleClubSelect() {
    var role = document.getElementById('roleSelect').value;
    var clubDiv = document.getElementById('clubSelectDiv');
    
    // Eğer clubDiv varsa (Sadece SystemAdmin'de vardır)
    if(clubDiv) {
        if (role == '1') { // Süper Yönetici (Role ID 1)
            clubDiv.style.display = 'none'; // Kulüp seçmeye gerek yok
        } else {
            clubDiv.style.display = 'block'; // Kulüp seçmek zorunda
        }
    }
}

// Sayfa yüklendiğinde de çalışsın (Varsayılan seçim için)
document.addEventListener("DOMContentLoaded", function() {
    toggleClubSelect();
});
</script>