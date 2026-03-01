<?php
/**
 * General Helper Functions
 */

/**
 * Format tiền tệ VNĐ
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

/**
 * Format ngày tháng
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Format ngày giờ
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Tính số ngày giữa 2 ngày
 */
function daysBetween($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->days;
}

/**
 * Kiểm tra ngày đã quá hạn chưa
 */
function isExpired($date) {
    return strtotime($date) < time();
}

/**
 * Lấy badge class theo trạng thái
 */
function getStatusBadge($status) {
    $badges = [
        'active' => 'bg-success',
        'inactive' => 'bg-secondary',
        'expired' => 'bg-danger',
        'sold_out' => 'bg-warning',
        'pending' => 'bg-info'
    ];
    
    return $badges[$status] ?? 'bg-secondary';
}

/**
 * Lấy text trạng thái tiếng Việt
 */
function getStatusText($status) {
    $texts = [
        'active' => 'Hoạt động',
        'inactive' => 'Không hoạt động',
        'expired' => 'Hết hạn',
        'sold_out' => 'Hết hàng',
        'pending' => 'Chờ xử lý',
        'staff' => 'Nhân viên',
        'manager' => 'Quản lý'
    ];
    
    return $texts[$status] ?? $status;
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $filename = pathinfo($originalName, PATHINFO_FILENAME);
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
    
    return $filename . '_' . time() . '_' . uniqid() . '.' . $extension;
}

/**
 * Get file size in human readable format
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Redirect với message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION[$type] = $message;
    header("Location: $url");
    exit;
}

/**
 * Get current page URL
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if current page is active
 */
function isActivePage($page) {
    return isset($_GET['page']) && $_GET['page'] === $page;
}

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav><ul class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage - 1) . '">Trước</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Trước</span></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage + 1) . '">Sau</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Sau</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Log activity
 */
function logActivity($action, $details = '') {
    if (!defined('BASE_PATH')) {
        return;
    }
    
    $logFile = BASE_PATH . '/logs/activity.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $userId = $_SESSION['user_id'] ?? 'guest';
    $username = $_SESSION['username'] ?? 'guest';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $logMessage = "[$timestamp] User: $username (ID: $userId) | IP: $ip | Action: $action";
    
    if (!empty($details)) {
        $logMessage .= " | Details: $details";
    }
    
    $logMessage .= PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Get Vietnamese day name
 */
function getVietnameseDayName($date) {
    $days = [
        'Monday' => 'Thứ Hai',
        'Tuesday' => 'Thứ Ba',
        'Wednesday' => 'Thứ Tư',
        'Thursday' => 'Thứ Năm',
        'Friday' => 'Thứ Sáu',
        'Saturday' => 'Thứ Bảy',
        'Sunday' => 'Chủ Nhật'
    ];
    
    $dayName = date('l', strtotime($date));
    return $days[$dayName] ?? $dayName;
}

/**
 * Calculate percentage
 */
function calculatePercentage($part, $total) {
    if ($total == 0) {
        return 0;
    }
    
    return round(($part / $total) * 100, 2);
}

/**
 * Array to CSV
 */
function arrayToCsv($data, $filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}
