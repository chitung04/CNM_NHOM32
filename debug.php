<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Information</h2>";

// 1. Check PHP version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// 2. Check if files exist
$files = [
    'config/config.php',
    'config/database.php',
    'helpers/auth.php',
    'models/Database.php'
];

echo "<h3>File Check:</h3>";
foreach ($files as $file) {
    $exists = file_exists($file) ? '✓ EXISTS' : '✗ NOT FOUND';
    echo "<p>$file: $exists</p>";
}

// 3. Try to load config
echo "<h3>Loading Config:</h3>";
try {
    require_once 'config/config.php';
    // Chỉ cho phép debug khi đang ở môi trường development hoặc chạy CLI và truy cập từ localhost
    $isCli = (php_sapi_name() === 'cli');
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', ['127.0.0.1', '::1']);
    $env = defined('APP_ENV') ? APP_ENV : (getenv('APP_ENV') ?: null);
    if (!($isCli || ($env === 'development' && $isLocal))) {
        http_response_code(403);
        echo "<p>Debug access forbidden.</p>";
        exit;
    }

    echo "<p>✓ config.php loaded</p>";
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

// 4. Try to load database config
echo "<h3>Loading Database Config:</h3>";
try {
    require_once 'config/database.php';
    echo "<p>✓ database.php loaded</p>";
    echo "<p>DB_HOST: " . DB_HOST . "</p>";
    echo "<p>DB_NAME: " . DB_NAME . "</p>";
    echo "<p>DB_USER: " . DB_USER . "</p>";
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

// 5. Try to connect to database
echo "<h3>Database Connection:</h3>";
try {
    require_once 'models/Database.php';
    $db = Database::getInstance();
    echo "<p>✓ Database connected successfully!</p>";
    
    // Test query
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>✓ Users table exists. Count: " . $result['count'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p>✗ Database Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

// 6. Try to load auth helper
echo "<h3>Loading Auth Helper:</h3>";
try {
    session_start();
    require_once 'helpers/auth.php';
    echo "<p>✓ auth.php loaded</p>";
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>If all checks pass, the issue might be in index.php routing.</strong></p>";
echo "<p><a href='index.php'>Try accessing index.php</a></p>";
