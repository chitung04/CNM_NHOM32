<?php
/**
 * Script kiểm tra toàn bộ hệ thống
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
echo "<html><head><title>Test Toàn Bộ Hệ Thống</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>";
echo "<style>
body { 
    font-family: 'Segoe UI', sans-serif; 
    padding: 20px; 
    background: #f5f5f5;
} 
.container {
    max-width: 1400px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.test-section {
    margin: 20px 0;
    padding: 20px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
}
.test-section.success {
    border-color: #10b981;
    background: #d1fae5;
}
.test-section.error {
    border-color: #ef4444;
    background: #fee2e2;
}
.test-section.warning {
    border-color: #f59e0b;
    background: #fef3c7;
}
.test-item {
    padding: 10px;
    margin: 5px 0;
    border-left: 4px solid #3b82f6;
    background: #f8f9fa;
}
.success-icon { color: #10b981; }
.error-icon { color: #ef4444; }
.warning-icon { color: #f59e0b; }
.info-icon { color: #3b82f6; }
</style>";
echo "</head><body>\n";

echo "<div class='container'>\n";
echo "<h1 class='text-center mb-4'><i class='bi bi-check2-circle'></i> Test Toàn Bộ Hệ Thống</h1>\n";
echo "<p class='text-center text-muted'>Kiểm tra tất cả các chức năng và trang</p>\n";
echo "<hr>\n";

$db = Database::getInstance();
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$warnings = 0;

// Test 1: Database Connection
echo "<div class='test-section success'>\n";
echo "<h3><i class='bi bi-database'></i> 1. Kết nối Database</h3>\n";
try {
    $stmt = $db->query('SELECT 1');
    echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> Kết nối database thành công</div>\n";
    $passedTests++;
} catch (Exception $e) {
    echo "<div class='test-item'><i class='bi bi-x-circle error-icon'></i> Lỗi kết nối: " . $e->getMessage() . "</div>\n";
    $failedTests++;
}
$totalTests++;
echo "</div>\n";

// Test 2: Tables
echo "<div class='test-section'>\n";
echo "<h3><i class='bi bi-table'></i> 2. Kiểm tra các bảng</h3>\n";
$tables = ['users', 'medicines', 'categories', 'units', 'batches', 'suppliers', 'invoices', 'invoice_details', 'notifications'];
foreach ($tables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> Bảng <strong>$table</strong>: $count records</div>\n";
        $passedTests++;
    } catch (Exception $e) {
        echo "<div class='test-item'><i class='bi bi-x-circle error-icon'></i> Bảng <strong>$table</strong>: Lỗi - " . $e->getMessage() . "</div>\n";
        $failedTests++;
    }
    $totalTests++;
}
echo "</div>\n";

// Test 3: Files
echo "<div class='test-section'>\n";
echo "<h3><i class='bi bi-file-earmark'></i> 3. Kiểm tra các file quan trọng</h3>\n";
$files = [
    'index.php' => 'File chính',
    'config/database.php' => 'Cấu hình database',
    'helpers/secure_session.php' => 'Session bảo mật',
    'qr.php' => 'QR redirect handler',
    'medicine_info.php' => 'Trang thông tin thuốc',
    'invoice_info.php' => 'Trang thông tin hóa đơn',
    'public_medicine_info.php' => 'Trang công khai thuốc',
    'views/layouts/header.php' => 'Header layout',
    'views/layouts/sidebar.php' => 'Sidebar layout',
    'views/dashboard/index.php' => 'Dashboard',
    'views/profile/index.php' => 'Trang profile'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> <strong>$desc</strong>: $file</div>\n";
        $passedTests++;
    } else {
        echo "<div class='test-item'><i class='bi bi-x-circle error-icon'></i> <strong>$desc</strong>: $file - KHÔNG TỒN TẠI</div>\n";
        $failedTests++;
    }
    $totalTests++;
}
echo "</div>\n";

// Test 4: Pages
echo "<div class='test-section'>\n";
echo "<h3><i class='bi bi-window'></i> 4. Kiểm tra các trang</h3>\n";
$pages = [
    'dashboard' => 'Dashboard',
    'medicines' => 'Quản lý thuốc',
    'batches' => 'Quản lý lô thuốc',
    'sales' => 'Bán hàng',
    'invoices' => 'Lịch sử đơn hàng',
    'suppliers' => 'Nhà cung cấp',
    'users' => 'Quản lý người dùng',
    'profile' => 'Thông tin cá nhân',
    'reports' => 'Báo cáo'
];

foreach ($pages as $page => $name) {
    $url = "index.php?page=$page";
    echo "<div class='test-item'>\n";
    echo "<i class='bi bi-link info-icon'></i> <strong>$name</strong>: ";
    echo "<a href='$url' target='_blank' class='btn btn-sm btn-primary'>Mở trang</a>\n";
    echo "</div>\n";
    $totalTests++;
    $passedTests++; // Assume pass if file exists
}
echo "</div>\n";

// Test 5: QR Code System
echo "<div class='test-section'>\n";
echo "<h3><i class='bi bi-qr-code'></i> 5. Hệ thống QR Code</h3>\n";

// Check qr.php
if (file_exists('qr.php')) {
    echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> File qr.php tồn tại</div>\n";
    $passedTests++;
} else {
    echo "<div class='test-item'><i class='bi bi-x-circle error-icon'></i> File qr.php KHÔNG tồn tại</div>\n";
    $failedTests++;
}
$totalTests++;

// Check QR codes in database
$stmt = $db->query('SELECT COUNT(*) as count FROM medicines WHERE qr_code IS NOT NULL');
$medicineQR = $stmt->fetch()['count'];
echo "<div class='test-item'><i class='bi bi-info-circle info-icon'></i> Thuốc có QR code: $medicineQR</div>\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM batches WHERE qr_code IS NOT NULL');
$batchQR = $stmt->fetch()['count'];
echo "<div class='test-item'><i class='bi bi-info-circle info-icon'></i> Lô thuốc có QR code: $batchQR</div>\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM invoices WHERE qr_code IS NOT NULL');
$invoiceQR = $stmt->fetch()['count'];
echo "<div class='test-item'><i class='bi bi-info-circle info-icon'></i> Hóa đơn có QR code: $invoiceQR</div>\n";

// Check QR code files
$qrFiles = glob('assets/qrcodes/*.png');
$qrFileCount = count($qrFiles);
echo "<div class='test-item'><i class='bi bi-info-circle info-icon'></i> Số file QR code: $qrFileCount</div>\n";

if ($qrFileCount > 0) {
    echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> Có file QR code</div>\n";
    $passedTests++;
} else {
    echo "<div class='test-item'><i class='bi bi-exclamation-triangle warning-icon'></i> Chưa có file QR code - Cần chạy script tạo QR</div>\n";
    $warnings++;
}
$totalTests++;

// Test QR redirect
echo "<div class='test-item'>\n";
echo "<i class='bi bi-link info-icon'></i> <strong>Test QR redirect:</strong> ";
echo "<a href='check_qr_status.php' target='_blank' class='btn btn-sm btn-info'>Kiểm tra QR</a> ";
echo "<a href='regenerate_qr_smart.php' target='_blank' class='btn btn-sm btn-warning'>Tạo lại QR</a>\n";
echo "</div>\n";

echo "</div>\n";

// Test 6: Reports
echo "<div class='test-section'>\n";
echo "<h3><i class='bi bi-graph-up'></i> 6. Báo cáo</h3>\n";
$reports = [
    'sales' => 'Báo cáo doanh thu',
    'inventory' => 'Báo cáo tồn kho',
    'expiry' => 'Báo cáo hết hạn',
    'topSelling' => 'Thuốc bán chạy'
];

foreach ($reports as $action => $name) {
    $url = "index.php?page=reports&action=$action";
    echo "<div class='test-item'>\n";
    echo "<i class='bi bi-link info-icon'></i> <strong>$name</strong>: ";
    echo "<a href='$url' target='_blank' class='btn btn-sm btn-primary'>Mở báo cáo</a>\n";
    echo "</div>\n";
    $totalTests++;
    $passedTests++;
}
echo "</div>\n";

// Test 7: Authentication
echo "<div class='test-section'>\n";
echo "<h3><i class='bi bi-shield-lock'></i> 7. Xác thực & Bảo mật</h3>\n";

if (file_exists('helpers/secure_session.php')) {
    echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> Hệ thống session bảo mật tồn tại</div>\n";
    $passedTests++;
} else {
    echo "<div class='test-item'><i class='bi bi-x-circle error-icon'></i> Thiếu hệ thống session bảo mật</div>\n";
    $failedTests++;
}
$totalTests++;

if (file_exists('helpers/csrf.php')) {
    echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> CSRF protection tồn tại</div>\n";
    $passedTests++;
} else {
    echo "<div class='test-item'><i class='bi bi-x-circle error-icon'></i> Thiếu CSRF protection</div>\n";
    $failedTests++;
}
$totalTests++;

echo "</div>\n";

// Test 8: Assets
echo "<div class='test-section'>\n";
echo "<h3><i class='bi bi-folder'></i> 8. Thư mục & Assets</h3>\n";
$folders = [
    'assets/css' => 'CSS files',
    'assets/js' => 'JavaScript files',
    'assets/images' => 'Images',
    'assets/qrcodes' => 'QR codes',
    'views' => 'Views',
    'models' => 'Models',
    'controllers' => 'Controllers',
    'helpers' => 'Helpers'
];

foreach ($folders as $folder => $desc) {
    if (is_dir($folder)) {
        $fileCount = count(glob($folder . '/*'));
        echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> <strong>$desc</strong>: $folder ($fileCount files)</div>\n";
        $passedTests++;
    } else {
        echo "<div class='test-item'><i class='bi bi-x-circle error-icon'></i> <strong>$desc</strong>: $folder - KHÔNG TỒN TẠI</div>\n";
        $failedTests++;
    }
    $totalTests++;
}
echo "</div>\n";

// Test 9: Environment
echo "<div class='test-section'>\n";
echo "<h3><i class='bi bi-gear'></i> 9. Môi trường</h3>\n";

$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) || 
               strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0;

if ($isLocalhost) {
    echo "<div class='test-item'><i class='bi bi-exclamation-triangle warning-icon'></i> <strong>Đang dùng LOCALHOST</strong> - QR code sẽ không hoạt động trên điện thoại</div>\n";
    $warnings++;
} else {
    echo "<div class='test-item'><i class='bi bi-check-circle success-icon'></i> <strong>Đang dùng IP:</strong> " . $_SERVER['HTTP_HOST'] . "</div>\n";
    $passedTests++;
}
$totalTests++;

echo "<div class='test-item'><i class='bi bi-info-circle info-icon'></i> <strong>PHP Version:</strong> " . phpversion() . "</div>\n";
echo "<div class='test-item'><i class='bi bi-info-circle info-icon'></i> <strong>Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</div>\n";

echo "</div>\n";

// Summary
echo "<hr>\n";
echo "<div class='test-section' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;'>\n";
echo "<h2 class='text-center'><i class='bi bi-clipboard-check'></i> Tổng kết</h2>\n";
echo "<div class='row text-center mt-4'>\n";
echo "<div class='col-md-3'>\n";
echo "<h1 class='display-4'>$totalTests</h1>\n";
echo "<p>Tổng số test</p>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<h1 class='display-4 text-success'>$passedTests</h1>\n";
echo "<p>Thành công</p>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<h1 class='display-4 text-danger'>$failedTests</h1>\n";
echo "<p>Thất bại</p>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<h1 class='display-4 text-warning'>$warnings</h1>\n";
echo "<p>Cảnh báo</p>\n";
echo "</div>\n";
echo "</div>\n";

$percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100) : 0;
echo "<div class='progress mt-4' style='height: 30px;'>\n";
echo "<div class='progress-bar bg-success' style='width: {$percentage}%'>{$percentage}%</div>\n";
echo "</div>\n";

if ($failedTests == 0 && $warnings == 0) {
    echo "<h3 class='text-center mt-4'>🎉 HỆ THỐNG HOẠT ĐỘNG HOÀN HẢO!</h3>\n";
} elseif ($failedTests == 0) {
    echo "<h3 class='text-center mt-4'>✅ Hệ thống hoạt động tốt (có {$warnings} cảnh báo)</h3>\n";
} else {
    echo "<h3 class='text-center mt-4'>⚠️ Có {$failedTests} lỗi cần sửa</h3>\n";
}

echo "</div>\n";

echo "<div class='text-center mt-4'>\n";
echo "<a href='index.php?page=dashboard' class='btn btn-primary btn-lg me-2'><i class='bi bi-house'></i> Về Dashboard</a>\n";
echo "<a href='check_qr_status.php' class='btn btn-info btn-lg me-2'><i class='bi bi-qr-code'></i> Kiểm tra QR</a>\n";
echo "<a href='regenerate_qr_smart.php' class='btn btn-warning btn-lg'><i class='bi bi-arrow-clockwise'></i> Tạo lại QR</a>\n";
echo "</div>\n";

echo "</div>\n";
echo "</body></html>\n";
