<?php
require_once 'models/AuditLog.php';
require_once 'helpers/audit.php';

class AuditController {
    private $auditModel;
    
    public function __construct() {
        $this->auditModel = new AuditLog();
    }
    
    /**
     * Trang chính - danh sách audit logs
     */
    public function index() {
        $page = max(1, intval($_GET['p'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        // Filters
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'action' => $_GET['action'] ?? '',
            'table_name' => $_GET['table_name'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];
        
        // Lấy logs với filters
        $logs = $this->getFilteredLogs($filters, $limit, $offset);
        $totalLogs = $this->auditModel->count(array_filter($filters));
        $totalPages = ceil($totalLogs / $limit);
        
        // Thống kê nhanh
        $todayCount = $this->auditModel->getTodayCount();
        $weekCount = $this->auditModel->getWeekCount();
        $activeUsers = $this->auditModel->getActiveUsersCount();
        
        // Lấy danh sách users cho filter
        require_once 'models/User.php';
        $userModel = new User();
        $users = $userModel->getAll();
        
        // Lấy danh sách actions và tables
        $actions = $this->getUniqueActions();
        $tables = $this->getUniqueTables();
        
        $pageTitle = "Nhật ký hoạt động hệ thống";
        require_once 'views/audit/index.php';
    }
    
    /**
     * Xem chi tiết một log entry
     */
    public function view() {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = "Không tìm thấy log entry";
            header('Location: index.php?page=audit');
            exit;
        }
        
        $log = $this->auditModel->getById($id);
        
        if (!$log) {
            $_SESSION['error'] = "Không tìm thấy log entry";
            header('Location: index.php?page=audit');
            exit;
        }
        
        // Parse JSON data
        $log['old_values_parsed'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
        $log['new_values_parsed'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
        
        $pageTitle = "Chi tiết log #" . $log['log_id'];
        require_once 'views/audit/view.php';
    }
    
    /**
     * Thống kê hoạt động
     */
    public function statistics() {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // Thống kê theo action
        $actionStats = $this->auditModel->getStatistics($startDate, $endDate);
        
        // Thống kê theo user
        $userStats = $this->getUserStatistics($startDate, $endDate);
        
        // Thống kê theo table
        $tableStats = $this->getTableStatistics($startDate, $endDate);
        
        // Thống kê theo ngày
        $dailyStats = $this->getDailyStatistics($startDate, $endDate);
        
        $pageTitle = "Thống kê hoạt động hệ thống";
        require_once 'views/audit/statistics.php';
    }
    
    /**
     * Export logs ra CSV
     */
    public function export() {
        $format = $_GET['format'] ?? 'csv';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $logs = $this->auditModel->getByDateRange($startDate, $endDate, 10000);
        
        if ($format === 'csv') {
            $this->exportCSV($logs, $startDate, $endDate);
        } else {
            $_SESSION['error'] = "Định dạng export không được hỗ trợ";
            header('Location: index.php?page=audit');
        }
    }
    
    /**
     * Dọn dẹp logs cũ
     */
    public function cleanup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=audit');
            exit;
        }
        
        try {
            $days = intval($_POST['days'] ?? 90);
            
            if ($days < 30) {
                throw new Exception("Không thể xóa logs mới hơn 30 ngày");
            }
            
            $deletedCount = $this->auditModel->cleanup($days);
            
            $_SESSION['success'] = "Đã xóa {$deletedCount} logs cũ hơn {$days} ngày";
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Lỗi khi dọn dẹp: " . $e->getMessage();
        }
        
        header('Location: index.php?page=audit');
        exit;
    }
    
    /**
     * Lấy logs với filters
     */
    private function getFilteredLogs($filters, $limit, $offset) {
        // Sử dụng method có sẵn trong AuditLog thay vì truy cập trực tiếp database
        return $this->auditModel->getFilteredLogs($filters, $limit, $offset);
    }
    
    /**
     * Lấy danh sách actions duy nhất
     */
    private function getUniqueActions() {
        return $this->auditModel->getUniqueActions();
    }
    
    /**
     * Lấy danh sách tables duy nhất
     */
    private function getUniqueTables() {
        return $this->auditModel->getUniqueTables();
    }
    
    /**
     * Thống kê theo user
     */
    private function getUserStatistics($startDate, $endDate) {
        return $this->auditModel->getUserStatistics($startDate, $endDate);
    }
    
    /**
     * Thống kê theo table
     */
    private function getTableStatistics($startDate, $endDate) {
        return $this->auditModel->getTableStatistics($startDate, $endDate);
    }
    
    /**
     * Thống kê theo ngày
     */
    private function getDailyStatistics($startDate, $endDate) {
        return $this->auditModel->getDailyStatistics($startDate, $endDate);
    }
    
    /**
     * Export ra CSV
     */
    private function exportCSV($logs, $startDate, $endDate) {
        $filename = "audit_logs_{$startDate}_to_{$endDate}.csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, [
            'ID',
            'Thời gian',
            'Người dùng',
            'Hành động',
            'Bảng',
            'ID bản ghi',
            'Dữ liệu cũ',
            'Dữ liệu mới',
            'IP Address',
            'User Agent'
        ]);
        
        // Data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['log_id'],
                $log['created_at'],
                $log['full_name'] ?? $log['username'] ?? 'System',
                getActionName($log['action']),
                getTableName($log['table_name']),
                $log['record_id'],
                $log['old_values'],
                $log['new_values'],
                $log['ip_address'],
                $log['user_agent']
            ]);
        }
        
        fclose($output);
        
        // Log export action
        auditExport('audit_logs', 'csv', count($logs));
        
        exit;
    }
}