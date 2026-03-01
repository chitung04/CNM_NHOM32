# Requirements Document

## Introduction

Hệ thống quản lý bán thuốc cho nhà thuốc tư nhân là một ứng dụng web được phát triển trên nền tảng PHP theo mô hình kiến trúc MVC, kết hợp với MySQL, AJAX, QR Code và Cron Job. Hệ thống hướng đến mục tiêu tin học hóa các quy trình nghiệp vụ từ nhập kho, quản lý thuốc theo lô và hạn sử dụng, bán lẻ, in hóa đơn, đến thống kê doanh thu và phân quyền người dùng. Thông qua việc tự động hóa và cảnh báo thời gian thực, hệ thống giúp giảm thiểu sai sót thủ công, tiết kiệm thời gian và nâng cao hiệu quả quản lý cho các nhà thuốc tư nhân quy mô vừa và nhỏ.

## Glossary

- **Pharmacy System**: Hệ thống quản lý bán thuốc
- **Medicine**: Thuốc - sản phẩm dược phẩm được quản lý theo lô và hạn sử dụng
- **Batch**: Lô thuốc - nhóm thuốc cùng loại được nhập kho cùng thời điểm với hạn sử dụng cụ thể
- **Invoice**: Hóa đơn - chứng từ ghi nhận giao dịch bán thuốc
- **Inventory**: Tồn kho - số lượng thuốc hiện có trong kho
- **Staff**: Nhân viên - người dùng thực hiện bán hàng và tra cứu
- **Manager**: Quản lý - người dùng có toàn quyền quản trị hệ thống
- **Supplier**: Nhà cung cấp - đơn vị cung cấp thuốc cho nhà thuốc
- **QR Code**: Mã QR - mã định danh cho lô thuốc hoặc hóa đơn
- **Realtime Notification**: Thông báo thời gian thực - cảnh báo tự động không cần tải lại trang

## Requirements

### Requirement 1

**User Story:** Là một Quản lý, tôi muốn quản lý danh mục thuốc, để có thể thêm, sửa, xóa và tra cứu thông tin thuốc một cách chính xác.

#### Acceptance Criteria

1. WHEN a Manager adds a new medicine, THE Pharmacy System SHALL store the medicine information including name, category, unit, price, and description
2. WHEN a Manager updates medicine information, THE Pharmacy System SHALL validate the data and save the changes to the database
3. WHEN a Manager deletes a medicine, THE Pharmacy System SHALL remove the medicine record from the database
4. WHEN a user searches for a medicine by name, THE Pharmacy System SHALL return all matching medicines with their complete information
5. WHEN a user scans a QR code of a medicine, THE Pharmacy System SHALL retrieve and display the medicine details instantly

### Requirement 2

**User Story:** Là một Quản lý, tôi muốn quản lý nhập kho thuốc theo lô, để có thể theo dõi số lượng và hạn sử dụng của từng lô thuốc.

#### Acceptance Criteria

1. WHEN a Manager adds a new batch of medicine, THE Pharmacy System SHALL store the batch information including medicine ID, quantity, expiry date, and import date
2. WHEN a Manager adds a batch, THE Pharmacy System SHALL generate a unique QR code for that batch
3. WHEN a Manager views batch list, THE Pharmacy System SHALL display all batches with medicine name, quantity, expiry date, and status
4. WHEN a batch expiry date is within 30 days, THE Pharmacy System SHALL mark it with a warning indicator
5. THE Pharmacy System SHALL automatically update inventory quantity when a new batch is added

### Requirement 3

**User Story:** Là một Nhân viên, tôi muốn thực hiện quy trình bán hàng, để có thể bán thuốc cho khách hàng một cách nhanh chóng và chính xác.

#### Acceptance Criteria

1. WHEN a Staff adds a medicine to cart by scanning QR code or searching, THE Pharmacy System SHALL verify that sufficient quantity exists in inventory
2. WHEN a Staff adds a medicine to cart, THE Pharmacy System SHALL display the medicine name, price, and allow quantity input
3. WHEN a Staff applies a discount to an invoice, THE Pharmacy System SHALL recalculate the total amount correctly
4. WHEN a Staff completes a sale, THE Pharmacy System SHALL generate an invoice with invoice number, date, items, quantities, prices, discount, and total amount
5. THE Pharmacy System SHALL automatically update inventory quantities when a sale is completed

### Requirement 4

**User Story:** Là một Nhân viên, tôi muốn in hóa đơn cho khách hàng, để cung cấp chứng từ thanh toán hợp lệ.

#### Acceptance Criteria

1. WHEN a Staff completes a sale, THE Pharmacy System SHALL provide an option to print the invoice
2. WHEN a Staff prints an invoice, THE Pharmacy System SHALL generate a printable format with pharmacy information, invoice details, and itemized list
3. WHEN an invoice is printed, THE Pharmacy System SHALL include the QR code of the invoice for tracking purposes
4. WHEN a Staff views invoice history, THE Pharmacy System SHALL allow reprinting previous invoices
5. THE Pharmacy System SHALL format the printed invoice in a clear and professional layout

### Requirement 5

**User Story:** Là một Nhân viên hoặc Quản lý, tôi muốn nhận cảnh báo thời gian thực, để kịp thời xử lý thuốc sắp hết hàng hoặc sắp hết hạn.

#### Acceptance Criteria

1. WHEN inventory quantity of a medicine falls below 10 units, THE Pharmacy System SHALL display a realtime low stock notification without page reload
2. WHEN a medicine batch has less than 30 days until expiry, THE Pharmacy System SHALL display a realtime expiry warning notification
3. WHEN a user receives a notification, THE Pharmacy System SHALL display the notification using AJAX technology without interrupting current work
4. WHEN a user clicks on a notification, THE Pharmacy System SHALL navigate to the relevant medicine or batch details
5. THE Pharmacy System SHALL use Cron Job to automatically check for expiring medicines daily and generate notifications

### Requirement 6

**User Story:** Là một Quản lý, tôi muốn xem báo cáo và thống kê, để theo dõi hiệu quả kinh doanh và tình trạng kho thuốc.

#### Acceptance Criteria

1. WHEN a Manager requests a sales report for a date range, THE Pharmacy System SHALL calculate and display total revenue for that period
2. WHEN a Manager views the sales report, THE Pharmacy System SHALL display revenue grouped by day, month, or year
3. WHEN a Manager views the inventory report, THE Pharmacy System SHALL display current stock levels for all medicines
4. WHEN a Manager views the expiry report, THE Pharmacy System SHALL display all medicine batches that are expiring within 30 days
5. WHEN a Manager views statistics, THE Pharmacy System SHALL show top-selling medicines and sales trends

### Requirement 7

**User Story:** Là một Quản lý, tôi muốn quản lý người dùng và phân quyền, để kiểm soát quyền truy cập vào các chức năng khác nhau của hệ thống.

#### Acceptance Criteria

1. WHEN a Manager creates a new user account, THE Pharmacy System SHALL require username, password, full name, and role assignment
2. WHEN a Manager assigns Staff role to a user, THE Pharmacy System SHALL grant access to sales, medicine search, and inventory check functions only
3. WHEN a Manager assigns Manager role to a user, THE Pharmacy System SHALL grant access to all system functions including user management, reports, and batch management
4. WHEN a user attempts to access a function without proper permissions, THE Pharmacy System SHALL deny access and display an authorization error message
5. THE Pharmacy System SHALL validate that usernames are unique across all user accounts

### Requirement 8

**User Story:** Là một người dùng hệ thống, tôi muốn đăng nhập và đăng xuất an toàn, để bảo vệ thông tin nhà thuốc khỏi truy cập trái phép.

#### Acceptance Criteria

1. WHEN a user attempts to log in with valid credentials, THE Pharmacy System SHALL authenticate the user and create a session
2. WHEN a user attempts to log in with invalid credentials, THE Pharmacy System SHALL reject the login and display an error message
3. WHEN a user logs out, THE Pharmacy System SHALL terminate the session and clear authentication data
4. WHEN a user session is inactive for 30 minutes, THE Pharmacy System SHALL automatically log out the user
5. THE Pharmacy System SHALL store passwords using secure hashing algorithms

### Requirement 9

**User Story:** Là một Quản lý, tôi muốn quản lý nhà cung cấp, để theo dõi nguồn cung cấp thuốc và thông tin liên hệ.

#### Acceptance Criteria

1. WHEN a Manager adds a new supplier, THE Pharmacy System SHALL store supplier information including name, phone number, address, and email
2. WHEN a Manager updates supplier information, THE Pharmacy System SHALL validate and save the changes to the database
3. WHEN a Manager deletes a supplier, THE Pharmacy System SHALL remove the supplier record from the database
4. WHEN a Manager views supplier list, THE Pharmacy System SHALL display all suppliers with their contact information
5. WHEN a Manager adds a medicine batch, THE Pharmacy System SHALL allow selecting the supplier from the supplier list

### Requirement 10

**User Story:** Là một Quản lý, tôi muốn sao lưu và khôi phục dữ liệu, để đảm bảo an toàn dữ liệu trong trường hợp sự cố.

#### Acceptance Criteria

1. WHEN a Manager initiates a backup, THE Pharmacy System SHALL create a complete backup file of the database
2. WHEN a backup is created, THE Pharmacy System SHALL include timestamp in the backup filename
3. WHEN a Manager initiates a restore, THE Pharmacy System SHALL allow selecting a backup file to restore
4. WHEN a restore is completed, THE Pharmacy System SHALL verify data integrity and display a success message
5. THE Pharmacy System SHALL store backup files in a secure location accessible only to Manager role

### Requirement 11

**User Story:** Là một người dùng hệ thống, tôi muốn giao diện thân thiện và dễ sử dụng, để thao tác nhanh chóng và hiệu quả.

#### Acceptance Criteria

1. WHEN a user navigates the system, THE Pharmacy System SHALL provide a consistent navigation menu across all pages
2. WHEN a user performs an action, THE Pharmacy System SHALL display feedback messages indicating success or failure
3. WHEN a user enters invalid data in a form, THE Pharmacy System SHALL display clear validation error messages in Vietnamese
4. WHEN a user views data tables, THE Pharmacy System SHALL provide search and filter capabilities
5. THE Pharmacy System SHALL display all interface text and labels in Vietnamese language
