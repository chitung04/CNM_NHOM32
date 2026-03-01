<?php
// Load environment variables
require_once __DIR__ . '/../helpers/env.php';
loadEnv(__DIR__ . '/../.env');

// Cấu hình kết nối database từ .env
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'pharmacy_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8'));
