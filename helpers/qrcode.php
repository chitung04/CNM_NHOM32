<?php
/**
 * QR Code Helper Functions
 * Sử dụng API QR Server để tạo QR code
 */

/**
 * Tạo QR code cho thuốc hoặc lô thuốc
 */
function generateQRCode($data, $filename) {
    // Đảm bảo thư mục tồn tại
    $qrcodePath = 'assets/qrcodes';
    if (!file_exists($qrcodePath)) {
        mkdir($qrcodePath, 0777, true);
    }
    
    $filepath = $qrcodePath . '/' . $filename . '.png';
    
    try {
        // Log để debug
        error_log("Generating QR Code: $data -> $filepath");
        
        // Sử dụng API QR Server để tạo QR code với kích thước lớn hơn
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&ecc=M&data=' . urlencode($data);
        
        // Tạo context với timeout và user agent
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
        
        // Download QR code image
        $qrImage = file_get_contents($qrUrl, false, $context);
        
        if ($qrImage !== false && strlen($qrImage) > 100) {
            file_put_contents($filepath, $qrImage);
            error_log("QR Code generated successfully: $filepath");
            return $filename . '.png';
        } else {
            error_log("QR Server failed, trying Google Charts API");
            // Fallback: sử dụng Google Charts API
            return generateGoogleQRCode($data, $filename);
        }
    } catch (Exception $e) {
        error_log("QR Code generation failed: " . $e->getMessage());
        // Fallback: tạo QR code bằng Google Charts
        return generateGoogleQRCode($data, $filename);
    }
}

/**
 * Tạo QR code bằng Google Charts API
 */
function generateGoogleQRCode($data, $filename) {
    $qrcodePath = 'assets/qrcodes';
    $filepath = $qrcodePath . '/' . $filename . '.png';
    
    try {
        // Sử dụng Google Charts API
        $qrUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($data) . '&choe=UTF-8';
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
        
        $qrImage = file_get_contents($qrUrl, false, $context);
        
        if ($qrImage !== false && strlen($qrImage) > 100) {
            file_put_contents($filepath, $qrImage);
            error_log("Google QR Code generated successfully: $filepath");
            return $filename . '.png';
        }
        
        // Nếu không thể tạo QR code, tạo placeholder image
        error_log("Google Charts failed, creating placeholder");
        return createPlaceholderQR($filename, $data);
        
    } catch (Exception $e) {
        error_log("Google QR Code generation failed: " . $e->getMessage());
        return createPlaceholderQR($filename, $data);
    }
}

/**
 * Tạo placeholder QR code với thông tin URL
 */
function createPlaceholderQR($filename, $data = '') {
    $qrcodePath = 'assets/qrcodes';
    $filepath = $qrcodePath . '/' . $filename . '.png';
    
    // Tạo image placeholder 300x300
    $image = imagecreate(300, 300);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 128, 128, 128);
    $blue = imagecolorallocate($image, 59, 130, 246);
    
    // Fill background
    imagefill($image, 0, 0, $white);
    
    // Draw border
    imagerectangle($image, 0, 0, 299, 299, $black);
    imagerectangle($image, 5, 5, 294, 294, $blue);
    
    // Draw QR pattern (simple squares)
    for ($i = 30; $i < 270; $i += 30) {
        for ($j = 30; $j < 270; $j += 30) {
            if (($i + $j) % 60 == 0) {
                imagefilledrectangle($image, $i, $j, $i + 20, $j + 20, $black);
            }
        }
    }
    
    // Add text
    imagestring($image, 3, 110, 130, 'QR CODE', $blue);
    imagestring($image, 2, 100, 150, 'PLACEHOLDER', $gray);
    
    // Add filename
    if (strlen($filename) > 20) {
        $displayName = substr($filename, 0, 17) . '...';
    } else {
        $displayName = $filename;
    }
    imagestring($image, 1, 150 - (strlen($displayName) * 3), 170, $displayName, $gray);
    
    // Add URL info if provided
    if (!empty($data) && strpos($data, 'http') === 0) {
        imagestring($image, 1, 50, 190, 'URL: medicine_info.php', $gray);
    }
    
    // Save image
    imagepng($image, $filepath);
    imagedestroy($image);
    
    error_log("Placeholder QR created: $filepath");
    return $filename . '.png';
}

/**
 * Tạo mã QR code unique
 */
function generateUniqueQRCode($prefix = 'MED') {
    return $prefix . '_' . time() . '_' . rand(1000, 9999);
}

/**
 * Tạo URL Google Search cho thuốc
 */
function generateGoogleSearchURL($medicineName, $manufacturer = '', $activeIngredient = '') {
    // Tạo query tìm kiếm Google với thông tin thuốc
    $searchTerms = [];
    
    // Thêm tên thuốc
    if (!empty($medicineName)) {
        $searchTerms[] = $medicineName;
    }
    
    // Thêm nhà sản xuất nếu có
    if (!empty($manufacturer)) {
        $searchTerms[] = $manufacturer;
    }
    
    // Thêm hoạt chất nếu có
    if (!empty($activeIngredient)) {
        $searchTerms[] = $activeIngredient;
    }
    
    // Thêm từ khóa để tìm thông tin chính thống
    $searchTerms[] = 'thuốc';
    $searchTerms[] = 'thông tin';
    $searchTerms[] = 'dược phẩm';
    
    $query = implode(' ', $searchTerms);
    
    // Tạo URL Google Search
    return 'https://www.google.com/search?q=' . urlencode($query);
}

/**
 * Tạo URL cho QR code dẫn đến trang thông tin thuốc của nhà thuốc
 */
function generateMedicineQRData($batch_id, $qr_code, $medicine_id) {
    // Tạo URL dẫn đến trang thông tin thuốc của nhà thuốc
    return BASE_URL . '/medicine_info.php?qr=' . urlencode($qr_code);
}

/**
 * Lấy đường dẫn QR code
 */
function getQRCodePath($filename) {
    if (empty($filename)) {
        return null;
    }
    return 'assets/qrcodes/' . $filename;
}

/**
 * Xóa file QR code
 */
function deleteQRCode($filename) {
    if (empty($filename)) {
        return false;
    }
    
    $filepath = 'assets/qrcodes/' . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return false;
}

/**
 * Tạo lại QR code cho tất cả lô thuốc
 */
function regenerateAllQRCodes() {
    require_once 'models/Database.php';
    
    $db = Database::getInstance();
    $sql = "SELECT batch_id, medicine_id, qr_code FROM batches WHERE qr_code IS NOT NULL";
    $stmt = $db->query($sql);
    $batches = $stmt->fetchAll();
    
    $success = 0;
    $failed = 0;
    
    foreach ($batches as $batch) {
        try {
            // Tạo URL dẫn đến trang thông tin thuốc
            $qrData = generateMedicineQRData($batch['batch_id'], $batch['qr_code'], $batch['medicine_id']);
            
            // Tạo QR code mới
            $result = generateQRCode($qrData, $batch['qr_code']);
            
            if ($result) {
                $success++;
            } else {
                $failed++;
            }
        } catch (Exception $e) {
            error_log("Failed to regenerate QR for batch {$batch['batch_id']}: " . $e->getMessage());
            $failed++;
        }
    }
    
    return ['success' => $success, 'failed' => $failed];
}
