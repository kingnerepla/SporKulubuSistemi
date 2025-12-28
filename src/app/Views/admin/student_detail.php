<div class="mb-3">
    <a href="index.php?page=students" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Listeye Dön</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <div class="avatar bg-primary text-white rounded-circle d-flex justify-content-center align-items-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?php echo strtoupper(substr($student['FullName'], 0, 1)); ?>
                </div>
                <h4><?php echo htmlspecialchars($student['FullName']); ?></h4>
                <span class="badge bg-secondary"><?php echo htmlspecialchars($student['GroupName']); ?></span>
                
                <hr>
                
                <div class="text-start">
                    <p><strong><i class="fa-solid fa-cake-candles me-2"></i> Doğum Tarihi:</strong> <br>
                    <?php echo $student['BirthDate'] ? date('d.m.Y', strtotime($student['BirthDate'])) : '-'; ?></p>

                    <p><strong><i class="fa-solid fa-user-group me-2"></i> Veli:</strong> <br>
                    <?php echo $student['ParentName'] ?? '-'; ?></p>

                    <p><strong><i class="fa-solid fa-phone me-2"></i> Telefon:</strong> <br>
                    <?php echo $student['ParentPhone'] ?? '-'; ?></p>
                </div>
            </div>
        </div>

        <div class="card shadow-sm bg-light">
            <div class="card-body text-center">
                <h6>Yoklama Durumu</h6>
                <div class="row">
                    <div class="col-6 border-end">
                        <h3 class="text-success"><?php echo $stats['Var']; ?></h3>
                        <small>Katıldı</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-danger"><?php echo $stats['Yok']; ?></h3>
                        <small>Gelmedi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <i class="fa-solid fa-wallet me-2"></i> Son Ödemeler
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Ay</th>
                            <th>Tutar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $pay): ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($pay['PaymentDate'])); ?></td>
                            <td><?php echo $pay['PaymentMonth']; ?></td>
                            <td><strong>₺<?php echo number_format($pay['Amount'], 2); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($payments)) echo "<tr><td colspan='3' class='text-center text-muted'>Kayıt yok.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="fa-solid fa-clipboard-check me-2"></i> Son Antrenmanlar
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($attendance as $att): ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($att['Date'])); ?></td>
                            <td>
                                <?php if($att['IsPresent']): ?>
                                    <span class="badge bg-success">Geldi</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Gelmedi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($attendance)) echo "<tr><td colspan='2' class='text-center text-muted'>Kayıt yok.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>