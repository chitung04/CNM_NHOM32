<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?page=dashboard">
            <i class="bi bi-hospital me-2"></i>
            Quản lý nhà thuốc
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count">
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
                    </ul>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        <span class="badge bg-light text-dark ms-2">
                            <?php echo $_SESSION['role'] === 'manager' ? 'Quản lý' : 'Nhân viên'; ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="index.php?page=profile&action=permissions">
                                <i class="bi bi-shield-check me-2"></i>Quyền của tôi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
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
