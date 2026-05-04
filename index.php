<?php
// Entry point của ứng dụng

// Load cấu hình
require_once 'config/config.php';

// Load secure session system
require_once 'helpers/secure_session.php';

// Khởi tạo secure session
$secureSession = SecureSession::getInstance();

require_once 'config/database.php';

// Load helpers
require_once 'helpers/functions.php';
require_once 'helpers/permissions.php';
require_once 'helpers/security.php';
require_once 'helpers/logger.php';
require_once 'helpers/audit.php';
require_once 'helpers/pharmacy.php';

// Simple routing
$page = sanitize($_GET['page'] ?? 'dashboard');
$action = sanitize($_GET['action'] ?? 'index');

// Kiểm tra đăng nhập (trừ trang login)
if ($page !== 'auth' && !$secureSession->isLoggedIn()) {
    header('Location: index.php?page=auth&action=login&reason=session_required');
    exit;
}

// Routing đơn giản
switch ($page) {
    case 'auth':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($action === 'login') {
            $controller->login();
        } elseif ($action === 'logout') {
            $controller->logout();
        } elseif ($action === 'register') {
            $controller->register();
        }
        break;
        
    case 'dashboard':
        require_once 'views/dashboard/index.php';
        break;
        
    case 'medicines':
        requireLogin();
        require_once 'controllers/MedicineController.php';
        $controller = new MedicineController();
        
        switch ($action) {
            case 'create':
                requireManager();
                $controller->create();
                break;
            case 'store':
                requireManager();
                $controller->store();
                break;
            case 'edit':
                requireManager();
                $controller->edit();
                break;
            case 'update':
                requireManager();
                $controller->update();
                break;
            case 'delete':
                requireManager();
                $controller->delete();
                break;
            case 'view':
                $controller->view();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    case 'batches':
        requireManager();
        require_once 'controllers/BatchController.php';
        $controller = new BatchController();
        
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'view':
                $controller->view();
                break;
            case 'import':
                $controller->import();
                break;
            case 'process_import':
                $controller->processImport();
                break;
            case 'import_sql':
                $controller->importSql();
                break;
            case 'process_import_sql':
                $controller->processImportSql();
                break;
            case 'download_template':
                $controller->downloadTemplate();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    case 'sales':
        requireLogin();
        require_once 'controllers/SalesController.php';
        $controller = new SalesController();
        
        switch ($action) {
            case 'checkout':
                $controller->checkout();
                break;
            case 'invoice':
                $controller->invoice();
                break;
            case 'complete':
                $controller->complete();
                break;
            case 'newOrder':
                $controller->newOrder();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    case 'invoices':
        requireLogin();
        require_once 'controllers/InvoiceController.php';
        $controller = new InvoiceController();
        
        switch ($action) {
            case 'view':
                $controller->view();
                break;
            case 'print':
                $controller->print();
                break;
            case 'delete':
                requireManager();
                $controller->delete();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    case 'reports':
        requireManager();
        require_once 'controllers/ReportController.php';
        $controller = new ReportController();
        
        switch ($action) {
            case 'sales':
                $controller->sales();
                break;
            case 'inventory':
                $controller->inventory();
                break;
            case 'expiry':
                $controller->expiry();
                break;
            case 'lowStock':
                $controller->lowStock();
                break;
            case 'topSelling':
                $controller->topSelling();
                break;
            default:
                $controller->sales();
                break;
        }
        break;
        
    case 'users':
        requireManager();
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'edit':
                $controller->edit();
                break;
            case 'update':
                $controller->update();
                break;
            case 'delete':
                $controller->delete();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    case 'suppliers':
        requireManager();
        require_once 'controllers/SupplierController.php';
        $controller = new SupplierController();
        
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'edit':
                $controller->edit();
                break;
            case 'update':
                $controller->update();
                break;
            case 'delete':
                $controller->delete();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    case 'backup':
        requireManager();
        require_once 'controllers/BackupController.php';
        $controller = new BackupController();
        
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'restore':
                $controller->restore();
                break;
            case 'download':
                $controller->download();
                break;
            case 'delete':
                $controller->delete();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    case 'profile':
        requireLogin();
        if ($action === 'permissions') {
            require_once 'views/profile/permissions.php';
        } elseif ($action === 'edit') {
            require_once 'views/profile/edit.php';
        } elseif ($action === 'change_password') {
            require_once 'views/profile/change_password.php';
        } else {
            require_once 'views/profile/index.php';
        }
        break;
        
    case 'admin':
        requireManager();
        if ($action === 'roles') {
            require_once 'views/admin/roles.php';
        }
        break;
        
    case 'audit':
        requireManager();
        require_once 'controllers/AuditController.php';
        $controller = new AuditController();
        
        switch ($action) {
            case 'view':
                $controller->view();
                break;
            case 'export':
                $controller->export();
                break;
            case 'statistics':
                $controller->statistics();
                break;
            case 'cleanup':
                $controller->cleanup();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    case 'notifications':
        requireLogin();
        require_once 'controllers/NotificationController.php';
        $controller = new NotificationController();
        
        switch ($action) {
            case 'markAsRead':
                $controller->markAsRead();
                break;
            case 'markAllAsRead':
                $controller->markAllAsRead();
                break;
            default:
                $controller->index();
                break;
        }
        break;
        
    default:
        http_response_code(404);
        require_once 'views/errors/404.php';
        break;
}
