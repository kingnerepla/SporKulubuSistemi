<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-circle-check fs-4 me-2"></i>
            <div>
                <strong>Başarılı!</strong> Yoklama kayıtları sisteme işlendi ve güncellendi.
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fa-solid fa-calendar-day me-2 text-primary"></i>Bugünkü Antrenman Programım
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4" style="width: 100px;">Saat</th>
                    <th>Grup / Ders Adı</th>
                    <th>Konum</th>
                    <th class="text-center">Durum</th>
                    <th class="text-end pe-4">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($todayTrainings)): ?>
                    <?php foreach ($todayTrainings as $training): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark">
                                    <?php echo date('H:i', strtotime($training['StartTime'])); ?>
                                </span>
                            </td>
                            
                            <td>
                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($training['GroupName']); ?></div>
                                <div class="text-muted small">Antrenman Seansı</div>
                            </td>
                            
                            <td>
                                <i class="fa-solid fa-location-dot me-1 text-danger small"></i>
                                <?php echo htmlspecialchars($training['Location'] ?? 'Belirtilmedi'); ?>
                            </td>

                            <td class="text-center">
                                <?php if (isset($training['AttendanceCount']) && $training['AttendanceCount'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                        <i class="fa-solid fa-check-double me-1"></i> Yoklama Alındı
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                                        <i class="fa-solid fa-clock me-1"></i> Bekliyor
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end pe-4">
                                <?php 
                                    // Eğer yoklama alınmışsa butonun rengini ve metnini değiştirebiliriz
                                    $hasAttendance = (isset($training['AttendanceCount']) && $training['AttendanceCount'] > 0);
                                    $btnClass = $hasAttendance ? 'btn-outline-primary' : 'btn-primary';
                                    $btnText = $hasAttendance ? 'Düzenle' : 'Yoklama Al';
                                ?>
                                <a href="index.php?page=attendance&session_id=<?php echo $training['SessionID']; ?>" 
                                   class="btn <?php echo $btnClass; ?> btn-sm shadow-sm px-3">
                                    <i class="fa-solid fa-clipboard-user me-1"></i> <?php echo $btnText; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-calendar-xmark fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Bugün için planlanmış bir antrenmanınız bulunmuyor.</p>
                                <small>Programda bir hata olduğunu düşünüyorsanız yöneticiye danışın.</small>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Bootstrap 5.3 Subtitle Badge Renkleri (Eski sürümlerde çalışmazsa manuel fallback) */
    .bg-success-subtle { background-color: #d1e7dd !important; color: #0f5132 !important; }
    .bg-warning-subtle { background-color: #fff3cd !important; color: #664d03 !important; }
    .table thead th { font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px; }
    .btn-sm { border-radius: 6px; }
</style>