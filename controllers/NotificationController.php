<?php
require_once 'models/Notification.php';

class NotificationController {
    private $notificationModel;
    
    public function __construct() {
        $this->notificationModel = new Notification();
    }
    
    /**
     * Lấy danh sách thông báo
     */
    public function index() {
        $notifications = $this->notificationModel->getUnread();
        $pageTitle = "Thông báo";
        require_once 'views/notifications/index.php';
    }
    
    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead() {
        $id = $_GET['id'] ?? 0;
        $this->notificationModel->markAsRead($id);
        
        // Redirect về trang trước
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=dashboard';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Đánh dấu tất cả đã đọc
     */
    public function markAllAsRead() {
        $this->notificationModel->markAllAsRead();
        
        $_SESSION['success'] = "Đã đánh dấu tất cả thông báo là đã đọc";
        header('Location: index.php?page=notifications');
        exit;
    }
}
