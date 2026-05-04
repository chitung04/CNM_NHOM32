<?php
/**
 * Sửa trạng thái is_active cho tất cả users
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<h2>🔧 Sửa trạng thái tất cả users</h2>";
echo "<hr>";

try {
    // Lấy tất cả users
    $stmt = $db->query("SELECT user_id, username, full_name, email, is_active FROM users ORDER BY user_id");
    $users = $stmt->fetchAll();
    
    echo "<h3>📊 Danh sách users hiện tại:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Username</th><th>Họ tên</th><th>Email</th><th>is_active</th><th>Trạng thái</th></tr>";
    
    $needFix = [];
    
    foreach ($users as $user) {
        $isActive = $user['is_active'] ?? 0;
        $status = $isActive ? "✅ OK" : "❌ Cần sửa";
        $color = $isActive ? "green" : "red";
        
        if (!$isActive) {
            $needFix[] = $user['user_id'];
        }
        
        echo "<tr>";
        echo "<td>{$user['user_id']}</td>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>" . ($user['email'] ?: '<em>Chưa có</em>') . "</td>";
        echo "<td style='text-align: center;'><strong>$isActive</strong></td>";
        echo "<td style='color: $color; font-weight: bold;'>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    
    if (count($needFix) > 0) {
        echo "<div style='background: #fff3cd; border: 2px solid #ffc107; padding: 20px;'>";
        echo "<h3>⚠️ Tìm thấy " . count($needFix) . " user cần sửa</h3>";
        echo "<p>User IDs: " . implode(", ", $needFix) . "</p>";
        echo "</div>";
        
        // Thực hiện fix
        echo "<h3>🔄 Đang sửa...</h3>";
        
        $sql = "UPDATE users SET is_active = 1 WHERE is_active = 0 OR is_active IS NULL";
        $db->query($sql);
        
        echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px; margin: 20px 0;'>";
        echo "<h3 style='color: #155724;'>✅ ĐÃ SỬA THÀNH CÔNG!</h3>";
        echo "<p>Đã set <strong>is_active = 1</strong> cho " . count($needFix) . " users.</p>";
        echo "</div>";
        
        // Hiển thị kết quả sau khi fix
        echo "<h3>📊 Kết quả sau khi sửa:</h3>";
        $stmt = $db->query("SELECT user_id, username, full_name, is_active FROM users ORDER BY user_id");
        $updatedUsers = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Username</th><th>Họ tên</th><th>is_active</th><th>Trạng thái</th></tr>";
        
        foreach ($updatedUsers as $user) {
            $isActive = $user['is_active'];
            $status = $isActive ? "✅ Đang hoạt động" : "❌ Không hoạt động";
            $color = $isActive ? "green" : "red";
            
            echo "<tr>";
            echo "<td>{$user['user_id']}</td>";
            echo "<td><strong>{$user['username']}</strong></td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td style='text-align: center;'><strong>$isActive</strong></td>";
            echo "<td style='color: $color; font-weight: bold;'>$status</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
    } else {
        echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px;'>";
        echo "<h3 style='color: #155724;'>✅ TẤT CẢ USERS ĐỀU OK!</h3>";
        echo "<p>Không có user nào cần sửa. Tất cả đều có is_active = 1.</p>";
        echo "</div>";
    }
    
    echo "<hr>";
    
    echo "<div style='background: #d1ecf1; border: 2px solid #17a2b8; padding: 20px;'>";
    echo "<h3>🎯 Bước tiếp theo:</h3>";
    echo "<ol>";
    echo "<li>Đăng xuất khỏi hệ thống</li>";
    echo "<li>Đăng nhập lại</li>";
    echo "<li>Vào <strong>Thông tin cá nhân</strong></li>";
    echo "<li>Kiểm tra trạng thái hiển thị <strong>'Đang hoạt động'</strong></li>";
    echo "</ol>";
    echo "<p><a href='index.php?page=profile' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>👤 Xem thông tin cá nhân</a></p>";
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
    max-width: 1400px;
    margin: 0 auto;
}
table {
    margin: 20px 0;
}
th {
    text-align: left;
}
</style>
