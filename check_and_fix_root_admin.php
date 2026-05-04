<?php
/**
 * Script kiểm tra và sửa lỗi Root Admin
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Kiểm tra hệ thống Root Admin</h2>";
    echo "<hr>";
    
    // 1. Kiểm tra cột is_root_admin có tồn tại không
    echo "<h3>1. Kiểm tra cột is_root_admin</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_root_admin'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "❌ Cột is_root_admin CHƯA tồn tại<br>";
        echo "📝 Đang thêm cột...<br>";
        
        $pdo->exec("ALTER TABLE users ADD COLUMN is_root_admin TINYINT(1) DEFAULT 0 AFTER role");
        echo "✅ Đã thêm cột is_root_admin<br>";
    } else {
        echo "✅ Cột is_root_admin đã tồn tại<br>";
    }
    
    echo "<hr>";
    
    // 2. Kiểm tra user admin3
    echo "<h3>2. Kiểm tra user admin3</h3>";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['admin3']);
    $admin3 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin3) {
        echo "✅ Tìm thấy user admin3<br>";
        echo "- User ID: " . $admin3['user_id'] . "<br>";
        echo "- Pharmacy ID: " . $admin3['pharmacy_id'] . "<br>";
        echo "- Role: " . $admin3['role'] . "<br>";
        echo "- is_root_admin: " . ($admin3['is_root_admin'] ?? 'NULL') . "<br>";
        
        // Nếu chưa phải root admin, set thành root admin
        if (empty($admin3['is_root_admin'])) {
            echo "<br>📝 Đang đặt admin3 làm Root Admin...<br>";
            $stmt = $pdo->prepare("UPDATE users SET is_root_admin = 1 WHERE user_id = ?");
            $stmt->execute([$admin3['user_id']]);
            echo "✅ Đã đặt admin3 làm Root Admin<br>";
        } else {
            echo "<br>✅ admin3 đã là Root Admin<br>";
        }
    } else {
        echo "❌ KHÔNG tìm thấy user admin3<br>";
    }
    
    echo "<hr>";
    
    // 3. Kiểm tra user nhanvienct2
    echo "<h3>3. Kiểm tra user nhanvienct2</h3>";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['nhanvienct2']);
    $nhanvienct2 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($nhanvienct2) {
        echo "✅ Tìm thấy user nhanvienct2<br>";
        echo "- User ID: " . $nhanvienct2['user_id'] . "<br>";
        echo "- Pharmacy ID: " . $nhanvienct2['pharmacy_id'] . "<br>";
        echo "- Role: " . $nhanvienct2['role'] . "<br>";
        echo "- is_root_admin: " . ($nhanvienct2['is_root_admin'] ?? 'NULL') . "<br>";
        
        // Đảm bảo nhanvienct2 KHÔNG phải root admin
        if (!empty($nhanvienct2['is_root_admin'])) {
            echo "<br>📝 Đang bỏ root admin của nhanvienct2...<br>";
            $stmt = $pdo->prepare("UPDATE users SET is_root_admin = 0 WHERE user_id = ?");
            $stmt->execute([$nhanvienct2['user_id']]);
            echo "✅ Đã bỏ root admin của nhanvienct2<br>";
        } else {
            echo "<br>✅ nhanvienct2 KHÔNG phải Root Admin (đúng)<br>";
        }
    } else {
        echo "❌ KHÔNG tìm thấy user nhanvienct2<br>";
    }
    
    echo "<hr>";
    
    // 4. Hiển thị tất cả users trong cùng pharmacy
    if ($admin3) {
        echo "<h3>4. Tất cả users trong pharmacy của admin3</h3>";
        $stmt = $pdo->prepare("
            SELECT user_id, username, full_name, role, is_root_admin, pharmacy_id
            FROM users 
            WHERE pharmacy_id = ?
            ORDER BY is_root_admin DESC, role DESC, user_id ASC
        ");
        $stmt->execute([$admin3['pharmacy_id']]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>User ID</th><th>Username</th><th>Họ tên</th><th>Role</th><th>is_root_admin</th><th>Loại</th>";
        echo "</tr>";
        
        foreach ($users as $user) {
            $type = '';
            $color = '';
            if ($user['is_root_admin'] == 1) {
                $type = '🛡️ Admin gốc';
                $color = '#ffcccc';
            } elseif ($user['role'] === 'manager') {
                $type = '👤 Admin được phân quyền';
                $color = '#cce5ff';
            } else {
                $type = '👤 Nhân viên';
                $color = '#ccffcc';
            }
            
            echo "<tr style='background: $color;'>";
            echo "<td>" . $user['user_id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($user['username']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['is_root_admin'] . "</td>";
            echo "<td>" . $type . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Hoàn thành kiểm tra!</h3>";
    echo "<p><strong>Bước tiếp theo:</strong></p>";
    echo "<ol>";
    echo "<li>Logout khỏi tài khoản admin3</li>";
    echo "<li>Login lại bằng admin3</li>";
    echo "<li>Vào trang Quản lý người dùng</li>";
    echo "<li>Thử edit user nhanvienct2</li>";
    echo "</ol>";
    
    echo "<br><a href='index.php?page=auth&action=logout' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Logout ngay</a>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #ffeeee; border: 1px solid red;'>";
    echo "<h3>❌ Lỗi:</h3>";
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
h2, h3 {
    color: #333;
}
hr {
    margin: 20px 0;
    border: none;
    border-top: 2px solid #ddd;
}
table {
    width: 100%;
    margin: 20px 0;
}
th {
    text-align: left;
}
</style>
