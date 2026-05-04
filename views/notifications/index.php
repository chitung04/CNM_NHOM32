<?php
$pageTitle = "Quản lý thông báo";
require_once 'views/layouts/header.php';

// Load notifications
require_once 'models/Notification.php';
$notificationModel = new Notification();

// Get all notifications (both read and unread) - CHỈ CỦA PHARMACY HIỆN TẠI
require_once 'helpers/pharmacy.php';
$pharmacyId = getCurrentPharmacyId();

$db = Database::getInstance();
$sql = "SELECT n.*, 
        CASE 
            WHEN n.type = 'low_stock' THEN 
                CONCAT('Thuốc ', m.medicine_name, ' sắp hết hàng')
            WHEN n.type = 'expiry_warning' THEN 
                CONCAT('Lô thuốc ', m2.medicine_name, ' sắp hết hạn')
            ELSE n.message
        END as detailed_message
        FROM notifications n
        LEFT JOIN medicines m ON n.type = 'low_stock' AND n.reference_id = m.medicine_id
        LEFT JOIN batches b2 ON n.type = 'expiry_warning' AND n.reference_id = b2.batch_id
        LEFT JOIN medicines m2 ON b2.medicine_id = m2.medicine_id
        WHERE n.pharmacy_id = ?
        ORDER BY n.created_at DESC 
        LIMIT 100";

$stmt = $db->query($sql, [$pharmacyId]);
$notifications = $stmt->fetchAll();

$unreadCount = $notificationModel->countUnread();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-bell me-2"></i>
                    Quản lý thông báo
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger ms-2"><?= $unreadCount ?> chưa đọc</span>
                    <?php endif; ?>
                </h2>
                
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="refreshNotifications()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Làm mới
                    </button>
                    <?php if ($unreadCount > 0): ?>
                        <button class="btn btn-outline-success" onclick="markAllAsRead()">
                            <i class="bi bi-check-all me-2"></i>Đánh dấu tất cả đã đọc
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-bell-slash display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">Không có thông báo nào</h4>
                        <p class="text-muted">Hệ thống sẽ tự động tạo thông báo khi có thuốc sắp hết hạn hoặc sắp hết hàng.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="list-group-item <?= $notification['is_read'] ? '' : 'list-group-item-warning' ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php if ($notification['type'] === 'low_stock'): ?>
                                                    <i class="bi bi-box-seam text-danger me-2 fs-5"></i>
                                                    <span class="badge bg-danger me-2">Sắp hết hàng</span>
                                                <?php else: ?>
                                                    <i class="bi bi-exclamation-triangle text-warning me-2 fs-5"></i>
                                                    <span class="badge bg-warning text-dark me-2">Sắp hết hạn</span>
                                                <?php endif; ?>
                                                
                                                <?php if (!$notification['is_read']): ?>
                                                    <span class="badge bg-primary">Mới</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <h6 class="mb-1"><?= htmlspecialchars($notification['detailed_message'] ?: $notification['message']) ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= $notification['created_at'] ? date('d/m/Y H:i', strtotime($notification['created_at'])) : '-' ?>
                                            </small>
                                        </div>
                                        
                                        <div class="btn-group-vertical btn-group-sm">
                                            <?php if (!$notification['is_read']): ?>
                                                <button class="btn btn-outline-success btn-sm" 
                                                        onclick="markAsRead(<?= $notification['notification_id'] ?>)"
                                                        title="Đánh dấu đã đọc">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($notification['type'] === 'low_stock' && $notification['reference_id']): ?>
                                                <a href="index.php?page=medicines&action=view&id=<?= $notification['reference_id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm" title="Xem thuốc">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            <?php elseif ($notification['type'] === 'expiry_warning' && $notification['reference_id']): ?>
                                                <a href="index.php?page=batches&action=view&id=<?= $notification['reference_id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm" title="Xem lô thuốc">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function refreshNotifications() {
    location.reload();
}

function markAsRead(notificationId) {
    $.ajax({
        url: 'index.php?page=notifications&action=markAsRead&id=' + notificationId,
        method: 'GET',
        success: function() {
            location.reload();
        },
        error: function() {
            alert('Có lỗi xảy ra khi đánh dấu thông báo');
        }
    });
}

function markAllAsRead() {
    if (confirm('Bạn có chắc chắn muốn đánh dấu tất cả thông báo là đã đọc?')) {
        $.ajax({
            url: 'index.php?page=notifications&action=markAllAsRead',
            method: 'GET',
            success: function() {
                location.reload();
            },
            error: function() {
                alert('Có lỗi xảy ra khi đánh dấu thông báo');
            }
        });
    }
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>