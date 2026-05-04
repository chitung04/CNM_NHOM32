<?php
/**
 * QR Code Redirect Handler - Trang chuyển hướng thông minh
 * URL ngắn gọn: qr.php?c=BATCH_1735000101_2001
 * 
 * QR code chỉ cần chứa: qr.php?c=CODE
 * Trang này sẽ tự động phát hiện loại (medicine/batch/invoice) và chuyển hướng
 */

// Bắt đầu session
session_start();

// Lấy code từ URL
$code = $_GET['c'] ?? $_GET['qr'] ?? $_GET['code'] ?? '';

if (empty($code)) {
    http_response_code(400);
    die('❌ Thiếu mã QR code. Vui lòng quét lại.');
}

try {
    // Kết nối database
    $pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Xác định loại QR code và chuyển hướng
    
    // 1. Kiểm tra xem có phải Invoice không (INV_)
    if (strpos($code, 'INV_') === 0) {
        $sql = "SELECT invoice_id FROM invoices WHERE qr_code = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($invoice) {
            header("Location: invoice_info.php?qr=" . urlencode($code) . "&id=" . $invoice['invoice_id']);
            exit;
        }
    }
    
    // 2. Kiểm tra xem có phải Medicine không (MED_)
    if (strpos($code, 'MED_') === 0) {
        $sql = "SELECT medicine_id FROM medicines WHERE qr_code = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code]);
        $medicine = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($medicine) {
            header("Location: medicine_info.php?qr=" . urlencode($code));
            exit;
        }
    }
    
    // 3. Kiểm tra xem có phải Batch không (BATCH_)
    if (strpos($code, 'BATCH_') === 0) {
        $sql = "SELECT batch_id, medicine_id FROM batches WHERE qr_code = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code]);
        $batch = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($batch) {
            header("Location: medicine_info.php?qr=" . urlencode($code));
            exit;
        }
    }
    
    // 4. Nếu không có prefix, tìm trong tất cả bảng
    
    // Tìm trong batches
    $sql = "SELECT batch_id FROM batches WHERE qr_code = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        header("Location: medicine_info.php?qr=" . urlencode($code));
        exit;
    }
    
    // Tìm trong medicines
    $sql = "SELECT medicine_id FROM medicines WHERE qr_code = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        header("Location: medicine_info.php?qr=" . urlencode($code));
        exit;
    }
    
    // Tìm trong invoices
    $sql = "SELECT invoice_id FROM invoices WHERE qr_code = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($invoice) {
        header("Location: invoice_info.php?qr=" . urlencode($code) . "&id=" . $invoice['invoice_id']);
        exit;
    }
    
    // Không tìm thấy
    http_response_code(404);
    showError("Không tìm thấy thông tin cho mã QR: " . htmlspecialchars($code));
    
} catch (Exception $e) {
    error_log("QR redirect error: " . $e->getMessage());
    http_response_code(500);
    showError("Lỗi hệ thống: " . $e->getMessage());
}

function showError($message) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lỗi QR Code</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .error-card {
                background: white;
                border-radius: 20px;
                padding: 3rem;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 500px;
            }
            .error-icon {
                font-size: 5rem;
                color: #ef4444;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <i class="bi bi-exclamation-triangle error-icon"></i>
            <h2 class="mb-3">Không tìm thấy thông tin</h2>
            <p class="text-muted"><?php echo $message; ?></p>
            <a href="index.php" class="btn btn-primary mt-3">
                <i class="bi bi-house me-2"></i>Về trang chủ
            </a>
        </div>
    </body>
    </html>
    <?php
}
