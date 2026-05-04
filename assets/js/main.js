// Main JavaScript file

// Biến global để quản lý thông báo tuần tự trong text popup
let currentNotifications = [];
let currentNotificationIndex = 0;
let notificationTextInterval = null;

$(document).ready(function() {
    console.log('Main.js loaded and DOM ready');
    
    // Load notifications nếu chưa có từ PHP
    if (!window.currentNotifications || window.currentNotifications.length === 0) {
        console.log('No initial notifications from PHP, loading via AJAX...');
        loadNotifications();
    } else {
        console.log('Using initial notifications from PHP:', window.currentNotifications.length);
    }
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Load notifications via AJAX
function loadNotifications() {
    console.log('Loading notifications via AJAX...');
    
    $.ajax({
        url: 'ajax/get_notifications.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('AJAX response received:', response);
            
            if (response.success) {
                console.log('Updating notification display with count:', response.count);
                updateNotificationBadge(response.count);
                updateNotificationList(response.notifications);
                updateNotificationBanner(response.notifications);
            } else {
                console.error('AJAX response indicates failure:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error loading notifications:', error, status, xhr.responseText);
        }
    });
}

// Update notification badge
function updateNotificationBadge(count) {
    const badge = $('#notification-count');
    const bell = $('#notification-bell');
    const notificationText = $('.notification-text-inline');
    
    console.log('Updating notification badge with count:', count);
    
    if (count > 0) {
        // Có thông báo - hiển thị popup với animation
        badge.text(count).show();
        notificationText.show();
        
        // Remove all existing animation classes
        bell.removeClass('notification-bell-active notification-bell-urgent');
        badge.removeClass('notification-badge-active notification-badge-urgent');
        notificationText.removeClass('notification-text-active notification-text-urgent notification-text-critical');
        
        // Xác định mức độ ưu tiên
        if (count >= 10) {
            bell.addClass('notification-bell-urgent');
            badge.addClass('notification-badge-urgent');
            notificationText.addClass('notification-text-critical');
            console.log('Critical notifications: ' + count + ' - critical animation activated');
        } else if (count >= 5) {
            bell.addClass('notification-bell-urgent');
            badge.addClass('notification-badge-active');
            notificationText.addClass('notification-text-urgent');
            console.log('High priority notifications: ' + count + ' - urgent animation activated');
        } else {
            bell.addClass('notification-bell-active');
            badge.addClass('notification-badge-active');
            notificationText.addClass('notification-text-active');
            console.log('Normal notifications: ' + count + ' - standard animation activated');
        }
        
        // Bắt đầu hiển thị thông báo chi tiết tuần tự trong text popup
        startSequentialNotificationText();
        
    } else {
        // Không có thông báo - ẩn tất cả
        badge.hide();
        notificationText.hide();
        
        // Remove all animation classes
        bell.removeClass('notification-bell-active notification-bell-urgent');
        badge.removeClass('notification-badge-active notification-badge-urgent');
        notificationText.removeClass('notification-text-active notification-text-urgent notification-text-critical');
        
        // Dừng hiển thị tuần tự
        stopSequentialNotificationText();
        
        console.log('No notifications');
    }
}

// Bắt đầu hiển thị thông báo tuần tự trong text popup
function startSequentialNotificationText() {
    if (currentNotifications.length === 0) return;
    
    // Dừng interval cũ nếu có
    stopSequentialNotificationText();
    
    // Reset index
    currentNotificationIndex = 0;
    
    // Hiển thị thông báo đầu tiên
    showNextNotificationText();
    
    // Tự động chuyển thông báo tiếp theo
    const interval = currentNotifications.length >= 10 ? 2000 : (currentNotifications.length >= 5 ? 3000 : 4000);
    notificationTextInterval = setInterval(showNextNotificationText, interval);
    
    console.log('Sequential notification text started');
}

// Dừng hiển thị thông báo tuần tự
function stopSequentialNotificationText() {
    if (notificationTextInterval) {
        clearInterval(notificationTextInterval);
        notificationTextInterval = null;
    }
}

// Hiển thị thông báo tiếp theo trong text popup
function showNextNotificationText() {
    if (currentNotifications.length === 0) return;
    
    const notificationMessage = $('.notification-message');
    const notification = currentNotifications[currentNotificationIndex];
    
    // Thêm icon dựa trên loại thông báo
    const icon = notification.type === 'low_stock' ? '⚠️' : '⏰';
    
    // Hiển thị thông báo chi tiết
    const message = `${icon} ${notification.message}`;
    
    // Hiệu ứng fade để chuyển đổi mượt mà
    notificationMessage.fadeOut(200, function() {
        notificationMessage.text(message);
        notificationMessage.fadeIn(200);
    });
    
    console.log(`Showing notification text ${currentNotificationIndex + 1}/${currentNotifications.length}: ${notification.message}`);
    
    // Chuyển sang thông báo tiếp theo
    currentNotificationIndex = (currentNotificationIndex + 1) % currentNotifications.length;
}

// Update notification banner với thông báo cố định
function updateNotificationBanner(notifications) {
    console.log('updateNotificationBanner called with:', notifications);
    
    // Lưu danh sách thông báo để sử dụng cho text popup
    currentNotifications = notifications || [];
    
    const banner = $('#notification-banner');
    const bannerContent = $('#notification-banner-content');
    const mainContent = $('.main-content');
    
    console.log('Banner element found:', banner.length);
    console.log('Banner content element found:', bannerContent.length);
    
    // LUÔN HIỂN THỊ THÔNG BÁO - dù có hay không có thông báo thật
    let notificationItems = '';
    
    if (notifications && notifications.length > 0) {
        // Có thông báo thật - hiển thị chúng
        const repeatCount = Math.max(3, Math.ceil(15 / notifications.length));
        
        for (let repeat = 0; repeat < repeatCount; repeat++) {
            notifications.forEach(function(notif, index) {
                const icon = notif.type === 'low_stock' ? '⚠️' : '⏰';
                const urgencyClass = notif.type === 'low_stock' ? 'low-stock' : 'expiry-warning';
                
                notificationItems += `<div class="notification-item ${urgencyClass}"><span class="icon">${icon}</span><span class="message">${notif.message}</span></div>`;
            });
        }
    } else {
        // Không có thông báo thật - hiển thị thông báo mặc định
        const defaultNotifications = [
            'Hệ thống hoạt động bình thường',
            'Kiểm tra hàng tồn kho định kỳ',
            'Theo dõi hạn sử dụng thuốc',
            'Cập nhật thông tin sản phẩm mới'
        ];
        
        // Lặp lại thông báo mặc định
        for (let repeat = 0; repeat < 5; repeat++) {
            defaultNotifications.forEach(function(message, index) {
                notificationItems += `<div class="notification-item default"><span class="icon">📋</span><span class="message">${message}</span></div>`;
            });
        }
    }
    
    console.log('Generated notification HTML length:', notificationItems.length);
    
    // Clear và set content
    bannerContent.empty();
    bannerContent.html(notificationItems);
    
    // LUÔN HIỂN THỊ VÀ KHÔNG THAY ĐỔI MÀU
    banner.removeClass('notification-banner-urgent notification-banner-critical');
    banner.addClass('show'); // Luôn có class show
    mainContent.addClass('with-banner');
    
    console.log('Banner always visible with orange color');
    
    // Force restart animation
    setTimeout(function() {
        bannerContent.css('animation', 'none');
        bannerContent[0].offsetHeight; // Trigger reflow
        bannerContent.css('animation', 'scrollLeft 8s linear infinite'); // Cố định tốc độ
        console.log('Animation restarted with fixed speed');
    }, 100);
}

// Update notification list
function updateNotificationList(notifications) {
    const list = $('#notification-list');
    
    if (notifications.length === 0) {
        list.html('<div class="dropdown-item text-muted text-center">Không có thông báo mới</div>');
        return;
    }
    
    let html = '';
    notifications.forEach(function(notif) {
        const icon = notif.type === 'low_stock' ? 'box-seam' : 'exclamation-triangle';
        const color = notif.type === 'low_stock' ? 'danger' : 'warning';
        
        html += `
            <li>
                <a class="dropdown-item" href="#">
                    <i class="bi bi-${icon} text-${color} me-2"></i>
                    <small>${notif.message}</small>
                </a>
            </li>
        `;
    });
    
    list.html(html);
}

// Confirm delete action
function confirmDelete(message) {
    return confirm(message || 'Bạn có chắc chắn muốn xóa?');
}

// Show loading spinner
function showLoading(element) {
    $(element).html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
    $(element).prop('disabled', true);
}

// Hide loading spinner
function hideLoading(element, text) {
    $(element).html(text);
    $(element).prop('disabled', false);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

// Function để khởi tạo thông báo từ PHP
function initializeNotificationsFromPHP(notifications) {
    if (notifications && notifications.length > 0) {
        currentNotifications = notifications;
        startSequentialNotificationText();
        console.log('Initialized notifications from PHP:', notifications.length, 'notifications');
    }
}

// Clear all notifications
function clearAllNotifications() {
    $.ajax({
        url: 'ajax/clear_notifications.php',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Reset notification display
                updateNotificationBadge(0);
                updateNotificationList([]);
                updateNotificationBanner([]);
                
                // Show success message
                console.log('All notifications cleared');
            } else {
                console.error('Failed to clear notifications:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error clearing notifications:', error);
        }
    });
}

// Expose function to global scope
window.startSequentialNotificationText = startSequentialNotificationText;
window.currentNotifications = currentNotifications;
window.initializeNotificationsFromPHP = initializeNotificationsFromPHP;
window.clearAllNotifications = clearAllNotifications;