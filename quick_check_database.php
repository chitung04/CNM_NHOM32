<?php
/**
 * Quick check database - Không cần đăng nhập
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔍 KIỂM TRA NHANH DATABASE</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // 1. Kiểm tra pharmacies
    echo "<h3>1️⃣ PHARMACIES</h3>";
    $sql = "SELECT * FROM pharmacies";
    $stmt = $conn->query($sql);
    $pharmacies = $stmt->fetchAll();
    echo "<p>Số pharmacy: " . count($pharmacies) . "</p>";
    if (count($pharmacies) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Tên</th><th>Code</th><th>Status</th></tr>";
        foreach ($pharmacies as $p) {
            echo "<tr><td>{$p['pharmacy_id']}</td><td>{$p['pharmacy_name']}</td><td>{$p['pharmacy_code']}</td><td>{$p['status']}</td></tr>";
        }
        echo "</table>";
    }
    
    // 2. Kiểm tra users
    echo "<h3>2️⃣ USERS</h3>";
    $sql = "SELECT user_id, username, full_name, pharmacy_id, role FROM users";
    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll();
    echo "<p>Số user: " . count($users) . "</p>";
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Pharmacy ID</th><th>Role</th></tr>";
        foreach ($users as $u) {
            echo "<tr><td>{$u['user_id']}</td><td>{$u['username']}</td><td>{$u['full_name']}</td><td>{$u['pharmacy_id']}</td><td>{$u['role']}</td></tr>";
        }
        echo "</table>";
    }
    
    // 3. Kiểm tra medicines
    echo "<h3>3️⃣ MEDICINES</h3>";
    $sql = "SELECT COUNT(*) as total FROM medicines";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch();
    echo "<p><strong>Tổng số thuốc:</strong> {$result['total']}</p>";
    
    if ($result['total'] > 0) {
        // Theo pharmacy
        $sql = "SELECT pharmacy_id, COUNT(*) as total FROM medicines GROUP BY pharmacy_id";
        $stmt = $conn->query($sql);
        $byPharmacy = $stmt->fetchAll();
        echo "<p><strong>Phân bố theo pharmacy:</strong></p>";
        echo "<ul>";
        foreach ($byPharmacy as $p) {
            echo "<li>Pharmacy ID {$p['pharmacy_id']}: {$p['total']} thuốc</li>";
        }
        echo "</ul>";
        
        // 10 thuốc đầu tiên
        $sql = "SELECT medicine_id, pharmacy_id, medicine_name, price FROM medicines LIMIT 10";
        $stmt = $conn->query($sql);
        $medicines = $stmt->fetchAll();
        echo "<p><strong>10 thuốc đầu tiên:</strong></p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Pharmacy ID</th><th>Tên thuốc</th><th>Giá</th></tr>";
        foreach ($medicines as $m) {
            echo "<tr><td>{$m['medicine_id']}</td><td>{$m['pharmacy_id']}</td><td>{$m['medicine_name']}</td><td>" . number_format($m['price']) . "đ</td></tr>";
        }
        echo "</table>";
    }
    
    // 4. Kiểm tra batches
    echo "<h3>4️⃣ BATCHES</h3>";
    $sql = "SELECT COUNT(*) as total FROM batches";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch();
    echo "<p><strong>Tổng số lô:</strong> {$result['total']}</p>";
    
    if ($result['total'] > 0) {
        // Theo status
        $sql = "SELECT status, COUNT(*) as total FROM batches GROUP BY status";
        $stmt = $conn->query($sql);
        $byStatus = $stmt->fetchAll();
        echo "<p><strong>Phân bố theo status:</strong></p>";
        echo "<ul>";
        foreach ($byStatus as $s) {
            echo "<li>{$s['status']}: {$s['total']} lô</li>";
        }
        echo "</ul>";
        
        // Lô có hàng
        $sql = "SELECT COUNT(*) as total FROM batches WHERE status = 'active' AND quantity > 0";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch();
        echo "<p><strong>Lô active có hàng:</strong> {$result['total']}</p>";
    }
    
    // 5. Kiểm tra categories
    echo "<h3>5️⃣ CATEGORIES</h3>";
    $sql = "SELECT * FROM categories";
    $stmt = $conn->query($sql);
    $categories = $stmt->fetchAll();
    echo "<p>Số danh mục: " . count($categories) . "</p>";
    if (count($categories) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Pharmacy ID</th><th>Tên danh mục</th></tr>";
        foreach ($categories as $c) {
            echo "<tr><td>{$c['category_id']}</td><td>{$c['pharmacy_id']}</td><td>{$c['category_name']}</td></tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>✅ HOÀN TẤT KIỂM TRA</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ LỖI:</strong> " . $e->getMessage() . "</p>";
}
