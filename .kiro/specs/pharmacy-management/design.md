# Design Document - Hệ Thống Quản Lý Bán Thuốc

## Overview

Hệ thống quản lý bán thuốc được thiết kế theo mô hình MVC (Model-View-Controller) sử dụng PHP thuần, MySQL, và các công nghệ hỗ trợ như AJAX, QR Code, và Cron Job. Kiến trúc MVC giúp tách biệt logic nghiệp vụ, giao diện người dùng và xử lý dữ liệu, tạo nền tảng dễ bảo trì và mở rộng.

### Công nghệ sử dụng

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap
- **AJAX**: jQuery AJAX cho cập nhật không đồng bộ
- **QR Code**: PHP QR Code Library (phpqrcode)
- **Automation**: Cron Job cho tác vụ định kỳ
- **Session Management**: PHP Sessions
- **Security**: Password hashing (password_hash/password_verify)

## Architecture

### Mô hình MVC

```
pharmacy-management/
├── index.php                 # Entry point, routing
├── config/
│   ├── database.php         # Cấu hình kết nối database
│   └── config.php           # Cấu hình chung hệ thống
├── models/                  # Model - Xử lý dữ liệu
│   ├── Database.php         # Class kết nối database
│   ├── Medicine.php         # Model quản lý thuốc
│   ├── Batch.php            # Model quản lý lô thuốc
│   ├── Invoice.php          # Model quản lý hóa đơn
│   ├── InvoiceDetail.php    # Model chi tiết hóa đơn
│   ├── User.php             # Model quản lý người dùng
│   ├── Supplier.php         # Model quản lý nhà cung cấp
│   ├── Category.php         # Model quản lý danh mục
│   ├── Unit.php             # Model quản lý đơn vị tính
│   └── Notification.php     # Model quản lý thông báo
├── views/                   # View - Giao diện người dùng
│   ├── layouts/
│   │   ├── header.php       # Header chung
│   │   ├── footer.php       # Footer chung
│   │   ├── sidebar.php      # Sidebar navigation
│   │   └── navbar.php       # Top navigation bar
│   ├── auth/
│   │   ├── login.php        # Trang đăng nhập
│   │   └── logout.php       # Xử lý đăng xuất
│   ├── dashboard/
│   │   └── index.php        # Trang dashboard
│   ├── medicines/
│   │   ├── index.php        # Danh sách thuốc
│   │   ├── create.php       # Thêm thuốc mới
│   │   ├── edit.php         # Sửa thông tin thuốc
│   │   └── view.php         # Xem chi tiết thuốc
│   ├── batches/
│   │   ├── index.php        # Danh sách lô thuốc
│   │   ├── create.php       # Nhập lô mới
│   │   └── view.php         # Xem chi tiết lô
│   ├── sales/
│   │   ├── index.php        # Trang bán hàng
│   │   └── invoice.php      # Xem/In hóa đơn
│   ├── reports/
│   │   ├── sales.php        # Báo cáo doanh thu
│   │   ├── inventory.php    # Báo cáo tồn kho
│   │   └── expiry.php       # Báo cáo thuốc sắp hết hạn
│   ├── users/
│   │   ├── index.php        # Danh sách người dùng
│   │   ├── create.php       # Thêm người dùng
│   │   └── edit.php         # Sửa người dùng
│   ├── suppliers/
│   │   ├── index.php        # Danh sách nhà cung cấp
│   │   ├── create.php       # Thêm nhà cung cấp
│   │   └── edit.php         # Sửa nhà cung cấp
│   └── backup/
│       └── index.php        # Sao lưu/Khôi phục
├── controllers/             # Controller - Xử lý logic
│   ├── AuthController.php   # Xử lý đăng nhập/đăng xuất
│   ├── MedicineController.php
│   ├── BatchController.php
│   ├── SalesController.php
│   ├── InvoiceController.php
│   ├── ReportController.php
│   ├── UserController.php
│   ├── SupplierController.php
│   ├── NotificationController.php
│   └── BackupController.php
├── ajax/                    # AJAX handlers
│   ├── search_medicine.php  # Tìm kiếm thuốc
│   ├── check_stock.php      # Kiểm tra tồn kho
│   ├── get_notifications.php # Lấy thông báo
│   └── add_to_cart.php      # Thêm vào giỏ hàng
├── cron/                    # Cron jobs
│   └── check_expiry.php     # Kiểm tra thuốc sắp hết hạn
├── assets/                  # Tài nguyên tĩnh
│   ├── css/
│   ├── js/
│   ├── images/
│   └── qrcodes/             # Lưu mã QR
├── uploads/                 # File upload
│   └── backups/             # File backup database
└── helpers/                 # Helper functions
    ├── functions.php        # Hàm tiện ích chung
    ├── auth.php             # Hàm xác thực
    └── qrcode.php           # Hàm tạo QR code
```

## Components and Interfaces

### 1. Database Layer (Model)

#### Database.php
Class singleton quản lý kết nối database sử dụng PDO.

```php
class Database {
    private static $instance = null;
    private $connection;
    
    public static function getInstance()
    public function getConnection()
    public function query($sql, $params = [])
    public function execute($sql, $params = [])
}
```

#### Medicine.php
Quản lý thông tin thuốc.

```php
class Medicine {
    public function getAll()
    public function getById($id)
    public function search($keyword)
    public function create($data)
    public function update($id, $data)
    public function delete($id)
    public function getByQRCode($qrcode)
    public function getTotalInventory($medicineId)
}
```

#### Batch.php
Quản lý lô thuốc.

```php
class Batch {
    public function getAll()
    public function getById($id)
    public function getByMedicine($medicineId)
    public function create($data)
    public function update($id, $data)
    public function getExpiringBatches($days = 30)
    public function updateQuantity($id, $quantity)
}
```

#### Invoice.php
Quản lý hóa đơn.

```php
class Invoice {
    public function create($data)
    public function getById($id)
    public function getAll($filters = [])
    public function getByDateRange($startDate, $endDate)
    public function getTotalRevenue($startDate, $endDate)
}
```

#### User.php
Quản lý người dùng và xác thực.

```php
class User {
    public function authenticate($username, $password)
    public function getById($id)
    public function getAll()
    public function create($data)
    public function update($id, $data)
    public function delete($id)
    public function checkPermission($userId, $permission)
}
```

### 2. Controller Layer

Controllers xử lý request từ người dùng, gọi Model để lấy/xử lý dữ liệu, và trả về View.

#### AuthController.php
```php
class AuthController {
    public function login()           // Xử lý đăng nhập
    public function logout()          // Xử lý đăng xuất
    public function checkSession()    // Kiểm tra session
}
```

#### MedicineController.php
```php
class MedicineController {
    public function index()           // Danh sách thuốc
    public function create()          // Form thêm thuốc
    public function store()           // Lưu thuốc mới
    public function edit($id)         // Form sửa thuốc
    public function update($id)       // Cập nhật thuốc
    public function delete($id)       // Xóa thuốc
    public function search()          // Tìm kiếm thuốc
}
```

#### SalesController.php
```php
class SalesController {
    public function index()           // Trang bán hàng
    public function addToCart()       // Thêm vào giỏ
    public function removeFromCart()  // Xóa khỏi giỏ
    public function checkout()        // Thanh toán
    public function printInvoice($id) // In hóa đơn
}
```

### 3. View Layer

Views sử dụng PHP template với HTML, CSS (Bootstrap), và JavaScript.

#### Layout Structure
- **header.php**: Meta tags, CSS links, navigation
- **sidebar.php**: Menu điều hướng theo role
- **footer.php**: Scripts, closing tags
- **navbar.php**: Top bar với thông báo và user info

#### AJAX Integration
Sử dụng jQuery AJAX để:
- Tìm kiếm thuốc realtime
- Cập nhật giỏ hàng không reload
- Hiển thị thông báo
- Kiểm tra tồn kho

```javascript
// Ví dụ: Tìm kiếm thuốc
$('#search-medicine').on('keyup', function() {
    $.ajax({
        url: 'ajax/search_medicine.php',
        method: 'POST',
        data: { keyword: $(this).val() },
        success: function(response) {
            $('#search-results').html(response);
        }
    });
});
```

## Data Models

### Database Schema

#### Table: users
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('staff', 'manager') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);
```

#### Table: categories
```sql
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Table: units
```sql
CREATE TABLE units (
    unit_id INT PRIMARY KEY AUTO_INCREMENT,
    unit_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Table: suppliers
```sql
CREATE TABLE suppliers (
    supplier_id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(200) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Table: medicines
```sql
CREATE TABLE medicines (
    medicine_id INT PRIMARY KEY AUTO_INCREMENT,
    medicine_name VARCHAR(200) NOT NULL,
    category_id INT,
    unit_id INT,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    qr_code VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (unit_id) REFERENCES units(unit_id)
);
```

#### Table: batches
```sql
CREATE TABLE batches (
    batch_id INT PRIMARY KEY AUTO_INCREMENT,
    medicine_id INT NOT NULL,
    supplier_id INT,
    quantity INT NOT NULL,
    expiry_date DATE NOT NULL,
    import_date DATE NOT NULL,
    qr_code VARCHAR(100) UNIQUE,
    status ENUM('active', 'expired', 'sold_out') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
);
```

#### Table: invoices
```sql
CREATE TABLE invoices (
    invoice_id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    qr_code VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

#### Table: invoice_details
```sql
CREATE TABLE invoice_details (
    detail_id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    medicine_id INT NOT NULL,
    batch_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id),
    FOREIGN KEY (batch_id) REFERENCES batches(batch_id)
);
```

#### Table: notifications
```sql
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('low_stock', 'expiry_warning') NOT NULL,
    message TEXT NOT NULL,
    reference_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Entity Relationships

```
users (1) ----< (N) invoices
medicines (1) ----< (N) batches
medicines (1) ----< (N) invoice_details
batches (1) ----< (N) invoice_details
invoices (1) ----< (N) invoice_details
suppliers (1) ----< (N) batches
categories (1) ----< (N) medicines
units (1) ----< (N) medicines
```



## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Property 1: Medicine CRUD operations preserve data integrity

*For any* medicine with valid information (name, category, unit, price, description), when added to the system, the retrieved medicine data should match the original input exactly.

**Validates: Requirements 1.1, 1.2**

### Property 2: Medicine deletion removes record completely

*For any* medicine in the system, after deletion, querying for that medicine by ID should return no results.

**Validates: Requirements 1.3**

### Property 3: Medicine search returns all matching results

*For any* search keyword, all returned medicines should contain the keyword in their name, and no medicine containing the keyword should be excluded from results.

**Validates: Requirements 1.4**

### Property 4: QR code retrieval returns correct medicine

*For any* medicine with a QR code, scanning that QR code should retrieve the exact medicine with all its details intact.

**Validates: Requirements 1.5**

### Property 5: Batch creation updates inventory correctly

*For any* medicine, when a new batch is added with quantity Q, the total inventory for that medicine should increase by exactly Q units.

**Validates: Requirements 2.1, 2.5**

### Property 6: QR codes are unique across all batches

*For any* two different batches in the system, their QR codes must be different.

**Validates: Requirements 2.2**

### Property 7: Batch list contains complete information

*For any* batch in the system, when viewing the batch list, the displayed information should include medicine name, quantity, expiry date, and status.

**Validates: Requirements 2.3**

### Property 8: Inventory verification prevents overselling

*For any* medicine with inventory quantity Q, attempting to add more than Q units to cart should be rejected by the system.

**Validates: Requirements 3.1**

### Property 9: Discount calculation is accurate

*For any* invoice with total amount T and discount D (where 0 ≤ D ≤ T), the final amount should equal T - D.

**Validates: Requirements 3.3**

### Property 10: Invoice generation includes all required fields

*For any* completed sale, the generated invoice should contain invoice number, date, all items with quantities and prices, discount amount, and total amount.

**Validates: Requirements 3.4**

### Property 11: Sales update inventory atomically

*For any* sale with N items each with quantity Qi, after sale completion, the inventory for each item i should decrease by exactly Qi units.

**Validates: Requirements 3.5**

### Property 12: Printed invoice contains complete information

*For any* invoice, the printable format should include pharmacy information, invoice details, itemized list, and a unique QR code.

**Validates: Requirements 4.2, 4.3**

### Property 13: Low stock notifications trigger correctly

*For any* medicine, when its inventory quantity falls below 10 units, a low stock notification should be generated.

**Validates: Requirements 5.1**

### Property 14: Expiry warnings trigger within threshold

*For any* batch with expiry date within 30 days from current date, an expiry warning notification should be generated.

**Validates: Requirements 5.2**

### Property 15: Sales report calculates revenue accurately

*For any* date range, the total revenue should equal the sum of final amounts of all invoices within that date range.

**Validates: Requirements 6.1**

### Property 16: Revenue grouping is consistent

*For any* set of invoices, when grouped by day/month/year, each invoice should appear in exactly one group based on its creation date.

**Validates: Requirements 6.2**

### Property 17: Inventory report reflects actual stock

*For any* medicine, the inventory report should show the sum of quantities across all active batches for that medicine.

**Validates: Requirements 6.3**

### Property 18: Expiry report filters correctly

*For any* batch in the expiry report, its expiry date should be within 30 days from the current date, and no batch expiring within 30 days should be excluded.

**Validates: Requirements 6.4**

### Property 19: Top-selling medicines are ranked correctly

*For any* two medicines A and B in the statistics, if A sold more units than B, then A should rank higher than B in the top-selling list.

**Validates: Requirements 6.5**

### Property 20: User creation requires all mandatory fields

*For any* user creation attempt, if any of username, password, full name, or role is missing, the creation should be rejected.

**Validates: Requirements 7.1**

### Property 21: Staff role has limited permissions

*For any* user with Staff role, access to user management, reports, and batch management functions should be denied.

**Validates: Requirements 7.2**

### Property 22: Manager role has full permissions

*For any* user with Manager role, access to all system functions including user management, reports, and batch management should be granted.

**Validates: Requirements 7.3**

### Property 23: Unauthorized access is blocked

*For any* user attempting to access a function without proper permissions, the access should be denied and an authorization error should be returned.

**Validates: Requirements 7.4**

### Property 24: Usernames are unique

*For any* two different users in the system, their usernames must be different.

**Validates: Requirements 7.5**

### Property 25: Valid credentials authenticate successfully

*For any* user with valid username and password, login attempt should succeed and create a valid session.

**Validates: Requirements 8.1**

### Property 26: Invalid credentials are rejected

*For any* login attempt with incorrect username or password, the authentication should fail and return an error message.

**Validates: Requirements 8.2**

### Property 27: Logout clears session data

*For any* authenticated user, after logout, the session should be terminated and subsequent requests should require re-authentication.

**Validates: Requirements 8.3**

### Property 28: Passwords are stored securely

*For any* user in the database, the stored password should be a hash (not plain text) and should verify correctly against the original password.

**Validates: Requirements 8.5**

### Property 29: Supplier CRUD operations maintain data integrity

*For any* supplier with valid information (name, phone, address, email), when added or updated, the retrieved supplier data should match the input.

**Validates: Requirements 9.1, 9.2**

### Property 30: Supplier deletion removes record

*For any* supplier in the system, after deletion, querying for that supplier should return no results.

**Validates: Requirements 9.3**

### Property 31: Supplier list is complete

*For any* supplier in the database, it should appear in the supplier list with all contact information.

**Validates: Requirements 9.4**

### Property 32: Backup creates complete database copy

*For any* database state, creating a backup should produce a file containing all current data.

**Validates: Requirements 10.1**

### Property 33: Backup filename includes timestamp

*For any* backup file created, the filename should contain a timestamp indicating when the backup was created.

**Validates: Requirements 10.2**

### Property 34: Backup and restore are inverse operations

*For any* database state S, creating a backup then restoring it should result in the same state S (round-trip property).

**Validates: Requirements 10.4**

### Property 35: Form validation displays error messages

*For any* form submission with invalid data, the system should reject the submission and display a validation error message in Vietnamese.

**Validates: Requirements 11.3**



## Error Handling

### Error Categories

#### 1. Validation Errors
- **Invalid Input**: Dữ liệu không hợp lệ (giá âm, số lượng âm, ngày hết hạn trong quá khứ)
- **Missing Required Fields**: Thiếu trường bắt buộc
- **Format Errors**: Sai định dạng (email, số điện thoại)

**Handling**: Hiển thị thông báo lỗi rõ ràng bằng tiếng Việt, giữ lại dữ liệu đã nhập, focus vào trường lỗi.

#### 2. Business Logic Errors
- **Insufficient Stock**: Không đủ hàng trong kho
- **Duplicate Entry**: Trùng lặp username, QR code
- **Invalid Operation**: Xóa thuốc đang có trong hóa đơn

**Handling**: Hiển thị thông báo cụ thể về lý do lỗi, đề xuất hành động khắc phục.

#### 3. Authentication/Authorization Errors
- **Invalid Credentials**: Sai username/password
- **Session Expired**: Hết phiên làm việc
- **Insufficient Permissions**: Không đủ quyền truy cập

**Handling**: Chuyển hướng đến trang đăng nhập, hiển thị thông báo lỗi phân quyền.

#### 4. Database Errors
- **Connection Failed**: Không kết nối được database
- **Query Error**: Lỗi truy vấn SQL
- **Constraint Violation**: Vi phạm ràng buộc (foreign key, unique)

**Handling**: Log lỗi chi tiết, hiển thị thông báo chung cho người dùng, rollback transaction nếu cần.

#### 5. System Errors
- **File Upload Failed**: Lỗi upload file backup
- **QR Code Generation Failed**: Lỗi tạo mã QR
- **Email/Notification Failed**: Lỗi gửi thông báo

**Handling**: Log lỗi, thử lại tự động nếu có thể, thông báo cho admin.

### Error Response Format

```php
// JSON response cho AJAX requests
{
    "success": false,
    "error": {
        "code": "INSUFFICIENT_STOCK",
        "message": "Không đủ hàng trong kho. Tồn kho hiện tại: 5, yêu cầu: 10",
        "field": "quantity"
    }
}

// HTML response cho form submissions
// Hiển thị alert/notification với class CSS phù hợp
```

### Exception Handling Strategy

```php
try {
    // Business logic
    $result = $model->performOperation();
    
} catch (ValidationException $e) {
    // Validation errors
    return $this->error($e->getMessage(), $e->getErrors());
    
} catch (InsufficientStockException $e) {
    // Business logic errors
    return $this->error($e->getMessage());
    
} catch (PDOException $e) {
    // Database errors
    $this->logError($e);
    return $this->error("Lỗi hệ thống. Vui lòng thử lại sau.");
    
} catch (Exception $e) {
    // Unexpected errors
    $this->logError($e);
    return $this->error("Đã xảy ra lỗi không mong muốn.");
}
```

## Testing Strategy

### Unit Testing

Sử dụng PHPUnit để test các Model và Controller methods.

**Test Coverage**:
- Model methods: CRUD operations, business logic calculations
- Validation functions: Input validation, data format checking
- Helper functions: QR code generation, date calculations
- Authentication: Login, logout, session management

**Example Unit Tests**:
```php
// Test medicine creation
public function testCreateMedicine() {
    $medicine = new Medicine();
    $data = [
        'medicine_name' => 'Paracetamol',
        'category_id' => 1,
        'unit_id' => 1,
        'price' => 5000
    ];
    $result = $medicine->create($data);
    $this->assertTrue($result);
}

// Test discount calculation
public function testDiscountCalculation() {
    $invoice = new Invoice();
    $total = 100000;
    $discount = 10000;
    $final = $invoice->calculateFinalAmount($total, $discount);
    $this->assertEquals(90000, $final);
}

// Test inventory update
public function testInventoryUpdateAfterSale() {
    $medicine = new Medicine();
    $initialStock = $medicine->getTotalInventory(1);
    // Perform sale of 5 units
    $sale->completeSale(['medicine_id' => 1, 'quantity' => 5]);
    $finalStock = $medicine->getTotalInventory(1);
    $this->assertEquals($initialStock - 5, $finalStock);
}
```

### Property-Based Testing

Sử dụng **PHPUnit với Data Providers** để test properties với nhiều input khác nhau.

**Configuration**: Mỗi property test chạy tối thiểu 100 iterations với dữ liệu ngẫu nhiên.

**Property Test Examples**:

```php
/**
 * Property 5: Batch creation updates inventory correctly
 * Feature: pharmacy-management, Property 5
 * Validates: Requirements 2.1, 2.5
 * 
 * @dataProvider batchDataProvider
 */
public function testBatchCreationUpdatesInventory($medicineId, $quantity) {
    $medicine = new Medicine();
    $batch = new Batch();
    
    $initialInventory = $medicine->getTotalInventory($medicineId);
    
    $batch->create([
        'medicine_id' => $medicineId,
        'quantity' => $quantity,
        'expiry_date' => date('Y-m-d', strtotime('+1 year')),
        'import_date' => date('Y-m-d')
    ]);
    
    $finalInventory = $medicine->getTotalInventory($medicineId);
    
    $this->assertEquals($initialInventory + $quantity, $finalInventory);
}

public function batchDataProvider() {
    // Generate 100 random test cases
    $cases = [];
    for ($i = 0; $i < 100; $i++) {
        $cases[] = [
            rand(1, 50),      // medicine_id
            rand(10, 1000)    // quantity
        ];
    }
    return $cases;
}

/**
 * Property 11: Sales update inventory atomically
 * Feature: pharmacy-management, Property 11
 * Validates: Requirements 3.5
 * 
 * @dataProvider salesDataProvider
 */
public function testSalesUpdateInventoryAtomically($items) {
    $medicine = new Medicine();
    $sales = new SalesController();
    
    // Record initial inventory for all items
    $initialInventories = [];
    foreach ($items as $item) {
        $initialInventories[$item['medicine_id']] = 
            $medicine->getTotalInventory($item['medicine_id']);
    }
    
    // Complete sale
    $sales->checkout($items);
    
    // Verify inventory decreased by exact quantities
    foreach ($items as $item) {
        $finalInventory = $medicine->getTotalInventory($item['medicine_id']);
        $expected = $initialInventories[$item['medicine_id']] - $item['quantity'];
        $this->assertEquals($expected, $finalInventory);
    }
}

/**
 * Property 34: Backup and restore are inverse operations
 * Feature: pharmacy-management, Property 34
 * Validates: Requirements 10.4
 */
public function testBackupRestoreRoundTrip() {
    $backup = new BackupController();
    
    // Get current database state
    $originalData = $this->getDatabaseSnapshot();
    
    // Create backup
    $backupFile = $backup->createBackup();
    
    // Modify database
    $this->modifyDatabase();
    
    // Restore from backup
    $backup->restore($backupFile);
    
    // Verify database matches original state
    $restoredData = $this->getDatabaseSnapshot();
    $this->assertEquals($originalData, $restoredData);
}
```

### Integration Testing

Test tương tác giữa các components:
- Controller → Model → Database
- AJAX requests → Server response
- QR Code generation → Scanning → Data retrieval
- Cron Job execution → Notification generation

### Manual Testing Checklist

- [ ] Giao diện hiển thị đúng trên các trình duyệt (Chrome, Firefox, Edge)
- [ ] Responsive design hoạt động trên mobile
- [ ] In hóa đơn hiển thị đúng định dạng
- [ ] QR code có thể quét được bằng thiết bị thực
- [ ] Thông báo realtime hiển thị không cần reload
- [ ] Session timeout hoạt động sau 30 phút
- [ ] Phân quyền chặn đúng các chức năng theo role

## Security Considerations

### 1. Authentication & Authorization
- Passwords được hash bằng `password_hash()` với BCRYPT
- Session management với secure flags
- Role-based access control (RBAC)
- Automatic session timeout sau 30 phút

### 2. Input Validation & Sanitization
- Validate tất cả input từ user
- Sử dụng prepared statements để prevent SQL injection
- Escape output để prevent XSS
- CSRF token cho các form quan trọng

### 3. Database Security
- Sử dụng PDO với prepared statements
- Principle of least privilege cho database user
- Regular backups
- Encrypted connection nếu database ở remote server

### 4. File Security
- Validate file types cho uploads
- Store backups ngoài web root
- Restrict direct access đến sensitive files
- Proper file permissions

### 5. API Security (AJAX endpoints)
- Verify session cho mọi AJAX request
- Rate limiting để prevent abuse
- Validate và sanitize JSON input
- Return appropriate HTTP status codes

## Deployment Considerations

### Server Requirements
- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn
- Apache/Nginx web server
- mod_rewrite enabled (Apache)
- PHP extensions: PDO, GD (cho QR code), mbstring

### Configuration
- Set proper timezone trong php.ini
- Configure session settings
- Set upload_max_filesize cho backups
- Enable error logging (disable display_errors trong production)

### Cron Job Setup
```bash
# Chạy check expiry mỗi ngày lúc 00:00
0 0 * * * /usr/bin/php /path/to/pharmacy-management/cron/check_expiry.php
```

### Database Setup
1. Tạo database và user
2. Import schema từ file SQL
3. Tạo admin user mặc định
4. Set proper permissions

### Initial Data
- Categories: Thuốc kháng sinh, Thuốc giảm đau, Vitamin, etc.
- Units: Viên, Hộp, Chai, Tuýp, etc.
- Default admin account

## Future Enhancements

### Phase 2 Features
- Quản lý khách hàng và lịch sử mua hàng
- Tích hợp thanh toán online
- Mobile app cho nhân viên
- Báo cáo nâng cao với charts
- Export báo cáo ra Excel/PDF
- Email notifications
- Multi-pharmacy support

### Technical Improvements
- Migrate to Laravel framework
- Implement REST API
- Add caching layer (Redis)
- Implement queue system cho background jobs
- Add automated testing CI/CD
- Implement logging system (Monolog)
- Add search engine (Elasticsearch)

