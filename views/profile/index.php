<?php 
require_once 'views/layouts/header.php';

// Load user model
require_once 'models/User.php';
$userModel = new User();
$user = $userModel->getById($_SESSION['user_id']);

if (!$user) {
    $_SESSION['error'] = "Không tìm thấy thông tin người dùng";
    header('Location: index.php?page=dashboard');
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-person-circle"></i> Thông tin cá nhân
                </h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Thông tin cá nhân -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Thông tin tài khoản</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th width="200">Tên đăng nhập:</th>
                                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Họ và tên:</th>
                                        <td><strong class="text-primary"><?= htmlspecialchars($user['full_name']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?= htmlspecialchars($user['email'] ?? 'Chưa cập nhật') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Số điện thoại:</th>
                                        <td><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Vai trò:</th>
                                        <td>
                                            <?php if ($user['role'] === 'manager'): ?>
                                                <span class="badge bg-primary">Quản lý</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Nhân viên</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Trạng thái:</th>
                                        <td>
                                            <?php if (isset($user['is_active']) && $user['is_active']): ?>
                                                <span class="badge bg-success">Đang hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Không hoạt động</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ngày tạo:</th>
                                        <td><?= $user['created_at'] ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A' ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="mt-3">
                                <a href="index.php?page=profile&action=edit" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>Chỉnh sửa thông tin
                                </a>
                                <a href="index.php?page=profile&action=change_password" class="btn btn-warning">
                                    <i class="bi bi-key me-2"></i>Đổi mật khẩu
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê hoạt động -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Thống kê</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Lấy thống kê
                            require_once 'models/Invoice.php';
                            $invoiceModel = new Invoice();
                            
                            // Số đơn hàng đã tạo
                            $db = Database::getInstance();
                            $stmt = $db->query('SELECT COUNT(*) as count FROM invoices WHERE user_id = ?', [$user['user_id']]);
                            $invoiceCount = $stmt->fetch()['count'];
                            
                            // Tổng doanh thu
                            $stmt = $db->query('SELECT SUM(final_amount) as total FROM invoices WHERE user_id = ?', [$user['user_id']]);
                            $totalRevenue = $stmt->fetch()['total'] ?? 0;
                            ?>
                            
                            <div class="mb-3">
                                <small class="text-muted">Số đơn hàng đã tạo</small>
                                <h3 class="mb-0"><?= number_format($invoiceCount) ?></h3>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Tổng doanh thu</small>
                                <h3 class="mb-0 text-success"><?= number_format($totalRevenue) ?>đ</h3>
                            </div>
                            
                            <div>
                                <small class="text-muted">Trung bình/đơn</small>
                                <h3 class="mb-0 text-info">
                                    <?= $invoiceCount > 0 ? number_format($totalRevenue / $invoiceCount) : 0 ?>đ
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Bảo mật</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Phiên đăng nhập hiện tại
                            </p>
                            <p class="mb-0">
                                <small>
                                    <strong>Trình duyệt:</strong> <?= $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown' ?><br>
                                    <strong>IP:</strong> <?= $_SERVER['REMOTE_ADDR'] ?? 'Unknown' ?>
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
