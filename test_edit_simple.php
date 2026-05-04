<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("Chưa đăng nhập");
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Edit</title></head><body>";
echo "<h1>Test Profile Edit</h1>";
echo "<p>Session User ID: " . $_SESSION['user_id'] . "</p>";

// Test load User model
try {
    require_once 'models/User.php';
    $userModel = new User();
    $user = $userModel->getById($_SESSION['user_id']);
    
    if ($user) {
        echo "<h2>✅ User Data:</h2>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        echo "<h2>Form Test:</h2>";
        echo "<form method='POST'>";
        echo "<input type='text' name='full_name' value='" . htmlspecialchars($user['full_name']) . "'><br>";
        echo "<input type='email' name='email' value='" . htmlspecialchars($user['email'] ?? '') . "'><br>";
        echo "<input type='tel' name='phone' value='" . htmlspecialchars($user['phone'] ?? '') . "'><br>";
        echo "<button type='submit'>Test Submit</button>";
        echo "</form>";
    } else {
        echo "<h2>❌ Không tìm thấy user</h2>";
    }
} catch (Exception $e) {
    echo "<h2>❌ Lỗi: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
