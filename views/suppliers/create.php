<?php require_once 'views/layouts/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Thêm nhà cung cấp</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="index.php?page=suppliers&action=store">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="supplier_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Điện thoại</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Lưu
                        </button>
                        <a href="index.php?page=suppliers" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
