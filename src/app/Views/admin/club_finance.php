<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-uppercase fw-bold opacity-75">Net Kasa</div>
                        <div class="h3 fw-bold mb-0"><?= number_format($balance, 2, ',', '.') ?> ₺</div>
                    </div>
                    <i class="fa-solid fa-wallet fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-uppercase fw-bold opacity-75">Giderler</div>
                        <div class="h3 fw-bold mb-0"><?= number_format($totalExpense, 2, ',', '.') ?> ₺</div>
                    </div>
                    <i class="fa-solid fa-arrow-trend-down fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-secondary text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-uppercase fw-bold opacity-75">Sistem Maliyeti</div>
                        <div class="h3 fw-bold mb-0"><?= number_format($systemCost, 2, ',', '.') ?> ₺</div>
                    </div>
                    <i class="fa-solid fa-server fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check me-2 text-success"></i>Tahsilat Listesi</h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" id="tableSearch" class="form-control border-start-0 ps-0" placeholder="Öğrenci adı ile arama yapın...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-0 border-bottom-0 ps-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-all">
                Tümü <span class="badge bg-secondary ms-1"><?= count($students) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-danger" data-bs-toggle="tab" data-bs-target="#tab-overdue">
                <i class="fa-solid fa-circle-exclamation me-1"></i>Bakiye Bitenler
                <span class="badge bg-danger ms-1"><?= count(array_filter($students, fn($s) => $s['status'] == 'overdue')) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-warning" data-bs-toggle="tab" data-bs-target="#tab-upcoming">
                Az Kalanlar
            </button>
        </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-bottom-4 border-top-0">
        <div class="card-body p-0">
            <div class="tab-content">
                
                <div class="tab-pane fade show active" id="tab-all">
                    <?php renderTable($students); ?>
                </div>

                <div class="tab-pane fade" id="tab-overdue">
                    <?php 
                        $overdueStudents = array_filter($students, fn($s) => $s['status'] == 'overdue');
                        renderTable($overdueStudents); 
                    ?>
                </div>

                <div class="tab-pane fade" id="tab-upcoming">
                    <?php 
                        $upcomingStudents = array_filter($students, fn($s) => $s['status'] == 'upcoming');
                        renderTable($upcomingStudents); 
                    ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php 
// TABLO OLUŞTURMA FONKSİYONU
function renderTable($data) {
    if (empty($data)) {
        echo '<div class="text-center py-5 text-muted"><i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i><p>Kayıt bulunamadı.</p></div>';
        return;
    }
?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 student-table">
            <thead class="bg-light text-uppercase small text-muted">
                <tr>
                    <th class="ps-4 py-3">Öğrenci Adı</th>
                    <th>Grup</th>
                    <th>Paket Tipi</th>
                    <th class="text-center">Kalan Hak</th>
                    <th>Son İşlem</th>
                    <th class="text-end pe-4">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $s): ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark"><?= htmlspecialchars($s['FullName']) ?></div>
                        <div class="x-small text-muted">ID: #<?= $s['StudentID'] ?></div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border fw-normal">
                            <?= htmlspecialchars($s['GroupName'] ?? '-') ?>
                        </span>
                    </td>
                    <td>
                        <div class="small">
                            <?php 
                                $std = $s['StandardSessions'];
                                if($std == 8) echo 'Standart (8)';
                                elseif($std == 12) echo 'Standart (12)';
                                elseif($std >= 20) echo 'Performans (' . $std . ')';
                                else echo $std . ' Ders';
                            ?>
                        </div>
                        <div class="x-small text-muted"><?= number_format($s['PackageFee'], 0) ?> ₺</div>
                    </td>
                    <td class="text-center">
                        <?php 
                            $rem = (int)$s['RemainingSessions'];
                            $bg = ($rem <= 0) ? 'bg-danger' : (($rem <= 2) ? 'bg-warning text-dark' : 'bg-success');
                        ?>
                        <span class="badge <?= $bg ?> rounded-pill px-3 py-2 fs-6 shadow-sm">
                            <?= $rem ?>
                        </span>
                    </td>
                    <td>
                        <div class="small text-muted">
                            <?php if($s['LastPaymentDate']): ?>
                                <i class="fa-regular fa-clock me-1"></i><?= date('d.m.Y', strtotime($s['LastPaymentDate'])) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <button type="button" class="btn btn-sm btn-dark shadow-sm fw-bold px-3"
                                data-bs-toggle="modal" 
                                data-bs-target="#paymentModal"
                                data-id="<?= $s['StudentID'] ?>"
                                data-name="<?= htmlspecialchars($s['FullName']) ?>"
                                data-fee="<?= number_format($s['PackageFee'], 0, '', '') ?>"
                                data-sessions="<?= $s['StandardSessions'] ?>">
                            <i class="fa-solid fa-wallet me-2"></i>Tahsil Et
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php } ?>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=payment_store" method="POST">
                <input type="hidden" name="student_id" id="payStudentId">
                
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-money-bill me-2"></i>Paket Yükleme / Tahsilat</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold text-dark" id="payStudentName">Öğrenci Adı</h5>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Tahsil Edilen (₺)</label>
                            <input type="number" name="amount" id="payAmount" class="form-control fs-5 fw-bold text-success" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1 text-primary">Yüklenecek Ders</label>
                            <input type="number" name="sessions_to_add" id="paySessions" class="form-control fw-bold border-primary text-primary" required>
                            <div class="form-text x-small">Varsayılan paket</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Ödeme Yöntemi</label>
                        <select name="method" class="form-select">
                            <option value="cash">Nakit</option>
                            <option value="credit_card">Kredi Kartı</option>
                            <option value="bank_transfer">Havale / EFT</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Açıklama</label>
                        <input type="text" name="description" class="form-control" placeholder="Örn: Aidat Ödemesi">
                        <input type="hidden" name="payment_date" value="<?= date('Y-m-d') ?>">
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                        Onayla ve Yükle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. MODAL VERİ AKTARIMI
        const paymentModal = document.getElementById('paymentModal');
        paymentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('payStudentId').value = button.getAttribute('data-id');
            document.getElementById('payStudentName').textContent = button.getAttribute('data-name');
            document.getElementById('payAmount').value = button.getAttribute('data-fee');
            document.getElementById('paySessions').value = button.getAttribute('data-sessions');
        });

        // 2. CANLI ARAMA (Tabloda Filtreleme)
        const searchInput = document.getElementById('tableSearch');
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const activeTab = document.querySelector('.tab-pane.active');
            const rows = activeTab.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const name = row.querySelector('td:first-child').textContent.toLowerCase();
                if (name.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>

<style>
    .x-small { font-size: 0.7rem; }
    .nav-tabs .nav-link { color: #6c757d; border: none; border-bottom: 2px solid transparent; }
    .nav-tabs .nav-link.active { color: #000; border-bottom: 2px solid #0d6efd; background: transparent; }
    .nav-tabs .nav-link:hover { border-bottom: 2px solid #e9ecef; }
</style>