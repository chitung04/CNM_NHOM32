<?php
/**
 * Kiểm tra tất cả các trang có yêu cầu authentication không
 */

require_once 'helpers/secure_session.php';

$secureSession = SecureSession::getInstance();
$isLoggedIn = $secureSession->isLoggedIn();

echo "<h2>🔍 Kiểm tra Authentication cho tất cả trang</h2>";

// Danh sách các trang cần kiểm tra
$pages = [
    'Trang chính' => [
        'url' => 'index.php',
        'should_require_auth' => true
    ],
    'Dashboard' => [
        'url' => 'index.php?page=dashboard',
        'should_require_auth' => true
    ],
    'Quản lý thuốc' => [
        'url' => 'index.php?page=medicines',
        'should_require_auth' => true
    ],
    'Quản lý lô thuốc' => [
        'url' => 'index.php?page=batches',
        'should_require_auth' => true
    ],
    'Bán hàng' => [
        'url' => 'index.php?page=sales',
        'should_require_auth' => true
    ],
    'Báo cáo' => [
        'url' => 'index.php?page=reports',
        'should_require_auth' => true
    ],
    'Thông tin thuốc QR' => [
        'url' => 'medicine_info.php?qr=BATCH_1735000101_2001',
        'should_require_auth' => true
    ],
    'Thông tin hóa đơn' => [
        'url' => 'invoice_info.php?invoice_id=1',
        'should_require_auth' => true
    ],
    'Trang đăng nhập' => [
        'url' => 'index.php?page=auth&action=login',
        'should_require_auth' => false
    ]
];

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>📊 Trạng thái đăng nhập hiện tại:</h4>";

if ($isLoggedIn) {
    $user = $secureSession->getCurrentUser();
    echo "<div style='color: green; margin: 10px 0;'>✅ <strong>Đã đăng nhập:</strong> " . $user['full_name'] . " (" . $user['username'] . ")</div>";
} else {
    echo "<div style='color: red; margin: 10px 0;'>❌ <strong>Chưa đăng nhập</strong></div>";
}

echo "</div>";

echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🧪 Test Copy Link - Session Isolation:</h4>";
echo "<p><strong>Hướng dẫn test:</strong></p>";
echo "<ol>";
echo "<li>Đăng nhập vào hệ thống (nếu chưa đăng nhập)</li>";
echo "<li>Click vào link bất kỳ dưới đây</li>";
echo "<li><strong>Copy URL</strong> từ thanh địa chỉ trình duyệt</li>";
echo "<li><strong>Mở tab/cửa sổ mới</strong> và paste URL</li>";
echo "<li><strong>Kết quả mong đợi:</strong> Redirect về trang login với thông báo 'Vui lòng đăng nhập để truy cập hệ thống'</li>";
echo "</ol>";

echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
echo "<thead>";
echo "<tr style='background: #007bff; color: white;'>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>Trang</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>URL</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>Yêu cầu đăng nhập</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd;'>Thao tác</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($pages as $name => $info) {
    $fullUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $info['url'];
    $authRequired = $info['should_require_auth'] ? '✅ Có' : '❌ Không';
    $authColor = $info['should_require_auth'] ? 'green' : 'orange';
    
    echo "<tr>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><strong>{$name}</strong></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd; font-family: monospace; font-size: 12px;'>";
    echo "<a href='{$info['url']}' target='_blank' style='color: #007bff; text-decoration: none;'>{$info['url']}</a>";
    echo "</td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd; color: {$authColor};'>{$authRequired}</td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'>";
    echo "<button onclick='testPage(\"{$fullUrl}\", \"{$name}\", " . ($info['should_require_auth'] ? 'true' : 'false') . ")' ";
    echo "style='background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin: 2px;'>Test</button>";
    echo "<button onclick='copyToClipboard(\"{$fullUrl}\")' ";
    echo "style='background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin: 2px;'>Copy</button>";
    echo "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

echo "</div>";

// Kết quả mong đợi
echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>✅ Kết quả mong đợi:</h4>";
echo "<ul>";
echo "<li><strong>Tab hiện tại (đã đăng nhập):</strong> Tất cả trang hoạt động bình thường</li>";
echo "<li><strong>Tab/cửa sổ mới (copy link):</strong> Các trang yêu cầu đăng nhập sẽ redirect về login</li>";
echo "<li><strong>Thông báo:</strong> 'Vui lòng đăng nhập để truy cập hệ thống'</li>";
echo "<li><strong>Session isolation:</strong> Mỗi tab/cửa sổ cần đăng nhập riêng</li>";
echo "</ul>";
echo "</div>";

// Thông tin session
if ($isLoggedIn) {
    $sessionStats = $secureSession->getSessionStats();
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📈 Thông tin Session:</h4>";
    echo "<ul>";
    echo "<li><strong>Thời gian đăng nhập:</strong> " . date('H:i:s d/m/Y', $sessionStats['login_time']) . "</li>";
    echo "<li><strong>Hoạt động cuối:</strong> " . date('H:i:s d/m/Y', $sessionStats['last_activity']) . "</li>";
    echo "<li><strong>Thời gian online:</strong> " . gmdate('H:i:s', $sessionStats['session_duration']) . "</li>";
    echo "<li><strong>Browser fingerprint:</strong> " . $sessionStats['browser_fingerprint'] . "</li>";
    echo "<li><strong>Session token:</strong> " . $sessionStats['session_token'] . "</li>";
    echo "</ul>";
    echo "</div>";
}

// Navigation
echo "<div style='text-align: center; margin: 30px 0;'>";
if ($isLoggedIn) {
    echo "<a href='index.php?page=dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Dashboard</a>";
    echo "<a href='test_session_isolation.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Test Session</a>";
    echo "<a href='index.php?page=auth&action=logout' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Đăng xuất</a>";
} else {
    echo "<a href='index.php?page=auth&action=login' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Đăng nhập</a>";
}
echo "</div>";

?>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('✅ Đã copy URL: ' + text + '\n\n🧪 Bây giờ hãy:\n1. Mở tab/cửa sổ trình duyệt MỚI\n2. Paste URL vào thanh địa chỉ\n3. Kiểm tra xem có redirect về login không\n\n🔒 Nếu hệ thống hoạt động đúng, bạn sẽ thấy trang login với thông báo yêu cầu đăng nhập!');
    }, function(err) {
        console.error('Không thể copy: ', err);
        alert('❌ Không thể copy URL. Vui lòng copy thủ công.');
    });
}

function testPage(url, pageName, requiresAuth) {
    const message = requiresAuth ? 
        '🔒 Trang "' + pageName + '" YÊU CẦU đăng nhập.\n\n🧪 Test:\n1. Click OK để mở trang\n2. Copy URL từ thanh địa chỉ\n3. Mở tab mới và paste URL\n4. Kết quả: Sẽ redirect về login' :
        '🔓 Trang "' + pageName + '" KHÔNG yêu cầu đăng nhập.\n\n🧪 Test:\n1. Click OK để mở trang\n2. Copy URL từ thanh địa chỉ\n3. Mở tab mới và paste URL\n4. Kết quả: Vẫn xem được trang';
    
    if (confirm(message)) {
        window.open(url, '_blank');
    }
}
</script>