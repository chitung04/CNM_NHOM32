# API Documentation - AJAX Endpoints

Tài liệu này mô tả các AJAX endpoints có sẵn trong hệ thống.

## Authentication

Tất cả endpoints yêu cầu user đã đăng nhập. Session được kiểm tra tự động.

## Response Format

Tất cả responses đều ở dạng JSON:

```json
{
    "success": true|false,
    "message": "Thông báo",
    "data": {}
}
```

---

## Medicine Endpoints

### 1. Search Medicine

Tìm kiếm thuốc theo tên.

**Endpoint:** `ajax/search_medicine.php`

**Method:** `POST`

**Parameters:**
```json
{
    "keyword": "string" // Từ khóa tìm kiếm
}
```

**Response Success:**
```json
{
    "success": true,
    "medicines": [
        {
            "medicine_id": 1,
            "medicine_name": "Paracetamol",
            "category_name": "Thuốc giảm đau",
            "unit_name": "Viên",
            "price": 5000,
            "inventory": 100
        }
    ]
}
```

**Example:**
```javascript
$.ajax({
    url: 'ajax/search_medicine.php',
    method: 'POST',
    data: { keyword: 'para' },
    success: function(response) {
        console.log(response.medicines);
    }
});
```

---

### 2. Get Medicine by QR Code

Lấy thông tin thuốc bằng mã QR.

**Endpoint:** `ajax/get_medicine_by_qr.php`

**Method:** `POST`

**Parameters:**
```json
{
    "qr_code": "string" // Mã QR
}
```

**Response Success:**
```json
{
    "success": true,
    "medicine": {
        "medicine_id": 1,
        "medicine_name": "Paracetamol",
        "category_name": "Thuốc giảm đau",
        "unit_name": "Viên",
        "price": 5000,
        "inventory": 100,
        "qr_code": "MED_123456"
    }
}
```

**Response Error:**
```json
{
    "success": false,
    "message": "Không tìm thấy thuốc với mã QR này"
}
```

---

## Cart Endpoints

### 3. Add to Cart

Thêm thuốc vào giỏ hàng.

**Endpoint:** `ajax/add_to_cart.php`

**Method:** `POST`

**Parameters:**
```json
{
    "medicine_id": 1,
    "quantity": 5
}
```

**Response Success:**
```json
{
    "success": true,
    "cart": {
        "1_5": {
            "medicine_id": 1,
            "batch_id": 5,
            "medicine_name": "Paracetamol",
            "unit_name": "Viên",
            "price": 5000,
            "quantity": 5
        }
    }
}
```

**Response Error:**
```json
{
    "success": false,
    "message": "Không đủ hàng trong kho"
}
```

---

### 4. Remove from Cart

Xóa thuốc khỏi giỏ hàng.

**Endpoint:** `ajax/remove_from_cart.php`

**Method:** `POST`

**Parameters:**
```json
{
    "key": "1_5" // Key của item trong cart
}
```

**Response Success:**
```json
{
    "success": true,
    "cart": {}
}
```

---

### 5. Update Cart Quantity

Cập nhật số lượng trong giỏ hàng.

**Endpoint:** `ajax/update_cart_quantity.php`

**Method:** `POST`

**Parameters:**
```json
{
    "key": "1_5",
    "quantity": 10
}
```

**Response Success:**
```json
{
    "success": true,
    "cart": {
        "1_5": {
            "medicine_id": 1,
            "batch_id": 5,
            "medicine_name": "Paracetamol",
            "unit_name": "Viên",
            "price": 5000,
            "quantity": 10
        }
    },
    "totalAmount": 50000
}
```

**Response Error:**
```json
{
    "success": false,
    "message": "Không đủ hàng trong kho. Tồn kho: 8"
}
```

---

## Notification Endpoints

### 6. Get Notifications

Lấy danh sách thông báo chưa đọc.

**Endpoint:** `ajax/get_notifications.php`

**Method:** `GET`

**Response Success:**
```json
{
    "success": true,
    "notifications": [
        {
            "notification_id": 1,
            "type": "low_stock",
            "message": "Thuốc 'Paracetamol' sắp hết hàng. Tồn kho: 5",
            "reference_id": 1,
            "is_read": false,
            "created_at": "2024-01-15 10:30:00"
        },
        {
            "notification_id": 2,
            "type": "expiry_warning",
            "message": "Lô thuốc 'Amoxicillin' sắp hết hạn trong 15 ngày",
            "reference_id": 3,
            "is_read": false,
            "created_at": "2024-01-15 09:00:00"
        }
    ],
    "count": 2
}
```

**Example:**
```javascript
// Polling notifications mỗi 30 giây
setInterval(function() {
    $.ajax({
        url: 'ajax/get_notifications.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateNotificationBadge(response.count);
                displayNotifications(response.notifications);
            }
        }
    });
}, 30000);
```

---

## Stock Endpoints

### 7. Check Stock

Kiểm tra tồn kho của thuốc.

**Endpoint:** `ajax/check_stock.php`

**Method:** `POST`

**Parameters:**
```json
{
    "medicine_id": 1
}
```

**Response Success:**
```json
{
    "success": true,
    "medicine_id": 1,
    "medicine_name": "Paracetamol",
    "inventory": 100,
    "batches": [
        {
            "batch_id": 5,
            "quantity": 50,
            "expiry_date": "2025-12-31",
            "days_to_expiry": 350
        },
        {
            "batch_id": 6,
            "quantity": 50,
            "expiry_date": "2026-06-30",
            "days_to_expiry": 530
        }
    ]
}
```

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 401 | Chưa đăng nhập | User chưa authenticate |
| 403 | Không có quyền truy cập | User không có permission |
| 404 | Không tìm thấy | Resource không tồn tại |
| 422 | Dữ liệu không hợp lệ | Validation failed |
| 500 | Lỗi hệ thống | Server error |

---

## Rate Limiting

- Login: 5 attempts / 5 minutes
- Other endpoints: Không giới hạn (có thể thêm sau)

---

## Security

### CSRF Protection

Tất cả POST requests cần CSRF token:

```javascript
$.ajax({
    url: 'ajax/add_to_cart.php',
    method: 'POST',
    data: {
        medicine_id: 1,
        quantity: 5,
        csrf_token: $('input[name="csrf_token"]').val()
    }
});
```

### Session Validation

Mỗi request tự động kiểm tra:
- Session còn hợp lệ
- User đã đăng nhập
- Session chưa timeout (30 phút)

---

## Testing

### Using cURL

```bash
# Search medicine
curl -X POST http://localhost/pharmacy/ajax/search_medicine.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "keyword=para" \
  --cookie "PHPSESSID=your_session_id"

# Add to cart
curl -X POST http://localhost/pharmacy/ajax/add_to_cart.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "medicine_id=1&quantity=5" \
  --cookie "PHPSESSID=your_session_id"
```

### Using Postman

1. Import collection từ `postman_collection.json` (nếu có)
2. Set session cookie
3. Test các endpoints

---

## Best Practices

### Client Side

```javascript
// Debounce search input
let searchTimeout;
$('#searchInput').on('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        searchMedicine($('#searchInput').val());
    }, 300);
});

// Handle errors
$.ajax({
    url: 'ajax/add_to_cart.php',
    method: 'POST',
    data: { medicine_id: 1, quantity: 5 },
    success: function(response) {
        if (response.success) {
            showSuccess(response.message);
        } else {
            showError(response.message);
        }
    },
    error: function(xhr, status, error) {
        showError('Có lỗi xảy ra. Vui lòng thử lại.');
    }
});

// Loading indicator
$.ajax({
    url: 'ajax/search_medicine.php',
    method: 'POST',
    data: { keyword: 'para' },
    beforeSend: function() {
        showLoading();
    },
    complete: function() {
        hideLoading();
    },
    success: function(response) {
        // Handle response
    }
});
```

### Server Side

```php
// Always validate input
$medicineId = filter_input(INPUT_POST, 'medicine_id', FILTER_VALIDATE_INT);
if (!$medicineId) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Always check authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

// Always use try-catch
try {
    // Your code here
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
```

---

## Future Endpoints

Các endpoints có thể thêm trong tương lai:

- `ajax/get_medicine_details.php` - Chi tiết thuốc
- `ajax/get_batch_details.php` - Chi tiết lô thuốc
- `ajax/mark_notification_read.php` - Đánh dấu đã đọc
- `ajax/get_sales_chart.php` - Dữ liệu biểu đồ
- `ajax/export_report.php` - Export báo cáo
- `ajax/scan_qr_code.php` - Quét QR bằng camera

---

## Support

Nếu có vấn đề với API, vui lòng:
1. Kiểm tra console log
2. Kiểm tra network tab
3. Kiểm tra server logs
4. Liên hệ support team
