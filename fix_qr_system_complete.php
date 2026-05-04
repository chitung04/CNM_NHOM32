<?php
/**
 * Sửa hoàn toàn hệ thống QR code
 * Kiểm tra và sửa tất cả vấn đề về QR code
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/qrcode.php';

echo "<h2>🔧 Sửa hoàn toàn hệ thống QR Code</h2>";

try {
    $db = Database::getInstance();
    
    // 1. Kiểm tra cấu hình
    echo "<h3>📋 Kiểm tra cấu hình</h3>";
    echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
    echo "<p><strong>QRCODE_PATH:</strong> " . QRCODE_PATH . "</p>";
    
    // Kiểm tra thư mục QR codes
    if (!file_exists(QRCODE_PATH)) {
        mkdir(QRCODE_PATH, 0777, true);
        echo "<p style='color: green;'>✅ Đã tạo thư mục QR codes</p>";
    } else {
        echo "<p style='color: green;'>✅ Thư mục QR codes đã tồn tại</p>";
    }
    
    // 2. Kiểm tra database
    echo "<h3>🗄️ Kiểm tra database</h3>";
    
    // Lấy tất cả batches có QR code
    $sql = "SELECT b.batch_id, b.qr_code, b.batch_number, m.medicine_name 
            FROM batches b 
            LEFT JOIN medicines m ON b.medicine_id = m.medicine_id 
            WHERE b.qr_code IS NOT NULL 
            ORDER BY b.batch_id DESC";
    $stmt = $db->query($sql);
    $batches = $stmt->fetchAll();
    
    echo "<p>Tìm thấy <strong>" . count($batches) . "</strong> lô thuốc có QR code</p>";
    
    // 3. Kiểm tra và sửa từng QR code
    echo "<h3>🔍 Kiểm tra và sửa QR codes</h3>";
    
    $action = $_GET['action'] ?? '';
    
    if ($action === 'fix_all') {
        echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px