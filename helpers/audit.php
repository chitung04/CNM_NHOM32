<?php
/**
 * Helper functions cho Audit Log
 */

require_once 'models/AuditLog.php';

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
 * Log IMPORT action
 */
function auditImport($tableName, $recordCount, $successCount, $errorCount) {
    return auditLog('IMPORT', $tableName, null, null, [
        'total' => $recordCount,
        'success' => $successCount,
        'errors' => $errorCount
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
        'RESTORE' => 'Khôi phục'
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
        'customers' => 'Khách hàng',
        'promotions' => 'Khuyến mãi',
        'categories' => 'Danh mục',
        'units' => 'Đơn vị'
    ];
    
    return $tables[$table] ?? $table;
}
