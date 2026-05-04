<?php
/**
 * Kiểm tra mật khẩu trong database
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập");
}

require_once 'models/Database.php';
require_once 'models/User.php';

$userModel = new User();
$user = $userModel->findByUsername($_SESSION['username']);

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Kiểm tra mật khẩu</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='p-5'>\n";

echo "<div class='container'>\n";
echo "<h2>Kiểm tra mật khẩu</h2>\n";

echo "<div class='card mt-3'>";
echo "<div class='card-body'>";
echo "<h5>Thông tin user:</h5>";
echo "<p><strong>Username:</strong> " . htmlspecialchars($user['username']) . "</p>";
echo "<p><strong>User ID:</strong> " . $user['user_id'] . "</p>";
echo "<p><strong>Password hash:</strong> <code>" . htmlspecialchars($user['password']) . "</code></p>";

// Kiểm tra xem password có phải là hash không
if (strlen($user['password']) === 60 && strpos($user['password'], '$2y$') === 0) {
    echo "<p class='text-success'><strong>✓ Mật khẩu đã được hash (bcrypt)</strong></p>";
} else {
    echo "<p class='text-danger'><strong>✗ Mật khẩu CHƯA được hash (plain text)</strong></p>";
    echo "<p class='text-warning'>Mật khẩu hiện tại: <code>" . htmlspecialchars($user['password']) . "</code></p>";
}

echo "</div>";
echo "</div>";

// Form test mật khẩu
echo "<div class='card mt-3'>";
echo "<div class='card-body'>";
echo "<h5>Test mật khẩu:</h5>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testPassword = $_POST['test_password'] ?? '';
    
    echo "<div class='alert alert-info'>";
    echo "<p><strong>Mật khẩu test:</strong> " . htmlspecialchars($testPassword) . "</p>";
    
    // Test với password_verify
    if (password_verify($testPassword, $user['password'])) {
        echo "<p class='text-success'><strong>✓ password_verify() = TRUE</strong></p>";
    } else {
        echo "<p class='text-danger'><strong>✗ password_verify() = FALSE</strong></p>";
    }
    
    // Test so sánh trực tiếp
    if ($testPassword === $user['password']) {
        echo "<p class='text-success'><strong>✓ So sánh trực tiếp = TRUE (mật khẩu plain text)</strong></p>";
    } else {
        echo "<p class='text-danger'><strong>✗ So sánh trực tiếp = FALSE</strong></p>";
    }
    
    echo "</div>";
}

echo "<form method='POST'>";
echo "<div class='mb-3'>";
echo "<label class='form-label'>Nhập mật khẩu để test:</label>";
echo "<input type='text' class='form-control' name='test_password' required>";
echo "</div>";
echo "<button type='submit' class='btn btn-primary'>Test</button>";
echo "</form>";

echo "</div>";
echo "</div>";

// Nút hash lại mật khẩu
echo "<div class='card mt-3'>";
echo "<div class='card-body'>";
echo "<h5>Hash lại mật khẩu:</h5>";

if (isset($_GET['rehash'])) {
    $newPassword = $_GET['password'] ?? '123456';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $db = Database::getInstance();
    $db->execute("UPDATE users SET password = ? WHERE user_id = ?", [$hashedPassword, $user['user_id']]);
    
    echo "<div class='alert alert-success'>";
    echo "✓ Đã hash lại mật khẩu thành: <strong>$newPassword</strong>";
    echo "<br>Hash: <code>$hashedPassword</code>";
    echo "</div>";
    echo "<a href='check_password.php' class='btn btn-info'>Kiểm tra lại</a>";
} else {
    echo "<p>Nếu mật khẩu chưa được hash, click nút dưới để hash lại:</p>";
    echo "<form method='GET'>";
    echo "<div class='mb-3'>";
    echo "<label class='form-label'>Mật khẩu mới:</label>";
    echo "<input type='text' class='form-control' name='password' value='123456'>";
    echo "</div>";
    echo "<button type='submit' name='rehash' value='1' class='btn btn-warning'>Hash lại mật khẩu</button>";
    echo "</form>";
}

echo "</div>";
echo "</div>";

echo "<div class='mt-3'>";
echo "<a href='index.php?page=profile' class='btn btn-secondary'>Về Profile</a> ";
echo "<a href='index.php?page=profile&action=change_password' class='btn btn-primary'>Đổi mật khẩu</a>";
echo "</div>";

echo "</div>\n";
echo "</body></html>\n";
