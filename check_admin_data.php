<?php
/**
 * Kiểm tra dữ liệu user admin
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<h2>🔍 Kiểm tra dữ liệu user admin</h2>";
echo "<hr>";

// Lấy thông tin user admin
$stmt = $db->query("SELECT * FROM users WHERE username = 'admin'");
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='color: red;'>❌ Không tìm thấy user admin</p>";
    exit;
}

echo "<h3>📊 Dữ liệu hiện tại:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Value</th><th>Status</th></tr>";

$fields = [
    'user_id' => 'ID',
    'username' => 'Username',
    'full_name' => 'Họ tên',
    'email' => 'Email',
    'phone' => 'Số điện thoại',
    'role' => 'Vai trò',
    'is_active' => 'Trạng thái',
    'is_root_admin' => 'Root Admin',
    'pharmacy_id' => 'Pharmacy ID',
    'created_at' => 'Ngày tạo'
];

foreach ($fields as $field => $label) {
    $value = $user[$field] ?? 'NULL';
    $isEmpty = empty($value) && $value !== '0';
    $status = $isEmpty ? "❌ EMPTY" : "✅ OK";
    $color = $isEmpty ? "red" : "green";
    
    echo "<tr>";
    echo "<td><strong>$label</strong></td>";
    echo "<td>" . htmlspecialchars($value) . "</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";

// Kiểm tra vấn đề cụ thể
echo "<h3>🐛 Phân tích vấn đề:</h3>";

$issues = [];

if (empty($user['email'])) {
    $issues[] = "❌ Email bị NULL hoặc empty → Hiển thị 'Chưa cập nhật'";
}

if (!isset($user['is_active']) || $user['is_active'] == 0) {
    $issues[] = "❌ is_active = 0 hoặc NULL → Hiển thị 'Không hoạt động'";
}

if (count($issues) > 0) {
    echo "<div style='background: #ffeeee; border: 2px solid red; padding: 20px;'>";
    echo "<h4>Vấn đề tìm thấy:</h4>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // Đề xuất fix
    echo "<hr>";
    echo "<h3>🔧 Cách sửa:</h3>";
    echo "<div style='background: #eeffee; border: 2px solid green; padding: 20px;'>";
    echo "<h4>Chạy SQL sau để fix:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
    echo "UPDATE users \n";
    echo "SET is_active = 1";
    
    if (empty($user['email'])) {
        echo ",\n    email = 'admin@duopharma.com'";
    }
    
    echo "\nWHERE username = 'admin';";
    echo "</pre>";
    
    echo "<p><a href='fix_admin_data.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>🔧 Tự động sửa ngay</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #eeffee; border: 2px solid green; padding: 20px;'>";
    echo "<h4>✅ Không có vấn đề!</h4>";
    echo "<p>Dữ liệu user admin đầy đủ và chính xác.</p>";
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
table {
    width: 100%;
    margin: 20px 0;
}
th {
    background: #f0f0f0;
    text-align: left;
}
</style>
