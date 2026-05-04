<?php
require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();
$stmt = $db->query('SELECT medicine_id, medicine_name, qr_code FROM medicines LIMIT 10');

echo "Medicine QR Codes in Database:\n";
echo str_repeat("=", 80) . "\n";

while ($row = $stmt->fetch()) {
    $qrCode = $row['qr_code'] ?? 'NULL';
    $qrFile = "assets/qrcodes/{$qrCode}.png";
    $fileExists = file_exists($qrFile) ? 'EXISTS' : 'MISSING';
    
    echo sprintf(
        "ID: %3d | %-30s | QR: %-25s | File: %s\n",
        $row['medicine_id'],
        substr($row['medicine_name'], 0, 30),
        $qrCode,
        $fileExists
    );
}

echo str_repeat("=", 80) . "\n";

// Count total medicines with QR codes
$stmt = $db->query('SELECT COUNT(*) as total FROM medicines WHERE qr_code IS NOT NULL');
$result = $stmt->fetch();
echo "\nTotal medicines with QR codes: " . $result['total'] . "\n";

// Count QR code files
$qrFiles = glob('assets/qrcodes/MED_*.png');
echo "Total MED QR code files: " . count($qrFiles) . "\n";
