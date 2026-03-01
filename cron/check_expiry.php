<?php
/**
 * Cron Job: Kiểm tra thuốc sắp hết hạn và tồn kho thấp
 * Chạy mỗi ngày lúc 00:00
 * 
 * Cách cài đặt trên Linux/Unix:
 * 0 0 * * * /usr/bin/php /path/to/pharmacy-management/cron/check_expiry.php
 * 
 * Cách cài đặt trên Windows Task Scheduler:
 * Program: C:\xampp\php\php.exe
 * Arguments: C:\xampp\htdocs\pharmacy-management\cron\check_expiry.php
 * Schedule: Daily at 00:00
 */

// Chỉ cho phép chạy từ command line
if (php_sapi_name() !== 'cli' && !defined('CRON_ALLOWED')) {
    die('This script can only be run from command line');
}

// Set working directory
chdir(dirname(__DIR__));

// Load config
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Notification.php';
require_once 'models/Batch.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting expiry check cron job...\n";

try {
    $notificationModel = new Notification();
    $batchModel = new Batch();
    
    // 1. Cập nhật status của các lô đã hết hạn
    echo "Updating expired batches...\n";
    $batchModel->updateExpiredBatches();
    
    // 2. Kiểm tra và tạo thông báo thuốc sắp hết hạn
    echo "Checking expiring medicines...\n";
    $notificationModel->checkExpiring();
    
    // 3. Kiểm tra và tạo thông báo tồn kho thấp
    echo "Checking low stock medicines...\n";
    $notificationModel->checkLowStock();
    
    // 4. Xóa các thông báo cũ đã đọc (> 30 ngày)
    echo "Cleaning old notifications...\n";
    $db = Database::getInstance();
    $sql = "DELETE FROM notifications 
            WHERE is_read = 1 
            AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $db->execute($sql);
    
    echo "[" . date('Y-m-d H:i:s') . "] Cron job completed successfully!\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    
    // Log error
    error_log("Cron job error: " . $e->getMessage());
    
    exit(1);
}

exit(0);
