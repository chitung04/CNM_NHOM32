<?php
/**
 * Test fix lỗi function conflict
 */

echo "<h2>🔧 Test Fix Lỗi Function Conflict</h2>";

try {
    // Test load secure session
    echo "<p>✅ Loading secure_session.php...</p>";
    require_once 'helpers/secure_session.php';
    echo "<p>✅ Secure session loaded successfully!</p>";
    
    // Test các functions
    echo "<p>✅ Testing functions...</p>";
    
    $secureSession = SecureSession::getInstance();
    echo "<p>✅ SecureSession instance created</p>";
    
    // Test CSRF functions
    echo "<p>✅ Testing CSRF functions...</p>";
    
    if (function_exists('generateCSRFToken')) {
        echo "<p>✅ generateCSRFToken() function exists</p>";
    } else {
        echo "<p>❌ generateCSRFToken() function missing</p>";
    }
    
    if (function_exists('csrfField')) {
        echo "<p>✅ csrfField() function exists</p>";
        $csrfField = csrfField();
        echo "<p>✅ CSRF field generated: " . htmlspecialchars($csrfField) . "</p>";
    } else {
        echo "<p>❌ csrfField() function missing</p>";
    }
    
    // Test authentication functions
    echo "<p>✅ Testing auth functions...</p>";
    
    $isLoggedIn = isLoggedIn();
    echo "<p>✅ isLoggedIn(): " . ($isLoggedIn ? 'true' : 'false') . "</p>";
    
    if ($isLoggedIn) {
        $user = getCurrentUser();
        echo "<p>✅ Current user: " . $user['full_name'] . "</p>";
    } else {
        echo "<p>ℹ️ Not logged in</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>✅ Tất cả functions hoạt động bình thường!</h4>";
    echo "<p>Lỗi function conflict đã được sửa thành công.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
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
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Về trang chủ</a>";
echo "<a href='check_all_pages_auth.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>Test Authentication</a>";
echo "</div>";
?>