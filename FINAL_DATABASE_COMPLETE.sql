-- ============================================================================
-- DATABASE HOÀN CHỈNH - DUO PHARMA
-- Bao gồm: Schema + Dữ liệu mẫu + Multi-tenant
-- Ngày: 04/05/2026
-- ============================================================================

-- Xóa database cũ và tạo mới
DROP DATABASE IF EXISTS qlnt_db;
CREATE DATABASE qlnt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qlnt_db;

-- ============================================================================
-- 1. BẢNG PHARMACIES (Nhà thuốc)
-- ============================================================================
CREATE TABLE pharmacies (
    pharmacy_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_name VARCHAR(255) NOT NULL,
    pharmacy_code VARCHAR(50) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    subscription_plan VARCHAR(50) DEFAULT 'free',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    INDEX idx_users_username (username)
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
    INDEX idx_medicines_qr (qr_code)
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
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cash', 'card', 'transfer') DEFAULT 'cash',
    status ENUM('completed', 'cancelled') DEFAULT 'completed',
    qr_code VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_invoices_pharmacy (pharmacy_id),
    INDEX idx_invoices_user (user_id),
    INDEX idx_invoices_qr (qr_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. BẢNG INVOICE_ITEMS (Chi tiết hóa đơn)
-- ============================================================================
CREATE TABLE invoice_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    medicine_id INT NOT NULL,
    batch_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE RESTRICT,
    FOREIGN KEY (batch_id) REFERENCES batches(batch_id) ON DELETE SET NULL,
    INDEX idx_invoice_items_invoice (invoice_id)
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
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DỮ LIỆU MẪU
-- ============================================================================

-- Pharmacy 1: DUO PHARMA
INSERT INTO pharmacies (pharmacy_name, pharmacy_code, address, phone, email, status) VALUES
('DUO PHARMA', 'PH001', '123 Đường Láng, Hà Nội', '0901234567', 'contact@duopharma.com', 'active');

-- Users cho Pharmacy 1
INSERT INTO users (pharmacy_id, username, password, full_name, phone, email, role, is_root_admin, is_active) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản lý DUO PHARMA', '0901234567', 'admin@duopharma.com', 'manager', 1, 1),
(1, 'nhanvien1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên 1', '0987654321', 'nv1@duopharma.com', 'staff', 0, 1);

-- Categories
INSERT INTO categories (pharmacy_id, category_name, description) VALUES
(1, 'Thuốc kháng sinh', 'Thuốc điều trị nhiễm khuẩn'),
(1, 'Thuốc giảm đau', 'Thuốc giảm đau, hạ sốt'),
(1, 'Thuốc hạ sốt', 'Thuốc hạ sốt'),
(1, 'Vitamin & Khoáng chất', 'Bổ sung vitamin'),
(1, 'Thuốc tiêu hóa', 'Thuốc điều trị tiêu hóa'),
(1, 'Thuốc tim mạch', 'Thuốc điều trị tim mạch'),
(1, 'Thuốc da liễu', 'Thuốc điều trị da'),
(1, 'Thực phẩm chức năng', 'TPCN');

-- Units
INSERT INTO units (pharmacy_id, unit_name) VALUES
(1, 'Viên'), (1, 'Vỉ'), (1, 'Hộp'), (1, 'Chai'), 
(1, 'Tuýp'), (1, 'Gói'), (1, 'Ống'), (1, 'Lọ');

-- Suppliers
INSERT INTO suppliers (pharmacy_id, supplier_name, address, phone, email) VALUES
(1, 'Công ty Dược phẩm Hà Nội', '123 Đường Láng, Hà Nội', '0901234567', 'contact@pharma-hn.com'),
(1, 'Công ty Dược phẩm Sài Gòn', '456 Nguyễn Huệ, TP.HCM', '0907654321', 'info@pharma-sg.com'),
(1, 'Công ty Dược phẩm Trung Ương', '789 Trần Hưng Đạo, Hà Nội', '0912345678', 'sales@pharma-central.com');

-- Medicines (10 thuốc mẫu)
INSERT INTO medicines (pharmacy_id, medicine_name, category_id, unit_id, price, description, qr_code) VALUES
(1, 'Amoxicillin 500mg', 1, 1, 5000, 'Kháng sinh điều trị nhiễm khuẩn đường hô hấp', 'MED_1746000001_1001'),
(1, 'Paracetamol 500mg', 2, 1, 3000, 'Thuốc giảm đau, hạ sốt hiệu quả', 'MED_1746000002_1002'),
(1, 'Ibuprofen 400mg', 2, 1, 4000, 'Thuốc giảm đau, chống viêm', 'MED_1746000003_1003'),
(1, 'Amlodipine 5mg', 6, 1, 8000, 'Thuốc điều trị tăng huyết áp', 'MED_1746000004_1004'),
(1, 'Metformin 500mg', 5, 1, 6000, 'Thuốc điều trị tiểu đường type 2', 'MED_1746000005_1005'),
(1, 'Vitamin C 1000mg', 4, 1, 2000, 'Bổ sung vitamin C, tăng sức đề kháng', 'MED_1746000006_1006'),
(1, 'Cefixime 200mg', 1, 1, 12000, 'Kháng sinh thế hệ 3 điều trị nhiễm khuẩn', 'MED_1746000007_1007'),
(1, 'Aspirin 100mg', 6, 1, 3500, 'Thuốc chống đông máu, phòng ngừa tai biến', 'MED_1746000008_1008'),
(1, 'Omeprazole 20mg', 5, 1, 7000, 'Thuốc điều trị loét dạ dày, trào ngược', 'MED_1746000009_1009'),
(1, 'Cetirizine 10mg', 7, 1, 2500, 'Thuốc chống dị ứng, viêm mũi', 'MED_1746000010_1010');

-- Batches (2 lô cho mỗi thuốc = 20 lô)
INSERT INTO batches (pharmacy_id, medicine_id, batch_number, quantity, import_date, expiry_date, supplier_id, status, qr_code) VALUES
(1, 1, 'BATCH_P1_0001', 150, '2026-05-01', '2027-05-01', 1, 'active', 'BATCH_1746000011_2001'),
(1, 1, 'BATCH_P1_0002', 200, '2026-05-01', '2027-06-01', 1, 'active', 'BATCH_1746000012_2002'),
(1, 2, 'BATCH_P1_0003', 300, '2026-05-01', '2027-07-01', 2, 'active', 'BATCH_1746000013_2003'),
(1, 2, 'BATCH_P1_0004', 250, '2026-05-01', '2027-08-01', 2, 'active', 'BATCH_1746000014_2004'),
(1, 3, 'BATCH_P1_0005', 180, '2026-05-01', '2027-09-01', 1, 'active', 'BATCH_1746000015_2005'),
(1, 3, 'BATCH_P1_0006', 220, '2026-05-01', '2027-10-01', 1, 'active', 'BATCH_1746000016_2006'),
(1, 4, 'BATCH_P1_0007', 160, '2026-05-01', '2027-11-01', 3, 'active', 'BATCH_1746000017_2007'),
(1, 4, 'BATCH_P1_0008', 190, '2026-05-01', '2027-12-01', 3, 'active', 'BATCH_1746000018_2008'),
(1, 5, 'BATCH_P1_0009', 210, '2026-05-01', '2028-01-01', 2, 'active', 'BATCH_1746000019_2009'),
(1, 5, 'BATCH_P1_0010', 240, '2026-05-01', '2028-02-01', 2, 'active', 'BATCH_1746000020_2010'),
(1, 6, 'BATCH_P1_0011', 280, '2026-05-01', '2028-03-01', 1, 'active', 'BATCH_1746000021_2011'),
(1, 6, 'BATCH_P1_0012', 320, '2026-05-01', '2028-04-01', 1, 'active', 'BATCH_1746000022_2012'),
(1, 7, 'BATCH_P1_0013', 140, '2026-05-01', '2027-05-15', 3, 'active', 'BATCH_1746000023_2013'),
(1, 7, 'BATCH_P1_0014', 170, '2026-05-01', '2027-06-15', 3, 'active', 'BATCH_1746000024_2014'),
(1, 8, 'BATCH_P1_0015', 260, '2026-05-01', '2027-07-15', 2, 'active', 'BATCH_1746000025_2015'),
(1, 8, 'BATCH_P1_0016', 290, '2026-05-01', '2027-08-15', 2, 'active', 'BATCH_1746000026_2016'),
(1, 9, 'BATCH_P1_0017', 200, '2026-05-01', '2027-09-15', 1, 'active', 'BATCH_1746000027_2017'),
(1, 9, 'BATCH_P1_0018', 230, '2026-05-01', '2027-10-15', 1, 'active', 'BATCH_1746000028_2018'),
(1, 10, 'BATCH_P1_0019', 270, '2026-05-01', '2027-11-15', 3, 'active', 'BATCH_1746000029_2019'),
(1, 10, 'BATCH_P1_0020', 310, '2026-05-01', '2027-12-15', 3, 'active', 'BATCH_1746000030_2020');

-- ============================================================================
-- HOÀN TẤT
-- ============================================================================
-- Mật khẩu mặc định cho tất cả users: 123456
-- Username: admin / Password: 123456
-- Username: nhanvien1 / Password: 123456
