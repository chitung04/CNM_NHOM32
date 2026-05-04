<?php
/**
 * Đặt lại mật khẩu cho bất kỳ user nào
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Reset mật khẩu</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='p-5'>\n";

echo "<div class='container'>\n";
echo "<h2>Đặt lại mật khẩu User</h2>\n";

if (isset($_POST['reset'])) {
    $username = $_POST['username'];
    $newPassword = $_POST['new_password'];
    
    // Hash mật khẩu mới
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Cập nhật vào database
    $result = $db->execute("UPDATE users SET password = ? WHERE username = ?", [$hashedPassword, $username]);
    
    if ($result) {
        echo "<div class='alert alert-success'>";
        echo "<h4>✓ Đặt lại mật khẩu thành công!</h4>";
        echo "<p><strong>Username:</strong> $username</p>";
        echo "<p><strong>Password mới:</strong> $newPassword</p>";
        echo "</div>";
        
        echo "<a href='index.php?page=auth&action=login' class='btn btn-primary btn-lg'>Đăng nhập ngay</a>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "❌ Không thể cập nhật mật khẩu";
        echo "</div>";
    }
} else {
    // Hiển thị form
    $stmt = $db->query("SELECT user_id, username, full_name, role FROM users ORDER BY username");
    $users = $stmt->fetchAll();
    
    echo "<div class='card'>";
    echo "<div class='card-body'>";
    echo "<form method='POST'>";
    
    echo "<div class='mb-3'>";
    echo "<label class='form-label'>Chọn user:</label>";
    echo "<select name='username' class='form-select' required>";
    foreach ($users as $user) {
        $role = $user['role'] === 'manager' ? 'Manager' : 'Staff';
        echo "<option value='{$user['username']}'>{$user['username']} - {$user['full_name']} ($role)</option>";
    }
    echo "</select>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<label class='form-label'>Mật khẩu mới:</label>";
    echo "<input type='text' name='new_password' class='form-control' value='123456' required>";
    echo "<small class='text-muted'>Mật khẩu mặc định: 123456</small>";
    echo "</div>";
    
    echo "<button type='submit' name='reset' class='btn btn-danger btn-lg'>Đặt lại mật khẩu</button>";
    echo "</form>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='alert alert-warning mt-3'>";
    echo "<h5>⚠️ Lưu ý:</h5>";
    echo "<ul>";
    echo "<li>Mật khẩu cũ sẽ bị XÓA và thay bằng mật khẩu mới</li>";
    echo "<li>User sẽ phải đăng nhập bằng mật khẩu mới</li>";
    echo "<li>Không thể khôi phục mật khẩu cũ</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>\n";
echo "</body></html>\n";
