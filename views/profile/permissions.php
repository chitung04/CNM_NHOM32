<?php 
require_once 'views/layouts/header.php';
require_once 'helpers/permissions.php';

$userPermissions = getUserPermissions();
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quyền truy cập của tôi</h1>
            </div>
            
            <!-- Thông tin user -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Thông tin tài khoản</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tên đăng nhập:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
                            <p><strong>Họ tên:</strong> <?= htmlspecialchars($_SESSION['full_name']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Vai trò:</strong> 
                                <span class="badge <?= $_SESSION['role'] === 'manager' ? 'bg-primary' : 'bg-success' ?>">
                                    <?= $_SESSION['role'] === 'manager' ? 'Quản lý' : 'Nhân viên' ?>
                                </span>
                            </p>
                            <p><strong>Tổng số quyền:</strong> <?= count($userPermissions) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Danh sách quyền -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Danh sách quyền</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Nhóm quyền theo module
                    $groupedPermissions = [];
                    foreach ($userPermissions as $permission) {
                        $parts = explode('.', $permission);
                        $module = $parts[0];
                        if (!isset($groupedPermissions[$module])) {
                            $groupedPermissions[$module] = [];
                        }
                        $groupedPermissions[$module][] = $permission;
                    }
                    
                    // Tên module bằng tiếng Việt
                    $moduleNames = [
                        'dashboard' => 'Trang chủ',
                        'sales' => 'Bán hàng',
                        'medicines' => 'Quản lý thuốc',
                        'batches' => 'Quản lý lô thuốc',
                        'suppliers' => 'Nhà cung cấp',
                        'reports' => 'Báo cáo & Thống kê',
                        'users' => 'Quản lý người dùng',
                        'backup' => 'Sao lưu dữ liệu'
                    ];
                    ?>
                    
                    <div class="row">
                        <?php foreach ($groupedPermissions as $module => $permissions): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card border-primary">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-folder me-2"></i>
                                            <?= $moduleNames[$module] ?? ucfirst($module) ?>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($permissions as $permission): ?>
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle text-success me-2"></i>
                                                    <?= getPermissionDescription($permission) ?>
                                                    <small class="text-muted">(<?= $permission ?>)</small>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (empty($userPermissions)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Bạn chưa có quyền nào trong hệ thống.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- So sánh quyền -->
            <?php if ($_SESSION['role'] === 'staff'): ?>
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quyền bổ sung của Quản lý</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Các quyền mà Quản lý có nhưng Nhân viên không có:</p>
                    <ul>
                        <li>Quản lý thuốc (Thêm, Sửa, Xóa)</li>
                        <li>Quản lý lô thuốc</li>
                        <li>Quản lý nhà cung cấp</li>
                        <li>Xem tất cả báo cáo và thống kê</li>
                        <li>Quản lý người dùng</li>
                        <li>Sao lưu và khôi phục dữ liệu</li>
                    </ul>
                    <p class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        <small>Liên hệ quản trị viên nếu bạn cần thêm quyền.</small>
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
