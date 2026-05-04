
            }, 100);
        });
    </script>
</body>
</html>         });
        });

        // Welcome animation
        document.addEventListener('DOMContentLoaded', function() {
            const welcomeCard = document.querySelector('.welcome-card');
            welcomeCard.style.opacity = '0';
            welcomeCard.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                welcomeCard.style.transition = 'all 0.8s ease';
                welcomeCard.style.opacity = '1';
                welcomeCard.style.transform = 'translateY(0)';).textContent = featureName;
            const toast = new bootstrap.Toast(document.getElementById('comingSoonToast'));
            toast.show();
        }

        // Sidebar active state
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
   ng>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Tính năng <strong id="featureName"></strong> sẽ có sẵn khi kết nối database thành công!
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showComingSoon(featureName) {
            document.getElementById('featureName'                            Đăng xuất
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="comingSoonToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="bi bi-info-circle text-primary me-2"></i>
                <strong class="me-auto">Thông báo</stro  Hệ thống đầy đủ (cần MySQL)
                            </a>
                            <a href="setup_quick.php" class="quick-action-btn">
                                <i class="bi bi-gear me-2"></i>
                                Setup Database
                            </a>
                            <a href="?logout=1" class="quick-action-btn" style="background: linear-gradient(135deg, var(--danger-color), #dc2626);">
                                <i class="bi bi-box-arrow-right me-2"></i>
                  </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h4 class="mb-4">
                            <i class="bi bi-lightning me-2"></i>
                            Thao tác nhanh
                        </h4>
                        <div class="d-flex flex-wrap">
                            <a href="login.php" class="quick-action-btn">
                                <i class="bi bi-database me-2"></i>
                              uốc</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#" class="feature-card" onclick="showComingSoon('Cài đặt')">
                                <i class="bi bi-gear feature-icon"></i>
                                <h5 class="feature-title">Cài đặt</h5>
                                <p class="feature-desc">Cấu hình hệ thống</p>
                            </a>
                        </div>
                           <p class="feature-desc">Thống kê doanh thu và báo cáo</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#" class="feature-card" onclick="showComingSoon('QR Code')">
                                <i class="bi bi-qr-code feature-icon"></i>
                                <h5 class="feature-title">QR Code</h5>
                                <p class="feature-desc">Quản lý mã QR cho th                 <h5 class="feature-title">Lô hàng</h5>
                                <p class="feature-desc">Quản lý nhập hàng và tồn kho</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#" class="feature-card" onclick="showComingSoon('Báo cáo')">
                                <i class="bi bi-graph-up feature-icon"></i>
                                <h5 class="feature-title">Báo cáo</h5>
           i class="bi bi-capsule feature-icon"></i>
                                <h5 class="feature-title">Quản lý thuốc</h5>
                                <p class="feature-desc">Thêm, sửa, xóa thông tin thuốc</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#" class="feature-card" onclick="showComingSoon('Lô hàng')">
                                <i class="bi bi-box-seam feature-icon"></i>
               ngSoon('Bán hàng')">
                                <i class="bi bi-cart-plus feature-icon"></i>
                                <h5 class="feature-title">Bán hàng</h5>
                                <p class="feature-desc">Tạo đơn hàng và quản lý bán hàng</p>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#" class="feature-card" onclick="showComingSoon('Quản lý thuốc')">
                                <     <i class="bi bi-people"></i>
                                </div>
                                <div class="stat-number">3</div>
                                <div class="stat-label">Người dùng</div>
                            </div>
                        </div>
                    </div>

                    <!-- Feature Cards -->
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <a href="#" class="feature-card" onclick="showComii>
                                </div>
                                <div class="stat-number">5</div>
                                <div class="stat-label">Hóa đơn hôm nay</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, var(--info-color), #0891b2);">
                                    </div>
                                <div class="stat-number">62</div>
                                <div class="stat-label">Lô thuốc</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning-color), #d97706);">
                                    <i class="bi bi-receipt"></          <div class="stat-number">61</div>
                                <div class="stat-label">Loại thuốc</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, var(--success-color), #059669);">
                                    <i class="bi bi-box-seam"></i>
                                          </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="bi bi-capsule"></i>
                                </div>
                      ame']); ?></strong>, 
                                    hôm nay là <?php echo date('d/m/Y'); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="status-badge status-online">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Hệ thống hoạt động
                                </div>
                            </div>
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <!-- Welcome Card -->
                    <div class="welcome-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="welcome-title">Chào mừng trở lại!</h1>
                                <p class="welcome-subtitle">
                                    Xin chào <strong><?php echo htmlspecialchars($user['full_nời dùng
                        </a>
                        <a class="nav-link" href="#suppliers">
                            <i class="bi bi-building me-3"></i>Nhà cung cấp
                        </a>
                        <?php endif; ?>
                        <a class="nav-link" href="#qr">
                            <i class="bi bi-qr-code me-3"></i>QR Code
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
         
                        <a class="nav-link" href="#invoices">
                            <i class="bi bi-receipt me-3"></i>Hóa đơn
                        </a>
                        <a class="nav-link" href="#reports">
                            <i class="bi bi-graph-up me-3"></i>Báo cáo
                        </a>
                        <?php if ($user['role'] === 'manager'): ?>
                        <a class="nav-link" href="#users">
                            <i class="bi bi-people me-3"></i>Ngư                        </a>
                        <a class="nav-link" href="#sales">
                            <i class="bi bi-cart-plus me-3"></i>Bán hàng
                        </a>
                        <a class="nav-link" href="#medicines">
                            <i class="bi bi-capsule me-3"></i>Quản lý thuốc
                        </a>
                        <a class="nav-link" href="#batches">
                            <i class="bi bi-box-seam me-3"></i>Lô hàng
                        </a>ox-arrow-right me-2"></i>Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <nav class="nav flex-column pt-4">
                        <a class="nav-link active" href="#dashboard">
                            <i class="bi bi-speedometer2 me-3"></i>Dashboard

                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Thông tin cá nhân</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Cài đặt</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="?logout=1"><i class="bi bi-b              <div class="me-3 text-end">
                            <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <small class="opacity-75">
                                <?php echo $user['role'] === 'manager' ? 'Quản lý' : 'Nhân viên'; ?>
                            </small>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-circle p-2">
                            <i class="bi bi-person-fill"></i>d>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-hospital me-2"></i>
                Quản lý nhà thuốc
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
          r-radius: 12px;
            padding: 15px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin: 5px;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
            color: white;
            text-decoration: none;
        }
    </style>
</heastatus-offline {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .quick-actions {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-top: 30px;
        }
        
        .quick-action-btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            borde  
        .welcome-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .status-online {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .b;
            font-size: 1rem;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.2);
        }
        
        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
             font-size: 4rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1e293b;
        }
        
        .feature-desc {
            color: #64748           border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
            color: inherit;
            text-decoration: none;
        }
        
        .feature-icon {
         }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #64748b;
            font-weight: 500;
            font-size: 1.1rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
 nt(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 20px;
     .stat-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradie-weight: 500;
        }
        
        .sidebar .nav-link:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
            border-radius: 0 20px 20px 0;
        }
        
        .sidebar .nav-link {
            color: #64748b;
            padding: 15px 20px;
            margin: 5px 15px;
            border-radius: 12px;
            transition: all 0.3s ease;
            fonta, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.15);
            border: none;
        }
        
       sheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #2563eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
        }
        
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdander('Location: login_fixed.php');
    exit;
}

// Xử lý logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login_fixed.php');
    exit;
}

$user = $_SESSION;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DUO PHARMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="style<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    hea