<?php
// Script để tạo lại users với password đúng
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Reset Users Script</h2>";

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Database.php';

try {
    $db = Database::getInstance();
    
    // Lấy danh sách users hiện tại
    echo "<h3>1. Checking existing users...</h3>";
    $stmt = $db->query("SELECT user_id, username, role FROM users ORDER BY user_id");
    $existingUsers = $stmt->fetchAll();
    echo "Found " . count($existingUsers) . " existing user(s)<br>";
    
    // Cập nhật hoặc tạo quản lý user
    echo "<h3>2. Setting up manager user...</h3>";
    $managerPassword = password_hash('quanly12345', PASSWORD_DEFAULT);
    
    // Tìm user có role manager
    $managerExists = false;
    foreach ($existingUsers as $user) {
        if ($user['role'] === 'manager') {
            $managerExists = $user['user_id'];
            break;
        }
    }
    
    if ($managerExists) {
        $db->execute(
            "UPDATE users SET username = ?, password = ?, full_name = ?, is_active = 1 WHERE user_id = ?",
            ['quanly', $managerPassword, 'Quản lý', $managerExists]
        );
        echo "✓ Manager user updated (ID: $managerExists)<br>";
    } else {
        $db->execute(
            "INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)",
            ['quanly', $managerPassword, 'Quản lý', 'manager', 1]
        );
        echo "✓ Manager user created<br>";
    }
    echo "Username: quanly<br>";
    echo "Password: quanly12345<br>";
    echo "Role: manager<br>";
    
    // Cập nhật hoặc tạo nhân viên user
    echo "<h3>3. Setting up staff user...</h3>";
    $staffPassword = password_hash('nhanvien2233', PASSWORD_DEFAULT);
    
    // Tìm user có role staff
    $staffExists = false;
    foreach ($existingUsers as $user) {
        if ($user['role'] === 'staff') {
            $staffExists = $user['user_id'];
            break;
        }
    }
    
    if ($staffExists) {
        $db->execute(
            "UPDATE users SET username = ?, password = ?, full_name = ?, is_active = 1 WHERE user_id = ?",
            ['nhanvien', $staffPassword, 'Nhân viên', $staffExists]
        );
        echo "✓ Staff user updated (ID: $staffExists)<br>";
    } else {
        $db->execute(
            "INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)",
            ['nhanvien', $staffPassword, 'Nhân viên', 'staff', 1]
        );
        echo "✓ Staff user created<br>";
    }
    echo "Username: nhanvien<br>";
    echo "Password: nhanvien2233<br>";
    echo "Role: staff<br>";
    
    // Verify users
    echo "<h3>4. Verifying users...</h3>";
    $stmt = $db->query("SELECT user_id, username, full_name, role, is_active FROM users");
    $users = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Active</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['user_id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['full_name'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test authentication
    echo "<h3>5. Testing authentication...</h3>";
    require_once 'models/User.php';
    $userModel = new User();
    
    $managerAuth = $userModel->authenticate('quanly', 'quanly12345');
    if ($managerAuth) {
        echo "✓ Manager authentication: <strong style='color:green'>SUCCESS</strong><br>";
    } else {
        echo "✗ Manager authentication: <strong style='color:red'>FAILED</strong><br>";
    }
    
    $staffAuth = $userModel->authenticate('nhanvien', 'nhanvien2233');
    if ($staffAuth) {
        echo "✓ Staff authentication: <strong style='color:green'>SUCCESS</strong><br>";
    } else {
        echo "✗ Staff authentication: <strong style='color:red'>FAILED</strong><br>";
    }
    
    echo "<h3 style='color:green'>✓ Done! You can now login with:</h3>";
    echo "<ul>";
    echo "<li><strong>quanly / quanly12345</strong> (Manager role)</li>";
    echo "<li><strong>nhanvien / nhanvien2233</strong> (Staff role)</li>";
    echo "</ul>";
    
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
