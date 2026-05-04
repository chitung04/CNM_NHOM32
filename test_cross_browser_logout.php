<?php
/**
 * Test Cross-Browser Logout - Đăng xuất khi truy cập từ trình duyệt khác
 */

require_once 'helpers/secure_session.php';

$secureSession = SecureSession::getInstance();

// YÊU CẦU ĐĂNG NHẬP
if (!$secureSession->isLoggedIn()) {
    header('Location: index.php?page=auth&action=login&reason=session_required&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user = $secureSession->getCurrentUser();
$sessionStats = $secureSession->getSessionStats();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Cross-Browser Logout</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: #212529; }
        .url-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; border-radius: 5px; font-family: monospace; word-break: break-all; margin: 10px 0; font-size: 12px; }
        .fingerprint { font-family: monospace; background: #f8f9fa; padding: 5px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Test Cross-Browser Logout</h1>
        
        <div class="success">
            <h3>✅ Đã đăng nhập thành công!</h3>
            <table>
                <tr><th>User</th><td><?php echo htmlspecialchars($user['full_name']); ?> (<?php echo htmlspecialchars($user['username']); ?>)</td></tr>
                <tr><th>Role</th><td><?php echo htmlspecialchars($user['role']); ?></td></tr>
                <tr><th>Login Time</th><td><?php echo date('H:i:s d/m/Y', $sessionStats['login_time']); ?></td></tr>
                <tr><th>Session Duration</th><td><?php echo gmdate('H:i:s', $sessionStats['session_duration']); ?></td></tr>
                <tr><th>Session Token</th><td class="fingerprint"><?php echo $sessionStats['session_token']; ?></td></tr>
            </table>
        </div>
        
        <div class="danger">
            <h3>🚨 Test Cross-Browser Security</h3>
            <p><strong>Mục tiêu:</strong> Khi copy link sang trình duyệt khác → Tự động đăng xuất tất cả sessions</p>
            <ol>
                <li><strong>Đăng nhập</strong> trên trình duyệt hiện tại (Chrome/Edge/Firefox)</li>
                <li><strong>Copy URL</strong> dưới đây</li>
                <li><strong>Mở trình duyệt KHÁC</strong> (nếu đang dùng Chrome thì mở Firefox/Edge)</li>
                <li><strong>Paste URL</strong> vào trình duyệt khác</li>
                <li><strong>Kết quả mong đợi:</strong>
                    <ul>
                        <li>❌ Trình duyệt khác: Redirect về login với thông báo bảo mật</li>
                        <li>❌ Trình duyệt gốc: Tự động đăng xuất (refresh trang này để kiểm tra)</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div class="warning">
            <h3>📋 URL để test:</h3>
            <div class="url-box">
                <?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
            </div>
            <button onclick="copyCurrentUrl()" class="btn btn-warning">📋 Copy URL này</button>
            <button onclick="window.location.reload()" class="btn btn-success">🔄 Refresh để kiểm tra logout</button>
        </div>
        
        <div class="info">
            <h3>🔍 Browser Fingerprint hiện tại:</h3>
            <div class="fingerprint"><?php echo $sessionStats['browser_fingerprint']; ?></div>
            <p><strong>Lưu ý:</strong> Mỗi trình duyệt có fingerprint khác nhau. Khi hệ thống phát hiện fingerprint khác → Đăng xuất tất cả!</p>
        </div>
        
        <div class="info">
            <h3>🌐 Thông tin trình duyệt:</h3>
            <table>
                <tr><th>User Agent</th><td style="font-size: 11px;"><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A'); ?></td></tr>
                <tr><th>Accept Language</th><td><?php echo htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A'); ?></td></tr>
                <tr><th>Accept Encoding</th><td><?php echo htmlspecialchars($_SERVER['HTTP_ACCEPT_ENCODING'] ?? 'N/A'); ?></td></tr>
                <tr><th>Remote Address</th><td><?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A'); ?></td></tr>
            </table>
        </div>
        
        <div class="success">
            <h3>✅ Kết quả mong đợi:</h3>
            <table>
                <tr><th>Trình duyệt hiện tại</th><td>✅ Hiển thị trang bình thường</td></tr>
                <tr><th>Copy link sang trình duyệt khác</th><td>❌ Redirect về login + thông báo bảo mật</td></tr>
                <tr><th>Quay lại trình duyệt gốc</th><td>❌ Tự động đăng xuất (cần refresh)</td></tr>
                <tr><th>Thông báo</th><td>"Phát hiện truy cập từ trình duyệt khác. Tất cả phiên đăng nhập đã bị hủy vì lý do bảo mật."</td></tr>
            </table>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="index.php?page=dashboard" class="btn">🏠 Dashboard</a>
            <a href="medicine_info.php?qr=BATCH_1735000101_2001" class="btn">💊 Medicine Info</a>
            <a href="test_copy_link.php" class="btn">🔗 Test Copy Link</a>
            <a href="index.php?page=auth&action=logout" class="btn btn-danger">🚪 Đăng xuất thủ công</a>
        </div>
        
        <div class="warning">
            <h3>⚠️ Lưu ý quan trọng:</h3>
            <ul>
                <li>Hệ thống sẽ <strong>tự động đăng xuất TẤT CẢ sessions</strong> khi phát hiện truy cập từ trình duyệt khác</li>
                <li>Đây là tính năng bảo mật để ngăn chặn session hijacking</li>
                <li>Sau khi test, bạn sẽ cần đăng nhập lại trên tất cả trình duyệt</li>
            </ul>
        </div>
    </div>
    
    <script>
        function copyCurrentUrl() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('✅ Đã copy URL!\n\n' + url + '\n\n🧪 Bây giờ:\n1. Mở TRÌNH DUYỆT KHÁC (Chrome→Firefox, Firefox→Edge, v.v.)\n2. Paste URL này vào trình duyệt khác\n3. Kết quả: Sẽ redirect về login với thông báo bảo mật\n4. Quay lại trình duyệt này và refresh → Sẽ thấy đã bị đăng xuất!\n\n🔒 Đây là tính năng bảo mật cross-browser logout!');
            }, function(err) {
                alert('❌ Không thể copy URL: ' + err);
            });
        }
        
        // Auto refresh every 30 seconds to check logout status
        let refreshInterval = setInterval(function() {
            if (confirm('🔄 Kiểm tra xem có bị đăng xuất không?\n\nClick OK để refresh trang và kiểm tra trạng thái đăng nhập.')) {
                window.location.reload();
            }
        }, 30000);
        
        // Stop auto refresh when user interacts
        document.addEventListener('click', function() {
            clearInterval(refreshInterval);
        });
    </script>
</body>
</html>