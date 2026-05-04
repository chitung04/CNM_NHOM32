<?php
/**
 * Xem tất cả mật khẩu trong database
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Xem mật khẩu</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>
.password-cell { 
    font-family: monospace; 
    font-size: 11px; 
    max-width: 400px; 
    word-break: break-all;
}
</style>";
echo "</head><body class='p-5'>\n";

echo "<div class='container-fluid'>\n";
echo "<h2>Danh sách Users & Passwords</h2>\n";

$stmt = $db->query("SELECT user_id, username, password, full_name, email, phone, role, pharmacy_id, is_active FROM users ORDER BY user_id");
$users = $stmt->fetchAll();

echo "<table class='table table-bordered table-striped'>";
echo "<thead class='table-dark'>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>Username</th>";
echo "<th>Full Name</th>";
echo "<th>Password Hash</th>";
echo "<th>Trạng thái</th>";
echo "<th>Role</th>";
echo "<th>Pharmacy ID</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($users as $user) {
    echo "<tr>";
    echo "<td>{$user['user_id']}</td>";
    echo "<td><strong>{$user['username']}</strong></td>";
    echo "<td>{$user['full_name']}</td>";
    
    // Password
    echo "<td class='password-cell'>";
    echo "<code>" . htmlspecialchars($user['password']) . "</code><br>";
    
    // Kiểm tra đã hash chưa
    if (strlen($user['password']) === 60 && strpos($user['password'], '$2y$') === 0) {
        echo "<span class='badge bg-success mt-1'>Đã hash (bcrypt)</span>";
    } else {
        echo "<span class='badge bg-danger mt-1'>Plain text</span>";
    }
    echo "</td>";
    
    // Status
    echo "<td>";
    if ($user['is_active']) {
        echo "<span class='badge bg-success'>Active</span>";
    } else {
        echo "<span class='badge bg-secondary'>Inactive</span>";
    }
    echo "</td>";
    
    // Role
    echo "<td>";
    if ($user['role'] === 'manager') {
        echo "<span class='badge bg-primary'>Manager</span>";
    } else {
        echo "<span class='badge bg-info'>Staff</span>";
    }
    echo "</td>";
    
    // Pharmacy ID
    echo "<td>{$user['pharmacy_id']}</td>";
    
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

echo "<div class='alert alert-info'>";
echo "<h5>Lưu ý:</h5>";
echo "<ul>";
echo "<li><strong>Đã hash:</strong> Mật khẩu đã được mã hóa bằng bcrypt (bắt đầu bằng <code>\$2y\$</code>)</li>";
echo "<li><strong>Plain text:</strong> Mật khẩu chưa được mã hóa - CẦN SỬA</li>";
echo "<li>Mật khẩu đã hash KHÔNG THỂ giải mã ngược lại</li>";
echo "<li>Để đăng nhập, bạn cần biết mật khẩu gốc (trước khi hash)</li>";
echo "</ul>";
echo "</div>";

echo "<div class='mt-3'>";
echo "<a href='fix_all_passwords.php' class='btn btn-warning'>Sửa mật khẩu plain text</a> ";
echo "<a href='reset_admin2_password.php' class='btn btn-danger'>Reset mật khẩu admin2</a> ";
echo "<a href='index.php?page=auth&action=login' class='btn btn-primary'>Đăng nhập</a>";
echo "</div>";

echo "</div>\n";
echo "</body></html>\n";
