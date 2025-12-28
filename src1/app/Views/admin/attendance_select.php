<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa-solid fa-clipboard-check me-2"></i>Yoklama Al</h5>
            </div>
            <div class="card-body">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="attendance_take">
                    
                    <div class="mb-3">
                        <label class="form-label">Hangi Grup?</label>
                        <select name="group_id" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach($groups as $group): ?>
                                <option value="<?php echo $group['GroupID']; ?>"><?php echo $group['GroupName']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tarih</label>
                        <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Listeyi Getir <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>