<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$currentAction = $_GET['action'] ?? 'index';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php?page=dashboard">
            <img src="assets/images/logo.png" alt="DUO PHARMA Logo" height="40" class="me-2" 
                 style="border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); background: white; padding: 4px;">
            <span style="font-weight: 700; font-size: 1.3rem; letter-spacing: 0.5px;">DUO PHARMA</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Notification Text Popup - Bên trái chuông -->
                <li class="nav-item d-flex align-items-center">
                    <div id="notification-text" class="notification-text-inline" style="display: none;">
                        <div class="notification-message">Đang tải thông báo...</div>
                    </div>
                </li>
                
                <!-- Notifications Bell - Bên phải -->
                <li class="nav-item dropdown position-relative">
                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5" id="notification-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count" style="display: none;">
                            0
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <li><h6 class="dropdown-header">Thông báo</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li id="notification-list">
                            <div class="dropdown-item text-muted text-center">
                                Không có thông báo mới
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item text-center text-primary" onclick="clearAllNotifications()">
                                <i class="bi bi-check-all me-2"></i>Đánh dấu tất cả đã đọc
                            </button>
                        </li>
                    </ul>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
                        <span class="badge bg-light text-dark ms-2">
                            <?php echo ($_SESSION['role'] ?? 'staff') === 'manager' ? 'Quản lý' : 'Nhân viên'; ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="index.php?page=profile">
                                <i class="bi bi-person me-2"></i>Thông tin cá nhân
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?page=auth&action=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
