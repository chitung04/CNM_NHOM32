<?php
/**
 * Helper functions cho Audit Log - Phiên bản đơn giản
 * Sử dụng khi có lỗi với audit log chính
 */

/**
 * Ghi audit log đơn giản - không throw exception
 */
function auditLogSafe($action, $tableName, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        // Kết nối database trực tiếp
        $pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress,
            $userAgent
        ]);
        
    } catch (Exception $e) {
        error_log("Audit log safe error: " . $e->getMessage());
        return false; // Không throw exception
    }
}

/**
 * Log CREATE action - safe version
 */
function auditCreateSafe($tableName, $recordId, $data) {
    return auditLogSafe('CREATE', $tableName, $recordId, null, $data);
}

/**
 * Log UPDATE action - safe version
 */
function auditUpdateSafe($tableName, $recordId, $oldData, $newData) {
    return auditLogSafe('UPDATE', $tableName, $recordId, $oldData, $newData);
}

/**
 * Log DELETE action - safe version
 */
function auditDeleteSafe($tableName, $recordId, $data) {
    return auditLogSafe('DELETE', $tableName, $recordId, $data, null);
}

/**
 * Log LOGIN action - safe version
 */
function auditLoginSafe($username, $success = true) {
    $action = $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
    return auditLogSafe($action, 'users', null, null, ['username' => $username]);
}

/**
 * Log LOGOUT action - safe version
 */
function auditLogoutSafe() {
    $username = $_SESSION['username'] ?? 'unknown';
    return auditLogSafe('LOGOUT', 'users', null, null, ['username' => $username]);
}