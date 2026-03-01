<?php require_once 'views/layouts/header.php'; ?>

<div class="mb-4">
    <h2><i class="bi bi-plus-circle me-2"></i>Nhập lô thuốc mới</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="index.php?page=batches&action=store">
                    <div class="mb-3">
                        <label class="form-label">Thuốc <span class="text-danger">*</span></label>
                        <select class="form-select" name="medicine_id" required>
                            <option value="">-- Chọn thuốc --</option>
                            <?php foreach ($medicines as $medicine): ?>
                                <option value="<?php echo $medicine['medicine_id']; ?>">
                                    <?php echo htmlspecialchars($medicine['medicine_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select class="form-select" name="supplier_id">
                            <option value="">-- Chọn nhà cung cấp --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['supplier_id']; ?>">
                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" required min="1">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ngày nhập <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="import_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hạn sử dụng <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="expiry_date" required>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Lưu lô thuốc
                        </button>
                        <a href="index.php?page=batches" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
