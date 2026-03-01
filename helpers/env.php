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
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Set vào $_ENV và putenv
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}
