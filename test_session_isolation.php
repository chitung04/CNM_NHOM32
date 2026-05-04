<?php
/**
 * Test Session Isolation - Kiểm tra việc cách ly session giữa các tab
 */

require_once 'helpers/secure_session.php';

$secureSession = SecureSession::getInstance();

echo "<h2>🔒 Test Session Isolation</h2>";

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = $secureSession->isLoggedIn();
$currentUser = $secureSession->getCurrentUser();

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>📊 Trạng thái hiện tại:</h4>";

if ($isLoggedIn) {
    echo "<div style='color: green; margin: 10px 0;'>✅ <strong>Đã đăng nhập</strong></div>";
    echo "<ul>";
    echo "<li><strong>User:</strong> " . $currentUser['full_name'] . " (" . $currentUser['username'] . ")</li>";
    echo "<li><strong>Role:</strong> " . $currentUser['role'] . "</li>";
    echo "<li><strong>Session Token:</strong> " . substr($currentUser['session_token'], 0, 8) . "...</li>";
    echo "</ul>";
} else {
    echo "<div style='color: red; margin: 10px 0;'>❌ <strong>Chưa đăng nhập</strong></div>";
    echo "<p>Bạn cần đăng nhập để test session isolation.</p>";
}

echo "</div>";

// Test URLs
$testUrls = [
    'Dashboard' => 'index.php?page=dashboard',
    'Quản lý thuốc' => 'index.php?page=medicines',
    'Quản lý lô thuốc' => 'index.php?page=batches',
    'Bán hàng' => 'index.php?page=sales',
    'Thông tin thuốc QR' => 'medicine_info.php?qr=BATCH_1735000101_2001',
    'Thông tin hóa đơn' => 'invoice_info.php?invoice_id=1',
    'Test secure session' => 'test_secure_session.php'
];

echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🧪 Test Session Isolation:</h4>";
echo "<p><strong>Hướng dẫn:</strong></p>";
echo "<ol>";
echo "<li>Đăng nhập vào hệ thống (nếu chưa đăng nhập)</li>";
echo "<li>Click vào các link dưới đây để kiểm tra</li>";
echo "<li>Copy URL và paste vào <strong>tab/cửa sổ trình duyệt mới</strong></li>";
echo "<li><strong>Kết quả mong đợi:</strong> Sẽ redirect về trang login</li>";
echo "</ol>";

echo "<div style='margin: 20px 0;'>";
foreach ($testUrls as $name => $url) {
    $fullUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $url;
    echo "<div style='margin: 10px 0; padding: 10px; background: white; border-radius: 5px; border: 1px solid #ddd;'>";
    echo "<strong>{$name}:</strong><br>";
    echo "<a href='{$url}' target='_blank' style='color: #007bff; text-decoration: none;'>{$fullUrl}</a>";
    echo "<button onclick='copyToClipboard(\"{$fullUrl}\")' style='margin-left: 10px; padding: 2px 8px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;'>Copy</button>";
    echo "</div>";
}
echo "</div>";

echo "</div>";

// Browser fingerprint info
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🔍 Browser Fingerprint:</h4>";
echo "<p>Hệ thống sử dụng browser fingerprint để xác định trình duyệt:</p>";
echo "<ul>";
echo "<li><strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</li>";
echo "<li><strong>Accept Language:</strong> " . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A') . "</li>";
echo "<li><strong>Remote Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</li>";
echo "</ul>";
echo "<p><strong>Lưu ý:</strong> Nếu thay đổi trình duyệt, session sẽ tự động bị hủy.</p>";
echo "</div>";

// Session cookies info
echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🍪 Session Cookies:</h4>";
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . (session_id() ?: 'Không có') . "</li>";
echo "<li><strong>Auth Token Cookie:</strong> " . (isset($_COOKIE['auth_token']) ? 'Có (' . substr($_COOKIE['auth_token'], 0, 8) . '...)' : 'Không có') . "</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "</ul>";
echo "</div>";

// Test results
if ($isLoggedIn) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>✅ Kết quả Test:</h4>";
    echo "<p>Nếu hệ thống hoạt động đúng:</p>";
    echo "<ul>";
    echo "<li>✅ Các link trên <strong>tab hiện tại</strong> sẽ hoạt động bình thường</li>";
    echo "<li>❌ Các link trên <strong>tab/cửa sổ mới</strong> sẽ redirect về login</li>";
    echo "<li>🔒 Mỗi tab/cửa sổ cần đăng nhập riêng biệt</li>";
    echo "</ul>";
    echo "</div>";
}

// Navigation
echo "<div style='text-align: center; margin: 30px 0;'>";
if ($isLoggedIn) {
    echo "<a href='index.php?page=dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Dashboard</a>";
    echo "<a href='index.php?page=auth&action=logout' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Đăng xuất</a>";
} else {
    echo "<a href='index.php?page=auth&action=login' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Đăng nhập</a>";
}
echo "</div>";

?>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('✅ Đã copy URL!\n\nBây giờ:\n1. Mở tab/cửa sổ trình duyệt mới\n2. Paste URL vào thanh địa chỉ\n3. Kiểm tra xem có redirect về login không');
    }, function(err) {
        console.error('Không thể copy: ', err);
        // Fallback cho trình duyệt cũ
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('✅ Đã copy URL!');
    });
}
</script>