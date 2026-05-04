<?php
/**
 * Tự động sửa dữ liệu user admin
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<h2>🔧 Sửa dữ liệu user admin</h2>";
echo "<hr>";

try {
    // Lấy thông tin hiện tại
    $stmt = $db->query("SELECT * FROM users WHERE username = 'admin'");
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "<p style='color: red;'>❌ Không tìm thấy user admin</p>";
        exit;
    }
    
    echo "<h3>📋 Dữ liệu TRƯỚC khi sửa:</h3>";
    echo "<ul>";
    echo "<li>Email: <strong>" . ($user['email'] ?: 'NULL') . "</strong></li>";
    echo "<li>is_active: <strong>" . ($user['is_active'] ?? 'NULL') . "</strong></li>";
    echo "</ul>";
    
    echo "<hr>";
    
    // Chuẩn bị dữ liệu cập nhật
    $updates = [];
    $needUpdate = false;
    
    // Kiểm tra is_active
    if (!isset($user['is_active']) || $user['is_active'] == 0) {
        $updates[] = "is_active = 1";
        $needUpdate = true;
        echo "<p>✅ Sẽ set is_active = 1</p>";
    }
    
    // Kiểm tra email
    if (empty($user['email'])) {
        $updates[] = "email = 'admin@duopharma.com'";
        $needUpdate = true;
        echo "<p>✅ Sẽ set email = 'admin@duopharma.com'</p>";
    }
    
    if (!$needUpdate) {
        echo "<div style='background: #fff3cd; border: 2px solid #ffc107; padding: 20px;'>";
        echo "<h3>⚠️ Không cần cập nhật</h3>";
        echo "<p>Dữ liệu đã đầy đủ và chính xác.</p>";
        echo "</div>";
        exit;
    }
    
    // Thực hiện update
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE username = 'admin'";
    
    echo "<h3>🔄 Đang thực hiện update...</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>$sql</pre>";
    
    $db->query($sql);
    
    echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>✅ CẬP NHẬT THÀNH CÔNG!</h3>";
    echo "</div>";
    
    // Lấy dữ liệu sau khi update
    $stmt = $db->query("SELECT * FROM users WHERE username = 'admin'");
    $updatedUser = $stmt->fetch();
    
    echo "<h3>📋 Dữ liệu SAU khi sửa:</h3>";
    echo "<ul>";
    echo "<li>Email: <strong>" . htmlspecialchars($updatedUser['email']) . "</strong></li>";
    echo "<li>is_active: <strong>" . $updatedUser['is_active'] . "</strong></li>";
    echo "</ul>";
    
    echo "<hr>";
    
    echo "<div style='background: #d1ecf1; border: 2px solid #17a2b8; padding: 20px;'>";
    echo "<h3>🎯 Bước tiếp theo:</h3>";
    echo "<ol>";
    echo "<li>Đăng xuất khỏi hệ thống</li>";
    echo "<li>Đăng nhập lại bằng tài khoản <strong>admin</strong></li>";
    echo "<li>Vào <strong>Thông tin cá nhân</strong></li>";
    echo "<li>Kiểm tra email và trạng thái đã hiển thị đúng chưa</li>";
    echo "</ol>";
    echo "<p><a href='index.php?page=auth&action=logout' style='display: inline-block; background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>🚪 Đăng xuất ngay</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 20px;'>";
    echo "<h3 style='color: #721c24;'>❌ LỖI:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}
</style>
