<?php
/**
 * Kiểm tra session hiện tại
 */

session_start();

echo "<h2>🔍 KIỂM TRA SESSION HIỆN TẠI</h2>";
echo "<hr>";

echo "<h3>📋 Thông tin Session:</h3>";

if (empty($_SESSION)) {
    echo "<p style='color: red; font-weight: bold;'>❌ CHƯA ĐĂNG NHẬP!</p>";
    echo "<p><a href='index.php?page=login'><strong>→ Đăng nhập ngay</strong></a></p>";
} else {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Key</th><th>Value</th></tr>";
    
    foreach ($_SESSION as $key => $value) {
        if (is_array($value)) {
            $displayValue = '<pre>' . print_r($value, true) . '</pre>';
        } else {
            $displayValue = htmlspecialchars($value);
        }
        echo "<tr><td><strong>{$key}</strong></td><td>{$displayValue}</td></tr>";
    }
    
    echo "</table>";
    
    // Kiểm tra các key quan trọng
    echo "<hr>";
    echo "<h3>✅ Kiểm tra các key quan trọng:</h3>";
    
    $requiredKeys = ['user_id', 'pharmacy_id', 'username', 'role'];
    $allOk = true;
    
    echo "<ul>";
    foreach ($requiredKeys as $key) {
        if (isset($_SESSION[$key])) {
            echo "<li style='color: green;'>✅ <strong>{$key}:</strong> {$_SESSION[$key]}</li>";
        } else {
            echo "<li style='color: red;'>❌ <strong>{$key}:</strong> KHÔNG TỒN TẠI</li>";
            $allOk = false;
        }
    }
    echo "</ul>";
    
    if (!$allOk) {
        echo "<p style='color: red; font-weight: bold;'>⚠️ Session thiếu thông tin! Hãy đăng xuất và đăng nhập lại.</p>";
        echo "<p><a href='index.php?page=logout'><strong>→ Đăng xuất</strong></a></p>";
    }
}

echo "<hr>";
echo "<h3>🎯 Tiếp theo:</h3>";
echo "<ol>";
echo "<li><a href='index.php?page=logout'><strong>Đăng xuất</strong></a></li>";
echo "<li><a href='index.php?page=login'><strong>Đăng nhập lại</strong></a> với <code>admin</code> / <code>123456</code></li>";
echo "<li><a href='index.php?page=sales'><strong>Vào trang bán hàng</strong></a></li>";
echo "</ol>";
