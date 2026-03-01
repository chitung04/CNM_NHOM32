<?php require_once 'views/layouts/header.php'; ?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=medicines">Quản lý thuốc</a></li>
            <li class="breadcrumb-item active">Thêm thuốc mới</li>
        </ol>
    </nav>
    <h2><i class="bi bi-plus-circle me-2"></i>Thêm thuốc mới</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin thuốc</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?page=medicines&action=store">
                    <div class="mb-3">
                        <label class="form-label">Tên thuốc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="medicine_name" required
                               placeholder="Nhập tên thuốc">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh mục</label>
                            <select class="form-select" name="category_id">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
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
                                    <option value="<?php echo $unit['unit_id']; ?>">
                                        <?php echo htmlspecialchars($unit['unit_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="price" required min="0" step="1000"
                               placeholder="Nhập giá bán">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="4"
                                  placeholder="Nhập mô tả về thuốc (tùy chọn)"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Lưu thuốc
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
                <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>Hướng dẫn</h6>
                <ul class="small mb-0">
                    <li>Tên thuốc và giá bán là thông tin bắt buộc</li>
                    <li>Danh mục và đơn vị tính giúp phân loại thuốc</li>
                    <li>Giá bán phải là số dương</li>
                    <li>Sau khi thêm thuốc, bạn cần nhập lô thuốc để có tồn kho</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
