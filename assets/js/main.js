// Main JavaScript file

$(document).ready(function() {
    // Load notifications
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Load notifications via AJAX
function loadNotifications() {
    $.ajax({
        url: 'ajax/get_notifications.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateNotificationBadge(response.count);
                updateNotificationList(response.notifications);
            }
        },
        error: function() {
            console.log('Error loading notifications');
        }
    });
}

// Update notification badge
function updateNotificationBadge(count) {
    const badge = $('#notification-count');
    if (count > 0) {
        badge.text(count).show();
    } else {
        badge.hide();
    }
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
