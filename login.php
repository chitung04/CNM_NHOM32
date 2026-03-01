<?php
// Simple login page without routing
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';

// Khởi tạo session sau khi cấu hình session đã được thiết lập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'models/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $userModel = new User();
        $user = $userModel->authenticate($username, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
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
    <title>Đăng nhập - Hệ thống quản lý nhà thuốc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 480px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
            padding: 50px 40px;
            text-align: center;
            color: white;
        }
        
        .login-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.95;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .login-header p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-group-custom {
            position: relative;
        }
        
        .input-group-custom i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f7fafc;
        }
        
        .form-control-custom:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .demo-info {
            margin-top: 24px;
            padding: 16px;
            background: #edf2f7;
            border-radius: 12px;
            text-align: center;
        }
        
        .demo-info i {
            color: #667eea;
            margin-right: 8px;
        }
        
        .demo-info p {
            margin: 0;
            color: #4a5568;
            font-size: 14px;
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            color: white;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .alert-custom {
            padding: 12px 16px;
            background: #fed7d7;
            border: 1px solid #fc8181;
            border-radius: 12px;
            color: #c53030;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-hospital"></i>
            </div>
            <h1>Hệ thống quản lý nhà thuốc</h1>
            <p>Đăng nhập để tiếp tục</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert-custom">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Tên đăng nhập</label>
                    <div class="input-group-custom">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               class="form-control-custom" 
                               name="username" 
                               placeholder="Nhập tên đăng nhập"
                               required 
                               autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mật khẩu</label>
                    <div class="input-group-custom">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               class="form-control-custom" 
                               name="password" 
                               id="password"
                               placeholder="Nhập mật khẩu"
                               required 
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Đăng nhập
                </button>
            </form>
            
            <div class="demo-info">
                <p>
                    <i class="fas fa-info-circle"></i>
                    <strong>Demo:</strong> quanly/quanly12345 hoặc nhanvien/nhanvien2233
                </p>
            </div>
        </div>
    </div>
    
    <div class="login-footer">
        © 2026 Pharmacy Management System
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
