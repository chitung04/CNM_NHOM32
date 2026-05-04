<?php require_once 'views/layouts/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-pencil me-2"></i>Sửa người dùng</h2>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="index.php?page=users&action=update">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="text-muted">Không thể thay đổi tên đăng nhập</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" name="password" placeholder="Để trống nếu không đổi" minlength="6">
                        <small class="text-muted">Tối thiểu 6 ký tự (nếu đổi)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               pattern="[0-9]{10,11}" 
                               title="Số điện thoại 10-11 chữ số">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                        <?php 
                        $isCurrentRootAdmin = isset($_SESSION['is_root_admin']) && $_SESSION['is_root_admin'] == 1;
                        $isTargetRootAdmin = isset($user['is_root_admin']) && $user['is_root_admin'] == 1;
                        $isSelf = $user['user_id'] == $_SESSION['user_id'];
                        ?>
                        
                        <?php if ($isSelf): ?>
                            <!-- Không thể tự sửa vai trò của chính mình -->
                            <input type="text" class="form-control" value="<?php echo $user['role'] === 'manager' ? 'Quản lý' : 'Nhân viên'; ?>" disabled>
                            <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                            <small class="text-warning">
                                <i class="bi bi-lock"></i> Không thể tự thay đổi vai trò của chính mình
                            </small>
                        <?php elseif ($isTargetRootAdmin): ?>
                            <!-- Không thể sửa vai trò của root admin -->
                            <input type="text" class="form-control" value="Quản lý (Admin gốc)" disabled>
                            <input type="hidden" name="role" value="manager">
                            <small class="text-warning">
                                <i class="bi bi-lock"></i> Không thể thay đổi vai trò của admin gốc
                            </small>
                        <?php elseif ($user['role'] === 'manager' && !$isCurrentRootAdmin): ?>
                            <!-- Admin được phân quyền không thể sửa vai trò của manager khác -->
                            <input type="text" class="form-control" value="Quản lý" disabled>
                            <input type="hidden" name="role" value="manager">
                            <small class="text-warning">
                                <i class="bi bi-lock"></i> Không thể thay đổi vai trò của quản lý khác
                            </small>
                        <?php else: ?>
                            <!-- Root admin có thể sửa vai trò của admin được phân quyền và staff -->
                            <!-- Admin được phân quyền có thể sửa vai trò của staff -->
                            <select class="form-select" name="role" required>
                                <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Nhân viên</option>
                                <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Quản lý</option>
                            </select>
                            <?php if ($isCurrentRootAdmin && $user['role'] === 'manager'): ?>
                                <small class="text-info">
                                    <i class="bi bi-info-circle"></i> Bạn có thể hạ quyền admin này xuống nhân viên
                                </small>
                            <?php endif; ?>
                        <?php endif; ?>
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
