<?php
/**
 * Test QR Code Generation
 * Script đơn giản để test tạo QR code
 */

// Define constants
define('BASE_PATH', dirname(__DIR__));
define('QRCODE_PATH', BASE_PATH . '/assets/qrcodes');

require_once BASE_PATH . '/helpers/qrcode.php';

echo "=== QR Code Test ===\n\n";

// Test 1: Generate test QR code
echo "1. Testing QR code generation...\n";

$testData = [
    [
        'name' => 'Test Medicine',
        'data' => 'MEDICINE_ID:1|CODE:MED_TEST_001',
        'filename' => 'MED_TEST_001'
    ],
    [
        'name' => 'Test Batch',
        'data' => 'BATCH_ID:1|CODE:BATCH_TEST_001',
        'filename' => 'BATCH_TEST_001'
    ],
    [
        'name' => 'Test Invoice',
        'data' => 'INVOICE:INV20240101|ID:1',
        'filename' => 'INV_TEST_001'
    ]
];

$successCount = 0;
foreach ($testData as $test) {
    echo "   Generating QR for: " . $test['name'] . "...";
    
    $result = generateQRCode($test['data'], $test['filename']);
    
    if ($result) {
        $filepath = QRCODE_PATH . '/' . $result;
        if (file_exists($filepath)) {
            $filesize = filesize($filepath);
            echo " ✓ (Size: " . $filesize . " bytes)\n";
            $successCount++;
        } else {
            echo " ✗ File not created\n";
        }
    } else {
        echo " ✗ Generation failed\n";
    }
}

echo "\n";
echo "Results: $successCount/" . count($testData) . " QR codes generated successfully.\n";
echo "QR codes saved to: " . QRCODE_PATH . "\n\n";

// Test 2: List all QR codes in directory
echo "2. Listing all QR codes in directory...\n";
if (file_exists(QRCODE_PATH)) {
    $files = glob(QRCODE_PATH . '/*.png');
    if (empty($files)) {
        echo "   No QR code files found.\n";
    } else {
        echo "   Found " . count($files) . " QR code files:\n";
        foreach ($files as $file) {
            $filename = basename($file);
            $filesize = filesize($file);
            echo "   - $filename (" . $filesize . " bytes)\n";
        }
    }
} else {
    echo "   QR code directory does not exist.\n";
}

echo "\n=== Test Complete ===\n";
