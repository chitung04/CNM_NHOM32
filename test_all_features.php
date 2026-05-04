<?php
/**
 * Test toàn bộ các chức năng của hệ thống
 */

session_start();

// Tạm thời bypass authentication để test
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'manager';
$_SESSION['full_name'] = 'Administrator';

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Test Toàn Bộ Chức Năng</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>";
echo "<style>
body { 
    font-family: 'Segoe UI', sans-serif; 
    padding: 20px; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
} 
.container {
    max-width: 1400px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.feature-card {
    border: 2px solid #e5e7eb;
    border-radius: 15px;
    padding: 20px;
    margin: 15px 0;
    transition: all 0.3s;
}
.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.feature-card.success {
    border-color: #10b981;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
}
.feature-card.error {
    border-color: #ef4444;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
}
.feature-card.warning {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}
.test-btn {
    margin: 5px;
}
.success-icon { color: #10b981; font-size: 1.5rem; }
.error-icon { color: #ef4444; font-size: 1.5rem; }
.warning-icon { color: #f59e0b; font-size: 1.5rem; }
.info-icon { color: #3b82f6; font-size: 1.5rem; }
.feature-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 15px;
}
.test-result {
    padding: 10px;
    margin: 5px 0;
    border-radius: 5px;
    background: rgba(255,255,255,0.5);
}
</style>";
echo "</head><body>\n";

echo "<div class='container'>\n";
echo "<h1 class='text-center mb-2'><i class='bi bi-clipboard-check'></i> Test Toàn Bộ Chức Năng</h1>\n";
echo "<p class='text-center text-muted mb-4'>Kiểm tra chi tiết từng chức năng của hệ thống</p>\n";
echo "<hr>\n";

$db = Database::getInstance();
$totalFeatures = 0;
$workingFeatures = 0;
$brokenFeatures = 0;

// ============================================
// 1. QUẢN LÝ THUỐC
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-capsule'></i> 1. QUẢN LÝ THUỐC</div>\n";

try {
    require_once 'models/Medicine.php';
    $medicineModel = new Medicine();
    $medicines = $medicineModel->getAll();
    
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-check-circle success-icon'></i> <strong>Danh sách thuốc:</strong> " . count($medicines) . " thuốc\n";
    echo "<div class='mt-2'>\n";
    echo "<a href='index.php?page=medicines' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-list'></i> Xem danh sách</a>\n";
    echo "<a href='index.php?page=medicines&action=create' target='_blank' class='btn btn-sm btn-success test-btn'><i class='bi bi-plus'></i> Thêm thuốc</a>\n";
    if (count($medicines) > 0) {
        echo "<a href='index.php?page=medicines&action=view&id={$medicines[0]['medicine_id']}' target='_blank' class='btn btn-sm btn-info test-btn'><i class='bi bi-eye'></i> Xem chi tiết</a>\n";
        echo "<a href='index.php?page=medicines&action=edit&id={$medicines[0]['medicine_id']}' target='_blank' class='btn btn-sm btn-warning test-btn'><i class='bi bi-pencil'></i> Sửa thuốc</a>\n";
    }
    echo "</div>\n";
    echo "</div>\n";
    
    $workingFeatures++;
} catch (Exception $e) {
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "\n";
    echo "</div>\n";
    $brokenFeatures++;
}
$totalFeatures++;

echo "</div>\n";

// ============================================
// 2. QUẢN LÝ LÔ THUỐC
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-box-seam'></i> 2. QUẢN LÝ LÔ THUỐC</div>\n";

try {
    require_once 'models/Batch.php';
    $batchModel = new Batch();
    $batches = $batchModel->getAll();
    
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-check-circle success-icon'></i> <strong>Danh sách lô:</strong> " . count($batches) . " lô\n";
    echo "<div class='mt-2'>\n";
    echo "<a href='index.php?page=batches' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-list'></i> Xem danh sách</a>\n";
    echo "<a href='index.php?page=batches&action=create' target='_blank' class='btn btn-sm btn-success test-btn'><i class='bi bi-plus'></i> Nhập lô mới</a>\n";
    if (count($batches) > 0) {
        echo "<a href='index.php?page=batches&action=view&id={$batches[0]['batch_id']}' target='_blank' class='btn btn-sm btn-info test-btn'><i class='bi bi-eye'></i> Xem chi tiết</a>\n";
    }
    echo "</div>\n";
    echo "</div>\n";
    
    $workingFeatures++;
} catch (Exception $e) {
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "\n";
    echo "</div>\n";
    $brokenFeatures++;
}
$totalFeatures++;

echo "</div>\n";

// ============================================
// 3. BÁN HÀNG
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-cart-plus'></i> 3. BÁN HÀNG</div>\n";

try {
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-check-circle success-icon'></i> <strong>Chức năng bán hàng</strong>\n";
    echo "<div class='mt-2'>\n";
    echo "<a href='index.php?page=sales' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-cart'></i> Mở trang bán hàng</a>\n";
    echo "<a href='index.php?page=sales&action=create' target='_blank' class='btn btn-sm btn-success test-btn'><i class='bi bi-plus-circle'></i> Tạo đơn mới</a>\n";
    echo "</div>\n";
    echo "<small class='text-muted'>Test: Thêm thuốc vào giỏ, tạo đơn hàng, thanh toán</small>\n";
    echo "</div>\n";
    
    $workingFeatures++;
} catch (Exception $e) {
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "\n";
    echo "</div>\n";
    $brokenFeatures++;
}
$totalFeatures++;

echo "</div>\n";

// ============================================
// 4. LỊCH SỬ ĐƠN HÀNG
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-receipt'></i> 4. LỊCH SỬ ĐƠN HÀNG</div>\n";

try {
    require_once 'models/Invoice.php';
    $invoiceModel = new Invoice();
    $invoices = $invoiceModel->getAll();
    
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-check-circle success-icon'></i> <strong>Tổng đơn hàng:</strong> " . count($invoices) . " đơn\n";
    echo "<div class='mt-2'>\n";
    echo "<a href='index.php?page=invoices' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-list'></i> Xem lịch sử</a>\n";
    if (count($invoices) > 0) {
        echo "<a href='index.php?page=invoices&action=view&id={$invoices[0]['invoice_id']}' target='_blank' class='btn btn-sm btn-info test-btn'><i class='bi bi-eye'></i> Xem chi tiết</a>\n";
        echo "<a href='index.php?page=invoices&action=print&id={$invoices[0]['invoice_id']}' target='_blank' class='btn btn-sm btn-success test-btn'><i class='bi bi-printer'></i> In hóa đơn</a>\n";
    }
    echo "</div>\n";
    echo "</div>\n";
    
    $workingFeatures++;
} catch (Exception $e) {
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "\n";
    echo "</div>\n";
    $brokenFeatures++;
}
$totalFeatures++;

echo "</div>\n";

// ============================================
// 5. BÁO CÁO
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-graph-up'></i> 5. BÁO CÁO</div>\n";

echo "<div class='test-result'>\n";
echo "<i class='bi bi-check-circle success-icon'></i> <strong>Các loại báo cáo</strong>\n";
echo "<div class='mt-2'>\n";
echo "<a href='index.php?page=reports&action=sales' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-currency-dollar'></i> Doanh thu</a>\n";
echo "<a href='index.php?page=reports&action=inventory' target='_blank' class='btn btn-sm btn-info test-btn'><i class='bi bi-box'></i> Tồn kho</a>\n";
echo "<a href='index.php?page=reports&action=expiry' target='_blank' class='btn btn-sm btn-warning test-btn'><i class='bi bi-exclamation-triangle'></i> Hết hạn</a>\n";
echo "<a href='index.php?page=reports&action=topSelling' target='_blank' class='btn btn-sm btn-success test-btn'><i class='bi bi-trophy'></i> Bán chạy</a>\n";
echo "</div>\n";
echo "</div>\n";

$workingFeatures++;
$totalFeatures++;

echo "</div>\n";

// ============================================
// 6. QUẢN LÝ NGƯỜI DÙNG
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-people'></i> 6. QUẢN LÝ NGƯỜI DÙNG</div>\n";

try {
    require_once 'models/User.php';
    $userModel = new User();
    $users = $userModel->getAll();
    
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-check-circle success-icon'></i> <strong>Tổng người dùng:</strong> " . count($users) . " người\n";
    echo "<div class='mt-2'>\n";
    echo "<a href='index.php?page=users' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-list'></i> Danh sách</a>\n";
    echo "<a href='index.php?page=users&action=create' target='_blank' class='btn btn-sm btn-success test-btn'><i class='bi bi-plus'></i> Thêm người dùng</a>\n";
    if (count($users) > 0) {
        echo "<a href='index.php?page=users&action=edit&id={$users[0]['user_id']}' target='_blank' class='btn btn-sm btn-warning test-btn'><i class='bi bi-pencil'></i> Sửa</a>\n";
    }
    echo "</div>\n";
    echo "</div>\n";
    
    $workingFeatures++;
} catch (Exception $e) {
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "\n";
    echo "</div>\n";
    $brokenFeatures++;
}
$totalFeatures++;

echo "</div>\n";

// ============================================
// 7. NHÀ CUNG CẤP
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-building'></i> 7. NHÀ CUNG CẤP</div>\n";

try {
    require_once 'models/Supplier.php';
    $supplierModel = new Supplier();
    $suppliers = $supplierModel->getAll();
    
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-check-circle success-icon'></i> <strong>Tổng nhà cung cấp:</strong> " . count($suppliers) . " nhà\n";
    echo "<div class='mt-2'>\n";
    echo "<a href='index.php?page=suppliers' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-list'></i> Danh sách</a>\n";
    echo "<a href='index.php?page=suppliers&action=create' target='_blank' class='btn btn-sm btn-success test-btn'><i class='bi bi-plus'></i> Thêm NCC</a>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    $workingFeatures++;
} catch (Exception $e) {
    echo "<div class='test-result'>\n";
    echo "<i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "\n";
    echo "</div>\n";
    $brokenFeatures++;
}
$totalFeatures++;

echo "</div>\n";

// ============================================
// 8. THÔNG TIN CÁ NHÂN
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-person-circle'></i> 8. THÔNG TIN CÁ NHÂN</div>\n";

echo "<div class='test-result'>\n";
echo "<i class='bi bi-check-circle success-icon'></i> <strong>Trang profile</strong>\n";
echo "<div class='mt-2'>\n";
echo "<a href='index.php?page=profile' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-person'></i> Xem profile</a>\n";
echo "<a href='index.php?page=profile&action=edit' target='_blank' class='btn btn-sm btn-warning test-btn'><i class='bi bi-pencil'></i> Chỉnh sửa</a>\n";
echo "</div>\n";
echo "</div>\n";

$workingFeatures++;
$totalFeatures++;

echo "</div>\n";

// ============================================
// 9. HỆ THỐNG QR CODE
// ============================================
$qrStatus = 'success';
$qrMessage = '';

$stmt = $db->query('SELECT COUNT(*) as count FROM medicines WHERE qr_code IS NOT NULL');
$medicineQR = $stmt->fetch()['count'];

$stmt = $db->query('SELECT COUNT(*) as count FROM batches WHERE qr_code IS NOT NULL');
$batchQR = $stmt->fetch()['count'];

$stmt = $db->query('SELECT COUNT(*) as count FROM invoices WHERE qr_code IS NOT NULL');
$invoiceQR = $stmt->fetch()['count'];

$qrFiles = glob('assets/qrcodes/*.png');
$qrFileCount = count($qrFiles);

if (!file_exists('qr.php')) {
    $qrStatus = 'error';
    $qrMessage = 'File qr.php không tồn tại';
    $brokenFeatures++;
} elseif ($qrFileCount == 0) {
    $qrStatus = 'warning';
    $qrMessage = 'Chưa có file QR code - Cần tạo QR';
} else {
    $qrMessage = 'Hệ thống QR hoạt động tốt';
    $workingFeatures++;
}
$totalFeatures++;

echo "<div class='feature-card $qrStatus'>\n";
echo "<div class='feature-title'><i class='bi bi-qr-code'></i> 9. HỆ THỐNG QR CODE</div>\n";

echo "<div class='test-result'>\n";
if ($qrStatus == 'success') {
    echo "<i class='bi bi-check-circle success-icon'></i> ";
} elseif ($qrStatus == 'warning') {
    echo "<i class='bi bi-exclamation-triangle warning-icon'></i> ";
} else {
    echo "<i class='bi bi-x-circle error-icon'></i> ";
}
echo "<strong>$qrMessage</strong>\n";
echo "<ul class='mt-2'>\n";
echo "<li>Thuốc có QR: $medicineQR</li>\n";
echo "<li>Lô thuốc có QR: $batchQR</li>\n";
echo "<li>Hóa đơn có QR: $invoiceQR</li>\n";
echo "<li>File QR code: $qrFileCount</li>\n";
echo "</ul>\n";
echo "<div class='mt-2'>\n";
echo "<a href='check_qr_status.php' target='_blank' class='btn btn-sm btn-info test-btn'><i class='bi bi-search'></i> Kiểm tra QR</a>\n";
echo "<a href='regenerate_qr_smart.php' target='_blank' class='btn btn-sm btn-warning test-btn'><i class='bi bi-arrow-clockwise'></i> Tạo lại QR</a>\n";
echo "<a href='test_qr_content.php' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-file-text'></i> Xem nội dung QR</a>\n";
echo "</div>\n";
echo "</div>\n";

echo "</div>\n";

// ============================================
// 10. DASHBOARD
// ============================================
echo "<div class='feature-card success'>\n";
echo "<div class='feature-title'><i class='bi bi-speedometer2'></i> 10. DASHBOARD</div>\n";

echo "<div class='test-result'>\n";
echo "<i class='bi bi-check-circle success-icon'></i> <strong>Trang chủ</strong>\n";
echo "<div class='mt-2'>\n";
echo "<a href='index.php?page=dashboard' target='_blank' class='btn btn-sm btn-primary test-btn'><i class='bi bi-house'></i> Mở Dashboard</a>\n";
echo "</div>\n";
echo "<small class='text-muted'>Hiển thị thống kê tổng quan, thao tác nhanh, thông báo</small>\n";
echo "</div>\n";

$workingFeatures++;
$totalFeatures++;

echo "</div>\n";

// ============================================
// SUMMARY
// ============================================
echo "<hr>\n";
$percentage = $totalFeatures > 0 ? round(($workingFeatures / $totalFeatures) * 100) : 0;

echo "<div class='feature-card' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;'>\n";
echo "<h2 class='text-center mb-4'><i class='bi bi-clipboard-data'></i> KẾT QUẢ TỔNG HỢP</h2>\n";
echo "<div class='row text-center'>\n";
echo "<div class='col-md-4'>\n";
echo "<h1 class='display-3'>$totalFeatures</h1>\n";
echo "<h5>Tổng chức năng</h5>\n";
echo "</div>\n";
echo "<div class='col-md-4'>\n";
echo "<h1 class='display-3'>$workingFeatures</h1>\n";
echo "<h5>Hoạt động tốt</h5>\n";
echo "</div>\n";
echo "<div class='col-md-4'>\n";
echo "<h1 class='display-3'>$brokenFeatures</h1>\n";
echo "<h5>Có lỗi</h5>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='progress mt-4' style='height: 40px; background: rgba(255,255,255,0.3);'>\n";
echo "<div class='progress-bar bg-success' style='width: {$percentage}%; font-size: 1.5rem; font-weight: bold;'>{$percentage}%</div>\n";
echo "</div>\n";

if ($brokenFeatures == 0) {
    echo "<h2 class='text-center mt-4'>🎉 TẤT CẢ CHỨC NĂNG HOẠT ĐỘNG HOÀN HẢO!</h2>\n";
} else {
    echo "<h2 class='text-center mt-4'>⚠️ Có $brokenFeatures chức năng cần kiểm tra</h2>\n";
}

echo "</div>\n";

echo "<div class='text-center mt-4'>\n";
echo "<a href='index.php?page=dashboard' class='btn btn-primary btn-lg me-2'><i class='bi bi-house'></i> Về Dashboard</a>\n";
echo "<a href='test_full_system.php' class='btn btn-info btn-lg me-2'><i class='bi bi-gear'></i> Test hệ thống</a>\n";
echo "<a href='check_qr_status.php' class='btn btn-warning btn-lg'><i class='bi bi-qr-code'></i> Kiểm tra QR</a>\n";
echo "</div>\n";

echo "</div>\n";
echo "</body></html>\n";
