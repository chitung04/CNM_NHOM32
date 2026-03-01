# 📋 Hệ thống phân loại thuốc - Pharmacy Management System

## 🎯 Tổng quan

Database đã được cập nhật với hệ thống phân loại thuốc theo đúng quy định dược phẩm Việt Nam.

## 📊 Thống kê

- **61 medicines** (tăng từ 29)
- **62 batches** (tăng từ 32)
- **17 categories** (phân loại chi tiết)
- **8 units**
- **5 suppliers**

## 🏷️ Các nhóm phân loại

### 1. Theo quy định pháp lý

#### 1.1 Thuốc kê đơn (Prescription drugs)
- Chỉ bán theo đơn của bác sĩ
- Ví dụ: Kháng sinh, thuốc tim mạch, thuốc đái tháo đường
- **10 thuốc** trong database

#### 1.2 Thuốc không kê đơn (OTC - Over The Counter)
- Bán tự do, không cần đơn
- Ví dụ: Paracetamol, Ibuprofen, thuốc chống dị ứng
- **8 thuốc** trong database

#### 1.3 Thuốc kiểm soát đặc biệt
- Thuốc gây nghiện, hướng thần, tiền chất
- Cần quản lý chặt chẽ theo quy định

### 2. Theo loại sản phẩm

#### 2.1 Dược phẩm
- Thuốc điều trị bệnh, có hoạt chất dược lý
- **7 thuốc** trong database

#### 2.2 Thực phẩm chức năng (TPCN)
- Bổ sung dinh dưỡng, vitamin, khoáng chất
- **10 TPCN** trong database
- Ví dụ: Vitamin C, Omega 3, Glucosamine, Collagen

#### 2.3 Dược mỹ phẩm (Cosmeceuticals)
- Mỹ phẩm có tác dụng điều trị
- **6 sản phẩm** trong database
- Ví dụ: Kem trị mụn, kem trị nám, kem chống nấm

#### 2.4 Thiết bị y tế
- Dụng cụ, thiết bị y tế, vật tư tiêu hao
- **6 sản phẩm** trong database
- Ví dụ: Băng gạc, nhiệt kế, máy đo huyết áp

### 3. Theo tác dụng dược lý

#### 3.1 Kháng sinh
- Điều trị nhiễm khuẩn
- **3 thuốc** trong database

#### 3.2 Giảm đau - Hạ sốt
- Thuốc giảm đau, hạ sốt, chống viêm
- **2 thuốc** trong database

#### 3.3 Tim mạch
- Điều trị bệnh tim mạch, huyết áp
- Đã có trong nhóm kê đơn

#### 3.4 Tiêu hóa
- Điều trị bệnh đường tiêu hóa
- **3 thuốc** trong database

#### 3.5 Hô hấp
- Điều trị bệnh đường hô hấp
- Đã có trong nhóm dược phẩm

#### 3.6 Nội tiết
- Điều trị bệnh nội tiết, đái tháo đường
- Đã có trong nhóm kê đơn

#### 3.7 Thần kinh
- Điều trị bệnh thần kinh, tâm thần
- Sẵn sàng mở rộng

#### 3.8 Da liễu
- Điều trị bệnh da
- Đã có trong nhóm dược mỹ phẩm

#### 3.9 Mắt - Tai - Mũi - Họng
- Điều trị bệnh mắt, tai, mũi, họng
- **4 sản phẩm** trong database

#### 3.10 Cơ xương khớp
- Điều trị bệnh cơ xương khớp
- **2 sản phẩm** trong database

## 📦 Danh sách thuốc theo nhóm

### Thuốc kê đơn (10)
1. Amoxicillin 500mg - Kháng sinh
2. Cefixime 200mg - Kháng sinh
3. Azithromycin 250mg - Kháng sinh
4. Ciprofloxacin 500mg - Kháng sinh
5. Metronidazole 250mg - Kháng sinh
6. Atorvastatin 10mg - Tim mạch
7. Amlodipine 5mg - Tim mạch
8. Losartan 50mg - Tim mạch
9. Metformin 500mg - Đái tháo đường
10. Glimepiride 2mg - Đái tháo đường

### Thuốc OTC (8)
1. Paracetamol 500mg - Giảm đau, hạ sốt
2. Ibuprofen 400mg - Giảm đau
3. Aspirin 100mg - Giảm đau
4. Cetirizine 10mg - Chống dị ứng
5. Loratadine 10mg - Chống dị ứng
6. Chlorpheniramine 4mg - Chống dị ứng
7. Domperidone 10mg - Chống nôn
8. Loperamide 2mg - Chống tiêu chảy

### Dược phẩm (7)
1. Omeprazole 20mg - Loét dạ dày
2. Esomeprazole 40mg - Trợt thực quản
3. Diclofenac 50mg - Giảm đau
4. Meloxicam 7.5mg - Chống viêm
5. Salbutamol Inhaler - Hen phế quản
6. Ambroxol 30mg - Long đờm
7. Bromhexine 8mg - Long đờm

### Thực phẩm chức năng (10)
1. Vitamin C 1000mg
2. Vitamin B Complex
3. Calcium + D3
4. Omega 3 Fish Oil
5. Multivitamin
6. Glucosamine 1500mg
7. Coenzyme Q10
8. Ginkgo Biloba
9. Spirulina
10. Collagen Peptide

### Dược mỹ phẩm (6)
1. Betamethasone Cream - Viêm da
2. Clotrimazole Cream - Nấm da
3. Acyclovir Cream 5% - Herpes
4. Tretinoin Cream 0.025% - Trị mụn
5. Hydroquinone 4% - Trị nám
6. Benzoyl Peroxide 5% - Trị mụn

### Thiết bị y tế (6)
1. Băng gạc vô trùng 10x10cm
2. Bông y tế 100g
3. Khẩu trang y tế 4 lớng
4. Nhiệt kế điện tử
5. Máy đo huyết áp điện tử
6. Que thử đường huyết

### Kháng sinh (3)
1. Cephalexin 500mg
2. Cefuroxime 250mg
3. Levofloxacin 500mg

### Giảm đau - Hạ sốt (2)
1. Paracetamol 650mg
2. Ibuprofen 200mg

### Tiêu hóa (3)
1. Men vi sinh Bio-acimin
2. Smecta
3. Buscopan 10mg

### Mắt - Tai - Mũi - Họng (4)
1. Thuốc nhỏ mắt Refresh
2. Thuốc nhỏ tai Otomax
3. Xịt mũi Otrivin
4. Viên ngậm họng Strepsils

### Cơ xương khớp (2)
1. Methyl Salicylate Cream
2. Diclofenac Gel

## 🎨 Hiển thị trên giao diện

### Màu sắc phân biệt (đề xuất)

```css
.category-prescription { background: #ff6b6b; color: white; } /* Đỏ - Kê đơn */
.category-otc { background: #51cf66; color: white; } /* Xanh lá - OTC */
.category-controlled { background: #ff8787; color: white; } /* Đỏ nhạt - Kiểm soát */
.category-pharma { background: #4dabf7; color: white; } /* Xanh dương - Dược phẩm */
.category-supplement { background: #ffd43b; color: #333; } /* Vàng - TPCN */
.category-cosmetic { background: #ff6b9d; color: white; } /* Hồng - Dược mỹ phẩm */
.category-device { background: #868e96; color: white; } /* Xám - Thiết bị */
```

### Badge hiển thị

```html
<span class="badge category-prescription">Kê đơn</span>
<span class="badge category-otc">OTC</span>
<span class="badge category-supplement">TPCN</span>
```

## 📝 Ghi chú quan trọng

1. **Thuốc kê đơn**: Cần kiểm tra đơn bác sĩ trước khi bán
2. **Thuốc kiểm soát đặc biệt**: Cần sổ theo dõi riêng
3. **TPCN**: Không phải thuốc, không có tác dụng điều trị bệnh
4. **Dược mỹ phẩm**: Có tác dụng điều trị nhưng dùng ngoài da
5. **Thiết bị y tế**: Cần kiểm tra hạn sử dụng và điều kiện bảo quản

## 🚀 Cách sử dụng

1. Import database mới:
```bash
mysql -u root -p < database_schema.sql
```

2. Generate QR codes:
```bash
php tools/generate_all_qrcodes.php
```

3. Xem danh sách thuốc theo phân loại:
```
http://localhost/CNM_NHOM32/tools/qr_list.php
```

## 📊 Mở rộng trong tương lai

- Thêm nhóm thuốc thần kinh
- Thêm nhóm thuốc ung thư
- Thêm nhóm vaccine
- Thêm nhóm thuốc sản phụ khoa
- Thêm nhóm thuốc nhi khoa
