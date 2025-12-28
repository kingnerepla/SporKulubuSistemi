<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-users me-2"></i>Öğrenci Listesi</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fa-solid fa-plus"></i> Yeni Öğrenci Ekle
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <?php if($_GET['success'] == '1'): ?>
        <div class="alert alert-success">Yeni öğrenci başarıyla eklendi!</div>
    <?php elseif($_GET['success'] == 'updated'): ?>
        <div class="alert alert-success">Öğrenci bilgileri güncellendi!</div>
    <?php elseif($_GET['success'] == 'deleted'): ?>
        <div class="alert alert-warning">Öğrenci pasife alındı (Listeden çıkarıldı)!</div>
    <?php elseif($_GET['success'] == 'restored'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check me-2"></i>Öğrenci tekrar aktif hale getirildi!</div>
    <?php endif; ?>
<?php endif; ?>

<div class="card shadow-sm mb-4 bg-light">
    <div class="card-body py-3">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">İsim Ara</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Örn: Ahmet Yılmaz...">
                </div>
            </div>
            
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Grup / Takım</label>
                <select id="groupFilter" class="form-select">
                    <option value="">Tümü</option>
                    <?php foreach($groups as $group): ?>
                        <option value="<?php echo $group['GroupName']; ?>"><?php echo $group['GroupName']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Durum</label>
                <select id="statusFilter" class="form-select">
                    <option value="">Tümü</option>
                    <option value="Aktif" selected>Sadece Aktifler</option>
                    <option value="Pasif">Sadece Pasifler</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="studentTable">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="25%">Ad Soyad</th>
                        <th width="15%">Grup</th>
                        <th width="15%">D.Tarihi</th>
                        <th width="20%">Veli</th>
                        <th width="10%">Durum</th>
                        <th width="10%">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $index => $student): ?>
                        <?php 
                            $isActive = $student['IsActive'] == 1;
                            $rowClass = !$isActive ? 'table-secondary text-muted' : '';
                            $statusText = $isActive ? 'Aktif' : 'Pasif';
                        ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td><?php echo $index + 1; ?></td>
                        
                        <td class="student-name">
                            <strong><?php echo htmlspecialchars($student['FullName']); ?></strong>
                            <?php if(!$isActive): ?>
                                <span class="badge bg-danger ms-1" style="font-size: 0.6em;">AYRILDI</span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="student-group"><?php echo htmlspecialchars($student['GroupName']); ?></td>
                        
                        <td><?php echo $student['BirthDate'] ? date('d.m.Y', strtotime($student['BirthDate'])) : '-'; ?></td>
                        
                        <td>
                            <?php if($student['ParentName']): ?>
                                <?php echo htmlspecialchars($student['ParentName']); ?><br>
                                <small class="text-muted"><?php echo $student['ParentPhone']; ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>

                        <td class="student-status">
                            <?php if($isActive): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <div class="d-flex gap-1">
                                <a href="index.php?page=student_detail&id=<?php echo $student['StudentID']; ?>" class="btn btn-sm btn-outline-primary" title="Detay">
                                    <i class="fa-solid fa-id-card"></i>
                                </a>

                                <?php if($isActive): ?>
                                    <a href="index.php?page=student_edit&id=<?php echo $student['StudentID']; ?>" class="btn btn-sm btn-outline-warning text-dark" title="Düzenle">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="index.php?page=student_delete&id=<?php echo $student['StudentID']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Pasife almak istediğinize emin misiniz?');" title="Sil">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="index.php?page=student_restore&id=<?php echo $student['StudentID']; ?>" 
                                       class="btn btn-sm btn-success" 
                                       onclick="return confirm('Öğrenciyi tekrar takıma dahil etmek istiyor musunuz?');" title="Geri Al">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <tr id="noResultRow" style="display: none;">
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fa-solid fa-search fa-2x mb-2"></i><br>
                            Aradığınız kriterlere uygun öğrenci bulunamadı.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=student_store" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Yeni Öğrenci Kaydı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Öğrenci Adı Soyadı</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Grubu / Takımı</label>
                        <select name="group_id" class="form-select" required>
                            <?php foreach($groups as $group): ?>
                                <option value="<?php echo $group['GroupID']; ?>"><?php echo $group['GroupName']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Doğum Tarihi</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Velisi (Varsa)</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- Seçiniz --</option>
                                <?php foreach($parents as $parent): ?>
                                    <option value="<?php echo $parent['UserID']; ?>"><?php echo $parent['FullName']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
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
document.addEventListener("DOMContentLoaded", function() {
    // Input elementlerini al
    const searchInput = document.getElementById('searchInput');
    const groupFilter = document.getElementById('groupFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('#studentTable tbody tr:not(#noResultRow)');
    const noResultRow = document.getElementById('noResultRow');

    // Filtreleme Fonksiyonu
    function filterTable() {
        const searchText = searchInput.value.toLocaleLowerCase('tr');
        const selectedGroup = groupFilter.value.toLocaleLowerCase('tr');
        const selectedStatus = statusFilter.value.toLocaleLowerCase('tr');
        let visibleCount = 0;

        tableRows.forEach(row => {
            // Hücrelerdeki metinleri al
            const name = row.querySelector('.student-name').innerText.toLocaleLowerCase('tr');
            const group = row.querySelector('.student-group').innerText.toLocaleLowerCase('tr');
            
            // Durum (Aktif/Pasif) kontrolü için badge içindeki metne bakıyoruz
            // .student-status hücresinin içindeki metni alalım (Aktif veya Pasif yazar)
            const statusText = row.querySelector('.student-status').innerText.trim().toLocaleLowerCase('tr');

            // Kontrol Et
            const matchesName = name.includes(searchText);
            const matchesGroup = selectedGroup === "" || group === selectedGroup;
            
            // Durum Kontrolü (Özel mantık)
            let matchesStatus = true;
            if (selectedStatus === "aktif") {
                matchesStatus = statusText.includes("aktif");
            } else if (selectedStatus === "pasif") {
                matchesStatus = statusText.includes("pasif");
            }

            // Hepsine uyuyorsa göster, yoksa gizle
            if (matchesName && matchesGroup && matchesStatus) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        // Hiç sonuç yoksa mesaj göster
        noResultRow.style.display = visibleCount === 0 ? "" : "none";
    }

    // Olay Dinleyicileri (Tuşa basınca veya seçim yapınca çalışır)
    searchInput.addEventListener('input', filterTable);
    groupFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);

    // Sayfa açılınca varsayılan filtreyi çalıştır (Sadece Aktifler)
    filterTable();
});
</script>