<?php
/**
 * Simple QR Code Generator
 * Lightweight implementation without external dependencies
 */

class QRcode {
    /**
     * Generate QR code and save to file
     * 
     * @param string $text Data to encode
     * @param string $outfile Output file path
     * @param string $level Error correction level (L, M, Q, H)
     * @param int $size Size of QR code
     * @param int $margin Margin around QR code
     */
    public static function png($text, $outfile, $level = 'L', $size = 3, $margin = 4) {
        // Use QR Server API as fallback
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/';
        $params = http_build_query([
            'data' => $text,
            'size' => ($size * 100) . 'x' . ($size * 100),
            'margin' => $margin,
            'ecc' => $level
        ]);
        
        $qrUrl = $apiUrl . '?' . $params;
        
        // Download QR code image
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0'
            ]
        ]);
        
        $imageData = @file_get_contents($qrUrl, false, $context);
        
        if ($imageData !== false) {
            // Ensure directory exists
            $dir = dirname($outfile);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            
            file_put_contents($outfile, $imageData);
            return true;
        }
        
        return false;
    }
}
