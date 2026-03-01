<?php
/**
 * File test để debug chức năng thêm vào giỏ hàng
 */
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/auth.php';
require_once 'models/Medicine.php';
require_once 'models/Batch.php';
require_once 'models/Database.php';

echo "<h2>Test Add to Cart Functionality</h2>";

// Kiểm tra đăng nhập
echo "<h3>1. Kiểm tra đăng nhập</h3>";
if (!isLoggedIn()) {
    echo "<p style='color: red;'>❌ Chưa đăng nhập. Vui lòng đăng nhập trước.</p>";
    echo "<a href='login.php'>Đăng nhập</a>";
    exit;
}
echo "<p style='color: green;'>✓ Đã đăng nhập - User ID: " . $_SESSION['user_id'] . "</p>";

// Kiểm tra database connection
echo "<h3>2. Kiểm tra kết nối database</h3>";
try {
    $db = Database::getInstance();
    echo "<p style='color: green;'>✓ Kết nối database thành công</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi kết nối database: " . $e->getMessage() . "</p>";
    exit;
}

// Kiểm tra có thuốc không
echo "<h3>3. Kiểm tra danh sách thuốc</h3>";
try {
    $medicineModel = new Medicine();
    $medicines = $medicineModel->getAll();
    echo "<p style='color: green;'>✓ Tìm thấy " . count($medicines) . " thuốc</p>";
    
    if (count($medicines) > 0) {
        $testMedicine = $medicines[0];
        echo "<p>Thuốc test: <strong>" . htmlspecialchars($testMedicine['medicine_name']) . "</strong> (ID: " . $testMedicine['medicine_id'] . ")</p>";
        
        // Kiểm tra tồn kho
        $inventory = $medicineModel->getTotalInventory($testMedicine['medicine_id']);
        echo "<p>Tồn kho: <strong>" . $inventory . "</strong></p>";
        
        if ($inventory <= 0) {
            echo "<p style='color: orange;'>⚠ Thuốc này không còn hàng trong kho</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Không có thuốc nào trong hệ thống</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
    exit;
}

// Kiểm tra batch
echo "<h3>4. Kiểm tra lô thuốc</h3>";
try {
    $batchModel = new Batch();
    $batches = $batchModel->getByMedicine($testMedicine['medicine_id']);
    echo "<p style='color: green;'>✓ Tìm thấy " . count($batches) . " lô thuốc</p>";
    
    if (count($batches) > 0) {
        foreach ($batches as $batch) {
            $status = $batch['status'];
            $color = $status === 'active' ? 'green' : 'orange';
            echo "<p>- Lô #" . $batch['batch_id'] . ": Số lượng = " . $batch['quantity'] . ", Trạng thái = <span style='color: $color;'>" . $status . "</span>, HSD = " . $batch['expiry_date'] . "</p>";
        }
        
        // Lọc batch hợp lệ
        $validBatches = array_filter($batches, function($batch) {
            return $batch['status'] === 'active' && 
                   $batch['quantity'] > 0 && 
                   strtotime($batch['expiry_date']) > time();
        });
        
        if (empty($validBatches)) {
            echo "<p style='color: red;'>❌ Không có lô thuốc khả dụng (active, còn hàng, chưa hết hạn)</p>";
        } else {
            echo "<p style='color: green;'>✓ Có " . count($validBatches) . " lô thuốc khả dụng</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Không có lô thuốc nào</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
    exit;
}

// Kiểm tra session invoice
echo "<h3>5. Kiểm tra session invoice</h3>";
$currentInvoiceId = $_SESSION['current_invoice_id'] ?? null;
if ($currentInvoiceId) {
    echo "<p style='color: green;'>✓ Có invoice đang mở: ID = " . $currentInvoiceId . "</p>";
} else {
    echo "<p style='color: orange;'>⚠ Chưa có invoice nào đang mở (sẽ tạo mới khi thêm sản phẩm)</p>";
}

// Kiểm tra thư mục QR code
echo "<h3>6. Kiểm tra thư mục QR code</h3>";
if (!file_exists(QRCODE_PATH)) {
    echo "<p style='color: orange;'>⚠ Thư mục QR code chưa tồn tại: " . QRCODE_PATH . "</p>";
    if (mkdir(QRCODE_PATH, 0777, true)) {
        echo "<p style='color: green;'>✓ Đã tạo thư mục QR code</p>";
    } else {
        echo "<p style='color: red;'>❌ Không thể tạo thư mục QR code</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Thư mục QR code tồn tại: " . QRCODE_PATH . "</p>";
    if (is_writable(QRCODE_PATH)) {
        echo "<p style='color: green;'>✓ Thư mục có quyền ghi</p>";
    } else {
        echo "<p style='color: red;'>❌ Thư mục không có quyền ghi</p>";
    }
}

// Test thêm vào giỏ hàng
echo "<h3>7. Test thêm vào giỏ hàng</h3>";
if (isset($_POST['test_add'])) {
    echo "<p>Đang thử thêm thuốc vào giỏ hàng...</p>";
    
    $_POST['medicine_id'] = $testMedicine['medicine_id'];
    $_POST['quantity'] = 1;
    
    // Include file ajax
    ob_start();
    include 'ajax/add_to_cart.php';
    $response = ob_get_clean();
    
    echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";
    
    $result = json_decode($response, true);
    if ($result && $result['success']) {
        echo "<p style='color: green;'>✓ Thêm vào giỏ hàng thành công!</p>";
    } else {
        echo "<p style='color: red;'>❌ Thêm vào giỏ hàng thất bại: " . ($result['message'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<form method='POST'>";
    echo "<button type='submit' name='test_add' class='btn btn-primary'>Test thêm vào giỏ hàng</button>";
    echo "</form>";
}

echo "<hr>";
echo "<p><a href='index.php?page=sales'>← Quay lại trang bán hàng</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2 { color: #333; }
h3 { color: #666; margin-top: 20px; }
.btn { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
.btn:hover { background: #0056b3; }
</style>
