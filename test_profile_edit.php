<?php
/**
 * Test profile edit page
 */

session_start();

// Giả lập đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "❌ Chưa đăng nhập. Vui lòng đăng nhập trước.<br>";
    echo "<a href='index.php?page=auth&action=login'>Đăng nhập</a>";
    exit;
}

echo "<h2>🔍 Test Profile Edit Page</h2>";
echo "<hr>";

// Test 1: Kiểm tra session
echo "<h3>1. Kiểm tra Session</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NULL') . "<br>";
echo "Username: " . ($_SESSION['username'] ?? 'NULL') . "<br>";
echo "Full Name: " . ($_SESSION['full_name'] ?? 'NULL') . "<br>";
echo "Role: " . ($_SESSION['role'] ?? 'NULL') . "<br>";
echo "<hr>";

// Test 2: Kiểm tra User model
echo "<h3>2. Kiểm tra User Model</h3>";
try {
    require_once 'models/User.php';
    $userModel = new User();
    $user = $userModel->getById($_SESSION['user_id']);
    
    if ($user) {
        echo "✅ Lấy thông tin user thành công<br>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "❌ Không tìm thấy user<br>";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 3: Kiểm tra file tồn tại
echo "<h3>3. Kiểm tra Files</h3>";
$files = [
    'views/profile/edit.php',
    'views/layouts/header.php',
    'views/layouts/sidebar.php',
    'views/layouts/footer.php',
    'models/User.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ $file (KHÔNG TỒN TẠI)<br>";
    }
}
echo "<hr>";

// Test 4: Thử load trang edit
echo "<h3>4. Test Load Trang Edit</h3>";
echo "<p><a href='index.php?page=profile&action=edit' target='_blank' class='btn'>Mở trang Edit</a></p>";
echo "<p><small>Nếu trang trắng, kiểm tra console browser (F12) để xem lỗi JavaScript</small></p>";
echo "<hr>";

// Test 5: Kiểm tra routing
echo "<h3>5. Kiểm tra Routing</h3>";
echo "<p>URL hiện tại: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Tham số page: " . ($_GET['page'] ?? 'NULL') . "</p>";
echo "<p>Tham số action: " . ($_GET['action'] ?? 'NULL') . "</p>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1000px;
    margin: 0 auto;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.btn:hover {
    background: #0056b3;
}
pre {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
}
</style>
