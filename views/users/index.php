<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people me-2"></i>Quản lý người dùng</h2>
    <a href="index.php?page=users&action=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm người dùng
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Họ tên</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] === 'manager' ? 'primary' : 'info'; ?>">
                                    <?php echo $user['role'] === 'manager' ? 'Quản lý' : 'Nhân viên'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $user['is_active'] ? 'Hoạt động' : 'Khóa'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="index.php?page=users&action=edit&id=<?php echo $user['user_id']; ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <a href="index.php?page=users&action=delete&id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirmDelete('Bạn có chắc muốn xóa người dùng này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
