<?php
/**
 * Test không có function conflict
 */

echo "<h2>🔧 Test No Function Conflict</h2>";

try {
    echo "<p>✅ Loading secure_session.php...</p>";
    require_once 'helpers/secure_session.php';
    echo "<p>✅ Loaded successfully!</p>";
    
    $secureSession = SecureSession::getInstance();
    echo "<p>✅ SecureSession created</p>";
    
    $isLoggedIn = isLoggedIn();
    echo "<p>✅ isLoggedIn(): " . ($isLoggedIn ? 'Đã đăng nhập' : 'Chưa đăng nhập') . "</p>";
    
    if ($isLoggedIn) {
        $user = getCurrentUser();
        echo "<p>✅ Current user: " . $user['full_name'] . "</p>";
        
        $isManager = isManager();
        echo "<p>✅ isManager(): " . ($isManager ? 'true' : 'false') . "</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>✅ SUCCESS!</h4>";
    echo "<p>Không có lỗi function conflict nữa!</p>";
    echo "<p>Tất cả functions hoạt động bình thường.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Exception:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Fatal Error:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Test Main System</a>";
echo "<a href='create_medicine_qr_codes.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Create QR Codes</a>";
echo "</div>";
?>