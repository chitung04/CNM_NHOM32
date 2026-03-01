<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .error-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-7">
                <div class="error-card text-center">
                    <i class="bi bi-shield-x text-danger" style="font-size: 100px;"></i>
                    <h1 class="display-1 fw-bold text-danger">403</h1>
                    <h2 class="mb-3">Không có quyền truy cập</h2>
                    
                    <?php if (isset($_SESSION['role'])): ?>
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            Bạn đang đăng nhập với vai trò: 
                            <strong><?= $_SESSION['role'] === 'manager' ? 'Quản lý' : 'Nhân viên' ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-muted mb-4">
                        Bạn không có quyền truy cập vào trang này. 
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff'): ?>
                            <br>Chức năng này chỉ dành cho <strong>Quản lý</strong>.
                        <?php endif; ?>
                    </p>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <a href="index.php?page=dashboard" class="btn btn-primary">
                            <i class="bi bi-house me-2"></i>Về trang chủ
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-start">
                        <h5 class="mb-3">Quyền truy cập theo vai trò:</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success"><i class="bi bi-person me-2"></i>Nhân viên (Staff)</h6>
                                <ul class="small text-muted">
                                    <li>Bán hàng</li>
                                    <li>Tra cứu thuốc</li>
                                    <li>Xem tồn kho</li>
                                    <li>In hóa đơn</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="bi bi-person-badge me-2"></i>Quản lý (Manager)</h6>
                                <ul class="small text-muted">
                                    <li>Tất cả quyền của Nhân viên</li>
                                    <li>Quản lý thuốc & lô hàng</li>
                                    <li>Quản lý nhà cung cấp</li>
                                    <li>Xem báo cáo & thống kê</li>
                                    <li>Quản lý người dùng</li>
                                    <li>Sao lưu dữ liệu</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
