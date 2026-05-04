<?php
echo "<h2>🔄 Cập nhật Users Database</h2>";
echo "<hr>";

// Thông tin kết nối
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "qlnt_db";

// Kết nối database
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; border-radius: 5px;'>";
    echo "❌ Không thể kết nối database: " . mysqli_connect_error();
    echo "</div>";
    exit;
}

echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
echo "✅ Kết nối database thành công!";
echo "</div>";

// Xóa tất cả users cũ
echo "<h3>🗑️ Xóa users cũ...</h3>";
$delete_sql = "DELETE FROM users";
if (mysqli_query($conn, $delete_sql)) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px;'>";
    echo "✅ Đã xóa tất cả users cũ";
    echo "</div>";
} else {
    echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; border-radius: 5px;'>";
    echo "❌ Lỗi xóa users cũ: " . mysqli_error($conn);
    echo "</div>";
}

// Reset AUTO_INCREMENT
mysqli_query($conn, "ALTER TABLE users AUTO_INCREMENT = 1");

echo "<h3>👥 Thêm users mới...</h3>";

// Tạo password hash cho 123456
$password_hash = password_hash('123456', PASSWORD_DEFAULT);

// Danh sách users mới theo yêu cầu
$users = [
    ['admin', $password_hash, 'Quản lý', '0123456789', 'manager'],
    ['nhanvien1', $password_hash, 'Nhân viên 1', '0987654321', 'staff'],
    ['nhanvien2', $password_hash, 'Nhân viên 2', '0912345678', 'staff']
];

$insert_sql = "INSERT INTO users (username, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $insert_sql);

$success_count = 0;
$error_count = 0;

foreach ($users as $user_data) {
    mysqli_stmt_bind_param($stmt, "sssss", $user_data[0], $user_data[1], $user_data[2], $user_data[3], $user_data[4]);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div style='background: #efe; border: 1px solid #0a0; padding: 5px; border-radius: 3px; margin: 5px 0;'>";
        echo "✅ Thêm user '<strong>{$user_data[0]}</strong>' - {$user_data[2]} ({$user_data[4]})";
        echo "</div>";
        $success_count++;
    } else {
        echo "<div style='background: #fee; border: 1px solid #f00; padding: 5px; border-radius: 3px; margin: 5px 0;'>";
        echo "❌ Lỗi thêm user '{$user_data[0]}': " . mysqli_error($conn);
        echo "</div>";
        $error_count++;
    }
}

mysqli_stmt_close($stmt);

echo "<h3>📊 Kết quả:</h3>";
echo "<div style='background: #e7f3ff; border: 1px solid #007bff; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Thống kê:</strong></p>";
echo "<ul>";
echo "<li>✅ Thành công: $success_count users</li>";
echo "<li>❌ Lỗi: $error_count users</li>";
echo "</ul>";
echo "</div>";

// Hiển thị danh sách users hiện tại
echo "<h3>👤 Danh sách users hiện tại:</h3>";
$list_sql = "SELECT username, full_name, role, created_at FROM users ORDER BY user_id";
$result = mysqli_query($conn, $list_sql);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa; border: 1px solid #dee2e6;'>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Username</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Họ tên</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Vai trò</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Ngày tạo</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $role_color = $row['role'] === 'manager' ? '#28a745' : '#007bff';
        echo "<tr>";
        echo "<td style='border: 1px solid #dee2e6; padding: 8px;'><strong>{$row['username']}</strong></td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$row['full_name']}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 8px; color: $role_color;'><strong>{$row['role']}</strong></td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test đăng nhập với các users mới
echo "<h3>🧪 Test đăng nhập:</h3>";

$test_users = [
    ['admin', '123456'],
    ['nhanvien1', '123456'],
    ['nhanvien2', '123456']
];

foreach ($test_users as $test_user) {
    $test_sql = "SELECT * FROM users WHERE username = ? AND is_active = 1";
    $test_stmt = mysqli_prepare($conn, $test_sql);
    mysqli_stmt_bind_param($test_stmt, "s", $test_user[0]);
    mysqli_stmt_execute($test_stmt);
    $test_result = mysqli_stmt_get_result($test_stmt);
    $user_row = mysqli_fetch_assoc($test_result);
    
    if ($user_row && password_verify($test_user[1], $user_row['password'])) {
        echo "<div style='background: #efe; border: 1px solid #0a0; padding: 5px; border-radius: 3px; margin: 5px 0;'>";
        echo "✅ <strong>{$test_user[0]}</strong> / {$test_user[1]} - Đăng nhập OK";
        echo "</div>";
    } else {
        echo "<div style='background: #fee; border: 1px solid #f00; padding: 5px; border-radius: 3px; margin: 5px 0;'>";
        echo "❌ <strong>{$test_user[0]}</strong> / {$test_user[1]} - Đăng nhập THẤT BẠI";
        echo "</div>";
    }
    mysqli_stmt_close($test_stmt);
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>🎯 Thông tin đăng nhập:</h3>";
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Các tài khoản có thể đăng nhập với mật khẩu 123456:</strong></p>";
echo "<ul>";
echo "<li><strong>admin</strong> / 123456 (Manager)</li>";
echo "<li><strong>nhanvien1</strong> / 123456 (Staff)</li>";
echo "<li><strong>nhanvien2</strong> / 123456 (Staff)</li>";
echo "</ul>";
echo "<br>";
echo "<p><a href='index.php?page=auth&action=login' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Đi đến trang đăng nhập</a></p>";
echo "</div>";
?>