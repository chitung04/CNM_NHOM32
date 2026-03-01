-- ============================================
-- PHARMACY MANAGEMENT SYSTEM - COMPLETE DATABASE
-- Bao gồm: Schema + Dữ liệu mẫu đầy đủ + QR Codes
-- ============================================

-- Tạo database
DROP DATABASE IF EXISTS qlnt_db;
CREATE DATABASE qlnt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qlnt_db;

-- Bảng users
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('staff', 'manager') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng categories
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng units
CREATE TABLE units (
    unit_id INT PRIMARY KEY AUTO_INCREMENT,
    unit_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng suppliers
CREATE TABLE suppliers (
    supplier_id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng medicines
CREATE TABLE medicines (
    medicine_id INT PRIMARY KEY AUTO_INCREMENT,
    medicine_name VARCHAR(150) NOT NULL,
    category_id INT,
    unit_id INT,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    qr_code VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (unit_id) REFERENCES units(unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Bảng batches
CREATE TABLE batches (
    batch_id INT PRIMARY KEY AUTO_INCREMENT,
    medicine_id INT NOT NULL,
    supplier_id INT,
    quantity INT NOT NULL,
    expiry_date DATE NOT NULL,
    import_date DATE NOT NULL,
    qr_code VARCHAR(50) UNIQUE,
    status ENUM('active', 'expired', 'sold_out') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Bảng invoices
CREATE TABLE invoices (
    invoice_id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    qr_code VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Bảng invoice_details
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Bảng notifications
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('low_stock', 'expiry_warning') NOT NULL,
    message TEXT,
    reference_id INT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Insert dữ liệu mẫu

-- ============================================
-- USERS
-- ============================================
-- Password: admin123 và staff123
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'manager'),
('staff', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Nhân viên bán hàng', 'staff'),
('manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', 'manager'),
('staff1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Trần Thị B', 'staff');

-- ============================================
-- CATEGORIES (Phân loại theo quy định dược phẩm VN)
-- ============================================
INSERT INTO categories (category_name, description) VALUES
-- Nhóm thuốc theo toa
('Thuốc kê đơn', 'Thuốc chỉ bán theo đơn của bác sĩ (Prescription drugs)'),
('Thuốc không kê đơn', 'Thuốc bán tự do, không cần đơn (OTC - Over The Counter)'),
('Thuốc kiểm soát đặc biệt', 'Thuốc gây nghiện, hướng thần, tiền chất (Controlled substances)'),

-- Nhóm sản phẩm
('Dược phẩm', 'Thuốc điều trị bệnh, có hoạt chất dược lý'),
('Thực phẩm chức năng', 'TPCN bổ sung dinh dưỡng, vitamin, khoáng chất'),
('Dược mỹ phẩm', 'Mỹ phẩm có tác dụng điều trị (Cosmeceuticals)'),
('Thiết bị y tế', 'Dụng cụ, thiết bị y tế, vật tư tiêu hao'),

-- Nhóm theo tác dụng dược lý
('Kháng sinh', 'Thuốc kháng sinh điều trị nhiễm khuẩn'),
('Giảm đau - Hạ sốt', 'Thuốc giảm đau, hạ sốt, chống viêm'),
('Tim mạch', 'Thuốc điều trị bệnh tim mạch, huyết áp'),
('Tiêu hóa', 'Thuốc điều trị bệnh đường tiêu hóa'),
('Hô hấp', 'Thuốc điều trị bệnh đường hô hấp'),
('Nội tiết', 'Thuốc điều trị bệnh nội tiết, đái tháo đường'),
('Thần kinh', 'Thuốc điều trị bệnh thần kinh, tâm thần'),
('Da liễu', 'Thuốc điều trị bệnh da, ngoài da'),
('Mắt - Tai - Mũi - Họng', 'Thuốc điều trị bệnh mắt, tai, mũi, họng'),
('Cơ xương khớp', 'Thuốc điều trị bệnh cơ xương khớp');

-- ============================================
-- UNITS
-- ============================================
INSERT INTO units (unit_name) VALUES
('Viên'),
('Hộp'),
('Chai'),
('Tuýp'),
('Gói'),
('Vỉ'),
('Ống'),
('Lọ');

-- ============================================
-- SUPPLIERS
-- ============================================
INSERT INTO suppliers (supplier_name, phone, email, address) VALUES
('Công ty Dược phẩm Hà Nội', '0241234567', 'hanoi@pharma.vn', '123 Đường Láng, Đống Đa, Hà Nội'),
('Công ty Dược Sài Gòn', '0281234567', 'saigon@pharma.vn', '456 Nguyễn Trãi, Quận 1, TP.HCM'),
('Công ty Dược phẩm Trung ương', '0243456789', 'central@pharma.vn', '789 Giải Phóng, Hai Bà Trưng, Hà Nội'),
('Công ty Dược Việt', '0912345678', 'viet@pharma.vn', '321 Lê Lợi, Quận 3, TP.HCM'),
('Công ty Dược phẩm Quốc tế', '0987654321', 'intl@pharma.vn', '555 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội');

-- ============================================
-- MEDICINES với QR CODES (Phân loại chi tiết)
-- ============================================

-- THUỐC KÊ ĐƠN (Category 1)
INSERT INTO medicines (medicine_name, category_id, unit_id, price, description, qr_code) VALUES
('Amoxicillin 500mg', 1, 1, 3500, 'Kháng sinh điều trị nhiễm khuẩn - Kê đơn', 'MED_1735000001_1001'),
('Cefixime 200mg', 1, 1, 8500, 'Kháng sinh thế hệ 3 - Kê đơn', 'MED_1735000002_1002'),
('Azithromycin 250mg', 1, 1, 12000, 'Kháng sinh Macrolid - Kê đơn', 'MED_1735000003_1003'),
('Ciprofloxacin 500mg', 1, 1, 6500, 'Kháng sinh Quinolon - Kê đơn', 'MED_1735000004_1004'),
('Metronidazole 250mg', 1, 1, 2500, 'Kháng sinh kháng kỵ khí - Kê đơn', 'MED_1735000005_1005'),
('Atorvastatin 10mg', 1, 1, 8500, 'Thuốc điều trị mỡ máu cao - Kê đơn', 'MED_1735000006_1006'),
('Amlodipine 5mg', 1, 1, 6500, 'Thuốc điều trị tăng huyết áp - Kê đơn', 'MED_1735000007_1007'),
('Losartan 50mg', 1, 1, 9500, 'Thuốc điều trị tăng huyết áp - Kê đơn', 'MED_1735000008_1008'),
('Metformin 500mg', 1, 1, 3000, 'Thuốc điều trị đái tháo đường type 2 - Kê đơn', 'MED_1735000009_1009'),
('Glimepiride 2mg', 1, 1, 5500, 'Thuốc điều trị đái tháo đường - Kê đơn', 'MED_1735000010_1010'),

-- THUỐC KHÔNG KÊ ĐƠN - OTC (Category 2)
('Paracetamol 500mg', 2, 1, 2000, 'Thuốc giảm đau, hạ sốt - OTC', 'MED_1735000011_1011'),
('Ibuprofen 400mg', 2, 1, 4500, 'Thuốc giảm đau, chống viêm - OTC', 'MED_1735000012_1012'),
('Aspirin 100mg', 2, 1, 3000, 'Thuốc giảm đau, chống đông máu - OTC', 'MED_1735000013_1013'),
('Cetirizine 10mg', 2, 1, 3000, 'Thuốc chống dị ứng - OTC', 'MED_1735000014_1014'),
('Loratadine 10mg', 2, 1, 3500, 'Thuốc chống dị ứng không gây buồn ngủ - OTC', 'MED_1735000015_1015'),
('Chlorpheniramine 4mg', 2, 1, 2500, 'Thuốc chống dị ứng - OTC', 'MED_1735000016_1016'),
('Domperidone 10mg', 2, 1, 2500, 'Thuốc chống nôn - OTC', 'MED_1735000017_1017'),
('Loperamide 2mg', 2, 1, 3500, 'Thuốc chống tiêu chảy - OTC', 'MED_1735000018_1018'),

-- DƯỢC PHẨM (Category 4)
('Omeprazole 20mg', 4, 1, 4500, 'Thuốc điều trị loét dạ dày', 'MED_1735000019_1019'),
('Esomeprazole 40mg', 4, 1, 8500, 'Thuốc điều trị trợt thực quản', 'MED_1735000020_1020'),
('Diclofenac 50mg', 4, 1, 5500, 'Thuốc giảm đau, chống viêm mạnh', 'MED_1735000021_1021'),
('Meloxicam 7.5mg', 4, 1, 6000, 'Thuốc chống viêm không steroid', 'MED_1735000022_1022'),
('Salbutamol Inhaler', 4, 3, 45000, 'Thuốc xịt điều trị hen phế quản', 'MED_1735000023_1023'),
('Ambroxol 30mg', 4, 1, 3500, 'Thuốc long đờm', 'MED_1735000024_1024'),
('Bromhexine 8mg', 4, 1, 4000, 'Thuốc long đờm, giảm ho', 'MED_1735000025_1025'),

-- THỰC PHẨM CHỨC NĂNG (Category 5)
('Vitamin C 1000mg', 5, 1, 5000, 'TPCN bổ sung vitamin C tăng sức đề kháng', 'MED_1735000026_1026'),
('Vitamin B Complex', 5, 1, 8000, 'TPCN bổ sung vitamin B tổng hợp', 'MED_1735000027_1027'),
('Calcium + D3', 5, 1, 12000, 'TPCN bổ sung canxi và vitamin D3', 'MED_1735000028_1028'),
('Omega 3 Fish Oil', 5, 1, 25000, 'TPCN dầu cá Omega 3 tốt cho tim mạch', 'MED_1735000029_1029'),
('Multivitamin', 5, 1, 15000, 'TPCN vitamin tổng hợp đa dạng', 'MED_1735000030_1030'),
('Glucosamine 1500mg', 5, 1, 18000, 'TPCN hỗ trợ xương khớp', 'MED_1735000031_1031'),
('Coenzyme Q10', 5, 1, 35000, 'TPCN hỗ trợ tim mạch, chống lão hóa', 'MED_1735000032_1032'),
('Ginkgo Biloba', 5, 1, 22000, 'TPCN hỗ trợ tuần hoàn não', 'MED_1735000033_1033'),
('Spirulina', 5, 5, 15000, 'TPCN tảo xoắn bổ sung dinh dưỡng', 'MED_1735000034_1034'),
('Collagen Peptide', 5, 5, 28000, 'TPCN collagen làm đẹp da', 'MED_1735000035_1035'),

-- DƯỢC MỸ PHẨM (Category 6)
('Betamethasone Cream', 6, 4, 15000, 'Kem bôi điều trị viêm da', 'MED_1735000036_1036'),
('Clotrimazole Cream', 6, 4, 12000, 'Kem bôi điều trị nấm da', 'MED_1735000037_1037'),
('Acyclovir Cream 5%', 6, 4, 18000, 'Kem bôi điều trị herpes', 'MED_1735000038_1038'),
('Tretinoin Cream 0.025%', 6, 4, 25000, 'Kem trị mụn, làm mờ thâm', 'MED_1735000039_1039'),
('Hydroquinone 4%', 6, 4, 32000, 'Kem trị nám, tàn nhang', 'MED_1735000040_1040'),
('Benzoyl Peroxide 5%', 6, 4, 18000, 'Gel trị mụn trứng cá', 'MED_1735000041_1041'),

-- THIẾT BỊ Y TẾ (Category 7)
('Băng gạc vô trùng 10x10cm', 7, 2, 5000, 'Băng gạc y tế vô trùng', 'MED_1735000042_1042'),
('Bông y tế 100g', 7, 5, 8000, 'Bông y tế vô trùng', 'MED_1735000043_1043'),
('Khẩu trang y tế 4 lớp', 7, 2, 15000, 'Khẩu trang y tế kháng khuẩn', 'MED_1735000044_1044'),
('Nhiệt kế điện tử', 7, 1, 85000, 'Nhiệt kế đo nhiệt độ cơ thể', 'MED_1735000045_1045'),
('Máy đo huyết áp điện tử', 7, 1, 450000, 'Máy đo huyết áp tự động', 'MED_1735000046_1046'),
('Que thử đường huyết', 7, 2, 120000, 'Que thử đường huyết 50 que', 'MED_1735000047_1047'),

-- KHÁNG SINH (Category 8)
('Cephalexin 500mg', 8, 1, 5500, 'Kháng sinh Cephalosporin thế hệ 1', 'MED_1735000048_1048'),
('Cefuroxime 250mg', 8, 1, 7500, 'Kháng sinh Cephalosporin thế hệ 2', 'MED_1735000049_1049'),
('Levofloxacin 500mg', 8, 1, 12000, 'Kháng sinh Fluoroquinolon', 'MED_1735000050_1050'),

-- GIẢM ĐAU - HẠ SỐT (Category 9)
('Paracetamol 650mg', 9, 1, 2500, 'Thuốc giảm đau, hạ sốt liều cao', 'MED_1735000051_1051'),
('Ibuprofen 200mg', 9, 1, 3000, 'Thuốc giảm đau, hạ sốt cho trẻ em', 'MED_1735000052_1052'),

-- TIÊU HÓA (Category 11)
('Men vi sinh Bio-acimin', 11, 5, 6000, 'Men vi sinh hỗ trợ tiêu hóa', 'MED_1735000053_1053'),
('Smecta', 11, 5, 3500, 'Thuốc điều trị tiêu chảy', 'MED_1735000054_1054'),
('Buscopan 10mg', 11, 1, 4500, 'Thuốc giảm đau co thắt đường tiêu hóa', 'MED_1735000055_1055'),

-- MẮT - TAI - MŨI - HỌNG (Category 16)
('Thuốc nhỏ mắt Refresh', 16, 3, 35000, 'Thuốc nhỏ mắt làm ẩm', 'MED_1735000056_1056'),
('Thuốc nhỏ tai Otomax', 16, 3, 28000, 'Thuốc nhỏ tai điều trị viêm', 'MED_1735000057_1057'),
('Xịt mũi Otrivin', 16, 3, 42000, 'Xịt mũi thông mũi', 'MED_1735000058_1058'),
('Viên ngậm họng Strepsils', 16, 1, 25000, 'Viên ngậm điều trị đau họng', 'MED_1735000059_1059'),

-- CƠ XƯƠNG KHỚP (Category 17)
('Methyl Salicylate Cream', 17, 4, 18000, 'Dầu xoa bóp giảm đau cơ', 'MED_1735000060_1060'),
('Diclofenac Gel', 17, 4, 22000, 'Gel bôi giảm đau khớp', 'MED_1735000061_1061');

-- ============================================
-- BATCHES với QR CODES
-- ============================================
INSERT INTO batches (medicine_id, supplier_id, quantity, expiry_date, import_date, qr_code, status) VALUES
-- Batches cho thuốc kê đơn
(1, 1, 500, '2025-12-31', '2024-01-15', 'BATCH_1735000101_2001', 'active'),
(2, 1, 200, '2025-11-30', '2024-02-01', 'BATCH_1735000102_2002', 'active'),
(3, 2, 150, '2026-03-31', '2024-03-15', 'BATCH_1735000103_2003', 'active'),
(4, 1, 400, '2025-10-31', '2024-01-20', 'BATCH_1735000104_2004', 'active'),
(5, 1, 300, '2026-01-31', '2024-02-10', 'BATCH_1735000105_2005', 'active'),
(6, 2, 250, '2026-06-30', '2024-07-01', 'BATCH_1735000106_2006', 'active'),
(7, 2, 400, '2026-05-31', '2024-06-01', 'BATCH_1735000107_2007', 'active'),
(8, 2, 350, '2026-07-31', '2024-08-01', 'BATCH_1735000108_2008', 'active'),
(9, 3, 600, '2026-04-30', '2024-05-01', 'BATCH_1735000109_2009', 'active'),
(10, 3, 400, '2026-03-31', '2024-04-01', 'BATCH_1735000110_2010', 'active'),

-- Batches cho thuốc OTC
(11, 3, 1000, '2026-12-31', '2024-01-10', 'BATCH_1735000111_2011', 'active'),
(11, 3, 800, '2025-08-31', '2023-08-15', 'BATCH_1735000112_2012', 'active'),
(12, 2, 600, '2026-05-31', '2024-05-01', 'BATCH_1735000113_2013', 'active'),
(13, 1, 500, '2025-09-30', '2024-01-05', 'BATCH_1735000114_2014', 'active'),
(14, 2, 700, '2026-08-31', '2024-09-01', 'BATCH_1735000115_2015', 'active'),
(15, 2, 650, '2026-07-31', '2024-08-01', 'BATCH_1735000116_2016', 'active'),
(16, 2, 800, '2026-06-30', '2024-07-01', 'BATCH_1735000117_2017', 'active'),
(17, 3, 550, '2025-11-30', '2024-02-01', 'BATCH_1735000118_2018', 'active'),
(18, 3, 450, '2025-10-31', '2024-01-10', 'BATCH_1735000119_2019', 'active'),

-- Batches cho dược phẩm
(19, 2, 450, '2025-12-31', '2024-01-10', 'BATCH_1735000120_2020', 'active'),
(20, 2, 250, '2026-01-31', '2024-02-01', 'BATCH_1735000121_2021', 'active'),
(21, 2, 300, '2026-07-31', '2024-07-10', 'BATCH_1735000122_2022', 'active'),
(22, 2, 280, '2026-06-30', '2024-06-15', 'BATCH_1735000123_2023', 'active'),
(23, 5, 80, '2026-12-31', '2024-01-15', 'BATCH_1735000124_2024', 'active'),
(24, 3, 600, '2025-10-31', '2024-01-10', 'BATCH_1735000125_2025', 'active'),
(25, 3, 550, '2025-09-30', '2024-01-05', 'BATCH_1735000126_2026', 'active'),

-- Batches cho TPCN
(26, 4, 800, '2026-12-31', '2024-01-01', 'BATCH_1735000127_2027', 'active'),
(27, 4, 400, '2026-11-30', '2024-02-01', 'BATCH_1735000128_2028', 'active'),
(28, 4, 350, '2026-10-31', '2024-03-01', 'BATCH_1735000129_2029', 'active'),
(29, 5, 200, '2026-09-30', '2024-04-01', 'BATCH_1735000130_2030', 'active'),
(30, 4, 600, '2026-08-31', '2024-02-15', 'BATCH_1735000131_2031', 'active'),
(31, 4, 300, '2026-07-31', '2024-03-10', 'BATCH_1735000132_2032', 'active'),
(32, 4, 250, '2026-06-30', '2024-04-05', 'BATCH_1735000133_2033', 'active'),
(33, 4, 280, '2026-05-31', '2024-05-01', 'BATCH_1735000134_2034', 'active'),
(34, 4, 400, '2026-04-30', '2024-06-01', 'BATCH_1735000135_2035', 'active'),
(35, 4, 350, '2026-03-31', '2024-07-01', 'BATCH_1735000136_2036', 'active'),

-- Batches cho dược mỹ phẩm
(36, 2, 150, '2026-04-30', '2024-05-01', 'BATCH_1735000137_2037', 'active'),
(37, 2, 200, '2026-03-31', '2024-04-01', 'BATCH_1735000138_2038', 'active'),
(38, 2, 100, '2026-02-28', '2024-03-01', 'BATCH_1735000139_2039', 'active'),
(39, 2, 120, '2026-01-31', '2024-02-01', 'BATCH_1735000140_2040', 'active'),
(40, 2, 90, '2025-12-31', '2024-01-01', 'BATCH_1735000141_2041', 'active'),
(41, 2, 150, '2025-11-30', '2023-12-01', 'BATCH_1735000142_2042', 'active'),

-- Batches cho thiết bị y tế
(42, 5, 500, '2027-12-31', '2024-01-01', 'BATCH_1735000143_2043', 'active'),
(43, 5, 300, '2027-11-30', '2024-02-01', 'BATCH_1735000144_2044', 'active'),
(44, 5, 1000, '2027-10-31', '2024-03-01', 'BATCH_1735000145_2045', 'active'),
(45, 5, 200, '2028-12-31', '2024-01-15', 'BATCH_1735000146_2046', 'active'),
(46, 5, 50, '2029-12-31', '2024-02-01', 'BATCH_1735000147_2047', 'active'),
(47, 5, 100, '2027-06-30', '2024-01-10', 'BATCH_1735000148_2048', 'active'),

-- Batches cho kháng sinh khác
(48, 1, 350, '2026-02-28', '2024-03-01', 'BATCH_1735000149_2049', 'active'),
(49, 1, 280, '2026-01-31', '2024-02-15', 'BATCH_1735000150_2050', 'active'),
(50, 1, 300, '2025-12-31', '2024-01-20', 'BATCH_1735000151_2051', 'active'),

-- Batches cho giảm đau khác
(51, 3, 900, '2026-11-30', '2024-02-01', 'BATCH_1735000152_2052', 'active'),
(52, 3, 700, '2026-10-31', '2024-03-01', 'BATCH_1735000153_2053', 'active'),

-- Batches cho tiêu hóa khác
(53, 3, 500, '2025-07-31', '2024-01-20', 'BATCH_1735000154_2054', 'active'),
(54, 3, 700, '2025-11-30', '2024-02-01', 'BATCH_1735000155_2055', 'active'),
(55, 3, 400, '2026-05-31', '2024-06-01', 'BATCH_1735000156_2056', 'active'),

-- Batches cho mắt tai mũi họng
(56, 5, 150, '2026-08-31', '2024-09-01', 'BATCH_1735000157_2057', 'active'),
(57, 5, 120, '2026-07-31', '2024-08-01', 'BATCH_1735000158_2058', 'active'),
(58, 5, 200, '2026-06-30', '2024-07-01', 'BATCH_1735000159_2059', 'active'),
(59, 5, 300, '2026-05-31', '2024-06-01', 'BATCH_1735000160_2060', 'active'),

-- Batches cho cơ xương khớp
(60, 2, 250, '2026-04-30', '2024-05-01', 'BATCH_1735000161_2061', 'active'),
(61, 2, 280, '2026-03-31', '2024-04-01', 'BATCH_1735000162_2062', 'active');

-- ============================================
-- INVOICES với QR CODES
-- ============================================
INSERT INTO invoices (invoice_number, user_id, total_amount, discount, final_amount, qr_code, created_at) VALUES
('INV20240115001', 2, 50000, 5000, 45000, 'INV_1735000201_3001', '2024-01-15 09:30:00'),
('INV20240115002', 2, 120000, 10000, 110000, 'INV_1735000202_3002', '2024-01-15 14:20:00'),
('INV20240116001', 4, 85000, 0, 85000, 'INV_1735000203_3003', '2024-01-16 10:15:00'),
('INV20240116002', 2, 200000, 20000, 180000, 'INV_1735000204_3004', '2024-01-16 16:45:00'),
('INV20240117001', 4, 65000, 5000, 60000, 'INV_1735000205_3005', '2024-01-17 11:00:00');

-- ============================================
-- INVOICE DETAILS
-- ============================================
INSERT INTO invoice_details (invoice_id, medicine_id, batch_id, quantity, unit_price, subtotal) VALUES
-- Invoice 1
(1, 5, 6, 10, 2000, 20000),
(1, 9, 11, 6, 5000, 30000),
-- Invoice 2
(2, 1, 1, 20, 3500, 70000),
(2, 6, 8, 10, 4500, 45000),
(2, 27, 30, 5, 3000, 15000),
-- Invoice 3
(3, 14, 17, 10, 4500, 45000),
(3, 5, 6, 20, 2000, 40000),
-- Invoice 4
(4, 12, 15, 4, 25000, 100000),
(4, 11, 14, 5, 12000, 60000),
(4, 10, 13, 5, 8000, 40000),
-- Invoice 5
(5, 17, 20, 10, 3500, 35000),
(5, 16, 19, 5, 6000, 30000);

-- ============================================
-- NOTIFICATIONS
-- ============================================
INSERT INTO notifications (type, message, reference_id, is_read) VALUES
('low_stock', 'Thuốc Acyclovir Cream sắp hết hàng (còn 8 viên)', 20, 0),
('expiry_warning', 'Lô thuốc Paracetamol 500mg sắp hết hạn (hết hạn: 2025-08-31)', 7, 0),
('low_stock', 'Thuốc Salbutamol Inhaler sắp hết hàng (còn 9 chai)', 24, 0);

-- ============================================
-- INDEXES để tối ưu performance
-- ============================================

-- Index cho batches table (tối ưu getTotalInventory)
ALTER TABLE batches ADD INDEX idx_medicine_status (medicine_id, status);
ALTER TABLE batches ADD INDEX idx_expiry_date (expiry_date);

-- Index cho invoices table
ALTER TABLE invoices ADD INDEX idx_created_at (created_at);
ALTER TABLE invoices ADD INDEX idx_user_id (user_id);

-- Index cho invoice_details table
ALTER TABLE invoice_details ADD INDEX idx_invoice_id (invoice_id);
ALTER TABLE invoice_details ADD INDEX idx_medicine_id (medicine_id);
ALTER TABLE invoice_details ADD INDEX idx_batch_id (batch_id);

-- Index cho medicines table
ALTER TABLE medicines ADD INDEX idx_category_id (category_id);
ALTER TABLE medicines ADD INDEX idx_medicine_name (medicine_name);

-- Index cho users table
ALTER TABLE users ADD INDEX idx_role (role);
ALTER TABLE users ADD INDEX idx_is_active (is_active);

-- Composite index cho search (nếu MySQL hỗ trợ FULLTEXT với utf8)
-- ALTER TABLE medicines ADD FULLTEXT INDEX idx_fulltext_search (medicine_name, description);


-- Bảng customers (Khách hàng)
CREATE TABLE customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE,
    email VARCHAR(100),
    address VARCHAR(500),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    loyalty_points INT DEFAULT 0,
    total_spent DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng promotions (Khuyến mãi)
CREATE TABLE promotions (
    promotion_id INT PRIMARY KEY AUTO_INCREMENT,
    promotion_code VARCHAR(50) UNIQUE NOT NULL,
    promotion_name VARCHAR(150) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_purchase DECIMAL(10,2) DEFAULT 0,
    max_discount DECIMAL(10,2),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    usage_limit INT,
    used_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng audit_logs (Nhật ký hoạt động)
CREATE TABLE audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột customer_id vào invoices
ALTER TABLE invoices ADD COLUMN customer_id INT AFTER user_id;
ALTER TABLE invoices ADD COLUMN promotion_id INT AFTER customer_id;
ALTER TABLE invoices ADD FOREIGN KEY (customer_id) REFERENCES customers(customer_id);
ALTER TABLE invoices ADD FOREIGN KEY (promotion_id) REFERENCES promotions(promotion_id);

-- Index cho audit_logs
CREATE INDEX idx_audit_user ON audit_logs(user_id);
CREATE INDEX idx_audit_table ON audit_logs(table_name, record_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at);

-- Index cho customers
CREATE INDEX idx_customer_phone ON customers(phone);
CREATE INDEX idx_customer_points ON customers(loyalty_points);

-- Index cho promotions
CREATE INDEX idx_promotion_code ON promotions(promotion_code);
CREATE INDEX idx_promotion_dates ON promotions(start_date, end_date);
CREATE INDEX idx_promotion_active ON promotions(is_active);

-- ============================================
-- HOÀN TẤT
-- ============================================
SELECT 'Database created successfully!' AS Status;
SELECT '========================================' AS '';
SELECT 'THỐNG KÊ DỮ LIỆU' AS '';
SELECT '========================================' AS '';
SELECT COUNT(*) AS 'Users' FROM users;
SELECT COUNT(*) AS 'Categories (Phân loại thuốc)' FROM categories;
SELECT COUNT(*) AS 'Units (Đơn vị)' FROM units;
SELECT COUNT(*) AS 'Suppliers (Nhà cung cấp)' FROM suppliers;
SELECT COUNT(*) AS 'Medicines (Thuốc & TPCN)' FROM medicines;
SELECT COUNT(*) AS 'Batches (Lô thuốc)' FROM batches;
SELECT COUNT(*) AS 'Invoices (Hóa đơn)' FROM invoices;
SELECT COUNT(*) AS 'Invoice Details' FROM invoice_details;
SELECT COUNT(*) AS 'Notifications' FROM notifications;
SELECT '========================================' AS '';
SELECT 'PHÂN LOẠI THUỐC' AS '';
SELECT '========================================' AS '';
SELECT c.category_name AS 'Loại', COUNT(m.medicine_id) AS 'Số lượng'
FROM categories c
LEFT JOIN medicines m ON c.category_id = m.category_id
GROUP BY c.category_id, c.category_name
ORDER BY COUNT(m.medicine_id) DESC;
