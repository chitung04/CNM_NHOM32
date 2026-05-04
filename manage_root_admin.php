<?php
/**
 * Công cụ quản lý Root Admin
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $message = '';
    $error = '';
    
    // Xử lý form submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'set_root_admin') {
            $userId = $_POST['user_id'] ?? 0;
            
            try {
                // Lấy thông tin user
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    throw new Exception("Không tìm thấy user");
                }
                
                // Bỏ root admin của tất cả user khác trong pharmacy
                $stmt = $pdo->prepare("UPDATE users SET is_root_admin = 0 WHERE pharmacy_id = ?");
                $stmt->execute([$user['pharmacy_id']]);
                
                // Set user này làm root admin
                $stmt = $pdo->prepare("UPDATE users SET is_root_admin = 1, role = 'manager' WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                $message = "✅ Đã đặt {$user['username']} làm Admin gốc!";
                
            } catch (Exception $e) {
                $error = "❌ Lỗi: " . $e->getMessage();
            }
        }
        
        if ($action === 'transfer_root_admin') {
            $fromUserId = $_POST['from_user_id'] ?? 0;
            $toUserId = $_POST['to_user_id'] ?? 0;
            
            try {
                // Kiểm tra 2 user cùng pharmacy
                $stmt = $pdo->prepare("
                    SELECT u1.pharmacy_id as p1, u2.pharmacy_id as p2
                    FROM users u1, users u2
                    WHERE u1.user_id = ? AND u2.user_id = ?
                ");
                $stmt->execute([$fromUserId, $toUserId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$result || $result['p1'] != $result['p2']) {
                    throw new Exception("2 user không cùng nhà thuốc");
                }
                
                // Chuyển quyền
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE users SET is_root_admin = 0 WHERE user_id = ?");
                $stmt->execute([$fromUserId]);
                
                $stmt = $pdo->prepare("UPDATE users SET is_root_admin = 1, role = 'manager' WHERE user_id = ?");
                $stmt->execute([$toUserId]);
                
                $pdo->commit();
                
                $message = "✅ Đã chuyển quyền Admin gốc thành công!";
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "❌ Lỗi: " . $e->getMessage();
            }
        }
    }
    
    // Lấy danh sách tất cả pharmacies và users
    $stmt = $pdo->query("
        SELECT 
            p.pharmacy_id,
            p.pharmacy_name,
            p.pharmacy_code,
            COUNT(u.user_id) as total_users,
            SUM(CASE WHEN u.is_root_admin = 1 THEN 1 ELSE 0 END) as root_admin_count,
            SUM(CASE WHEN u.role = 'manager' THEN 1 ELSE 0 END) as manager_count
        FROM pharmacies p
        LEFT JOIN users u ON p.pharmacy_id = u.pharmacy_id
        GROUP BY p.pharmacy_id
        ORDER BY p.pharmacy_id
    ");
    $pharmacies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Quản lý Root Admin</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                max-width: 1400px;
                margin: 0 auto;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1, h2 {
                color: #333;
            }
            .alert {
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .alert-success {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
            }
            .alert-danger {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            th {
                background: #f8f9fa;
                font-weight: bold;
            }
            .badge {
                padding: 5px 10px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            .badge-danger {
                background: #dc3545;
                color: white;
            }
            .badge-primary {
                background: #007bff;
                color: white;
            }
            .badge-info {
                background: #17a2b8;
                color: white;
            }
            .badge-warning {
                background: #ffc107;
                color: #333;
            }
            .btn {
                padding: 8px 15px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                margin: 2px;
            }
            .btn-primary {
                background: #007bff;
                color: white;
            }
            .btn-danger {
                background: #dc3545;
                color: white;
            }
            .btn-success {
                background: #28a745;
                color: white;
            }
            .btn:hover {
                opacity: 0.8;
            }
            .pharmacy-section {
                margin: 30px 0;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🛡️ Quản lý Root Admin</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <p><strong>Công cụ này cho phép:</strong></p>
            <ul>
                <li>Xem danh sách Root Admin của mỗi nhà thuốc</li>
                <li>Đặt user làm Root Admin</li>
                <li>Chuyển quyền Root Admin cho user khác</li>
            </ul>
            
            <hr>
            
            <?php foreach ($pharmacies as $pharmacy): ?>
                <div class="pharmacy-section">
                    <h2>🏥 <?php echo htmlspecialchars($pharmacy['pharmacy_name']); ?></h2>
                    <p>
                        <strong>Mã:</strong> <?php echo $pharmacy['pharmacy_code']; ?> | 
                        <strong>Tổng users:</strong> <?php echo $pharmacy['total_users']; ?> | 
                        <strong>Managers:</strong> <?php echo $pharmacy['manager_count']; ?> | 
                        <strong>Root Admin:</strong> 
                        <?php if ($pharmacy['root_admin_count'] == 0): ?>
                            <span class="badge badge-warning">⚠️ Chưa có</span>
                        <?php elseif ($pharmacy['root_admin_count'] == 1): ?>
                            <span class="badge badge-success">✅ 1 người</span>
                        <?php else: ?>
                            <span class="badge badge-danger">❌ <?php echo $pharmacy['root_admin_count']; ?> người (Lỗi!)</span>
                        <?php endif; ?>
                    </p>
                    
                    <?php
                    // Lấy danh sách users của pharmacy này
                    $stmt = $pdo->prepare("
                        SELECT user_id, username, full_name, role, is_root_admin
                        FROM users
                        WHERE pharmacy_id = ?
                        ORDER BY is_root_admin DESC, role DESC, user_id
                    ");
                    $stmt->execute([$pharmacy['pharmacy_id']]);
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Họ tên</th>
                                <th>Vai trò</th>
                                <th>Loại</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo $user['role'] === 'manager' ? 'Quản lý' : 'Nhân viên'; ?></td>
                                    <td>
                                        <?php if ($user['is_root_admin'] == 1): ?>
                                            <span class="badge badge-danger">🛡️ Admin gốc</span>
                                        <?php elseif ($user['role'] === 'manager'): ?>
                                            <span class="badge badge-primary">👤 Admin được phân quyền</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">👤 Nhân viên</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_root_admin'] != 1): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Đặt <?php echo htmlspecialchars($user['username']); ?> làm Root Admin?\n\nRoot Admin hiện tại sẽ bị hạ quyền.')">
                                                <input type="hidden" name="action" value="set_root_admin">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" class="btn btn-primary">Đặt làm Root Admin</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #28a745;">✅ Đang là Root Admin</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
            
            <hr>
            <p><a href="index.php" class="btn btn-success">← Quay lại trang chủ</a></p>
        </div>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #ffeeee; border: 1px solid red;'>";
    echo "<h3>❌ Lỗi:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
