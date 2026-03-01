<?php require_once 'views/layouts/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-person-plus me-2"></i>Thêm người dùng</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="index.php?page=users&action=store">
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="staff">Nhân viên</option>
                            <option value="manager">Quản lý</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Lưu
                        </button>
                        <a href="index.php?page=users" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
