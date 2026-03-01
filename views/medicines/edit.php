<?php require_once 'views/layouts/header.php'; ?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=medicines">Quản lý thuốc</a></li>
            <li class="breadcrumb-item active">Sửa thông tin thuốc</li>
        </ol>
    </nav>
    <h2><i class="bi bi-pencil me-2"></i>Sửa thông tin thuốc</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin thuốc</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?page=medicines&action=update">
                    <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên thuốc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="medicine_name" required
                               value="<?php echo htmlspecialchars($medicine['medicine_name']); ?>"
                               placeholder="Nhập tên thuốc">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh mục</label>
                            <select class="form-select" name="category_id">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo ($medicine['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Đơn vị tính</label>
                            <select class="form-select" name="unit_id">
                                <option value="">-- Chọn đơn vị --</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?php echo $unit['unit_id']; ?>"
                                            <?php echo ($medicine['unit_id'] == $unit['unit_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($unit['unit_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="price" required min="0" step="1000"
                               value="<?php echo $medicine['price']; ?>"
                               placeholder="Nhập giá bán">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="4"
                                  placeholder="Nhập mô tả về thuốc (tùy chọn)"><?php echo htmlspecialchars($medicine['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <?php if (!empty($medicine['qr_code'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Mã QR</label>
                            <div class="alert alert-info">
                                <i class="bi bi-qr-code me-2"></i>
                                <strong><?php echo htmlspecialchars($medicine['qr_code']); ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật
                        </button>
                        <a href="index.php?page=medicines" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>Thông tin</h6>
                <ul class="small mb-0">
                    <li><strong>ID:</strong> <?php echo $medicine['medicine_id']; ?></li>
                    <li><strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($medicine['created_at'])); ?></li>
                    <?php if (!empty($medicine['qr_code'])): ?>
                        <li><strong>Mã QR:</strong> <?php echo htmlspecialchars($medicine['qr_code']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="card bg-light mt-3">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-exclamation-triangle me-2"></i>Lưu ý</h6>
                <ul class="small mb-0">
                    <li>Thay đổi giá bán sẽ không ảnh hưởng đến hóa đơn đã tạo</li>
                    <li>Không thể xóa thuốc đã có trong hóa đơn</li>
                    <li>Mã QR không thể thay đổi sau khi tạo</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
