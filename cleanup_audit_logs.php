<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'models/AuditLog.php';
require_once 'helpers/secure_session.php';
require_once 'helpers/audit.php';

// Kiểm tra đăng nhập và quyền
if (!isLoggedIn() || $_SESSION['role'] !== 'manager') {
    $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này";
    header('Location: index.php?page=dashboard');
    exit;
}

try {
    $auditModel = new AuditLog();
    
    // Xóa logs cũ hơn 90 ngày
    $deletedCount = $auditModel->cleanup(90);
    
    // Ghi log việc dọn dẹp
    auditLog('CLEANUP', 'audit_logs', null, null, [
        'deleted_count' => $deletedCount,
        'days_threshold' => 90
    ]);
    
    $_SESSION['success'] = "Đã xóa thành công $deletedCount logs cũ hơn 90 ngày";
    
} catch (Exception $e) {
    error_log("Cleanup audit logs error: " . $e->getMessage());
    $_SESSION['error'] = "Có lỗi xảy ra khi dọn dẹp logs: " . $e->getMessage();
}

header('Location: view_audit_logs.php');
exit;