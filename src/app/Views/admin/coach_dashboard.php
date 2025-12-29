<tbody>
    <?php if (!empty($todayTrainings)): ?>
        <?php foreach ($todayTrainings as $training): ?>
            <tr>
                <td class="ps-4 fw-bold"><?php echo date('H:i', strtotime($training['StartTime'])); ?></td>
                <td><?php echo htmlspecialchars($training['GroupName']); ?></td>
                <td><?php echo htmlspecialchars($training['Location'] ?? 'Belirtilmedi'); ?></td>
                <td class="text-center">
                    <span class="badge bg-soft-info text-info">Aktif</span>
                </td>
                <td class="text-end pe-4">
                    <a href="index.php?page=attendance&group_id=<?php echo $training['GroupID']; ?>" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="fa-solid fa-check-to-slot me-1"></i> Yoklama Al
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">Bugün için planlanmış bir antrenmanınız bulunmuyor.</td>
        </tr>
    <?php endif; ?>
</tbody>