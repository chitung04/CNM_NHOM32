<?php
/**
 * Xóa user an toàn
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Xóa User</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='p-5'>\n";

echo "<div class='container'>\n";
echo "<h2>Xóa User</h2>\n";

if (isset($_POST['delete'])) {
    $userId = $_POST['user_id'];
    $deleteType = $_POST['delete_type'];
    
    $stmt = $db->query("SELECT * FROM users WHERE user_id = ?", [$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($deleteType === 'soft') {
            // Soft delete - Chỉ đặt is_active = 0
            $result = $db->execute("UPDATE users SET is_active = 0 WHERE user_id = ?", [$userId]);
            
            if ($result) {
                echo "<div class='alert alert-success'>";
                echo "<h4>✓ Đã vô hiệu hóa user!</h4>";
                echo "<p>User <strong>{$user['username']}</strong> đã bị vô hiệu hóa (is_active = 0)</p>";
                echo "<p>User không thể đăng nhập nhưng dữ liệu vẫn còn trong database</p>";
                echo "</div>";
            }
        } else {
            // Hard delete - Xóa hẳn
            try {
                $db->execute("DELETE FROM audit_logs WHERE user_id = ?", [$userId]);
                $db->execute("DELETE FROM invoices WHERE user_id = ?", [$userId]);
                $db->execute("DELETE FROM notifications WHERE user_id = ?", [$userId]);
                $db->execute("DELETE FROM users WHERE user_id = ?", [$userId]);
                
                echo "<div class='alert alert-success'>";
                echo "<h4>✓ Đã xóa user hoàn toàn!</h4>";
                echo "<p>User <strong>{$user['username']}</strong> và tất cả dữ liệu liên quan đã bị xóa</p>";
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>";
                echo "<h4>❌ Không thể xóa user</h4>";
                echo "<p>Lỗi: " . $e->getMessage() . "</p>";
                echo "<p>Có thể user này có dữ liệu quan trọng không thể xóa</p>";
                echo "</div>";
            }
        }
        
        echo "<a href='delete_user.php' class='btn btn-primary'>Quay lại</a> ";
        echo "<a href='view_all_passwords.php' class='btn btn-info'>Xem danh sách users</a>";
    }
} else {
    // Hiển thị form
    $stmt = $db->query("SELECT * FROM users ORDER BY username");
    $users = $stmt->fetchAll();
    
    echo "<div class='card'>";
    echo "<div class='card-body'>";
    echo "<form method='POST' onsubmit='return confirm(\"Bạn chắc chắn muốn xóa user này?\");'>";
    
    echo "<div class='mb-3'>";
    echo "<label class='form-label'>Chọn user cần xóa:</label>";
    echo "<select name='user_id' class='form-select' required>";
    echo "<option value=''>-- Chọn user --</option>";
    foreach ($users as $user) {
        $role = $user['role'] === 'manager' ? 'Manager' : 'Staff';
        $status = $user['is_active'] ? 'Active' : 'Inactive';
        echo "<option value='{$user['user_id']}'>{$user['username']} - {$user['full_name']} ($role) - $status</option>";
    }
    echo "</select>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<label class='form-label'>Loại xóa:</label>";
    echo "<div class='form-check'>";
    echo "<input class='form-check-input' type='radio' name='delete_type' value='soft' id='soft' checked>";
    echo "<label class='form-check-label' for='soft'>";
    echo "<strong>Vô hiệu hóa (Soft Delete)</strong> - Khuyến nghị<br>";
    echo "<small class='text-muted'>User không thể đăng nhập nhưng dữ liệu vẫn còn</small>";
    echo "</label>";
    echo "</div>";
    
    echo "<div class='form-check mt-2'>";
    echo "<input class='form-check-input' type='radio' name='delete_type' value='hard' id='hard'>";
    echo "<label class='form-check-label' for='hard'>";
    echo "<strong>Xóa hoàn toàn (Hard Delete)</strong> - Nguy hiểm<br>";
    echo "<small class='text-danger'>Xóa user và TẤT CẢ dữ liệu liên quan (không thể khôi phục)</small>";
    echo "</label>";
    echo "</div>";
    echo "</div>";
    
    echo "<button type='submit' name='delete' class='btn btn-danger btn-lg'>Xóa User</button>";
    echo "</form>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='alert alert-warning mt-3'>";
    echo "<h5>⚠️ Lưu ý:</h5>";
    echo "<ul>";
    echo "<li><strong>Soft Delete:</strong> User bị vô hiệu hóa, không thể đăng nhập nhưng dữ liệu vẫn còn</li>";
    echo "<li><strong>Hard Delete:</strong> Xóa hoàn toàn user và tất cả dữ liệu (đơn hàng, audit logs, notifications)</li>";
    echo "<li>Nên dùng Soft Delete để giữ lại lịch sử</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>\n";
echo "</body></html>\n";
