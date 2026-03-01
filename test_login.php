<?php
// Test login debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Login System</h2>";

// Test 1: Load config
echo "<h3>1. Testing Config Load</h3>";
try {
    require_once 'config/config.php';
    echo "✓ Config loaded successfully<br>";
    echo "BASE_PATH: " . BASE_PATH . "<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
} catch (Exception $e) {
    echo "✗ Config error: " . $e->getMessage() . "<br>";
    die();
}

// Test 2: Start session
echo "<h3>2. Testing Session</h3>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "✓ Session started successfully<br>";
    echo "Session ID: " . session_id() . "<br>";
} catch (Exception $e) {
    echo "✗ Session error: " . $e->getMessage() . "<br>";
}

// Test 3: Database connection
echo "<h3>3. Testing Database Connection</h3>";
try {
    require_once 'config/database.php';
    require_once 'models/Database.php';
    $db = Database::getInstance();
    echo "✓ Database connected successfully<br>";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
    die();
}

// Test 4: Check users table
echo "<h3>4. Testing Users Table</h3>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✓ Users table exists<br>";
    echo "Total users: " . $result['count'] . "<br>";
} catch (Exception $e) {
    echo "✗ Users table error: " . $e->getMessage() . "<br>";
    die();
}

// Test 5: List all users
echo "<h3>5. List All Users</h3>";
try {
    $stmt = $db->query("SELECT user_id, username, full_name, role, is_active FROM users");
    $users = $stmt->fetchAll();
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Active</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['user_id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['full_name'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "✗ Error listing users: " . $e->getMessage() . "<br>";
}

// Test 6: Test authentication
echo "<h3>6. Testing Authentication</h3>";
try {
    require_once 'models/User.php';
    $userModel = new User();
    
    // Test with admin
    echo "<strong>Testing admin/admin123:</strong><br>";
    $user = $userModel->authenticate('admin', 'admin123');
    if ($user) {
        echo "✓ Admin authentication successful<br>";
        echo "User ID: " . $user['user_id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Full Name: " . $user['full_name'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
    } else {
        echo "✗ Admin authentication failed<br>";
        
        // Check if password hash is correct
        $stmt = $db->query("SELECT password FROM users WHERE username = 'admin'");
        $adminData = $stmt->fetch();
        if ($adminData) {
            echo "Password hash in DB: " . substr($adminData['password'], 0, 20) . "...<br>";
            echo "Testing password_verify: " . (password_verify('admin123', $adminData['password']) ? 'PASS' : 'FAIL') . "<br>";
        }
    }
    
    echo "<br><strong>Testing staff/staff123:</strong><br>";
    $user = $userModel->authenticate('staff', 'staff123');
    if ($user) {
        echo "✓ Staff authentication successful<br>";
    } else {
        echo "✗ Staff authentication failed<br>";
    }
} catch (Exception $e) {
    echo "✗ Authentication error: " . $e->getMessage() . "<br>";
}

echo "<h3>Done!</h3>";
?>
