<?php
session_start();

echo "<h2>🔍 Debug Session</h2>";
echo "<hr>";

echo "<h3>Session hiện tại:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";

echo "<h3>Kiểm tra:</h3>";
echo "- Đã đăng nhập: " . (isset($_SESSION['user_id']) ? '✅ Có' : '❌ Không') . "<br>";
echo "- Username: " . ($_SESSION['username'] ?? 'Chưa có') . "<br>";
echo "- Role: " . ($_SESSION['role'] ?? 'Chưa có') . "<br>";
echo "- is_root_admin: " . (isset($_SESSION['is_root_admin']) ? $_SESSION['is_root_admin'] : '❌ CHƯA CÓ') . "<br>";
echo "- pharmacy_id: " . ($_SESSION['pharmacy_id'] ?? 'Chưa có') . "<br>";

echo "<hr>";

if (!isset($_SESSION['is_root_admin'])) {
    echo "<div style='background: #fff3cd; padding: 20px; border: 1px solid #ffc107; border-radius: 5px;'>";
    echo "<h3>⚠️ Vấn đề tìm thấy!</h3>";
    echo "<p><strong>Session không có is_root_admin</strong></p>";
    echo "<p>Bạn cần <strong>LOGOUT và LOGIN lại</strong> để session cập nhật.</p>";
    echo "<br>";
    echo "<a href='index.php?page=auth&action=logout' style='padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px;'>Logout ngay</a>";
    echo "</div>";
} elseif ($_SESSION['is_root_admin'] == 1) {
    echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #28a745; border-radius: 5px;'>";
    echo "<h3>✅ Hoàn hảo!</h3>";
    echo "<p>Bạn là <strong>Root Admin</strong>. Bạn có thể sửa admin được phân quyền.</p>";
    echo "<br>";
    echo "<a href='index.php?page=users' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Đi đến Quản lý người dùng</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border: 1px solid #dc3545; border-radius: 5px;'>";
    echo "<h3>ℹ️ Thông tin</h3>";
    echo "<p>Bạn <strong>KHÔNG phải Root Admin</strong>. Bạn không thể sửa admin khác.</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}
</style>
