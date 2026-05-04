<?php
/**
 * Test Copy Link - Session Isolation
 */

require_once 'helpers/secure_session.php';

$secureSession = SecureSession::getInstance();

// YÊU CẦU ĐĂNG NHẬP
if (!$secureSession->isLoggedIn()) {
    header('Location: index.php?page=auth&action=login&reason=session_required&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user = $secureSession->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Copy Link - Session Isolation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .url-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; border-radius: 5px; font-family: monospace; word-break: break-all; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Test Copy Link - Session Isolation</h1>
        
        <div class="success">
            <h3>✅ Đã đăng nhập thành công!</h3>
            <p><strong>User:</strong> <?php echo htmlspecialchars($user['full_name']); ?> (<?php echo htmlspecialchars($user['username']); ?>)</p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
            <p><strong>Session Token:</strong> <?php echo substr($user['session_token'], 0, 8); ?>...</p>
        </div>
        
        <div class="info">
            <h3>🧪 Hướng dẫn Test Session Isolation:</h3>
            <ol>
                <li><strong>Copy URL hiện tại</strong> từ thanh địa chỉ trình duyệt</li>
                <li><strong>Mở tab/cửa sổ trình duyệt MỚI</strong></li>
                <li><strong>Paste URL</strong> vào tab mới</li>
                <li><strong>Kết quả mong đợi:</strong> Sẽ redirect về trang login</li>
            </ol>
        </div>
        
        <div class="warning">
            <h3>📋 URL hiện tại:</h3>
            <div class="url-box">
                <?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
            </div>
            <button onclick="copyCurrentUrl()" class="btn btn-success">📋 Copy URL này</button>
        </div>
        
        <div class="info">
            <h3>🔍 Thông tin Session:</h3>
            <?php $sessionStats = $secureSession->getSessionStats(); ?>
            <ul>
                <li><strong>Login time:</strong> <?php echo date('H:i:s d/m/Y', $sessionStats['login_time']); ?></li>
                <li><strong>Last activity:</strong> <?php echo date('H:i:s d/m/Y', $sessionStats['last_activity']); ?></li>
                <li><strong>Session duration:</strong> <?php echo gmdate('H:i:s', $sessionStats['session_duration']); ?></li>
                <li><strong>Browser fingerprint:</strong> <?php echo $sessionStats['browser_fingerprint']; ?></li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="index.php?page=dashboard" class="btn">🏠 Dashboard</a>
            <a href="medicine_info.php?qr=BATCH_1735000101_2001" class="btn">💊 Medicine Info</a>
            <a href="test_simple.php" class="btn">🔧 Test Simple</a>
            <a href="index.php?page=auth&action=logout" class="btn btn-danger">🚪 Đăng xuất</a>
        </div>
        
        <div class="success">
            <h3>✅ Nếu hệ thống hoạt động đúng:</h3>
            <ul>
                <li>✅ Trang này hiển thị bình thường trên <strong>tab hiện tại</strong></li>
                <li>❌ Khi copy link sang <strong>tab mới</strong> → Redirect về login</li>
                <li>🔒 Mỗi tab/cửa sổ cần đăng nhập riêng biệt</li>
                <li>🛡️ Session được cách ly hoàn toàn</li>
            </ul>
        </div>
    </div>
    
    <script>
        function copyCurrentUrl() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('✅ Đã copy URL!\n\n' + url + '\n\n🧪 Bây giờ:\n1. Mở tab/cửa sổ trình duyệt MỚI\n2. Paste URL này\n3. Kiểm tra xem có redirect về login không\n\n🔒 Nếu redirect về login = SUCCESS!');
            }, function(err) {
                alert('❌ Không thể copy URL: ' + err);
            });
        }
    </script>
</body>
</html>