# Implementation Plan - Hệ Thống Quản Lý Bán Thuốc

## Tổng quan

Danh sách tasks này hướng dẫn từng bước để xây dựng hệ thống quản lý bán thuốc theo mô hình MVC. Mỗi task được thiết kế để xây dựng dần các chức năng, đảm bảo hệ thống hoạt động ổn định sau mỗi bước.

---

- [x] 1. Thiết lập cấu trúc dự án và cấu hình cơ bản


  - Tạo cấu trúc thư mục MVC theo design document
  - Tạo file config/database.php để cấu hình kết nối MySQL
  - Tạo file config/config.php cho các cấu hình chung (timezone, session, paths)
  - Tạo file index.php làm entry point với routing cơ bản
  - Tạo file .htaccess cho URL rewriting (nếu dùng Apache)
  - _Requirements: Tất cả requirements cần cấu trúc MVC_



- [x] 2. Tạo database schema và Model cơ sở


  - Viết SQL script tạo tất cả các bảng theo design document (users, medicines, batches, invoices, etc.)
  - Tạo class Database.php với singleton pattern và PDO connection
  - Tạo base Model class với các phương thức CRUD chung
  - Insert dữ liệu mẫu cho categories, units, và admin user mặc định



  - _Requirements: 1.1, 2.1, 7.1, 8.1, 9.1_



- [x] 3. Xây dựng hệ thống Authentication
  - Tạo Model User.php với methods: authenticate(), getById(), create(), update()
  - Tạo AuthController.php xử lý login/logout
  - Tạo views/auth/login.php - giao diện đăng nhập
  - Implement session management và password hashing
  - Tạo helper/auth.php với functions: isLoggedIn(), checkRole(), requireLogin()
  - _Requirements: 8.1, 8.2, 8.3, 8.5_

- [ ]* 3.1 Viết property test cho authentication
  - **Property 25: Valid credentials authenticate successfully**


  - **Property 26: Invalid credentials are rejected**
  - **Property 27: Logout clears session data**
  - **Property 28: Passwords are stored securely**
  - **Validates: Requirements 8.1, 8.2, 8.3, 8.5**

- [x] 4. Tạo layout và dashboard cơ bản




  - Tạo views/layouts/header.php với navigation menu
  - Tạo views/layouts/sidebar.php với menu theo role (Staff/Manager)
  - Tạo views/layouts/footer.php với scripts
  - Tạo views/dashboard/index.php hiển thị thống kê tổng quan


  - Tích hợp Bootstrap CSS framework
  - _Requirements: 11.1, 11.2_

- [x] 5. Implement quản lý danh mục thuốc (Medicines)
  - Tạo Model Medicine.php với đầy đủ CRUD methods
  - Tạo Model Category.php và Unit.php
  - Tạo MedicineController.php xử lý các actions
  - Tạo views/medicines/index.php - danh sách thuốc với search
  - Tạo views/medicines/create.php - form thêm thuốc mới
  - Tạo views/medicines/edit.php - form sửa thuốc
  - Implement validation cho form nhập liệu
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [ ]* 5.1 Viết property test cho medicine CRUD
  - **Property 1: Medicine CRUD operations preserve data integrity**
  - **Property 2: Medicine deletion removes record completely**


  - **Property 3: Medicine search returns all matching results**
  - **Validates: Requirements 1.1, 1.2, 1.3, 1.4**



- [x] 6. Tích hợp QR Code cho thuốc
  - Cài đặt thư viện phpqrcode
  - Tạo helper/qrcode.php với function generateQRCode()
  - Tự động tạo QR code khi thêm thuốc mới
  - Lưu QR code vào thư mục assets/qrcodes/
  - Hiển thị QR code trong chi tiết thuốc
  - _Requirements: 1.5_




- [ ]* 6.1 Viết property test cho QR code
  - **Property 4: QR code retrieval returns correct medicine**
  - **Validates: Requirements 1.5**




- [x] 7. Implement quản lý lô thuốc (Batches)
  - Tạo Model Batch.php với methods: create(), getByMedicine(), getExpiringBatches()
  - Tạo BatchController.php
  - Tạo views/batches/index.php - danh sách lô thuốc
  - Tạo views/batches/create.php - form nhập lô mới
  - Tự động tạo QR code cho mỗi lô
  - Hiển thị warning cho lô sắp hết hạn (< 30 ngày)
  - _Requirements: 2.1, 2.2, 2.3, 2.4_

- [ ]* 7.1 Viết property test cho batch management
  - **Property 5: Batch creation updates inventory correctly**
  - **Property 6: QR codes are unique across all batches**
  - **Property 7: Batch list contains complete information**
  - **Validates: Requirements 2.1, 2.2, 2.3, 2.5**

- [x] 8. Xây dựng chức năng bán hàng (Sales)
  - Tạo Model Invoice.php và InvoiceDetail.php
  - Tạo SalesController.php với shopping cart logic
  - Tạo views/sales/index.php - giao diện bán hàng với giỏ hàng
  - Implement tìm kiếm thuốc và thêm vào giỏ
  - Implement tính toán tổng tiền và giảm giá
  - Xử lý checkout: tạo invoice, cập nhật inventory
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [ ]* 8.1 Viết property test cho sales operations
  - **Property 8: Inventory verification prevents overselling**
  - **Property 9: Discount calculation is accurate**
  - **Property 10: Invoice generation includes all required fields**
  - **Property 11: Sales update inventory atomically**



  - **Validates: Requirements 3.1, 3.3, 3.4, 3.5**

- [x] 9. Implement in hóa đơn
  - Tạo views/sales/invoice.php - template in hóa đơn
  - Tạo CSS riêng cho print layout
  - Hiển thị QR code trên hóa đơn
  - Implement JavaScript window.print()
  - Cho phép xem lại và in lại hóa đơn cũ
  - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [ ]* 9.1 Viết property test cho invoice printing
  - **Property 12: Printed invoice contains complete information**
  - **Validates: Requirements 4.2, 4.3**

- [x] 10. Tích hợp AJAX cho tìm kiếm và cập nhật realtime
  - Tạo ajax/search_medicine.php - tìm kiếm thuốc không reload
  - Tạo ajax/check_stock.php - kiểm tra tồn kho realtime
  - Tạo ajax/add_to_cart.php - thêm vào giỏ không reload
  - Viết JavaScript/jQuery xử lý AJAX requests
  - Implement debounce cho search input
  - _Requirements: 3.1, 3.2, 5.3_

- [x] 11. Xây dựng hệ thống thông báo (Notifications)
  - Tạo Model Notification.php
  - Tạo NotificationController.php
  - Tạo ajax/get_notifications.php - lấy thông báo mới
  - Implement logic phát hiện thuốc sắp hết hàng (< 10 units)
  - Implement logic phát hiện lô sắp hết hạn (< 30 days)
  - Hiển thị notification badge trên navbar
  - _Requirements: 5.1, 5.2, 5.4_





- [ ]* 11.1 Viết property test cho notifications
  - **Property 13: Low stock notifications trigger correctly**
  - **Property 14: Expiry warnings trigger within threshold**
  - **Validates: Requirements 5.1, 5.2**

- [x] 12. Tạo Cron Job kiểm tra hết hạn tự động
  - Tạo cron/check_expiry.php - script chạy định kỳ
  - Quét tất cả batches và tạo notifications cho lô sắp hết hạn
  - Viết hướng dẫn cài đặt cron job trên server
  - Test script bằng cách chạy thủ công
  - _Requirements: 5.5_

- [x] 13. Checkpoint - Đảm bảo tất cả tests pass
  - Chạy tất cả property tests đã viết
  - Kiểm tra các chức năng cơ bản hoạt động đúng
  - Fix bugs nếu có
  - Hỏi user nếu có vấn đề phát sinh

- [x] 14. Implement báo cáo doanh thu (Sales Reports)
  - Tạo ReportController.php
  - Tạo views/reports/sales.php - báo cáo doanh thu
  - Implement filter theo date range
  - Hiển thị doanh thu theo ngày/tháng/năm
  - Tính tổng doanh thu cho khoảng thời gian
  - _Requirements: 6.1, 6.2_

- [ ]* 14.1 Viết property test cho sales reports
  - **Property 15: Sales report calculates revenue accurately**
  - **Property 16: Revenue grouping is consistent**
  - **Validates: Requirements 6.1, 6.2**

- [x] 15. Implement báo cáo tồn kho và hết hạn
  - Tạo views/reports/inventory.php - báo cáo tồn kho
  - Hiển thị tồn kho hiện tại của tất cả thuốc
  - Tạo views/reports/expiry.php - báo cáo thuốc sắp hết hạn
  - Hiển thị danh sách lô sắp hết hạn trong 30 ngày
  - Highlight các mục cần chú ý
  - _Requirements: 6.3, 6.4_

- [ ]* 15.1 Viết property test cho inventory reports
  - **Property 17: Inventory report reflects actual stock**
  - **Property 18: Expiry report filters correctly**
  - **Validates: Requirements 6.3, 6.4**

- [x] 16. Implement thống kê thuốc bán chạy
  - Thêm method vào ReportController để tính top-selling medicines
  - Hiển thị chart/table thuốc bán chạy nhất
  - Hiển thị xu hướng bán hàng theo thời gian
  - Có thể dùng Chart.js để vẽ biểu đồ
  - _Requirements: 6.5_

- [ ]* 16.1 Viết property test cho statistics
  - **Property 19: Top-selling medicines are ranked correctly**
  - **Validates: Requirements 6.5**

- [x] 17. Implement quản lý người dùng (User Management)

  - Tạo UserController.php với CRUD operations
  - Tạo views/users/index.php - danh sách người dùng
  - Tạo views/users/create.php - form thêm user
  - Tạo views/users/edit.php - form sửa user


  - Implement role assignment (Staff/Manager)
  - Validate username uniqueness
  - _Requirements: 7.1, 7.5_

- [ ]* 17.1 Viết property test cho user management
  - **Property 20: User creation requires all mandatory fields**
  - **Property 24: Usernames are unique**
  - **Validates: Requirements 7.1, 7.5**

- [x] 18. Implement phân quyền (Authorization)
  - Tạo middleware kiểm tra role trước khi truy cập chức năng
  - Restrict các chức năng Manager-only (user management, reports, batches)
  - Cho phép Staff truy cập sales, medicine search, inventory check
  - Hiển thị menu khác nhau theo role
  - Hiển thị error message khi truy cập trái phép
  - _Requirements: 7.2, 7.3, 7.4_

- [ ]* 18.1 Viết property test cho authorization
  - **Property 21: Staff role has limited permissions**
  - **Property 22: Manager role has full permissions**
  - **Property 23: Unauthorized access is blocked**
  - **Validates: Requirements 7.2, 7.3, 7.4**




- [x] 19. Implement quản lý nhà cung cấp (Suppliers)
  - Tạo Model Supplier.php
  - Tạo SupplierController.php
  - Tạo views/suppliers/index.php - danh sách nhà cung cấp
  - Tạo views/suppliers/create.php - form thêm supplier
  - Tạo views/suppliers/edit.php - form sửa supplier
  - Liên kết supplier với batch khi nhập kho
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [ ]* 19.1 Viết property test cho supplier management
  - **Property 29: Supplier CRUD operations maintain data integrity**
  - **Property 30: Supplier deletion removes record**
  - **Property 31: Supplier list is complete**
  - **Validates: Requirements 9.1, 9.2, 9.3, 9.4**

- [x] 20. Implement sao lưu và khôi phục dữ liệu (Backup/Restore)
  - Tạo BackupController.php
  - Tạo views/backup/index.php - giao diện backup/restore
  - Implement function tạo backup file SQL với timestamp
  - Lưu backup vào thư mục uploads/backups/
  - Implement function restore từ backup file
  - Chỉ cho phép Manager truy cập chức năng này
  - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [ ]* 20.1 Viết property test cho backup/restore
  - **Property 32: Backup creates complete database copy**
  - **Property 33: Backup filename includes timestamp**
  - **Property 34: Backup and restore are inverse operations**
  - **Validates: Requirements 10.1, 10.2, 10.4**

- [x] 21. Hoàn thiện validation và error handling
  - Implement validation cho tất cả forms
  - Hiển thị error messages bằng tiếng Việt
  - Implement try-catch blocks trong controllers
  - Tạo error pages (404, 403, 500)
  - Log errors vào file
  - _Requirements: 11.3_

- [ ]* 21.1 Viết property test cho validation
  - **Property 35: Form validation displays error messages**
  - **Validates: Requirements 11.3**

- [x] 22. Tối ưu giao diện người dùng
  - Đảm bảo tất cả text/labels bằng tiếng Việt
  - Implement consistent navigation menu
  - Thêm success/error notifications sau mỗi action
  - Implement search và filter cho data tables
  - Tối ưu responsive design cho mobile
  - Thêm loading indicators cho AJAX requests
  - _Requirements: 11.1, 11.2, 11.4, 11.5_

- [x] 23. Testing và bug fixes
  - Test toàn bộ user flows (login → bán hàng → báo cáo → logout)
  - Test trên nhiều trình duyệt (Chrome, Firefox, Edge)
  - Test phân quyền Staff vs Manager
  - Test edge cases (số lượng âm, ngày hết hạn quá khứ, etc.)
  - Fix tất cả bugs phát hiện được
  - Optimize database queries nếu cần

- [x] 24. Checkpoint cuối - Đảm bảo tất cả tests pass
  - Chạy toàn bộ test suite
  - Verify tất cả 35 correctness properties
  - Kiểm tra tất cả requirements đã được implement
  - Hỏi user nếu có vấn đề

- [x] 25. Chuẩn bị deployment
  - Viết file README.md với hướng dẫn cài đặt
  - Tạo file SQL để setup database
  - Viết hướng dẫn cấu hình server
  - Viết hướng dẫn setup cron job
  - Tạo file .env.example cho cấu hình
  - Document API endpoints (nếu có)

---

## Ghi chú

- Tasks đánh dấu `*` là optional (tests) - có thể bỏ qua để tập trung vào core features
- Mỗi task nên được test thủ công sau khi hoàn thành
- Commit code sau mỗi task hoàn thành
- Hỏi user nếu gặp vấn đề hoặc cần clarification
