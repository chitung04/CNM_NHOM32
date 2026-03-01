# Changelog

Tất cả các thay đổi quan trọng của dự án sẽ được ghi lại trong file này.

## [1.0.0] - 2024-01-15

### Đã thêm
- ✅ Hệ thống authentication với phân quyền Staff/Manager
- ✅ Quản lý thuốc (CRUD) với QR Code
- ✅ Quản lý lô thuốc theo FIFO
- ✅ Chức năng bán hàng với giỏ hàng
- ✅ In hóa đơn với QR Code
- ✅ Thông báo realtime (AJAX)
  - Cảnh báo thuốc sắp hết hàng (< 10 đơn vị)
  - Cảnh báo lô sắp hết hạn (< 30 ngày)
- ✅ Cron job tự động kiểm tra hết hạn
- ✅ Báo cáo doanh thu theo ngày/tháng/năm
- ✅ Báo cáo tồn kho
- ✅ Báo cáo thuốc sắp hết hạn
- ✅ Thống kê thuốc bán chạy
- ✅ Quản lý người dùng
- ✅ Quản lý nhà cung cấp
- ✅ Backup/Restore database
- ✅ Audit logging cho tất cả hành động quan trọng
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ SQL injection prevention
- ✅ Rate limiting cho login
- ✅ Session security
- ✅ Strong password validation

### Tính năng bảo mật
- CSRF token cho tất cả forms
- Prepared statements cho database queries
- Input validation và sanitization
- Output escaping
- Session regeneration
- HttpOnly cookies
- Rate limiting (5 attempts/5 minutes)
- Audit logging
- Error handling với production mode

### Models
- Database.php - Singleton PDO connection
- Medicine.php - Quản lý thuốc
- Batch.php - Quản lý lô thuốc
- Invoice.php - Quản lý hóa đơn
- InvoiceDetail.php - Chi tiết hóa đơn
- User.php - Quản lý người dùng
- Supplier.php - Quản lý nhà cung cấp
- Category.php - Danh mục thuốc
- Unit.php - Đơn vị tính
- Notification.php - Thông báo
- AuditLog.php - Audit logging

### Controllers
- AuthController.php - Đăng nhập/đăng xuất
- MedicineController.php - CRUD thuốc
- BatchController.php - CRUD lô thuốc
- SalesController.php - Bán hàng
- ReportController.php - Báo cáo
- UserController.php - CRUD người dùng
- SupplierController.php - CRUD nhà cung cấp
- BackupController.php - Backup/Restore
- NotificationController.php - Thông báo
- AuditController.php - Audit logs

### Views
- Responsive design với Bootstrap 5
- Dashboard với thống kê tổng quan
- Giao diện bán hàng với giỏ hàng
- Template in hóa đơn
- Các trang quản lý CRUD
- Error pages (403, 404, 500)

### AJAX Endpoints
- search_medicine.php - Tìm kiếm thuốc realtime
- add_to_cart.php - Thêm vào giỏ hàng
- remove_from_cart.php - Xóa khỏi giỏ hàng
- update_cart_quantity.php - Cập nhật số lượng
- get_notifications.php - Lấy thông báo
- get_medicine_by_qr.php - Quét QR code

### Helpers
- auth.php - Authentication functions
- permissions.php - Authorization functions
- security.php - Security functions
- csrf.php - CSRF protection
- qrcode.php - QR code generation
- logger.php - Logging functions
- audit.php - Audit logging
- functions.php - General helper functions

### Cron Jobs
- check_expiry.php - Kiểm tra thuốc sắp hết hạn (chạy hàng ngày)

## [Kế hoạch tương lai]

### Version 2.0
- [ ] Quản lý khách hàng
- [ ] Lịch sử mua hàng của khách
- [ ] Tích hợp thanh toán online
- [ ] Mobile app
- [ ] Export báo cáo ra Excel/PDF
- [ ] Email notifications
- [ ] SMS notifications
- [ ] Multi-pharmacy support
- [ ] API REST
- [ ] Caching với Redis
- [ ] Queue system
- [ ] Automated testing
- [ ] CI/CD pipeline

### Version 1.1
- [ ] Tìm kiếm nâng cao
- [ ] Filter và sort cho tất cả danh sách
- [ ] Pagination cho danh sách dài
- [ ] Dark mode
- [ ] Multi-language support
- [ ] Biểu đồ thống kê với Chart.js
- [ ] Xuất báo cáo PDF
- [ ] Import thuốc từ Excel
- [ ] Quét QR code bằng camera
- [ ] Tích hợp máy in nhiệt

## Ghi chú

Format dựa trên [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
