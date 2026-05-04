<?php
/**
 * Tự động hash tất cả mật khẩu plain text trong database
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Sửa mật khẩu</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='p-5'>\n";

echo "<div class='container'>\n";
echo "<h2>Sửa tất cả mật khẩu plain text</h2>\n";

if (isset($_GET['fix'])) {
    echo "<div class='alert alert-info'>Đang xử lý...</div>";
    
    // Lấy tất cả users
    $stmt = $db->query("SELECT user_id, username, password FROM users");
    $users = $stmt->fetchAll();
    
    $fixed = 0;
    $skipped = 0;
    
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Username</th><th>Trạng thái</th><th>Hành động</th></tr></thead>";
    echo "<tbody>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td><strong>{$user['username']}</strong></td>";
        
        // Kiểm tra xem password đã được hash chưa
        if (strlen($user['password']) === 60 && strpos($user['password'], '$2y$') === 0) {
            echo "<td><span class='badge bg-success'>Đã hash</span></td>";
            echo "<td>Bỏ qua</td>";
            $skipped++;
        } else {
            // Password chưa hash - hash nó
            $plainPassword = $user['password'];
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
            
            $db->execute("UPDATE users SET password = ? WHERE user_id = ?", [$hashedPassword, $user['user_id']]);
            
            echo "<td><span class='badge bg-warning'>Plain text</span></td>";
            echo "<td><span class='text-success'>✓ Đã hash</span><br>";
            echo "<small>Mật khẩu cũ: <code>" . htmlspecialchars($plainPassword) . "</code></small></td>";
            $fixed++;
        }
        
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    
    echo "<div class='alert alert-success'>";
    echo "<h4>Hoàn thành!</h4>";
    echo "<p>✓ Đã sửa: <strong>$fixed</strong> users</p>";
    echo "<p>○ Bỏ qua: <strong>$skipped</strong> users (đã hash từ trước)</p>";
    echo "</div>";
    
    echo "<a href='index.php?page=auth&action=login' class='btn btn-primary btn-lg'>Đăng nhập</a>";
    
} else {
    // Hiển thị danh sách users cần sửa
    $stmt = $db->query("SELECT user_id, username, password FROM users");
    $users = $stmt->fetchAll();
    
    $needFix = 0;
    $alreadyHashed = 0;
    
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Username</th><th>Password</th><th>Trạng thái</th></tr></thead>";
    echo "<tbody>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td><code>" . substr($user['password'], 0, 30) . "...</code></td>";
        
        if (strlen($user['password']) === 60 && strpos($user['password'], '$2y$') === 0) {
            echo "<td><span class='badge bg-success'>Đã hash</span></td>";
            $alreadyHashed++;
        } else {
            echo "<td><span class='badge bg-danger'>Plain text - CẦN SỬA</span></td>";
            $needFix++;
        }
        
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    
    echo "<div class='alert alert-warning'>";
    echo "<h4>Tổng kết:</h4>";
    echo "<p>✓ Đã hash: <strong>$alreadyHashed</strong> users</p>";
    echo "<p>✗ Cần sửa: <strong>$needFix</strong> users</p>";
    echo "</div>";
    
    if ($needFix > 0) {
        echo "<div class='alert alert-info'>";
        echo "<h5>Lưu ý:</h5>";
        echo "<p>Script sẽ hash tất cả mật khẩu plain text. Mật khẩu cũ sẽ được giữ nguyên (hash từ plain text).</p>";
        echo "<p>Ví dụ: Nếu mật khẩu hiện tại là <code>t2/14sbi7d</code>, sau khi hash bạn vẫn đăng nhập bằng <code>t2/14sbi7d</code></p>";
        echo "</div>";
        
        echo "<a href='fix_all_passwords.php?fix=1' class='btn btn-danger btn-lg'>Sửa tất cả mật khẩu</a>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h4>✓ Tất cả mật khẩu đã được hash!</h4>";
        echo "</div>";
        
        echo "<a href='index.php?page=auth&action=login' class='btn btn-primary btn-lg'>Đăng nhập</a>";
    }
}

echo "</div>\n";
echo "</body></html>\n";
