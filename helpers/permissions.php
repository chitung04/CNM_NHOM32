<?php
/**
 * Hệ thống phân quyền chi tiết
 */

// Định nghĩa các quyền trong hệ thống
define('PERMISSIONS', [
    // Dashboard
    'dashboard.view' => ['staff', 'manager'],
    
    // Bán hàng
    'sales.view' => ['staff', 'manager'],
    'sales.create' => ['staff', 'manager'],
    'sales.invoice' => ['staff', 'manager'],
    
    // Thuốc
    'medicines.view' => ['staff', 'manager'],
    'medicines.create' => ['manager'],
    'medicines.edit' => ['manager'],
    'medicines.delete' => ['manager'],
    
    // Lô thuốc
    'batches.view' => ['manager'],
    'batches.create' => ['manager'],
    'batches.edit' => ['manager'],
    'batches.delete' => ['manager'],
    
    // Nhà cung cấp
    'suppliers.view' => ['manager'],
    'suppliers.create' => ['manager'],
    'suppliers.edit' => ['manager'],
    'suppliers.delete' => ['manager'],
    
    // Báo cáo
    'reports.sales' => ['manager'],
    'reports.inventory' => ['manager'],
    'reports.expiry' => ['manager'],
    'reports.topSelling' => ['manager'],
    
    // Người dùng
    'users.view' => ['manager'],
    'users.create' => ['manager'],
    'users.edit' => ['manager'],
    'users.delete' => ['manager'],
    
    // Backup
    'backup.view' => ['manager'],
    'backup.create' => ['manager'],
    'backup.restore' => ['manager'],
    'backup.download' => ['manager'],
    'backup.delete' => ['manager'],
]);

/**
 * Kiểm tra user có quyền không
 */
function userHasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role'];
    
    // Kiểm tra quyền có tồn tại không
    if (!isset(PERMISSIONS[$permission])) {
        return false;
    }
    
    // Kiểm tra role có trong danh sách quyền không
    return in_array($userRole, PERMISSIONS[$permission]);
}

/**
 * Yêu cầu quyền cụ thể với hệ thống phân quyền chi tiết (redirect nếu không có quyền)
 * Note: Tránh khai báo trùng với auth.php - hàm này sử dụng userHasPermission() thay vì checkPermission()
 */
function requireDetailedPermission($permission) {
    requireLogin();
    
    if (!userHasPermission($permission)) {
        http_response_code(403);
        require_once 'views/errors/403.php';
        exit;
    }
}

/**
 * Lấy danh sách tất cả quyền của user hiện tại
 */
function getUserPermissions() {
    if (!isLoggedIn()) {
        return [];
    }
    
    $userRole = $_SESSION['role'];
    $userPermissions = [];
    
    foreach (PERMISSIONS as $permission => $roles) {
        if (in_array($userRole, $roles)) {
            $userPermissions[] = $permission;
        }
    }
    
    return $userPermissions;
}

/**
 * Kiểm tra user có ít nhất một trong các quyền không
 */
function userHasAnyPermission($permissions) {
    foreach ($permissions as $permission) {
        if (userHasPermission($permission)) {
            return true;
        }
    }
    return false;
}

/**
 * Kiểm tra user có tất cả các quyền không
 */
function userHasAllPermissions($permissions) {
    foreach ($permissions as $permission) {
        if (!userHasPermission($permission)) {
            return false;
        }
    }
    return true;
}

/**
 * Lấy mô tả quyền bằng tiếng Việt
 */
function getPermissionDescription($permission) {
    $descriptions = [
        'dashboard.view' => 'Xem trang chủ',
        'sales.view' => 'Xem bán hàng',
        'sales.create' => 'Tạo đơn hàng',
        'sales.invoice' => 'Xem hóa đơn',
        'medicines.view' => 'Xem danh sách thuốc',
        'medicines.create' => 'Thêm thuốc mới',
        'medicines.edit' => 'Sửa thông tin thuốc',
        'medicines.delete' => 'Xóa thuốc',
        'batches.view' => 'Xem lô thuốc',
        'batches.create' => 'Nhập lô mới',
        'batches.edit' => 'Sửa lô thuốc',
        'batches.delete' => 'Xóa lô thuốc',
        'suppliers.view' => 'Xem nhà cung cấp',
        'suppliers.create' => 'Thêm nhà cung cấp',
        'suppliers.edit' => 'Sửa nhà cung cấp',
        'suppliers.delete' => 'Xóa nhà cung cấp',
        'reports.sales' => 'Xem báo cáo doanh thu',
        'reports.inventory' => 'Xem báo cáo tồn kho',
        'reports.expiry' => 'Xem báo cáo hết hạn',
        'reports.topSelling' => 'Xem thống kê bán chạy',
        'users.view' => 'Xem người dùng',
        'users.create' => 'Thêm người dùng',
        'users.edit' => 'Sửa người dùng',
        'users.delete' => 'Xóa người dùng',
        'backup.view' => 'Xem backup',
        'backup.create' => 'Tạo backup',
        'backup.restore' => 'Khôi phục backup',
        'backup.download' => 'Tải backup',
        'backup.delete' => 'Xóa backup',
    ];
    
    return $descriptions[$permission] ?? $permission;
}
