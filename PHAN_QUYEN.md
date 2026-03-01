# Hệ Thống Phân Quyền - Quản Lý Nhà Thuốc

## Tổng Quan

Hệ thống sử dụng phân quyền dựa trên vai trò (Role-Based Access Control - RBAC) với 2 vai trò chính:
- **Staff (Nhân viên)**: Vai trò cơ bản cho nhân viên bán hàng
- **Manager (Quản lý)**: Vai trò quản trị với đầy đủ quyền

## Cấu Trúc Phân Quyền

### 1. Vai Trò (Roles)

#### Staff (Nhân viên)
- Tập trung vào nghiệp vụ bán hàng
- Không thể thay đổi dữ liệu master (thuốc, lô hàng, nhà cung cấp)
- Không thể xem báo cáo quản lý
- Không thể quản lý người dùng

#### Manager (Quản lý)
- Có tất cả quyền của Staff
- Quản lý toàn bộ hệ thống
- Xem tất cả báo cáo
- Quản lý người dùng và phân quyền

### 2. Quyền (Permissions)

Quyền được định nghĩa theo format: `module.action`

#### Dashboard
- `dashboard.view` - Xem trang chủ
  - Staff: ✅ Có
  - Manager: ✅ Có

#### Bán Hàng (Sales)
- `sales.view` - Xem giao diện bán hàng
  - Staff: ✅ Có
  - Manager: ✅ Có
- `sales.create` - Tạo đơn hàng
  - Staff: ✅ Có
  - Manager: ✅ Có
- `sales.invoice` - Xem và in hóa đơn
  - Staff: ✅ Có
  - Manager: ✅ Có

#### Quản Lý Thuốc (Medicines)
- `medicines.view` - Xem danh sách thuốc
  - Staff: ✅ Có
  - Manager: ✅ Có
- `medicines.create` - Thêm thuốc mới
  - Staff: ❌ Không
  - Manager: ✅ Có
- `medicines.edit` - Sửa thông tin thuốc
  - Staff: ❌ Không
  - Manager: ✅ Có
- `medicines.delete` - Xóa thuốc
  - Staff: ❌ Không
  - Manager: ✅ Có

#### Quản Lý Lô Thuốc (Batches)
- `batches.view` - Xem lô thuốc
  - Staff: ❌ Không
  - Manager: ✅ Có
- `batches.create` - Nhập lô mới
  - Staff: ❌ Không
  - Manager: ✅ Có
- `batches.edit` - Sửa lô thuốc
  - Staff: ❌ Không
  - Manager: ✅ Có
- `batches.delete` - Xóa lô thuốc
  - Staff: ❌ Không
  - Manager: ✅ Có

#### Nhà Cung Cấp (Suppliers)
- `suppliers.view` - Xem nhà cung cấp
  - Staff: ❌ Không
  - Manager: ✅ Có
- `suppliers.create` - Thêm nhà cung cấp
  - Staff: ❌ Không
  - Manager: ✅ Có
- `suppliers.edit` - Sửa nhà cung cấp
  - Staff: ❌ Không
  - Manager: ✅ Có
- `suppliers.delete` - Xóa nhà cung cấp
  - Staff: ❌ Không
  - Manager: ✅ Có

#### Báo Cáo (Reports)
- `reports.sales` - Báo cáo doanh thu
  - Staff: ❌ Không
  - Manager: ✅ Có
- `reports.inventory` - Báo cáo tồn kho
  - Staff: ❌ Không
  - Manager: ✅ Có
- `reports.expiry` - Báo cáo hết hạn
  - Staff: ❌ Không
  - Manager: ✅ Có
- `reports.topSelling` - Thống kê bán chạy
  - Staff: ❌ Không
  - Manager: ✅ Có

#### Quản Lý Người Dùng (Users)
- `users.view` - Xem người dùng
  - Staff: ❌ Không
  - Manager: ✅ Có
- `users.create` - Thêm người dùng
  - Staff: ❌ Không
  - Manager: ✅ Có
- `users.edit` - Sửa người dùng
  - Staff: ❌ Không
  - Manager: ✅ Có
- `users.delete` - Xóa người dùng
  - Staff: ❌ Không
  - Manager: ✅ Có

#### Sao Lưu (Backup)
- `backup.view` - Xem backup
  - Staff: ❌ Không
  - Manager: ✅ Có
- `backup.create` - Tạo backup
  - Staff: ❌ Không
  - Manager: ✅ Có
- `backup.restore` - Khôi phục
  - Staff: ❌ Không
  - Manager: ✅ Có
- `backup.download` - Tải backup
  - Staff: ❌ Không
  - Manager: ✅ Có
- `backup.delete` - Xóa backup
  - Staff: ❌ Không
  - Manager: ✅ Có

## Cách Sử Dụng

### 1. Kiểm Tra Quyền Trong Code

```php
// Kiểm tra quyền cụ thể
if (userHasPermission('medicines.create')) {
    // Cho phép thêm thuốc
}

// Yêu cầu quyền (redirect nếu không có)
requirePermission('reports.sales');

// Kiểm tra nhiều quyền
if (userHasAnyPermission(['medicines.edit', 'medicines.delete'])) {
    // Có ít nhất một quyền
}
```

### 2. Kiểm Tra Quyền Trong View

```php
<?php if (userHasPermission('medicines.create')): ?>
    <a href="index.php?page=medicines&action=create" class="btn btn-primary">
        Thêm thuốc mới
    </a>
<?php endif; ?>
```

### 3. Bảo Vệ Controller Actions

```php
// Trong controller
public function create() {
    requirePermission('medicines.create');
    // Logic tạo thuốc
}
```

### 4. Xem Quyền Của User

Người dùng có thể xem quyền của mình tại:
- Menu User → "Quyền của tôi"
- URL: `index.php?page=profile&action=permissions`

### 5. Quản Lý Vai Trò (Chỉ Manager)

Manager có thể xem ma trận quyền chi tiết tại:
- Menu "Vai trò & Quyền"
- URL: `index.php?page=admin&action=roles`

## Files Liên Quan

### Core Files
- `helpers/auth.php` - Functions authentication cơ bản
- `helpers/permissions.php` - Hệ thống phân quyền chi tiết
- `views/errors/403.php` - Trang lỗi không có quyền

### View Files
- `views/profile/permissions.php` - Xem quyền của user
- `views/admin/roles.php` - Quản lý vai trò (Manager only)

## Thêm Quyền Mới

Để thêm quyền mới, chỉnh sửa file `helpers/permissions.php`:

```php
define('PERMISSIONS', [
    // ... existing permissions
    
    // Thêm quyền mới
    'new_module.action' => ['staff', 'manager'], // Cả 2 role
    'new_module.admin' => ['manager'],           // Chỉ manager
]);
```

Sau đó thêm mô tả:

```php
function getPermissionDescription($permission) {
    $descriptions = [
        // ... existing descriptions
        'new_module.action' => 'Mô tả quyền mới',
    ];
    // ...
}
```

## Thay Đổi Vai Trò User

### Cách 1: Qua Giao Diện
1. Đăng nhập với tài khoản Manager
2. Vào "Quản lý người dùng"
3. Chọn user cần thay đổi
4. Nhấn "Sửa"
5. Chọn vai trò mới
6. Lưu

### Cách 2: Qua Database
```sql
UPDATE users 
SET role = 'manager' 
WHERE username = 'username';
```

**Lưu ý:** User cần đăng xuất và đăng nhập lại để quyền mới có hiệu lực.

## Bảo Mật

### Các Biện Pháp Đã Implement
1. ✅ Kiểm tra quyền ở mọi controller action
2. ✅ Hiển thị trang 403 khi không có quyền
3. ✅ Ẩn menu/button cho chức năng không có quyền
4. ✅ Log tất cả hành động quan trọng
5. ✅ Session timeout tự động
6. ✅ Không cho phép privilege escalation

### Best Practices
1. Luôn kiểm tra quyền ở cả frontend và backend
2. Không tin tưởng input từ client
3. Log tất cả thay đổi về quyền
4. Review quyền định kỳ
5. Principle of least privilege (quyền tối thiểu cần thiết)

## Troubleshooting

### User không thấy menu/chức năng
1. Kiểm tra vai trò trong database
2. Đảm bảo user đã logout/login lại
3. Kiểm tra session có đúng role không
4. Xem log để debug

### Lỗi 403 không mong muốn
1. Kiểm tra quyền trong `helpers/permissions.php`
2. Verify role của user
3. Kiểm tra logic trong controller
4. Xem error log

### Thêm quyền không có hiệu lực
1. Clear PHP opcache nếu có
2. Restart web server
3. Clear browser cache
4. Logout/login lại

## Testing

### Test Cases Cần Kiểm Tra
1. ✅ Staff không thể truy cập chức năng Manager
2. ✅ Manager có thể truy cập tất cả chức năng
3. ✅ Trang 403 hiển thị đúng khi không có quyền
4. ✅ Menu hiển thị đúng theo role
5. ✅ Thay đổi role có hiệu lực sau login
6. ✅ Session timeout hoạt động đúng

## Changelog

### Version 1.0 (Current)
- Implement RBAC với 2 roles: Staff và Manager
- 30+ permissions được định nghĩa
- Trang xem quyền cho user
- Trang quản lý vai trò cho admin
- Trang 403 với thông tin chi tiết
- Full documentation

## Liên Hệ

Nếu có câu hỏi về hệ thống phân quyền, vui lòng liên hệ team phát triển.
