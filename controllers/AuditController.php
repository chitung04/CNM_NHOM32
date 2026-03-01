<?php
require_once 'models/AuditLog.php';
require_once 'helpers/audit.php';

class AuditController {
    private $auditModel;
    
    public function __construct() {
        $this->auditModel = new AuditLog();
    }
    
    /**
     * Danh sách audit logs
     */
    public function index() {
        requireManager();
        
        $page = $_GET['p'] ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        // Filters
        $filters = [];
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = $_GET['user_id'];
        }
        if (!empty($_GET['action'])) {
            $filters['action'] = $_GET['action'];
        }
        if (!empty($_GET['table_name'])) {
            $filters['table_name'] = $_GET['table_name'];
        }
        
        // Lấy logs
        if (!empty($filters)) {
            $logs = $this->getFilteredLogs($filters, $limit, $offset);
        } else {
            $logs = $this->auditModel->getAll($limit, $offset);
        }
        
        $totalLogs = $this->auditModel->count($filters);
        $totalPages = ceil($totalLogs / $limit);
        
        // Lấy danh sách users cho filter
        require_once 'models/User.php';
        $userModel = new User();
        $users = $userModel->getAll();
        
        $pageTitle = "Nhật ký hoạt động";
        require_once 'views/audit/index.php';
    }
    
    /**
     * Xem chi tiết log
     */
    public function view() {
        requireManager();
        
        $logId = $_GET['id'] ?? 0;
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE al.log_id = ?";
        
        $stmt = $this->auditModel->db->query($sql, [$logId]);
        $log = $stmt->fetch();
        
        if (!$log) {
            $_SESSION['error'] = "Không tìm thấy log";
            header('Location: index.php?page=audit');
            exit;
        }
        
        $pageTitle = "Chi tiết nhật ký";
        require_once 'views/audit/view.php';
    }
    
    /**
     * Thống kê hoạt động
     */
    public function statistics() {
        requireManager();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $stats = $this->auditModel->getStatistics($startDate, $endDate);
        
        // Thống kê theo user
        $sql = "SELECT u.username, u.full_name, COUNT(*) as action_count
                FROM audit_logs al
                INNER JOIN users u ON al.user_id = u.user_id
                WHERE DATE(al.created_at) BETWEEN ? AND ?
                GROUP BY al.user_id
                ORDER BY action_count DESC
                LIMIT 10";
        $stmt = $this->auditModel->db->query($sql, [$startDate, $endDate]);
        $userStats = $stmt->fetchAll();
        
        $pageTitle = "Thống kê hoạt động";
        require_once 'views/audit/statistics.php';
    }
    
    /**
     * Cleanup logs cũ
     */
    public function cleanup() {
        requireManager();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $days = $_POST['days'] ?? 90;
            
            try {
                $deleted = $this->auditModel->cleanup($days);
                $_SESSION['success'] = "Đã xóa logs cũ hơn $days ngày";
                auditLog('CLEANUP', 'audit_logs', null, null, ['days' => $days, 'deleted' => $deleted]);
            } catch (Exception $e) {
                $_SESSION['error'] = "Lỗi: " . $e->getMessage();
            }
        }
        
        header('Location: index.php?page=audit');
        exit;
    }
    
    /**
     * Lấy logs với filters
     */
    private function getFilteredLogs($filters, $limit, $offset) {
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $sql .= " AND al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->auditModel->db->query($sql, $params);
        return $stmt->fetchAll();
    }
}
