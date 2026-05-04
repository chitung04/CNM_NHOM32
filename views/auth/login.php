<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - DUO PHARMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.3) 0%, rgba(59, 130, 246, 0.3) 100%), 
                        url('assets/images/bìa.png') center/cover no-repeat fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.3) 0%, rgba(59, 130, 246, 0.3) 100%);
            color: white;
            padding: 30px 25px;
            text-align: center;
        }
        .login-header i {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        .login-header h3 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .login-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .login-body {
            padding: 30px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
            background-color: #fff;
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }
        .btn-outline-secondary {
            border: 1px solid rgba(0, 0, 0, 0.2);
            color: #666;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            font-weight: 500;
            border-left: none;
        }
        .btn-outline-secondary:hover {
            background: rgba(255, 255, 255, 1);
            border-color: rgba(0, 0, 0, 0.3);
            color: #3b82f6;
        }
        .input-group-text {
            background-color: rgba(248, 249, 250, 0.9);
            border-right: none;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .form-control {
            border-left: none;
            border-right: none;
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 0;
        }
        .btn-outline-secondary {
            border: 1px solid rgba(0, 0, 0, 0.2);
            color: #666;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            font-weight: 500;
            border-left: none;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/images/logo.png" alt="DUO PHARMA Logo" 
                     style="height: 100px; width: auto; max-width: 100%; margin-bottom: 15px; 
                            border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.2); 
                            background: white; padding: 10px; border: 3px solid rgba(255,255,255,0.3);">
                <h3 class="mb-0" style="font-weight: 700; font-size: 2rem; letter-spacing: 1px; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">DUO PHARMA</h3>
                <p class="mb-0 mt-2" style="font-size: 1.1rem; opacity: 0.95;">Đăng nhập để tiếp tục</p>
            </div>
            
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['timeout'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-clock-fill me-2"></i>
                        Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['reason'])): ?>
                    <?php 
                    $reason = $_GET['reason'];
                    $messages = [
                        'session_required' => 'Vui lòng đăng nhập để truy cập hệ thống.',
                        'session_expired' => 'Phiên làm việc đã hết hạn.',
                        'browser_changed' => 'Phát hiện truy cập từ trình duyệt khác. Tất cả phiên đăng nhập đã bị hủy vì lý do bảo mật.',
                        'security_violation' => 'Phát hiện vi phạm bảo mật. Tất cả phiên đăng nhập đã bị hủy.',
                        'logged_out' => 'Bạn đã đăng xuất thành công.',
                        'browser_fingerprint_mismatch' => 'Phát hiện truy cập từ trình duyệt/thiết bị khác. Tất cả phiên đăng nhập đã bị đăng xuất vì lý do bảo mật.'
                    ];
                    $message = $messages[$reason] ?? 'Vui lòng đăng nhập.';
                    $alertClass = $reason === 'logged_out' ? 'alert-success' : 
                                 (in_array($reason, ['browser_changed', 'security_violation', 'browser_fingerprint_mismatch']) ? 'alert-danger' : 'alert-info');
                    ?>
                    <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Bạn đã đăng xuất thành công.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" class="form-control" name="username" 
                                   placeholder="admin, nhanvien1, nhanvien2" required autofocus
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" name="password" 
                                   placeholder="123456" required id="password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Đăng nhập
                    </button>
                </form>
                
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        Chưa có tài khoản? 
                        <a href="index.php?page=auth&action=register" class="text-primary fw-bold">Đăng ký ngay</a>
                    </small>
                </div>
                
                <div class="mt-2 text-center">
                    <small class="text-muted">
                        <strong>Tài khoản test:</strong><br>
                        <span class="badge bg-primary me-1">admin</span> / 123456 (Quản lý)<br>
                        <span class="badge bg-info me-1">nhanvien1</span> / 123456 (Nhân viên)<br>
                        <span class="badge bg-info me-1">nhanvien2</span> / 123456 (Nhân viên)
                    </small>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3 text-white">
            <small>&copy; 2026 Pharmacy Management System</small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>
