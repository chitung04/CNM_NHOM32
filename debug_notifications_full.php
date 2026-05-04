<?php
/**
 * Script debug đầy đủ hệ thống thông báo
 */

session_start();

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/secure_session.php';

if (!isLoggedIn()) {
    die("❌ Vui lòng đăng nhập trước!");
}

require_once 'helpers/pharmacy.php';
$pharmacyId = getCurrentPharmacyId();

echo "<h2>🐛 Debug Hệ Thống Thông Báo</h2>";
echo "<hr>";

$db = Database::getInstance();

try {
    // 1. Thông tin session
    echo "<h3>1. Session Info:</h3>";
    echo "<ul>";
    echo "<li>user_id: <strong>{$_SESSION['user_id']}</strong></li>";
    echo "<li>pharmacy_id: <strong>{$pharmacyId}</strong></li>";
    echo "<li>username: <strong>{$_SESSION['username']}</strong></li>";
    echo "</ul>";
    
    // 2. Kiểm tra thuốc
    echo "<h3>2. Kiểm tra thuốc trong pharmacy:</h3>";
    $medicines = $db->query("SELECT * FROM medicines WHERE pharmacy_id = ?", [$pharmacyId])->fetchAll();
    echo "<p>Tìm thấy: <strong>" . count($medicines) . "</strong> thuốc</p>";
    
    if (count($medicines) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Tên thuốc</th><th>Giá</th></tr>";
        foreach ($medicines as $med) {
            echo "<tr>";
            echo "<td>{$med['medicine_id']}</td>";
            echo "<td>{$med['medicine_name']}</td>";
            echo "<td>" . number_format($med['price']) . " VNĐ</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Kiểm tra lô thuốc
    echo "<h3>3. Kiểm tra lô thuốc:</h3>";
    $batches = $db->query(
        "SELECT b.*, m.medicine_name, 
         DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
         FROM batches b
         LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
         WHERE b.pharmacy_id = ?
         ORDER BY b.expiry_date ASC",
        [$pharmacyId]
    )->fetchAll();
    
    echo "<p>Tìm thấy: <strong>" . count($batches) . "</strong> lô thuốc</p>";
    
    if (count($batches) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Thuốc</th><th>Số lượng</th><th>Hạn SD</th><th>Còn (ngày)</th><th>Trạng thái</th></tr>";
        foreach ($batches as $batch) {
            $rowColor = '';
            if ($batch['days_to_expiry'] <= 60 && $batch['days_to_expiry'] > 0) {
                $rowColor = 'background-color: #fff3cd;'; // Vàng - sắp hết hạn
            } elseif ($batch['days_to_expiry'] <= 0) {
                $rowColor = 'background-color: #f8d7da;'; // Đỏ - đã hết hạn
            }
            
            if ($batch['quantity'] < 50) {
                $rowColor = 'background-color: #ffc107;'; // Cam - sắp hết hàng
            }
            
            echo "<tr style='{$rowColor}'>";
            echo "<td>{$batch['batch_id']}</td>";
            echo "<td>{$batch['medicine_name']}</td>";
            echo "<td><strong>{$batch['quantity']}</strong></td>";
            echo "<td>{$batch['expiry_date']}</td>";
            echo "<td><strong>{$batch['days_to_expiry']}</strong></td>";
            echo "<td>{$batch['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Kiểm tra thuốc sắp hết hàng (tổng tồn kho < 50)
    echo "<h3>4. Thuốc sắp hết hàng (tổng tồn kho < 50):</h3>";
    
    $lowStockMedicines = $db->query(
        "SELECT m.medicine_id, m.medicine_name, 
         COALESCE(SUM(b.quantity), 0) as total_quantity
         FROM medicines m
         LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
         WHERE m.pharmacy_id = ?
         GROUP BY m.medicine_id, m.medicine_name
         HAVING total_quantity < 50
         ORDER BY total_quantity ASC",
        [$pharmacyId]
    )->fetchAll();
    
    echo "<p>Tìm thấy: <strong>" . count($lowStockMedicines) . "</strong> thuốc</p>";
    
    if (count($lowStockMedicines) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Tên thuốc</th><th>Tồn kho</th></tr>";
        foreach ($lowStockMedicines as $med) {
            echo "<tr style='background-color: #ffc107;'>";
            echo "<td>{$med['medicine_id']}</td>";
            echo "<td>{$med['medicine_name']}</td>";
            echo "<td><strong>{$med['total_quantity']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>✅ Không có thuốc nào sắp hết hàng</p>";
    }
    
    // 5. Kiểm tra lô thuốc sắp hết hạn (< 60 ngày)
    echo "<h3>5. Lô thuốc sắp hết hạn (< 60 ngày):</h3>";
    
    $expiringBatches = $db->query(
        "SELECT b.*, m.medicine_name,
         DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
         FROM batches b
         LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
         WHERE b.pharmacy_id = ? 
         AND b.status = 'active'
         AND DATEDIFF(b.expiry_date, CURDATE()) <= 60
         AND DATEDIFF(b.expiry_date, CURDATE()) > 0
         ORDER BY days_to_expiry ASC",
        [$pharmacyId]
    )->fetchAll();
    
    echo "<p>Tìm thấy: <strong>" . count($expiringBatches) . "</strong> lô</p>";
    
    if (count($expiringBatches) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Thuốc</th><th>Số lô</th><th>Hạn SD</th><th>Còn (ngày)</th></tr>";
        foreach ($expiringBatches as $batch) {
            echo "<tr style='background-color: #fff3cd;'>";
            echo "<td>{$batch['batch_id']}</td>";
            echo "<td>{$batch['medicine_name']}</td>";
            echo "<td>{$batch['batch_number']}</td>";
            echo "<td>{$batch['expiry_date']}</td>";
            echo "<td><strong>{$batch['days_to_expiry']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>✅ Không có lô nào sắp hết hạn</p>";
    }
    
    // 6. Kiểm tra thông báo trong database
    echo "<h3>6. Thông báo trong database:</h3>";
    
    $notifications = $db->query(
        "SELECT * FROM notifications 
         WHERE pharmacy_id = ? 
         ORDER BY created_at DESC 
         LIMIT 20",
        [$pharmacyId]
    )->fetchAll();
    
    echo "<p>Tìm thấy: <strong>" . count($notifications) . "</strong> thông báo</p>";
    
    if (count($notifications) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Loại</th><th>Nội dung</th><th>Đã đọc</th><th>Ngày tạo</th></tr>";
        foreach ($notifications as $notif) {
            $bgColor = $notif['is_read'] ? '#f8f9fa' : '#fff3cd';
            echo "<tr style='background-color: {$bgColor};'>";
            echo "<td>{$notif['notification_id']}</td>";
            echo "<td>{$notif['type']}</td>";
            echo "<td>{$notif['message']}</td>";
            echo "<td>" . ($notif['is_read'] ? 'Đã đọc' : '<strong>Chưa đọc</strong>') . "</td>";
            echo "<td>{$notif['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ KHÔNG CÓ THÔNG BÁO NÀO!</p>";
    }
    
    // 7. Tạo thông báo mới
    echo "<h3>7. Tạo thông báo mới:</h3>";
    
    require_once 'models/Notification.php';
    $notificationModel = new Notification();
    
    echo "<p>Đang kiểm tra và tạo thông báo...</p>";
    
    $notificationModel->checkLowStock();
    echo "<p>✅ Đã kiểm tra thuốc sắp hết hàng</p>";
    
    $notificationModel->checkExpiring();
    echo "<p>✅ Đã kiểm tra lô thuốc sắp hết hạn</p>";
    
    // Đếm lại thông báo
    $newCount = $db->query(
        "SELECT COUNT(*) as count FROM notifications WHERE pharmacy_id = ?",
        [$pharmacyId]
    )->fetch();
    
    echo "<p>Tổng số thông báo sau khi tạo: <strong>{$newCount['count']}</strong></p>";
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH DEBUG!</h2>";
    echo "<p><a href='index.php' style='font-size: 16px; font-weight: bold;'>→ Quay lại trang chủ</a></p>";
    echo "<p><a href='index.php?page=notifications' style='font-size: 16px; font-weight: bold;'>→ Xem trang thông báo</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
