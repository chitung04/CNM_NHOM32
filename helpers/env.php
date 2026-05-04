<?php
/**
 * Helper để load và đọc file .env
 */

function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        throw new Exception('File .env không tồn tại. Vui lòng copy từ .env.example');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Bỏ qua comment
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') === false) {
            continue; // Bỏ qua dòng không có dấu =
        }
        
        $parts = explode('=', $line, 2);
        if (count($parts) < 2) {
            continue; // Bỏ qua nếu không đủ 2 phần
        }
        
        $key = trim($parts[0]);
        $value = isset($parts[1]) ? trim($parts[1]) : '';
        
        // Bỏ qua nếu key rỗng hoặc chỉ có khoảng trắng
        if (empty($key) || ctype_space($key)) {
            continue;
        }
        
        // Set vào $_ENV và putenv
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}
