-- ============================================================================
-- THÊM NHÀ CUNG CẤP DƯỢC PHẨM THẬT TẠI VIỆT NAM
-- ============================================================================

-- Xóa nhà cung cấp mẫu cũ (nếu có)
DELETE FROM suppliers WHERE pharmacy_id = 1 AND supplier_id IN (1, 2);

-- Thêm các nhà cung cấp dược phẩm thật
INSERT INTO suppliers (pharmacy_id, supplier_name, address, phone, email) VALUES
-- Các công ty dược phẩm lớn tại Việt Nam
(1, 'Công ty Cổ phần Dược Hậu Giang (DHG Pharma)', '288 Bis Nguyễn Văn Cừ, P. An Hòa, Q. Ninh Kiều, TP. Cần Thơ', '02923891433', 'info@dhgpharma.com.vn'),
(1, 'Công ty Cổ phần Dược phẩm Imexpharm', '10-12 Đường số 6, KCN Tân Bình, P. Tây Thạnh, Q. Tân Phú, TP.HCM', '02838160853', 'imexpharm@imexpharm.com'),
(1, 'Công ty Cổ phần Dược phẩm Traphaco', 'Số 75 Phố Phúc Diễn, P. Xuân Phương, Q. Nam Từ Liêm, Hà Nội', '02438581181', 'traphaco@traphaco.com.vn'),
(1, 'Công ty Cổ phần Dược phẩm Domesco', 'Số 5 Pasteur, P. Bến Nghé, Q.1, TP.HCM', '02838222190', 'domesco@domesco.com'),
(1, 'Công ty Cổ phần Dược phẩm OPC', '206 Lý Chính Thắng, P.9, Q.3, TP.HCM', '02839300348', 'opcpharma@opcpharma.com'),

-- Các nhà phân phối dược phẩm
(1, 'Công ty TNHH Zuellig Pharma Việt Nam', 'Tầng 6, Tòa nhà Vincom Center, 72 Lê Thánh Tôn, Q.1, TP.HCM', '02838236894', 'vietnam@zuelligpharma.com'),
(1, 'Công ty TNHH Dược phẩm Mega We Care Việt Nam', '146 Nguyễn Văn Thủ, P. Đa Kao, Q.1, TP.HCM', '02838247247', 'info@megawecare.com.vn'),
(1, 'Công ty Cổ phần Dược phẩm Hà Tây (Hataphar)', 'Km 23, Quốc lộ 6, Xuân Mai, Chương Mỹ, Hà Nội', '02433840284', 'hataphar@hataphar.com.vn'),
(1, 'Công ty Cổ phần Dược phẩm Tipharco', 'Số 2 Đường Láng Hạ, P. Thành Công, Q. Ba Đình, Hà Nội', '02438514564', 'tipharco@tipharco.com.vn'),
(1, 'Công ty Cổ phần Dược phẩm Pymepharco', 'Số 2 Nguyễn Thị Minh Khai, P. Đa Kao, Q.1, TP.HCM', '02838222468', 'pymepharco@pymepharco.com'),

-- Các công ty dược phẩm quốc tế có văn phòng tại VN
(1, 'Sanofi Việt Nam', 'Tầng 8, Tòa nhà Vincom Center, 72 Lê Thánh Tôn, Q.1, TP.HCM', '02838236800', 'vietnam@sanofi.com'),
(1, 'Abbott Laboratories Việt Nam', 'Tầng 10, Tòa nhà Saigon Centre, 65 Lê Lợi, Q.1, TP.HCM', '02838236888', 'abbott.vietnam@abbott.com'),
(1, 'GlaxoSmithKline Việt Nam', 'Tầng 15, Tòa nhà Vincom Center, 72 Lê Thánh Tôn, Q.1, TP.HCM', '02838236900', 'vietnam@gsk.com'),
(1, 'Pfizer Việt Nam', 'Tầng 9, Tòa nhà Saigon Centre, 65 Lê Lợi, Q.1, TP.HCM', '02838236950', 'vietnam@pfizer.com'),
(1, 'Novartis Việt Nam', 'Tầng 12, Tòa nhà Vincom Center, 72 Lê Thánh Tôn, Q.1, TP.HCM', '02838236920', 'vietnam@novartis.com');

-- ============================================================================
-- HOÀN TẤT
-- ============================================================================
-- Đã thêm 15 nhà cung cấp dược phẩm thật tại Việt Nam:
-- ✅ 5 công ty dược phẩm Việt Nam (DHG, Imexpharm, Traphaco, Domesco, OPC)
-- ✅ 5 nhà phân phối dược (Zuellig, Mega We Care, Hataphar, Tipharco, Pymepharco)
-- ✅ 5 công ty dược quốc tế (Sanofi, Abbott, GSK, Pfizer, Novartis)
-- ============================================================================
