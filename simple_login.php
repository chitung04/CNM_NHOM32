<?php
// Simple login without CSRF and rate limiting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'models/User.php';

$error = '';
$debug = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $debug[] = "Username: $username";
    $debug[] = "Password length: " . strlen($password);
    
    if (!empty($username) && !empty($password)) {
        try {
            $userModel = new User();
            $user = $userModel->authenticate($username, $password);
            
            $debug[] = "Authentication result: " . ($user ? 'SUCCESS' : 'FAILED');
            
            if ($user) {
                $debug[] = "User found: " . print_r($user, true);
                
                // Regenerate session ID
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                $debug[] = "Session set successfully";
                $debug[] = "Session data: " . print_r($_SESSION, true);
                
                // Redirect
                header('Location: index.php?page=dashboard');
                exit;
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
                
                // Debug: Check if user exists
                require_once 'models/Database.php';
                $db = Database::getInstance();
                $stmt = $db->query("SELECT user_id, username, is_active FROM users WHERE username = ?", [$username]);
                $userCheck = $stmt->fetch();
                
                if ($userCheck) {
                    $debug[] = "User exists in DB: " . print_r($userCheck, true);
                    $debug[] = "User is_active: " . $userCheck['is_active'];
                    
                    // Check password hash
                    $stmt = $db->query("SELECT password FROM users WHERE username = ?", [$username]);
                    $passData = $stmt->fetch();
                    $debug[] = "Password hash: " . substr($passData['password'], 0, 30) . "...";
                    $debug[] = "password_verify result: " . (password_verify($password, $passData['password']) ? 'TRUE' : 'FALSE');
                } else {
                    $debug[] = "User NOT found in database";
                }
            }
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
            $debug[] = "Exception: " . $e->getMessage();
            $debug[] = "Trace: " . $e->getTraceAsString();
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login - Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .login-card { background: white; border-radius: 15px; padding: 30px; max-width: 500px; margin: 0 auto; }
        .debug-info { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-top: 20px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4">🔐 Simple Login (Debug Mode)</h3>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Tên đăng nhập</label>
                <input type="text" class="form-control" name="username" value="admin" required autofocus>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" name="password" value="admin123" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
        </form>
        
        <hr>
        
        <div class="text-center text-muted">
            <small>Demo: admin/admin123 hoặc staff/staff123</small>
        </div>
        
        <?php if (!empty($debug)): ?>
            <div class="debug-info">
                <strong>🐛 Debug Information:</strong><br><br>
                <?php foreach ($debug as $line): ?>
                    <?php echo htmlspecialchars($line); ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="mt-3 text-center">
            <a href="reset_users.php" class="btn btn-sm btn-warning">Reset Users</a>
            <a href="test_login.php" class="btn btn-sm btn-info">Test System</a>
        </div>
    </div>
</body>
</html>
