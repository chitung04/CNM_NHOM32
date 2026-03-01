<?php
/**
 * Logging Helper
 */

/**
 * Ghi log vào file
 */
function writeLog($message, $level = 'INFO', $logFile = 'app.log') {
    $logDir = BASE_PATH . '/logs';
    
    // Tạo thư mục logs nếu chưa có
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logPath = $logDir . '/' . $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $username = $_SESSION['username'] ?? 'guest';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $logMessage = "[$timestamp] [$level] [$username] [$ip] $message" . PHP_EOL;
    
    file_put_contents($logPath, $logMessage, FILE_APPEND);
}

/**
 * Log hành động quan trọng
 */
function logAction($action, $details = '') {
    writeLog("$action - $details", 'ACTION', 'actions.log');
}

/**
 * Log lỗi
 */
function logError($error, $details = '') {
    writeLog("$error - $details", 'ERROR', 'error.log');
}

/**
 * Log đăng nhập
 */
function logLogin($username, $success = true) {
    $status = $success ? 'SUCCESS' : 'FAILED';
    writeLog("Login attempt: $username - $status", 'AUTH', 'auth.log');
}

/**
 * Log thay đổi dữ liệu
 */
function logDataChange($table, $action, $recordId, $details = '') {
    $message = "Table: $table, Action: $action, ID: $recordId";
    if ($details) {
        $message .= ", Details: $details";
    }
    writeLog($message, 'DATA', 'data.log');
}
