<?php
echo "<h2>🔍 Kiểm tra trạng thái MySQL</h2>";
echo "<hr>";

// Lấy thông tin từ .env
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "qlnt_db";

echo "<h3>📋 Thông tin kết nối:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>User:</strong> $user</li>";
echo "<li><strong>Password:</strong> " . (empty($pass) ? "(trống)" : "có mật khẩu") . "</li>";
echo "<li><strong>Database:</strong> $dbname</li>";
echo "</ul>";

echo "<h3>🔌 Test kết nối MySQL Server:</h3>";

// Test 1: Kết nối MySQL server
$conn = @mysqli_connect($host, $user, $pass);
if (!$conn) {
    echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>KHÔNG THỂ KẾT NỐI MYSQL SERVER</strong><br>";
    echo "Lỗi: " . mysqli_connect_error() . "<br><br>";
    echo "<strong>Nguyên nhân có thể:</strong><br>";
    echo "• XAMPP MySQL chưa được khởi động<br>";
    echo "• Port 3306 bị chiếm bởi ứng dụng khác<br>";
    echo "• MySQL service bị lỗi<br><br>";
    echo "<strong>Giải pháp:</strong><br>";
    echo "1. Mở XAMPP Control Panel<br>";
    echo "2. Click 'Start' cho MySQL<br>";
    echo "3. Kiểm tra không có lỗi trong log<br>";
    echo "</div>";
    exit;
} else {
    echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>KẾT NỐI MYSQL SERVER THÀNH CÔNG!</strong><br>";
    echo "MySQL version: " . mysqli_get_server_info($conn);
    echo "</div>";
}

echo "<h3>🗄️ Test kết nối Database:</h3>";

// Test 2: Kết nối database cụ thể
$db_conn = @mysqli_connect($host, $user, $pass, $dbname);
if (!$db_conn) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "⚠️ <strong>DATABASE '$dbname' CHƯA TỒN TẠI</strong><br>";
    echo "Lỗi: " . mysqli_connect_error() . "<br><br>";
    echo "Đang thử tạo database...<br>";
    
    // Thử tạo database
    $create_db = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (mysqli_query($conn, $create_db)) {
        echo "✅ Tạo database '$dbname' thành công!<br>";
        $db_conn = mysqli_connect($host, $user, $pass, $dbname);
        if ($db_conn) {
            echo "✅ Kết nối database '$dbname' thành công!";
        }
    } else {
        echo "❌ Không thể tạo database: " . mysqli_error($conn);
    }
    echo "</div>";
} else {
    echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>KẾT NỐI DATABASE '$dbname' THÀNH CÔNG!</strong>";
    echo "</div>";
}

if ($db_conn) {
    echo "<h3>📊 Kiểm tra bảng trong database:</h3>";
    
    // Liệt kê các bảng
    $tables_query = "SHOW TABLES";
    $result = mysqli_query($db_conn, $tables_query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<div style='background: #e7f3ff; border: 1px solid #007bff; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "📋 <strong>Các bảng trong database:</strong><br>";
        echo "<ul>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        // Kiểm tra bảng users cụ thể
        $users_check = "SELECT COUNT(*) as total FROM users";
        $users_result = @mysqli_query($db_conn, $users_check);
        if ($users_result) {
            $users_count = mysqli_fetch_assoc($users_result);
            echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "👥 <strong>Bảng 'users':</strong> Có {$users_count['total']} người dùng";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "⚠️ <strong>Database trống - chưa có bảng nào</strong><br>";
        echo "Cần chạy setup để tạo các bảng cần thiết.";
        echo "</div>";
    }
}

echo "<h3>🧪 Test hệ thống hiện tại:</h3>";

// Test hệ thống authentication
try {
    require_once 'models/Database.php';
    $db_instance = Database::getInstance();
    echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>HỆ THỐNG DATABASE CLASS HOẠT ĐỘNG BÌNH THƯỜNG</strong>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>HỆ THỐNG DATABASE CLASS GẶP LỖI:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
}

// Đóng kết nối
if ($conn) mysqli_close($conn);
if ($db_conn) mysqli_close($db_conn);

echo "<hr>";
echo "<h3>🎯 Kết luận:</h3>";
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Để hệ thống hoạt động hoàn hảo, bạn cần:</strong></p>";
echo "<ol>";
echo "<li>✅ MySQL Server đang chạy</li>";
echo "<li>✅ Database '$dbname' tồn tại</li>";
echo "<li>✅ Bảng 'users' có dữ liệu</li>";
echo "<li>✅ Hệ thống authentication hoạt động</li>";
echo "</ol>";
echo "<br>";
echo "<p><strong>Liên kết hữu ích:</strong></p>";
echo "<p>";
echo "<a href='test_connection.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>🔧 Chạy Setup Tự Động</a>";
echo "<a href='index.php?page=auth&action=login' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🚀 Đi đến Login</a>";
echo "</p>";
echo "</div>";
?>