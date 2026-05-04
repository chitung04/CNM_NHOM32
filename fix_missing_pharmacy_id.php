<?php
/**
 * Script để fix users không có pharmacy_id
 * Chạy script này một lần để cập nhật dữ liệu cũ
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Fix Missing pharmacy_id</h2>";
    echo "<hr>";
    
    // 1. Kiểm tra xem có pharmacy nào chưa
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pharmacies");
    $pharmacyCount = $stmt->fetch()['count'];
    
    if ($pharmacyCount == 0) {
        echo "<h3>Tạo pharmacy mặc định...</h3>";
        
        // Tạo pharmacy mặc định
        $sql = "INSERT INTO pharmacies (pharmacy_name, address, phone, email, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $pdo->prepare($sql)->execute([
            'DUO PHARMA',
            '123 Đường ABC, Quận XYZ, TP.HCM',
            '0123456789',
            'contact@duopharma.com'
        ]);
        
        $defaultPharmacyId = $pdo->lastInsertId();
        echo "✅ Đã tạo pharmacy mặc định với ID: $defaultPharmacyId<br>";
    } else {
        // Lấy pharmacy_id đầu tiên
        $stmt = $pdo->query("SELECT pharmacy_id FROM pharmacies ORDER BY pharmacy_id ASC LIMIT 1");
        $defaultPharmacyId = $stmt->fetch()['pharmacy_id'];
        echo "✅ Sử dụng pharmacy_id: $defaultPharmacyId<br>";
    }
    
    echo "<hr>";
    
    // 2. Cập nhật users không có pharmacy_id
    echo "<h3>Cập nhật users...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
    $usersNeedUpdate = $stmt->fetch()['count'];
    
    if ($usersNeedUpdate > 0) {
        $sql = "UPDATE users SET pharmacy_id = ? WHERE pharmacy_id IS NULL OR pharmacy_id = 0";
        $pdo->prepare($sql)->execute([$defaultPharmacyId]);
        echo "✅ Đã cập nhật $usersNeedUpdate users với pharmacy_id = $defaultPharmacyId<br>";
    } else {
        echo "✅ Tất cả users đã có pharmacy_id<br>";
    }
    
    echo "<hr>";
    
    // 3. Cập nhật medicines không có pharmacy_id
    echo "<h3>Cập nhật medicines...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM medicines WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
    $medicinesNeedUpdate = $stmt->fetch()['count'];
    
    if ($medicinesNeedUpdate > 0) {
        $sql = "UPDATE medicines SET pharmacy_id = ? WHERE pharmacy_id IS NULL OR pharmacy_id = 0";
        $pdo->prepare($sql)->execute([$defaultPharmacyId]);
        echo "✅ Đã cập nhật $medicinesNeedUpdate medicines với pharmacy_id = $defaultPharmacyId<br>";
    } else {
        echo "✅ Tất cả medicines đã có pharmacy_id<br>";
    }
    
    echo "<hr>";
    
    // 4. Cập nhật batches không có pharmacy_id
    echo "<h3>Cập nhật batches...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM batches WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
    $batchesNeedUpdate = $stmt->fetch()['count'];
    
    if ($batchesNeedUpdate > 0) {
        $sql = "UPDATE batches SET pharmacy_id = ? WHERE pharmacy_id IS NULL OR pharmacy_id = 0";
        $pdo->prepare($sql)->execute([$defaultPharmacyId]);
        echo "✅ Đã cập nhật $batchesNeedUpdate batches với pharmacy_id = $defaultPharmacyId<br>";
    } else {
        echo "✅ Tất cả batches đã có pharmacy_id<br>";
    }
    
    echo "<hr>";
    
    // 5. Cập nhật suppliers không có pharmacy_id
    echo "<h3>Cập nhật suppliers...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM suppliers WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
    $suppliersNeedUpdate = $stmt->fetch()['count'];
    
    if ($suppliersNeedUpdate > 0) {
        $sql = "UPDATE suppliers SET pharmacy_id = ? WHERE pharmacy_id IS NULL OR pharmacy_id = 0";
        $pdo->prepare($sql)->execute([$defaultPharmacyId]);
        echo "✅ Đã cập nhật $suppliersNeedUpdate suppliers với pharmacy_id = $defaultPharmacyId<br>";
    } else {
        echo "✅ Tất cả suppliers đã có pharmacy_id<br>";
    }
    
    echo "<hr>";
    
    // 6. Cập nhật invoices không có pharmacy_id
    echo "<h3>Cập nhật invoices...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM invoices WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
    $invoicesNeedUpdate = $stmt->fetch()['count'];
    
    if ($invoicesNeedUpdate > 0) {
        $sql = "UPDATE invoices SET pharmacy_id = ? WHERE pharmacy_id IS NULL OR pharmacy_id = 0";
        $pdo->prepare($sql)->execute([$defaultPharmacyId]);
        echo "✅ Đã cập nhật $invoicesNeedUpdate invoices với pharmacy_id = $defaultPharmacyId<br>";
    } else {
        echo "✅ Tất cả invoices đã có pharmacy_id<br>";
    }
    
    echo "<hr>";
    
    // 7. Cập nhật categories không có pharmacy_id
    echo "<h3>Cập nhật categories...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
    $categoriesNeedUpdate = $stmt->fetch()['count'];
    
    if ($categoriesNeedUpdate > 0) {
        $sql = "UPDATE categories SET pharmacy_id = ? WHERE pharmacy_id IS NULL OR pharmacy_id = 0";
        $pdo->prepare($sql)->execute([$defaultPharmacyId]);
        echo "✅ Đã cập nhật $categoriesNeedUpdate categories với pharmacy_id = $defaultPharmacyId<br>";
    } else {
        echo "✅ Tất cả categories đã có pharmacy_id<br>";
    }
    
    echo "<hr>";
    
    // 8. Cập nhật units không có pharmacy_id
    echo "<h3>Cập nhật units...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM units WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
    $unitsNeedUpdate = $stmt->fetch()['count'];
    
    if ($unitsNeedUpdate > 0) {
        $sql = "UPDATE units SET pharmacy_id = ? WHERE pharmacy_id IS NULL OR pharmacy_id = 0";
        $pdo->prepare($sql)->execute([$defaultPharmacyId]);
        echo "✅ Đã cập nhật $unitsNeedUpdate units với pharmacy_id = $defaultPharmacyId<br>";
    } else {
        echo "✅ Tất cả units đã có pharmacy_id<br>";
    }
    
    echo "<hr>";
    
    // 9. Cập nhật notifications không có pharmacy_id
    echo "<h3>Cập nhật notifications...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM notifications WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
    $notificationsNeedUpdate = $stmt->fetch()['count'];
    
    if ($notificationsNeedUpdate > 0) {
        $sql = "UPDATE notifications SET pharmacy_id = ? WHERE pharmacy_id IS NULL OR pharmacy_id = 0";
        $pdo->prepare($sql)->execute([$defaultPharmacyId]);
        echo "✅ Đã cập nhật $notificationsNeedUpdate notifications với pharmacy_id = $defaultPharmacyId<br>";
    } else {
        echo "✅ Tất cả notifications đã có pharmacy_id<br>";
    }
    
    echo "<hr>";
    
    // 10. Cập nhật is_root_admin cho users
    echo "<h3>Cập nhật is_root_admin...</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'manager' AND (is_root_admin IS NULL OR is_root_admin = 0)");
    $managersNeedUpdate = $stmt->fetch()['count'];
    
    if ($managersNeedUpdate > 0) {
        $sql = "UPDATE users SET is_root_admin = 1 WHERE role = 'manager' AND (is_root_admin IS NULL OR is_root_admin = 0)";
        $pdo->exec($sql);
        echo "✅ Đã cập nhật $managersNeedUpdate managers với is_root_admin = 1<br>";
    } else {
        echo "✅ Tất cả managers đã có is_root_admin<br>";
    }
    
    echo "<hr>";
    
    // Thống kê cuối cùng
    echo "<h3>📊 Thống kê sau khi fix:</h3>";
    
    $tables = ['users', 'medicines', 'batches', 'suppliers', 'invoices', 'categories', 'units', 'notifications'];
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Bảng</th><th>Tổng records</th><th>Có pharmacy_id</th><th>%</th></tr>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
        $total = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as has_pharmacy FROM $table WHERE pharmacy_id IS NOT NULL AND pharmacy_id > 0");
        $hasPharmacy = $stmt->fetch()['has_pharmacy'];
        
        $percent = $total > 0 ? round($hasPharmacy / $total * 100, 1) : 0;
        
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td>$total</td>";
        echo "<td>$hasPharmacy</td>";
        echo "<td>" . ($percent == 100 ? "✅ " : "⚠️ ") . "$percent%</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>✅ Hoàn tất!</h2>";
    echo "<p><strong>Bây giờ hãy:</strong></p>";
    echo "<ol>";
    echo "<li>Đăng xuất khỏi hệ thống</li>";
    echo "<li>Đăng nhập lại</li>";
    echo "<li>Kiểm tra xem còn lỗi không</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #ffeeee; border: 1px solid red;'>";
    echo "<h3>❌ Lỗi:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}
h2, h3 {
    color: #333;
}
hr {
    margin: 20px 0;
    border: none;
    border-top: 2px solid #ddd;
}
table {
    width: 100%;
    margin: 20px 0;
}
th {
    background: #f0f0f0;
    text-align: left;
}
</style>
