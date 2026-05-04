<?php
/**
 * Đặt lại mật khẩu admin2 = 123456
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Reset mật khẩu admin2</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='p-5'>\n";

echo "<div class='container'>\n";
echo "<h2>Đặt lại mật khẩu admin2</h2>\n";

if (isset($_GET['reset'])) {
    // Đặt mật khẩu mới = 123456
    $newPassword = '123456';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Cập nhật vào database
    $result = $db->execute("UPDATE users SET password = ? WHERE username = 'admin2'", [$hashedPassword]);
    
    if ($result) {
        echo "<div class='alert alert-success'>";
        echo "<h4>✓ Đặt lại mật khẩu thành công!</h4>";
        echo "<p><strong>Username:</strong> admin2</p>";
        echo "<p><strong>Password mới:</strong> 123456</p>";
        echo "<p><strong>Hash:</strong> <code>$hashedPassword</code></p>";
        echo "</div>";
        
        echo "<a href='index.php?page=auth&action=login' class='btn btn-primary btn-lg'>Đăng nhập ngay</a>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "❌ Không thể cập nhật mật khẩu";
        echo "</div>";
    }
} else {
    // Hiển thị thông tin hiện tại
    $stmt = $db->query("SELECT * FROM users WHERE username = 'admin2'");
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<div class='card'>";
        echo "<div class='card-body'>";
        echo "<h5>Thông tin hiện tại:</h5>";
        echo "<p><strong>Username:</strong> {$user['username']}</p>";
        echo "<p><strong>Full name:</strong> {$user['full_name']}</p>";
        echo "<p><strong>Password hash:</strong> <code>" . htmlspecialchars($user['password']) . "</code></p>";
        
        // Kiểm tra password đã hash chưa
        if (strlen($user['password']) === 60 && strpos($user['password'], '$2y$') === 0) {
            echo "<p><span class='badge bg-success'>Đã hash</span></p>";
        } else {
            echo "<p><span class='badge bg-danger'>Plain text - Chưa hash</span></p>";
        }
        
        echo "</div>";
        echo "</div>";
        
        echo "<div class='alert alert-warning mt-3'>";
        echo "<h5>Đặt lại mật khẩu</h5>";
        echo "<p>Mật khẩu mới sẽ là: <strong>123456</strong></p>";
        echo "</div>";
        
        echo "<a href='reset_admin2_password.php?reset=1' class='btn btn-danger btn-lg'>Đặt lại mật khẩu</a>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "❌ Không tìm thấy user admin2";
        echo "</div>";
    }
}

echo "</div>\n";
echo "</body></html>\n";
