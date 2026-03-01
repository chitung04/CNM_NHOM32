<?php require_once 'views/layouts/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-pencil me-2"></i>Sửa người dùng</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="index.php?page=users&action=update">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="text-muted">Không thể thay đổi tên đăng nhập</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" name="password" placeholder="Để trống nếu không đổi">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Nhân viên</option>
                            <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Quản lý</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật
                        </button>
                        <a href="index.php?page=users" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
