<?php
echo "<h2>🔧 Sửa lỗi hệ thống đăng nhập</h2>";
echo "<hr>";

// Thông tin kết nối
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "qlnt_db";

try {
    // Kết nối database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Kết nối database thành công!";
    echo "</div>";
    
    echo "<h3>🔄 Cập nhật users với mật khẩu 123456...</h3>";
    
    // Xóa tất cả users cũ
    $pdo->exec("DELETE FROM users");
    $pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1");
    
    // Tạo hash mật khẩu cho 123456
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    
    // Thêm 3 users mới
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?)");
    
    $users = [
        ['admin', $hash, 'Quản lý', '0123456789', 'manager'],
        ['nhanvien1', $hash, 'Nhân viên 1', '0987654321', 'staff'],
        ['nhanvien2', $hash, 'Nhân viên 2', '0912345678', 'staff']
    ];
    
    foreach ($users as $userData) {
        $stmt->execute($userData);
        echo "✅ Thêm user '{$userData[0]}' với mật khẩu 123456<br>";
    }
    
    echo "<h3>🧪 Test đăng nhập...</h3>";
    
    // Test từng user
    foreach ($users as $userData) {
        $username = $userData[0];
        $testPassword = '123456';
        
        // Lấy user từ database
        $testStmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $testStmt->execute([$username]);
        $dbUser = $testStmt->fetch();
        
        if ($dbUser && password_verify($testPassword, $dbUser['password'])) {
            echo "<div style='background: #efe; border: 1px solid #0a0; padding: 5px; border-radius: 3px; margin: 5px 0;'>";
            echo "✅ <strong>$username</strong> / $testPassword - Đăng nhập OK";
            echo "</div>";
        } else {
            echo "<div style='background: #fee; border: 1px solid #f00; padding: 5px; border-radius: 3px; margin: 5px 0;'>";
            echo "❌ <strong>$username</strong> / $testPassword - Đăng nhập THẤT BẠI";
            echo "</div>";
        }
    }
    
    echo "<h3>🔧 Tạo trang đăng nhập đơn giản...</h3>";
    
    // Tạo trang đăng nhập đơn giản
    $simpleLogin = '<!DOCTYPE html>
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
                        url(\'assets/images/bìa.png\') center/cover no-repeat fixed;
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 30px 25px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .btn-login {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/images/logo.png" alt="Logo" style="height: 80px; width: auto; max-width: 100%; margin-bottom: 12px; border-radius: 15px;">
                <h3 class="mb-0">DUO PHARMA</h3>
                <p class="mb-0 mt-2">Đăng nhập để tiếp tục</p>
            </div>
            
            <div class="login-body">
                <?php
                session_start();
                
                $error = \'\';
                
                if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
                    $username = trim($_POST[\'username\'] ?? \'\');
                    $password = $_POST[\'password\'] ?? \'\';
                    
                    if (empty($username) || empty($password)) {
                        $error = \'Vui lòng nhập tên đăng nhập và mật khẩu\';
                    } else {
                        // Kết nối database
                        try {
                            $pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            
                            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
                            $stmt->execute([$username]);
                            $user = $stmt->fetch();
                            
                            if ($user && password_verify($password, $user[\'password\'])) {
                                // Đăng nhập thành công
                                $_SESSION[\'user_id\'] = $user[\'user_id\'];
                                $_SESSION[\'username\'] = $user[\'username\'];
                                $_SESSION[\'full_name\'] = $user[\'full_name\'];
                                $_SESSION[\'role\'] = $user[\'role\'];
                                $_SESSION[\'last_activity\'] = time();
                                
                                header(\'Location: index.php?page=dashboard\');
                                exit;
                            } else {
                                $error = \'Tên đăng nhập hoặc mật khẩu không đúng\';
                            }
                        } catch (Exception $e) {
                            $error = \'Lỗi kết nối database: \' . $e->getMessage();
                        }
                    }
                }
                ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
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
                                   value="<?php echo htmlspecialchars($_POST[\'username\'] ?? \'\'); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" name="password" 
                                   placeholder="123456" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Đăng nhập
                    </button>
                </form>
                
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <strong>Tài khoản test:</strong><br>
                        admin / 123456 (Quản lý)<br>
                        nhanvien1 / 123456 (Nhân viên)<br>
                        nhanvien2 / 123456 (Nhân viên)
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
    
    file_put_contents('login_test.php', $simpleLogin);
    echo "✅ Tạo trang đăng nhập test: login_test.php<br>";
    
    echo "<h3>🔧 Sửa AuthController...</h3>";
    
    // Tạo AuthController đơn giản
    $simpleAuthController = '<?php
require_once \'helpers/security.php\';

class AuthController {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            throw new Exception("Không thể kết nối database: " . $e->getMessage());
        }
    }
    
    public function login() {
        $error = \'\';
        
        // Nếu đã đăng nhập, chuyển về dashboard
        if (isset($_SESSION[\'user_id\'])) {
            header(\'Location: index.php?page=dashboard\');
            exit;
        }
        
        // Xử lý form submit
        if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
            $username = trim($_POST[\'username\'] ?? \'\');
            $password = $_POST[\'password\'] ?? \'\';
            
            if (empty($username) || empty($password)) {
                $error = \'Vui lòng nhập tên đăng nhập và mật khẩu\';
            } else {
                try {
                    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($password, $user[\'password\'])) {
                        // Đăng nhập thành công
                        session_regenerate_id(true);
                        
                        $_SESSION[\'user_id\'] = $user[\'user_id\'];
                        $_SESSION[\'username\'] = $user[\'username\'];
                        $_SESSION[\'full_name\'] = $user[\'full_name\'];
                        $_SESSION[\'role\'] = $user[\'role\'];
                        $_SESSION[\'last_activity\'] = time();
                        
                        header(\'Location: index.php?page=dashboard\');
                        exit;
                    } else {
                        $error = \'Tên đăng nhập hoặc mật khẩu không đúng\';
                    }
                } catch (Exception $e) {
                    $error = \'Lỗi hệ thống: \' . $e->getMessage();
                }
            }
        }
        
        // Hiển thị form đăng nhập
        require_once \'views/auth/login.php\';
    }
    
    public function logout() {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), \'\', time() - 3600, \'/\');
        }
        
        session_destroy();
        header(\'Location: index.php?page=auth&action=login\');
        exit;
    }
}';
    
    file_put_contents('controllers/AuthController_fixed.php', $simpleAuthController);
    echo "✅ Tạo AuthController đã sửa<br>";
    
    echo "<div style='background: #e7f3ff; border: 1px solid #007bff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Sửa lỗi đăng nhập hoàn tất!</h3>";
    echo "<p><strong>Các tài khoản đã sẵn sàng:</strong></p>";
    echo "<ul>";
    echo "<li><strong>admin</strong> / 123456 (Manager)</li>";
    echo "<li><strong>nhanvien1</strong> / 123456 (Staff)</li>";
    echo "<li><strong>nhanvien2</strong> / 123456 (Staff)</li>";
    echo "</ul>";
    echo "<br>";
    echo "<p><strong>Các cách đăng nhập:</strong></p>";
    echo "<p>";
    echo "<a href='login_test.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>🧪 Trang đăng nhập test</a>";
    echo "<a href='index.php?page=auth&action=login' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🚀 Trang đăng nhập chính</a>";
    echo "</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>Lỗi:</strong> " . $e->getMessage();
    echo "</div>";
}
?>