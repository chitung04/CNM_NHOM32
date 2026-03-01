<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'dashboard' ? 'active' : ''; ?>" 
                   href="index.php?page=dashboard">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'sales' ? 'active' : ''; ?>" 
                   href="index.php?page=sales">
                    <i class="bi bi-cart-plus me-2"></i>
                    Bán hàng
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'invoices' ? 'active' : ''; ?>" 
                   href="index.php?page=invoices">
                    <i class="bi bi-receipt me-2"></i>
                    Lịch sử đơn hàng
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'medicines' ? 'active' : ''; ?>" 
                   href="index.php?page=medicines">
                    <i class="bi bi-capsule me-2"></i>
                    Quản lý thuốc
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'batches' ? 'active' : ''; ?>" 
                   href="index.php?page=batches">
                    <i class="bi bi-box-seam me-2"></i>
                    Quản lý lô thuốc
                </a>
            </li>
            
            <?php if (isManager()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'suppliers' ? 'active' : ''; ?>" 
                   href="index.php?page=suppliers">
                    <i class="bi bi-truck me-2"></i>
                    Nhà cung cấp
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading px-3 text-muted">
                    <span>Báo cáo & Thống kê</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'reports' && ($_GET['action'] ?? '') === 'sales' ? 'active' : ''; ?>" 
                   href="index.php?page=reports&action=sales">
                    <i class="bi bi-bar-chart me-2"></i>
                    Báo cáo doanh thu
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'reports' && ($_GET['action'] ?? '') === 'topSelling' ? 'active' : ''; ?>" 
                   href="index.php?page=reports&action=topSelling">
                    <i class="bi bi-trophy me-2"></i>
                    Thuốc bán chạy
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'reports' && ($_GET['action'] ?? '') === 'inventory' ? 'active' : ''; ?>" 
                   href="index.php?page=reports&action=inventory">
                    <i class="bi bi-boxes me-2"></i>
                    Báo cáo tồn kho
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'reports' && ($_GET['action'] ?? '') === 'expiry' ? 'active' : ''; ?>" 
                   href="index.php?page=reports&action=expiry">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Thuốc sắp hết hạn
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading px-3 text-muted">
                    <span>Quản trị</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'users' ? 'active' : ''; ?>" 
                   href="index.php?page=users">
                    <i class="bi bi-people me-2"></i>
                    Quản lý người dùng
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'admin' && ($_GET['action'] ?? '') === 'roles' ? 'active' : ''; ?>" 
                   href="index.php?page=admin&action=roles">
                    <i class="bi bi-shield-lock me-2"></i>
                    Vai trò & Quyền
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'audit' ? 'active' : ''; ?>" 
                   href="index.php?page=audit">
                    <i class="bi bi-journal-text me-2"></i>
                    Nhật ký hoạt động
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo ($page ?? '') === 'backup' ? 'active' : ''; ?>" 
                   href="index.php?page=backup">
                    <i class="bi bi-database me-2"></i>
                    Sao lưu dữ liệu
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
