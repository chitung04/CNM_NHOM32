<?php
/**
 * Test đơn giản - không conflict
 */

echo "<h2>🔧 Test Simple - No Conflict</h2>";

try {
    echo "<p>✅ Loading secure_session.php...</p>";
    require_once 'helpers/secure_session.php';
    echo "<p>✅ Loaded successfully!</p>";
    
    $secureSession = SecureSession::getInstance();
    echo "<p>✅ SecureSession created</p>";
    
    $isLoggedIn = isLoggedIn();
    echo "<p>✅ isLoggedIn(): " . ($isLoggedIn ? 'Đã đăng nhập' : 'Chưa đăng nhập') . "</p>";
    
    $token = generateCSRFToken();
    echo "<p>✅ CSRF Token: " . substr($token, 0, 8) . "...</p>";
    
    $field = csrfField();
    echo "<p>✅ CSRF Field: " . htmlspecialchars($field) . "</p>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>✅ SUCCESS!</h4>";
    echo "<p>Không có lỗi function conflict nữa!</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Error:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Fatal Error:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Main System</a>";
echo "</div>";
?>