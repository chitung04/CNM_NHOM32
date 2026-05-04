<?php
/**
 * Script kiểm tra thuốc hiển thị trên trang bán hàng
 */

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/pharmacy.php';

session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("❌ Chưa đăng nhập. Vui lòng đăng nhập trước.");
}

echo "<h2>🔍 KIỂM TRA THUỐC TRÊN TRANG BÁN HÀNG</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Lấy pharmacy_id của user hiện tại
    $pharmacyId = requirePharmacyId();
    echo "<h3>✅ Pharmacy ID: {$pharmacyId}</h3>";
    echo "<hr>";
    
    // 1. Kiểm tra tổng số thuốc trong database
    echo "<h3>1️⃣ TỔNG SỐ THUỐC TRONG DATABASE</h3>";
    $sql = "SELECT COUNT(*) as total FROM medicines WHERE pharmacy_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pharmacyId]);
    $result = $stmt->fetch();
    echo "<p><strong>Tổng số thuốc:</strong> {$result['total']}</p>";
    
    // 2. Kiểm tra số lô thuốc
    echo "<h3>2️⃣ TỔNG SỐ LÔ THUỐC</h3>";
    $sql = "SELECT COUNT(*) as total FROM batches WHERE pharmacy_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pharmacyId]);
    $result = $stmt->fetch();
    echo "<p><strong>Tổng số lô:</strong> {$result['total']}</p>";
    
    // 3. Kiểm tra lô thuốc active có tồn kho
    echo "<h3>3️⃣ LÔ THUỐC ACTIVE CÓ TỒN KHO</h3>";
    $sql = "SELECT COUNT(*) as total FROM batches WHERE pharmacy_id = ? AND status = 'active' AND quantity > 0";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pharmacyId]);
    $result = $stmt->fetch();
    echo "<p><strong>Số lô active có hàng:</strong> {$result['total']}</p>";
    
    // 4. Chạy query giống như trong Medicine::getAll()
    echo "<h3>4️⃣ QUERY GIỐNG TRANG BÁN HÀNG (Medicine::getAll())</h3>";
    $sql = "SELECT m.medicine_id, m.medicine_name, m.price, c.category_name, u.unit_name,
                   COALESCE(m.qr_code, 
                            (SELECT b.qr_code FROM batches b WHERE b.medicine_id = m.medicine_id AND b.qr_code IS NOT NULL LIMIT 1)
                   ) as qr_code,
                   COALESCE(SUM(b.quantity), 0) as inventory
            FROM medicines m
            LEFT JOIN categories c ON m.category_id = c.category_id
            LEFT JOIN units u ON m.unit_id = u.unit_id
            LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
            WHERE m.pharmacy_id = ?
            GROUP BY m.medicine_id, m.medicine_name, m.category_id, m.unit_id, m.price, m.description, m.qr_code, c.category_name, u.unit_name
            ORDER BY m.medicine_id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pharmacyId]);
    $medicines = $stmt->fetchAll();
    
    echo "<p><strong>Số thuốc trả về:</strong> " . count($medicines) . "</p>";
    
    if (count($medicines) > 0) {
        echo "<h4>📋 DANH SÁCH THUỐC (10 thuốc đầu tiên):</h4>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>
                <th>ID</th>
                <th>Tên thuốc</th>
                <th>Giá</th>
                <th>Danh mục</th>
                <th>Đơn vị</th>
                <th>Tồn kho</th>
                <th>QR Code</th>
              </tr>";
        
        $count = 0;
        foreach ($medicines as $med) {
            if ($count >= 10) break;
            
            $qrStatus = !empty($med['qr_code']) ? '✅ Có' : '❌ Không';
            $inventoryColor = $med['inventory'] > 0 ? 'green' : 'red';
            
            echo "<tr>";
            echo "<td>{$med['medicine_id']}</td>";
            echo "<td>{$med['medicine_name']}</td>";
            echo "<td>" . number_format($med['price']) . "đ</td>";
            echo "<td>{$med['category_name']}</td>";
            echo "<td>{$med['unit_name']}</td>";
            echo "<td style='color: {$inventoryColor}; font-weight: bold;'>{$med['inventory']}</td>";
            echo "<td>{$qrStatus}</td>";
            echo "</tr>";
            
            $count++;
        }
        echo "</table>";
        
        // Thống kê
        echo "<h4>📊 THỐNG KÊ:</h4>";
        $withStock = array_filter($medicines, function($m) { return $m['inventory'] > 0; });
        $withoutStock = array_filter($medicines, function($m) { return $m['inventory'] == 0; });
        
        echo "<ul>";
        echo "<li><strong>Thuốc có tồn kho:</strong> " . count($withStock) . "</li>";
        echo "<li><strong>Thuốc hết hàng:</strong> " . count($withoutStock) . "</li>";
        echo "</ul>";
        
        if (count($withoutStock) > 0) {
            echo "<h4>⚠️ THUỐC HẾT HÀNG (10 thuốc đầu):</h4>";
            echo "<ul>";
            $count = 0;
            foreach ($withoutStock as $med) {
                if ($count >= 10) break;
                echo "<li>{$med['medicine_name']} (ID: {$med['medicine_id']})</li>";
                $count++;
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ KHÔNG CÓ THUỐC NÀO!</p>";
        echo "<p>Nguyên nhân có thể:</p>";
        echo "<ul>";
        echo "<li>Chưa có thuốc nào trong database cho pharmacy_id = {$pharmacyId}</li>";
        echo "<li>Chưa import file ULTIMATE_DATABASE_FINAL.sql</li>";
        echo "<li>Đang đăng nhập với pharmacy khác</li>";
        echo "</ul>";
    }
    
    // 5. Kiểm tra thuốc có lô nhưng không hiển thị
    echo "<h3>5️⃣ KIỂM TRA THUỐC CÓ LÔ NHƯNG KHÔNG HIỂN THỊ</h3>";
    $sql = "SELECT m.medicine_id, m.medicine_name, 
                   COUNT(b.batch_id) as batch_count,
                   SUM(CASE WHEN b.status = 'active' THEN 1 ELSE 0 END) as active_batches,
                   SUM(CASE WHEN b.status = 'active' AND b.quantity > 0 THEN b.quantity ELSE 0 END) as total_quantity
            FROM medicines m
            LEFT JOIN batches b ON m.medicine_id = b.medicine_id
            WHERE m.pharmacy_id = ?
            GROUP BY m.medicine_id, m.medicine_name
            HAVING batch_count > 0 AND total_quantity = 0";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pharmacyId]);
    $problematic = $stmt->fetchAll();
    
    if (count($problematic) > 0) {
        echo "<p style='color: orange; font-weight: bold;'>⚠️ Có {count($problematic)} thuốc có lô nhưng không có tồn kho:</p>";
        echo "<ul>";
        foreach ($problematic as $med) {
            echo "<li>{$med['medicine_name']} - Tổng lô: {$med['batch_count']}, Lô active: {$med['active_batches']}, Tồn kho: {$med['total_quantity']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: green;'>✅ Không có vấn đề về lô thuốc</p>";
    }
    
    echo "<hr>";
    echo "<h3>✅ HOÀN TẤT KIỂM TRA</h3>";
    echo "<p><a href='index.php?page=sales'>← Quay lại trang bán hàng</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ LỖI:</strong> " . $e->getMessage() . "</p>";
}
