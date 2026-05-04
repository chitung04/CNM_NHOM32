<?php
session_start();
require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'models/AuditLog.php';
require_once 'helpers/audit.php';
require_once 'helpers/secure_session.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: index.php?page=auth&action=login');
    exit;
}

$auditModel = new AuditLog();
$db = Database::getInstance();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tóm tắt hệ thống Audit Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .summary-card {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .feature-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .integration-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        
        .integration-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .integration-item:last-child {
            border-bottom: none;
        }
        
        .integration-item i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="summary-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-shield-alt"></i> Hệ thống Audit Log</h1>
                    <p class="lead mb-0">Theo dõi và ghi lại tất cả hoạt động trong DUO PHARMA</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex flex-column">
                        <span class="status-badge bg-success mb-2">
                            <i class="fas fa-check-circle"></i> Đã triển khai
                        </span>
                        <small>Phiên bản: 1.0.0</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tính năng chính -->
            <div class="col-md-6">
                <h3><i class="fas fa-star"></i> Tính năng chính</h3>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <i class="fas fa-database feature-icon text-primary"></i>
                        <h5>Ghi log tự động</h5>
                        <p class="text-muted">Tự động ghi lại tất cả thao tác CRUD (Create, Read, Update, Delete) vào database</p>
                        <span class="badge bg-success">Hoạt động</span>
                    </div>
                </div>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <i class="fas fa-users feature-icon text-info"></i>
                        <h5>Theo dõi người dùng</h5>
                        <p class="text-muted">Ghi lại thông tin người thực hiện, IP address, User Agent</p>
                        <span class="badge bg-success">Hoạt động</span>
                    </div>
                </div>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <i class="fas fa-search feature-icon text-warning"></i>
                        <h5>Tìm kiếm & Lọc</h5>
                        <p class="text-muted">Tìm kiếm logs theo người dùng, hành động, bảng dữ liệu, thời gian</p>
                        <span class="badge bg-success">Hoạt động</span>
                    </div>
                </div>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar feature-icon text-success"></i>
                        <h5>Thống kê & Báo cáo</h5>
                        <p class="text-muted">Thống kê hoạt động theo thời gian, người dùng, loại hành động</p>
                        <span class="badge bg-success">Hoạt động</span>
                    </div>
                </div>
            </div>

            <!-- Tích hợp hệ thống -->
            <div class="col-md-6">
                <h3><i class="fas fa-puzzle-piece"></i> Tích hợp hệ thống</h3>
                
                <div class="integration-list">
                    <div class="integration-item">
                        <i class="fas fa-sign-in-alt text-success"></i>
                        <div>
                            <strong>AuthController</strong>
                            <br><small>Ghi log đăng nhập/đăng xuất thành công/thất bại</small>
                        </div>
                    </div>
                    
                    <div class="integration-item">
                        <i class="fas fa-pills text-primary"></i>
                        <div>
                            <strong>MedicineController</strong>
                            <br><small>Ghi log thêm/sửa/xóa/xem thuốc</small>
                        </div>
                    </div>
                    
                    <div class="integration-item">
                        <i class="fas fa-shopping-cart text-info"></i>
                        <div>
                            <strong>SalesController</strong>
                            <br><small>Ghi log tạo đơn hàng, thanh toán, hủy đơn</small>
                        </div>
                    </div>
                    
                    <div class="integration-item">
                        <i class="fas fa-users text-warning"></i>
                        <div>
                            <strong>UserController</strong>
                            <br><small>Ghi log quản lý người dùng</small>
                        </div>
                    </div>
                    
                    <div class="integration-item">
                        <i class="fas fa-exchange-alt text-secondary"></i>
                        <div>
                            <strong>AJAX Endpoints</strong>
                            <br><small>Ghi log các thao tác AJAX (cập nhật giỏ hàng, xóa sản phẩm)</small>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h5><i class="fas fa-cog"></i> Các loại log được ghi</h5>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-plus text-success"></i> CREATE (Tạo mới)</li>
                                <li><i class="fas fa-edit text-warning"></i> UPDATE (Cập nhật)</li>
                                <li><i class="fas fa-trash text-danger"></i> DELETE (Xóa)</li>
                                <li><i class="fas fa-eye text-info"></i> VIEW (Xem)</li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-sign-in-alt text-success"></i> LOGIN_SUCCESS</li>
                                <li><i class="fas fa-times text-danger"></i> LOGIN_FAILED</li>
                                <li><i class="fas fa-sign-out-alt text-secondary"></i> LOGOUT</li>
                                <li><i class="fas fa-credit-card text-primary"></i> PAYMENT</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line"></i> Thống kê hệ thống</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $totalLogs = $auditModel->count();
                            $stats = $auditModel->getStatistics();
                            $recentLogs = $auditModel->getAll(5);
                            
                            // Đếm logs theo ngày
                            $todayLogs = $auditModel->count(['created_at' => date('Y-m-d')]);
                        ?>
                        
                        <div class="row text-center mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h3><?= number_format($totalLogs) ?></h3>
                                        <p class="mb-0">Tổng số logs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h3><?= count($stats) ?></h3>
                                        <p class="mb-0">Loại hoạt động</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h3><?= count($recentLogs) ?></h3>
                                        <p class="mb-0">Logs gần đây</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h3><?= date('d/m/Y') ?></h3>
                                        <p class="mb-0">Ngày hiện tại</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6>Top 5 hoạt động phổ biến:</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Hoạt động</th>
                                        <th>Số lượng</th>
                                        <th>Người dùng</th>
                                        <th>Tỷ lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($stats, 0, 5) as $stat): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= getActionName($stat['action']) ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($stat['count']) ?></td>
                                            <td><?= $stat['unique_users'] ?></td>
                                            <td>
                                                <?php $percentage = ($stat['count'] / $totalLogs) * 100; ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" style="width: <?= $percentage ?>%">
                                                        <?= number_format($percentage, 1) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php
                        } catch (Exception $e) {
                            echo "<div class='alert alert-danger'>Lỗi khi lấy thống kê: " . $e->getMessage() . "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tools"></i> Thao tác</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="view_audit_logs.php" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-list"></i> Xem nhật ký đầy đủ
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="test_audit_system.php" class="btn btn-info w-100 mb-2">
                                    <i class="fas fa-vial"></i> Test hệ thống
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="cleanup_audit_logs.php" class="btn btn-warning w-100 mb-2" 
                                   onclick="return confirm('Xóa logs cũ hơn 90 ngày?')">
                                    <i class="fas fa-broom"></i> Dọn dẹp logs
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="index.php?page=dashboard" class="btn btn-secondary w-100 mb-2">
                                    <i class="fas fa-home"></i> Về Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>