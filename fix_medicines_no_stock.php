<?php
/**
 * Script tự động sửa thuốc không có tồn kho
 * Thêm lô hàng cho các thuốc đã có nhưng chưa có lô hoặc hết hàng
 */

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/pharmacy.php';

session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("❌ Chưa đăng nhập. Vui lòng đăng nhập trước.");
}

echo "<h2>🔧 TỰ ĐỘNG SỬA THUỐC KHÔNG CÓ TỒN KHO</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Lấy pharmacy_id của user hiện tại
    $pharmacyId = requirePharmacyId();
    echo "<h3>✅ Pharmacy ID: {$pharmacyId}</h3>";
    echo "<hr>";
    
    // 1. Tìm thuốc không có tồn kho
    echo "<h3>1️⃣ TÌM THUỐC KHÔNG CÓ TỒN KHO</h3>";
    
    $sql = "SELECT m.medicine_id, m.medicine_name, m.price,
                   COALESCE(SUM(CASE WHEN b.status = 'active' THEN b.quantity ELSE 0 END), 0) as total_stock
            FROM medicines m
            LEFT JOIN batches b ON m.medicine_id = b.medicine_id
            WHERE m.pharmacy_id = ?
            GROUP BY m.medicine_id, m.medicine_name, m.price
            HAVING total_stock = 0
            ORDER BY m.medicine_id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pharmacyId]);
    $medicinesWithoutStock = $stmt->fetchAll();
    
    $count = count($medicinesWithoutStock);
    echo "<p><strong>Số thuốc không có tồn kho:</strong> {$count}</p>";
    
    if ($count == 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ TẤT CẢ THUỐC ĐỀU CÓ TỒN KHO!</p>";
        echo "<p>Không cần sửa gì cả.</p>";
        echo "<hr>";
        echo "<p><a href='index.php?page=sales'>← Quay lại trang bán hàng</a></p>";
        exit;
    }
    
    echo "<h4>📋 Danh sách thuốc cần thêm lô:</h4>";
    echo "<ul>";
    foreach ($medicinesWithoutStock as $med) {
        echo "<li>ID: {$med['medicine_id']} - {$med['medicine_name']} - Giá: " . number_format($med['price']) . "đ</li>";
    }
    echo "</ul>";
    
    // 2. Lấy supplier_id đầu tiên
    echo "<h3>2️⃣ LẤY NHÀ CUNG CẤP</h3>";
    $sql = "SELECT supplier_id FROM suppliers WHERE pharmacy_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pharmacyId]);
    $supplier = $stmt->fetch();
    
    if (!$supplier) {
        echo "<p style='color: red;'>❌ Không tìm thấy nhà cung cấp. Vui lòng tạo nhà cung cấp trước.</p>";
        exit;
    }
    
    $supplierId = $supplier['supplier_id'];
    echo "<p>✅ Sử dụng supplier_id: {$supplierId}</p>";
    
    // 3. Thêm lô hàng cho từng thuốc
    echo "<h3>3️⃣ THÊM LÔ HÀNG</h3>";
    
    $conn->beginTransaction();
    
    $addedCount = 0;
    $today = date('Y-m-d');
    $expiryDate = date('Y-m-d', strtotime('+365 days')); // Hết hạn sau 1 năm
    
    foreach ($medicinesWithoutStock as $med) {
        try {
            // Tạo batch_number unique
            $batchNumber = 'LOT' . date('YmdHis') . str_pad($med['medicine_id'], 3, '0', STR_PAD_LEFT);
            
            // Tạo QR code unique
            $qrCode = 'BATCH_' . time() . '_' . rand(1000, 9999);
            
            // Số lượng ngẫu nhiên từ 50-200
            $quantity = rand(50, 200);
            
            // Insert batch
            $sql = "INSERT INTO batches (pharmacy_id, medicine_id, supplier_id, batch_number, quantity, import_date, expiry_date, qr_code, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $pharmacyId,
                $med['medicine_id'],
                $supplierId,
                $batchNumber,
                $quantity,
                $today,
                $expiryDate,
                $qrCode
            ]);
            
            echo "<p>✅ Đã thêm lô cho <strong>{$med['medicine_name']}</strong> - Số lượng: {$quantity}</p>";
            $addedCount++;
            
            // Sleep để tránh trùng timestamp
            usleep(100000); // 0.1 giây
            
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Lỗi khi thêm lô cho {$med['medicine_name']}: " . $e->getMessage() . "</p>";
        }
    }
    
    $conn->commit();
    
    echo "<hr>";
    echo "<h3>✅ HOÀN TẤT</h3>";
    echo "<p><strong>Đã thêm {$addedCount}/{$count} lô hàng thành công!</strong></p>";
    
    // 4. Kiểm tra lại
    echo "<h3>4️⃣ KIỂM TRA LẠI</h3>";
    
    $sql = "SELECT m.medicine_id, m.medicine_name,
                   COALESCE(SUM(CASE WHEN b.status = 'active' THEN b.quantity ELSE 0 END), 0) as total_stock
            FROM medicines m
            LEFT JOIN batches b ON m.medicine_id = b.medicine_id
            WHERE m.pharmacy_id = ?
            GROUP BY m.medicine_id, m.medicine_name
            HAVING total_stock = 0";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pharmacyId]);
    $stillNoStock = $stmt->fetchAll();
    
    if (count($stillNoStock) == 0) {
        echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ TẤT CẢ THUỐC ĐỀU ĐÃ CÓ TỒN KHO!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Vẫn còn " . count($stillNoStock) . " thuốc chưa có tồn kho:</p>";
        echo "<ul>";
        foreach ($stillNoStock as $med) {
            echo "<li>{$med['medicine_name']}</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<h3>🎯 TIẾP THEO</h3>";
    echo "<p>1. <a href='check_sales_medicines.php' target='_blank'><strong>Kiểm tra lại danh sách thuốc</strong></a></p>";
    echo "<p>2. <a href='index.php?page=sales'><strong>Vào trang bán hàng</strong></a></p>";
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<p style='color: red;'><strong>❌ LỖI:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
