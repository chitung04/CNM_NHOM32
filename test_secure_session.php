<?php
/**
 * Test hệ thống session bảo mật
 */

require_once 'helpers/secure_session.php';

$secureSession = SecureSession::getInstance();

echo "<h2>🔒 Test Hệ thống Session Bảo mật</h2>";

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = $secureSession->isLoggedIn();
$currentUser = $secureSession->getCurrentUser();
$sessionStats = $secureSession->getSessionStats();

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>📊 Trạng thái Session:</h4>";

if ($isLoggedIn) {
    echo "<div style='color: green; margin: 10px 0;'>✅ <strong>Đã đăng nhập</strong></div>";
    echo "<ul>";
    echo "<li><strong>User ID:</strong> " . $currentUser['user_id'] . "</li>";
    echo "<li><strong>Username:</strong> " . $currentUser['username'] . "</li>";
    echo "<li><strong>Họ tên:</strong> " . $currentUser['full_name'] . "</li>";
    echo "<li><strong>Vai trò:</strong> " . $currentUser['role'] . "</li>";
    echo "</ul>";
    
    if ($sessionStats) {
        echo "<h5>📈 Thống kê Session:</h5>";
        echo "<ul>";
        echo "<li><strong>Thời gian đăng nhập:</strong> " . date('H:i:s d/m/Y', $sessionStats['login_time']) . "</li>";
        echo "<li><strong>Hoạt động cuối:</strong> " . date('H:i:s d/m/Y', $sessionStats['last_activity']) . "</li>";
        echo "<li><strong>Thời gian online:</strong> " . gmdate('H:i:s', $sessionStats['session_duration']) . "</li>";
        echo "<li><strong>Browser fingerprint:</strong> " . $sessionStats['browser_fingerprint'] . "</li>";
        echo "<li><strong>Session token:</strong> " . $sessionStats['session_token'] . "</li>";
        echo "</ul>";
    }
} else {
    echo "<div style='color: red; margin: 10px 0;'>❌ <strong>Chưa đăng nhập</strong></div>";
}

echo "</div>";

// Test browser fingerprint
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🔍 Thông tin Trình duyệt:</h4>";
echo "<ul>";
echo "<li><strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</li>";
echo "<li><strong>Accept Language:</strong> " . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A') . "</li>";
echo "<li><strong>Accept Encoding:</strong> " . ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? 'N/A') . "</li>";
echo "<li><strong>Remote Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</li>";
echo "</ul>";
echo "</div>";

// Test CSRF token
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🛡️ CSRF Protection:</h4>";
if ($isLoggedIn) {
    $csrfToken = $secureSession->generateCSRFToken();
    echo "<p><strong>CSRF Token:</strong> " . substr($csrfToken, 0, 16) . "...</p>";
    echo "<form method='POST' style='margin: 10px 0;'>";
    echo csrfField();
    echo "<button type='submit' name='test_csrf' style='background: #007bff; color: white; padding: 5px 10px; border: none; border-radius: 3px;'>Test CSRF</button>";
    echo "</form>";
    
    if (isset($_POST['test_csrf'])) {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if ($secureSession->validateCSRFToken($submittedToken)) {
            echo "<div style='color: green;'>✅ CSRF token hợp lệ</div>";
        } else {
            echo "<div style='color: red;'>❌ CSRF token không hợp lệ</div>";
        }
    }
} else {
    echo "<p>Cần đăng nhập để test CSRF</p>";
}
echo "</div>";

// Test session security
echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🔐 Bảo mật Session:</h4>";
echo "<ul>";
echo "<li><strong>Session Cookie Only:</strong> " . (ini_get('session.cookie_httponly') ? '✅ Enabled' : '❌ Disabled') . "</li>";
echo "<li><strong>Secure Cookies:</strong> " . (ini_get('session.cookie_secure') ? '✅ Enabled' : '⚠️ Disabled (HTTP)') . "</li>";
echo "<li><strong>SameSite:</strong> " . (ini_get('session.cookie_samesite') ?: 'Not set') . "</li>";
echo "<li><strong>Use Only Cookies:</strong> " . (ini_get('session.use_only_cookies') ? '✅ Enabled' : '❌ Disabled') . "</li>";
echo "<li><strong>Strict Mode:</strong> " . (ini_get('session.use_strict_mode') ? '✅ Enabled' : '❌ Disabled') . "</li>";
echo "</ul>";
echo "</div>";

// Hướng dẫn test
echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🧪 Hướng dẫn Test:</h4>";
echo "<ol>";
echo "<li><strong>Test session isolation:</strong>";
echo "<ul>";
echo "<li>Đăng nhập vào hệ thống</li>";
echo "<li>Copy URL này và paste vào tab/cửa sổ trình duyệt mới</li>";
echo "<li>Kết quả: Sẽ hiển thị 'Chưa đăng nhập' vì session không được chia sẻ</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Test browser fingerprint:</strong>";
echo "<ul>";
echo "<li>Đăng nhập trên trình duyệt A</li>";
echo "<li>Copy session cookie sang trình duyệt B</li>";
echo "<li>Kết quả: Session sẽ bị hủy vì browser fingerprint khác nhau</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Test session timeout:</strong>";
echo "<ul>";
echo "<li>Đăng nhập và để idle 30 phút</li>";
echo "<li>Kết quả: Session sẽ tự động hết hạn</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "</div>";

// Links
echo "<div style='text-align: center; margin: 30px 0;'>";
if ($isLoggedIn) {
    echo "<a href='index.php?page=dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Dashboard</a>";
    echo "<a href='index.php?page=auth&action=logout' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Đăng xuất</a>";
} else {
    echo "<a href='index.php?page=auth&action=login' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Đăng nhập</a>";
}
echo "</div>";
?>