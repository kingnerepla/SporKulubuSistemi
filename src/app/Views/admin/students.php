<?php ob_start(); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h3>Öğrenci Yönetimi</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">Yeni Öğrenci Ekle</button>
    </div>

    <div class="card shadow-sm">
        <table class="table m-0">
            <thead>
                <tr>
                    <th>ID</th><th>Ad Soyad</th><th>Grup</th><th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                <tr>
                    <td>#<?php echo $s['student_id']; ?></td>
                    <td><?php echo htmlspecialchars($s['full_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s['GroupName'] ?? 'Atanmamış'); ?></td>
                    <td><button class="btn btn-sm btn-danger">Sil</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=student_store" method="POST">
                <div class="modal-header"><h5 class="modal-title">Yeni Kayıt</h5></div>
                <div class="modal-body">
                    <input type="text" name="full_name" class="form-control" placeholder="Ad Soyad" required>
                    <?php if($_SESSION['role'] === 'SystemAdmin'): ?>
                        <input type="number" name="club_id" class="form-control mt-2" placeholder="Kulüp ID" required>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php 
$content = ob_get_clean(); 
require_once __DIR__ . '/../layouts/admin_layout.php'; 
?>