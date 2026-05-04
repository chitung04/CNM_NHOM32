<?php
/**
 * Helper functions cho Audit Log
 */

// Sử dụng đường dẫn tuyệt đối để tránh lỗi
$basePath = dirname(__DIR__);
require_once $basePath . '/models/AuditLog.php';

/**
 * Ghi audit log
 */
function auditLog($action, $tableName, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        $auditLog = new AuditLog();
        return $auditLog->log($action, $tableName, $recordId, $oldValues, $newValues);
    } catch (Exception $e) {
        error_log("Audit log error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log CREATE action
 */
function auditCreate($tableName, $recordId, $data) {
    return auditLog('CREATE', $tableName, $recordId, null, $data);
}

/**
 * Log UPDATE action
 */
function auditUpdate($tableName, $recordId, $oldData, $newData) {
    return auditLog('UPDATE', $tableName, $recordId, $oldData, $newData);
}

/**
 * Log DELETE action
 */
function auditDelete($tableName, $recordId, $data) {
    return auditLog('DELETE', $tableName, $recordId, $data, null);
}

/**
 * Log VIEW action (cho dữ liệu nhạy cảm)
 */
function auditView($tableName, $recordId) {
    return auditLog('VIEW', $tableName, $recordId);
}

/**
 * Log LOGIN action
 */
function auditLogin($username, $success = true) {
    $action = $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
    return auditLog($action, 'users', null, null, ['username' => $username]);
}

/**
 * Log LOGOUT action
 */
function auditLogout() {
    $username = $_SESSION['username'] ?? 'unknown';
    return auditLog('LOGOUT', 'users', null, null, ['username' => $username]);
}

/**
 * Log EXPORT action
 */
function auditExport($tableName, $format, $recordCount) {
    return auditLog('EXPORT', $tableName, null, null, [
        'format' => $format,
        'record_count' => $recordCount
    ]);
}

/**
 * Log CANCEL ORDER action
 */
function auditCancelOrder($invoiceId, $invoiceData) {
    return auditLog('CANCEL_ORDER', 'invoices', $invoiceId, null, $invoiceData);
}

/**
 * Log PAYMENT action
 */
function auditPayment($invoiceId, $paymentData) {
    return auditLog('PAYMENT', 'invoices', $invoiceId, null, $paymentData);
}

/**
 * Log INVENTORY UPDATE action
 */
function auditInventoryUpdate($batchId, $oldQuantity, $newQuantity, $reason) {
    return auditLog('INVENTORY_UPDATE', 'batches', $batchId, 
        ['quantity' => $oldQuantity], 
        ['quantity' => $newQuantity, 'reason' => $reason]
    );
}

/**
 * Log SYSTEM MAINTENANCE action
 */
function auditMaintenance($action, $details) {
    return auditLog('MAINTENANCE', 'system', null, null, [
        'action' => $action,
        'details' => $details
    ]);
}

/**
 * Lấy tên action bằng tiếng Việt
 */
function getActionName($action) {
    $actions = [
        'CREATE' => 'Tạo mới',
        'UPDATE' => 'Cập nhật',
        'DELETE' => 'Xóa',
        'VIEW' => 'Xem',
        'LOGIN_SUCCESS' => 'Đăng nhập thành công',
        'LOGIN_FAILED' => 'Đăng nhập thất bại',
        'LOGOUT' => 'Đăng xuất',
        'EXPORT' => 'Xuất dữ liệu',
        'IMPORT' => 'Nhập dữ liệu',
        'BACKUP' => 'Sao lưu',
        'RESTORE' => 'Khôi phục',
        'CANCEL_ORDER' => 'Hủy đơn hàng',
        'PAYMENT' => 'Thanh toán',
        'INVENTORY_UPDATE' => 'Cập nhật tồn kho',
        'MAINTENANCE' => 'Bảo trì hệ thống',
        'CLEANUP' => 'Dọn dẹp dữ liệu'
    ];
    
    return $actions[$action] ?? $action;
}

/**
 * Lấy tên bảng bằng tiếng Việt
 */
function getTableName($table) {
    $tables = [
        'users' => 'Người dùng',
        'medicines' => 'Thuốc',
        'batches' => 'Lô thuốc',
        'suppliers' => 'Nhà cung cấp',
        'invoices' => 'Hóa đơn',
        'invoice_details' => 'Chi tiết hóa đơn',
        'customers' => 'Khách hàng',
        'promotions' => 'Khuyến mãi',
        'categories' => 'Danh mục',
        'units' => 'Đơn vị',
        'notifications' => 'Thông báo',
        'audit_logs' => 'Nhật ký hệ thống',
        'system' => 'Hệ thống'
    ];
    
    return $tables[$table] ?? $table;
}
