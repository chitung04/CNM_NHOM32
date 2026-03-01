<?php require_once 'views/layouts/header.php'; ?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=suppliers">Nhà cung cấp</a></li>
            <li class="breadcrumb-item active">Sửa nhà cung cấp</li>
        </ol>
    </nav>
    <h2><i class="bi bi-pencil me-2"></i>Sửa nhà cung cấp</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="index.php?page=suppliers&action=update">
                    <input type="hidden" name="supplier_id" value="<?php echo $supplier['supplier_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="supplier_name" required
                               value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <input type="tel" class="form-control" name="phone"
                                   value="<?php echo htmlspecialchars($supplier['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   value="<?php echo htmlspecialchars($supplier['email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($supplier['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật
                        </button>
                        <a href="index.php?page=suppliers" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
