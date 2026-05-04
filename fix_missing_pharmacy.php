<?php
/**
 * Script sửa lỗi pharmacy_id không tồn tại
 */

session_start();

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/secure_session.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    die("❌ Vui lòng đăng nhập trước!");
}

// Lấy pharmacy_id từ session
require_once 'helpers/pharmacy.php';
$pharmacyId = getCurrentPharmacyId();

if (!$pharmacyId) {
    die("❌ Không tìm thấy pharmacy_id trong session!");
}

echo "<h2>🔧 Kiểm tra và sửa lỗi Pharmacy</h2>";
echo "<hr>";

$db = Database::getInstance();

try {
    // 1. Kiểm tra pharmacy có tồn tại không
    echo "<h3>1. Kiểm tra Pharmacy ID: {$pharmacyId}</h3>";
    
    $pharmacy = $db->query("SELECT * FROM pharmacies WHERE pharmacy_id = ?", [$pharmacyId])->fetch();
    
    if ($pharmacy) {
        echo "✅ Pharmacy đã tồn tại:<br>";
        echo "<ul>";
        echo "<li>ID: {$pharmacy['pharmacy_id']}</li>";
        echo "<li>Tên: {$pharmacy['pharmacy_name']}</li>";
        echo "<li>Mã: {$pharmacy['pharmacy_code']}</li>";
        echo "<li>Trạng thái: {$pharmacy['status']}</li>";
        echo "</ul>";
    } else {
        echo "❌ Pharmacy ID {$pharmacyId} KHÔNG TỒN TẠI!<br>";
        echo "<strong>Đang tạo pharmacy mới...</strong><br><br>";
        
        // Tạo pharmacy mới
        $pharmacyCode = 'PHARM' . str_pad($pharmacyId, 4, '0', STR_PAD_LEFT);
        $pharmacyName = 'Nhà thuốc ' . $pharmacyCode;
        
        $db->query(
            "INSERT INTO pharmacies (pharmacy_id, pharmacy_name, pharmacy_code, status) 
             VALUES (?, ?, ?, 'active')",
            [$pharmacyId, $pharmacyName, $pharmacyCode]
        );
        
        echo "✅ Đã tạo pharmacy mới:<br>";
        echo "<ul>";
        echo "<li>ID: {$pharmacyId}</li>";
        echo "<li>Tên: {$pharmacyName}</li>";
        echo "<li>Mã: {$pharmacyCode}</li>";
        echo "</ul>";
    }
    
    // 2. Kiểm tra user có thuộc pharmacy này không
    echo "<h3>2. Kiểm tra User</h3>";
    
    $userId = $_SESSION['user_id'];
    $user = $db->query("SELECT * FROM users WHERE user_id = ?", [$userId])->fetch();
    
    if ($user) {
        echo "✅ User hiện tại:<br>";
        echo "<ul>";
        echo "<li>ID: {$user['user_id']}</li>";
        echo "<li>Username: {$user['username']}</li>";
        echo "<li>Pharmacy ID: {$user['pharmacy_id']}</li>";
        echo "<li>Role: {$user['role']}</li>";
        echo "</ul>";
        
        // Kiểm tra pharmacy_id có khớp không
        if ($user['pharmacy_id'] != $pharmacyId) {
            echo "⚠️ CẢNH BÁO: User pharmacy_id ({$user['pharmacy_id']}) khác với session pharmacy_id ({$pharmacyId})<br>";
            echo "Đang cập nhật session...<br>";
            $_SESSION['pharmacy_id'] = $user['pharmacy_id'];
            echo "✅ Đã cập nhật session pharmacy_id = {$user['pharmacy_id']}<br>";
        }
    }
    
    // 3. Liệt kê tất cả pharmacies
    echo "<h3>3. Danh sách tất cả Pharmacies</h3>";
    
    $allPharmacies = $db->query("SELECT * FROM pharmacies ORDER BY pharmacy_id")->fetchAll();
    
    if (count($allPharmacies) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Tên</th><th>Mã</th><th>Trạng thái</th><th>Ngày tạo</th></tr>";
        foreach ($allPharmacies as $p) {
            $highlight = ($p['pharmacy_id'] == $pharmacyId) ? " style='background-color: #ffffcc;'" : "";
            echo "<tr{$highlight}>";
            echo "<td>{$p['pharmacy_id']}</td>";
            echo "<td>{$p['pharmacy_name']}</td>";
            echo "<td>{$p['pharmacy_code']}</td>";
            echo "<td>{$p['status']}</td>";
            echo "<td>{$p['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Không có pharmacy nào trong database!<br>";
    }
    
    // 4. Liệt kê tất cả users
    echo "<h3>4. Danh sách tất cả Users</h3>";
    
    $allUsers = $db->query("SELECT user_id, username, full_name, pharmacy_id, role FROM users ORDER BY user_id")->fetchAll();
    
    if (count($allUsers) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Họ tên</th><th>Pharmacy ID</th><th>Role</th></tr>";
        foreach ($allUsers as $u) {
            $highlight = ($u['user_id'] == $userId) ? " style='background-color: #ffffcc;'" : "";
            echo "<tr{$highlight}>";
            echo "<td>{$u['user_id']}</td>";
            echo "<td>{$u['username']}</td>";
            echo "<td>{$u['full_name']}</td>";
            echo "<td>{$u['pharmacy_id']}</td>";
            echo "<td>{$u['role']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH KIỂM TRA!</h2>";
    echo "<p><strong>Bước tiếp theo:</strong></p>";
    echo "<ol>";
    echo "<li><a href='generate_data_for_current_pharmacy.php'>Tạo dữ liệu mẫu cho pharmacy này</a></li>";
    echo "<li><a href='index.php'>Quay lại trang chủ</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
