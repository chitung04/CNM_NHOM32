tf<?php
/**
 * Script cập nhật thông tin đăng nhập người dùng
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>Cập nhật thông tin người dùng</h2>";
    
    // Cập nhật tài khoản quản lý
    $username1 = 'quanly';
    $password1 = 'quanly12345';
    $hashedPassword1 = password_hash($password1, PASSWORD_DEFAULT);
    
    // Kiểm tra xem user manager có tồn tại không
    $checkSql = "SELECT user_id, username FROM users WHERE role = 'manager' LIMIT 1";
    $stmt = $db->query($checkSql);
    $managerUser = $stmt->fetch();
    
    if ($managerUser) {
        $sql1 = "UPDATE users SET username = ?, password = ?, is_active = 1 WHERE user_id = ?";
        $db->execute($sql1, [$username1, $hashedPassword1, $managerUser['user_id']]);
        echo "<p style='color: green;'>✓ Đã cập nhật tài khoản Quản lý: <strong>$username1</strong> / <strong>$password1</strong></p>";
    } else {
        // Tạo mới nếu chưa có
        $sql1 = "INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, 1)";
        $db->execute($sql1, [$username1, $hashedPassword1, 'Quản lý', 'manager']);
        echo "<p style='color: green;'>✓ Đã tạo tài khoản Quản lý: <strong>$username1</strong> / <strong>$password1</strong></p>";
    }
    
    // Cập nhật tài khoản nhân viên
    $username2 = 'nhanvien';
    $password2 = 'nhanvien2233';
    $hashedPassword2 = password_hash($password2, PASSWORD_DEFAULT);
    
    // Kiểm tra xem user staff có tồn tại không
    $checkSql2 = "SELECT user_id, username FROM users WHERE role = 'staff' LIMIT 1";
    $stmt2 = $db->query($checkSql2);
    $staffUser = $stmt2->fetch();
    
    if ($staffUser) {
        $sql2 = "UPDATE users SET username = ?, password = ?, is_active = 1 WHERE user_id = ?";
        $db->execute($sql2, [$username2, $hashedPassword2, $staffUser['user_id']]);
        echo "<p style='color: green;'>✓ Đã cập nhật tài khoản Nhân viên: <strong>$username2</strong> / <strong>$password2</strong></p>";
    } else {
        // Tạo mới nếu chưa có
        $sql2 = "INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, 1)";
        $db->execute($sql2, [$username2, $hashedPassword2, 'Nhân viên', 'staff']);
        echo "<p style='color: green;'>✓ Đã tạo tài khoản Nhân viên: <strong>$username2</strong> / <strong>$password2</strong></p>";
    }
    
    echo "<hr>";
    echo "<h3>Thông tin đăng nhập mới:</h3>";
    echo "<ul>";
    echo "<li><strong>Quản lý:</strong> $username1 / $password1</li>";
    echo "<li><strong>Nhân viên:</strong> $username2 / $password2</li>";
    echo "</ul>";
    
    echo "<p><a href='login.php'>Đi đến trang đăng nhập</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>
