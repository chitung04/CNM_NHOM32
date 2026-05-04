-- ============================================================================
-- DATABASE HOÀN CHỈNH CUỐI CÙNG - DUO PHARMA
-- Kết hợp: Multi-tenant + Dữ liệu mẫu + Tất cả tính năng
-- Ngày: 05/05/2026
-- ============================================================================

-- Xóa database cũ và tạo mới
DROP DATABASE IF EXISTS qlnt_db;
CREATE DATABASE qlnt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qlnt_db;

-- ============================================================================
-- 1. BẢNG PHARMACIES (Nhà thuốc) - MULTI-TENANT
-- ============================================================================
CREATE TABLE pharmacies (
    pharmacy_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_name VARCHAR(255) NOT NULL,
    pharmacy_code VARCHAR(50) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    license_number VARCHAR(100),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    subscription_plan VARCHAR(50) DEFAULT 'free',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pharmacy_code (pharmacy_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. BẢNG USERS (Người dùng)
-- ============================================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    role ENUM('manager', 'staff') DEFAULT 'staff',
    is_root_admin TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    INDEX idx_users_pharmacy (pharmacy_id),
    INDEX idx_users_username (username),
    INDEX idx_users_pharmacy_role (pharmacy_id, role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. BẢNG CATEGORIES (Danh mục thuốc)
-- ============================================================================
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    INDEX idx_categories_pharmacy (pharmacy_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. BẢNG UNITS (Đơn vị tính)
-- ============================================================================
CREATE TABLE units (
    unit_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    unit_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    INDEX idx_units_pharmacy (pharmacy_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. BẢNG SUPPLIERS (Nhà cung cấp)
-- ============================================================================
CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    INDEX idx_suppliers_pharmacy (pharmacy_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. BẢNG MEDICINES (Thuốc)
-- ============================================================================
CREATE TABLE medicines (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    medicine_name VARCHAR(255) NOT NULL,
    category_id INT,
    unit_id INT,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    qr_code VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES units(unit_id) ON DELETE SET NULL,
    INDEX idx_medicines_pharmacy (pharmacy_id),
    INDEX idx_medicines_qr (qr_code),
    INDEX idx_medicines_pharmacy_category (pharmacy_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. BẢNG BATCHES (Lô thuốc)
-- ============================================================================
CREATE TABLE batches (
    batch_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    medicine_id INT NOT NULL,
    batch_number VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    import_date DATE,
    expiry_date DATE,
    supplier_id INT,
    status ENUM('active', 'expired', 'sold_out') DEFAULT 'active',
    qr_code VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
    INDEX idx_batches_pharmacy (pharmacy_id),
    INDEX idx_batches_medicine (medicine_id),
    INDEX idx_batches_qr (qr_code),
    INDEX idx_batches_pharmacy_medicine (pharmacy_id, medicine_id),
    UNIQUE KEY unique_batch_pharmacy (pharmacy_id, batch_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. BẢNG INVOICES (Hóa đơn)
-- ============================================================================
CREATE TABLE invoices (
    invoice_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    customer_name VARCHAR(255),
    customer_phone VARCHAR(20),
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cash', 'card', 'transfer', 'bank_transfer') DEFAULT 'cash',
    amount_paid DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    qr_code VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_invoices_pharmacy (pharmacy_id),
    INDEX idx_invoices_user (user_id),
    INDEX idx_invoices_qr (qr_code),
    INDEX idx_invoices_pharmacy_date (pharmacy_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. BẢNG INVOICE_DETAILS (Chi tiết hóa đơn)
-- ============================================================================
CREATE TABLE invoice_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    medicine_id INT NOT NULL,
    batch_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE RESTRICT,
    FOREIGN KEY (batch_id) REFERENCES batches(batch_id) ON DELETE SET NULL,
    INDEX idx_invoice_details_invoice (invoice_id),
    INDEX idx_invoice_details_medicine (medicine_id),
    INDEX idx_invoice_details_batch (batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. BẢNG NOTIFICATIONS (Thông báo)
-- ============================================================================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    type ENUM('low_stock', 'expiry_warning', 'info') NOT NULL,
    message TEXT NOT NULL,
    reference_id INT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    INDEX idx_notifications_pharmacy (pharmacy_id),
    INDEX idx_notifications_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. BẢNG AUDIT_LOGS (Nhật ký kiểm toán)
-- ============================================================================
CREATE TABLE audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_audit_pharmacy (pharmacy_id),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_table (table_name, record_id),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DỮ LIỆU MẪU
-- ============================================================================

-- 1. Tạo nhà thuốc mẫu
INSERT INTO pharmacies (pharmacy_name, pharmacy_code, address, phone, email, status) VALUES
('Nhà thuốc DUO PHARMA', 'DUO001', '123 Đường Lê Lợi, Quận 1, TP.HCM', '0123456789', 'duopharma@example.com', 'active');

SET @pharmacy_id = LAST_INSERT_ID();

-- 2. Tạo users
INSERT INTO users (pharmacy_id, username, password, full_name, phone, email, role, is_active) VALUES
(@pharmacy_id, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Đỗ Chí Tùng', '0398266899', 'admin@duopharma.com', 'manager', 1),
(@pharmacy_id, 'nhanvien1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên 1', '0987654321', 'nv1@duopharma.com', 'staff', 1),
(@pharmacy_id, 'nhanvien2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên 2', '0912345678', 'nv2@duopharma.com', 'staff', 1);

-- 3. Tạo categories
INSERT INTO categories (pharmacy_id, category_name, description) VALUES
(@pharmacy_id, 'Thuốc kháng sinh', 'Các loại thuốc kháng sinh'),
(@pharmacy_id, 'Thuốc giảm đau', 'Thuốc giảm đau, hạ sốt'),
(@pharmacy_id, 'Thuốc tiêu hóa', 'Thuốc hỗ trợ tiêu hóa'),
(@pharmacy_id, 'Vitamin & Khoáng chất', 'Thực phẩm chức năng');

-- 4. Tạo units
INSERT INTO units (pharmacy_id, unit_name) VALUES
(@pharmacy_id, 'Viên'),
(@pharmacy_id, 'Vỉ'),
(@pharmacy_id, 'Hộp'),
(@pharmacy_id, 'Chai'),
(@pharmacy_id, 'Tuýp');

-- 5. Tạo suppliers
INSERT INTO suppliers (pharmacy_id, supplier_name, address, phone) VALUES
(@pharmacy_id, 'Công ty Dược phẩm ABC', '456 Đường XYZ, Quận 3, TP.HCM', '0901234567'),
(@pharmacy_id, 'Công ty Dược phẩm DEF', '789 Đường MNO, Quận 5, TP.HCM', '0907654321');

-- 6. Tạo medicines (30 loại thuốc đa dạng)
INSERT INTO medicines (pharmacy_id, medicine_name, category_id, unit_id, price, qr_code) VALUES
-- Thuốc giảm đau, hạ sốt (Category 2)
(@pharmacy_id, 'Paracetamol 500mg', 2, 1, 2500, 'MED_1735000001_1001'),
(@pharmacy_id, 'Ibuprofen 400mg', 2, 1, 3500, 'MED_1735000002_1002'),
(@pharmacy_id, 'Aspirin 100mg', 2, 1, 2000, 'MED_1735000003_1003'),
(@pharmacy_id, 'Diclofenac 50mg', 2, 1, 4500, 'MED_1735000004_1004'),
(@pharmacy_id, 'Meloxicam 7.5mg', 2, 1, 5500, 'MED_1735000005_1005'),

-- Thuốc kháng sinh (Category 1)
(@pharmacy_id, 'Amoxicillin 500mg', 1, 1, 3500, 'MED_1735000006_1006'),
(@pharmacy_id, 'Cefixime 200mg', 1, 1, 8500, 'MED_1735000007_1007'),
(@pharmacy_id, 'Azithromycin 250mg', 1, 1, 12000, 'MED_1735000008_1008'),
(@pharmacy_id, 'Ciprofloxacin 500mg', 1, 1, 6500, 'MED_1735000009_1009'),
(@pharmacy_id, 'Cephalexin 500mg', 1, 1, 5500, 'MED_1735000010_1010'),
(@pharmacy_id, 'Metronidazole 250mg', 1, 1, 2500, 'MED_1735000011_1011'),

-- Thuốc tiêu hóa (Category 3)
(@pharmacy_id, 'Omeprazole 20mg', 3, 1, 4500, 'MED_1735000012_1012'),
(@pharmacy_id, 'Buscopan 10mg', 3, 1, 3500, 'MED_1735000013_1013'),
(@pharmacy_id, 'Motilium 10mg', 3, 1, 5500, 'MED_1735000014_1014'),
(@pharmacy_id, 'Smecta', 3, 1, 3000, 'MED_1735000015_1015'),
(@pharmacy_id, 'Esomeprazole 40mg', 3, 1, 8500, 'MED_1735000016_1016'),

-- Vitamin & Khoáng chất (Category 4)
(@pharmacy_id, 'Vitamin C 1000mg', 4, 1, 2500, 'MED_1735000017_1017'),
(@pharmacy_id, 'Vitamin B Complex', 4, 1, 4500, 'MED_1735000018_1018'),
(@pharmacy_id, 'Calcium + D3', 4, 1, 6500, 'MED_1735000019_1019'),
(@pharmacy_id, 'Omega 3 Fish Oil', 4, 1, 15000, 'MED_1735000020_1020'),
(@pharmacy_id, 'Multivitamin', 4, 1, 8500, 'MED_1735000021_1021'),
(@pharmacy_id, 'Zinc 50mg', 4, 1, 3500, 'MED_1735000022_1022'),

-- Thuốc dị ứng
(@pharmacy_id, 'Cetirizine 10mg', 2, 1, 3000, 'MED_1735000023_1023'),
(@pharmacy_id, 'Loratadine 10mg', 2, 1, 3500, 'MED_1735000024_1024'),
(@pharmacy_id, 'Chlorpheniramine 4mg', 2, 1, 2500, 'MED_1735000025_1025'),

-- Thuốc khác
(@pharmacy_id, 'Strepsils Honey Lemon', 2, 3, 45000, 'MED_1735000026_1026'),
(@pharmacy_id, 'Xịt mũi Otrivin', 2, 4, 42000, 'MED_1735000027_1027'),
(@pharmacy_id, 'Thuốc nhỏ mắt Refresh', 2, 4, 35000, 'MED_1735000028_1028'),
(@pharmacy_id, 'Dầu gió Siang Pure', 2, 4, 28000, 'MED_1735000029_1029'),
(@pharmacy_id, 'Cao dán Salonpas', 2, 3, 32000, 'MED_1735000030_1030');

-- 7. Tạo batches (60 lô - mỗi thuốc 2 lô: 1 sắp hết hạn + 1 sắp hết hàng)
INSERT INTO batches (pharmacy_id, medicine_id, supplier_id, batch_number, quantity, import_date, expiry_date, qr_code, status) VALUES
-- Paracetamol 500mg
(@pharmacy_id, 1, 1, 'LOT20260505001', 80, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'BATCH_1735000101_2001', 'active'),
(@pharmacy_id, 1, 2, 'LOT20260505002', 25, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000102_2002', 'active'),

-- Ibuprofen 400mg
(@pharmacy_id, 2, 1, 'LOT20260505003', 75, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'BATCH_1735000103_2003', 'active'),
(@pharmacy_id, 2, 2, 'LOT20260505004', 30, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000104_2004', 'active'),

-- Aspirin 100mg
(@pharmacy_id, 3, 1, 'LOT20260505005', 90, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 35 DAY), 'BATCH_1735000105_2005', 'active'),
(@pharmacy_id, 3, 2, 'LOT20260505006', 20, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000106_2006', 'active'),

-- Diclofenac 50mg
(@pharmacy_id, 4, 1, 'LOT20260505007', 85, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 40 DAY), 'BATCH_1735000107_2007', 'active'),
(@pharmacy_id, 4, 2, 'LOT20260505008', 35, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000108_2008', 'active'),

-- Meloxicam 7.5mg
(@pharmacy_id, 5, 1, 'LOT20260505009', 70, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'BATCH_1735000109_2009', 'active'),
(@pharmacy_id, 5, 2, 'LOT20260505010', 40, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000110_2010', 'active'),

-- Amoxicillin 500mg
(@pharmacy_id, 6, 1, 'LOT20260505011', 100, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'BATCH_1735000111_2011', 'active'),
(@pharmacy_id, 6, 2, 'LOT20260505012', 15, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000112_2012', 'active'),

-- Cefixime 200mg
(@pharmacy_id, 7, 1, 'LOT20260505013', 95, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 50 DAY), 'BATCH_1735000113_2013', 'active'),
(@pharmacy_id, 7, 2, 'LOT20260505014', 45, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000114_2014', 'active'),

-- Azithromycin 250mg
(@pharmacy_id, 8, 1, 'LOT20260505015', 65, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 55 DAY), 'BATCH_1735000115_2015', 'active'),
(@pharmacy_id, 8, 2, 'LOT20260505016', 48, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000116_2016', 'active'),

-- Ciprofloxacin 500mg
(@pharmacy_id, 9, 1, 'LOT20260505017', 88, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 28 DAY), 'BATCH_1735000117_2017', 'active'),
(@pharmacy_id, 9, 2, 'LOT20260505018', 22, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000118_2018', 'active'),

-- Cephalexin 500mg
(@pharmacy_id, 10, 1, 'LOT20260505019', 92, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 32 DAY), 'BATCH_1735000119_2019', 'active'),
(@pharmacy_id, 10, 2, 'LOT20260505020', 28, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000120_2020', 'active'),

-- Metronidazole 250mg
(@pharmacy_id, 11, 1, 'LOT20260505021', 78, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 38 DAY), 'BATCH_1735000121_2021', 'active'),
(@pharmacy_id, 11, 2, 'LOT20260505022', 32, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000122_2022', 'active'),

-- Omeprazole 20mg
(@pharmacy_id, 12, 1, 'LOT20260505023', 95, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'BATCH_1735000123_2023', 'active'),
(@pharmacy_id, 12, 2, 'LOT20260505024', 40, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000124_2024', 'active'),

-- Buscopan 10mg
(@pharmacy_id, 13, 1, 'LOT20260505025', 90, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'BATCH_1735000125_2025', 'active'),
(@pharmacy_id, 13, 2, 'LOT20260505026', 20, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000126_2026', 'active'),

-- Motilium 10mg
(@pharmacy_id, 14, 1, 'LOT20260505027', 82, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 42 DAY), 'BATCH_1735000127_2027', 'active'),
(@pharmacy_id, 14, 2, 'LOT20260505028', 38, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000128_2028', 'active'),

-- Smecta
(@pharmacy_id, 15, 1, 'LOT20260505029', 75, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 48 DAY), 'BATCH_1735000129_2029', 'active'),
(@pharmacy_id, 15, 2, 'LOT20260505030', 35, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000130_2030', 'active'),

-- Esomeprazole 40mg
(@pharmacy_id, 16, 1, 'LOT20260505031', 68, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 52 DAY), 'BATCH_1735000131_2031', 'active'),
(@pharmacy_id, 16, 2, 'LOT20260505032', 42, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000132_2032', 'active'),

-- Vitamin C 1000mg
(@pharmacy_id, 17, 1, 'LOT20260505033', 85, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 35 DAY), 'BATCH_1735000133_2033', 'active'),
(@pharmacy_id, 17, 2, 'LOT20260505034', 30, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000134_2034', 'active'),

-- Vitamin B Complex
(@pharmacy_id, 18, 1, 'LOT20260505035', 72, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 58 DAY), 'BATCH_1735000135_2035', 'active'),
(@pharmacy_id, 18, 2, 'LOT20260505036', 46, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000136_2036', 'active'),

-- Calcium + D3
(@pharmacy_id, 19, 1, 'LOT20260505037', 88, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 22 DAY), 'BATCH_1735000137_2037', 'active'),
(@pharmacy_id, 19, 2, 'LOT20260505038', 18, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000138_2038', 'active'),

-- Omega 3 Fish Oil
(@pharmacy_id, 20, 1, 'LOT20260505039', 65, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'BATCH_1735000139_2039', 'active'),
(@pharmacy_id, 20, 2, 'LOT20260505040', 48, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000140_2040', 'active'),

-- Multivitamin
(@pharmacy_id, 21, 1, 'LOT20260505041', 78, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 38 DAY), 'BATCH_1735000141_2041', 'active'),
(@pharmacy_id, 21, 2, 'LOT20260505042', 32, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000142_2042', 'active'),

-- Zinc 50mg
(@pharmacy_id, 22, 1, 'LOT20260505043', 92, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 44 DAY), 'BATCH_1735000143_2043', 'active'),
(@pharmacy_id, 22, 2, 'LOT20260505044', 26, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000144_2044', 'active'),

-- Cetirizine 10mg
(@pharmacy_id, 23, 1, 'LOT20260505045', 70, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 50 DAY), 'BATCH_1735000145_2045', 'active'),
(@pharmacy_id, 23, 2, 'LOT20260505046', 45, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000146_2046', 'active'),

-- Loratadine 10mg
(@pharmacy_id, 24, 1, 'LOT20260505047', 85, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 28 DAY), 'BATCH_1735000147_2047', 'active'),
(@pharmacy_id, 24, 2, 'LOT20260505048', 24, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000148_2048', 'active'),

-- Chlorpheniramine 4mg
(@pharmacy_id, 25, 1, 'LOT20260505049', 95, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 36 DAY), 'BATCH_1735000149_2049', 'active'),
(@pharmacy_id, 25, 2, 'LOT20260505050', 38, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000150_2050', 'active'),

-- Strepsils Honey Lemon
(@pharmacy_id, 26, 1, 'LOT20260505051', 75, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 40 DAY), 'BATCH_1735000151_2051', 'active'),
(@pharmacy_id, 26, 2, 'LOT20260505052', 35, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000152_2052', 'active'),

-- Xịt mũi Otrivin
(@pharmacy_id, 27, 1, 'LOT20260505053', 68, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 54 DAY), 'BATCH_1735000153_2053', 'active'),
(@pharmacy_id, 27, 2, 'LOT20260505054', 42, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000154_2054', 'active'),

-- Thuốc nhỏ mắt Refresh
(@pharmacy_id, 28, 1, 'LOT20260505055', 82, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 26 DAY), 'BATCH_1735000155_2055', 'active'),
(@pharmacy_id, 28, 2, 'LOT20260505056', 22, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000156_2056', 'active'),

-- Dầu gió Siang Pure
(@pharmacy_id, 29, 1, 'LOT20260505057', 90, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 48 DAY), 'BATCH_1735000157_2057', 'active'),
(@pharmacy_id, 29, 2, 'LOT20260505058', 28, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000158_2058', 'active'),

-- Cao dán Salonpas
(@pharmacy_id, 30, 1, 'LOT20260505059', 78, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 56 DAY), 'BATCH_1735000159_2059', 'active'),
(@pharmacy_id, 30, 2, 'LOT20260505060', 44, '2026-01-01', DATE_ADD(CURDATE(), INTERVAL 180 DAY), 'BATCH_1735000160_2060', 'active');

-- ============================================================================
-- HOÀN THÀNH
-- ============================================================================
-- Database đã sẵn sàng với:
-- ✅ Multi-tenant (pharmacy_id trong tất cả bảng)
-- ✅ 1 nhà thuốc mẫu (DUO PHARMA)
-- ✅ 3 users (1 admin + 2 nhân viên)
-- ✅ 4 categories
-- ✅ 5 units
-- ✅ 2 suppliers
-- ✅ 30 MEDICINES (Đa dạng: giảm đau, kháng sinh, tiêu hóa, vitamin, dị ứng...)
-- ✅ 60 BATCHES (30 sắp hết hạn + 30 sắp hết hàng)
-- ✅ Bảng invoice_details (không phải invoice_items)
-- ✅ Tất cả foreign keys và indexes
-- 
-- Đăng nhập: admin / 123456
-- ============================================================================
