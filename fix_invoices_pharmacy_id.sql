-- Thêm cột pharmacy_id vào bảng invoices nếu chưa có

-- Kiểm tra và thêm cột pharmacy_id
ALTER TABLE invoices 
ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL AFTER invoice_id;

-- Thêm foreign key
ALTER TABLE invoices 
ADD CONSTRAINT fk_invoices_pharmacy 
FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) 
ON DELETE CASCADE;

-- Cập nhật pharmacy_id cho các invoice cũ (gán về pharmacy_id = 1)
UPDATE invoices 
SET pharmacy_id = 1 
WHERE pharmacy_id IS NULL;

-- Sau khi cập nhật xong, set NOT NULL
ALTER TABLE invoices 
MODIFY COLUMN pharmacy_id INT NOT NULL;

-- Thêm index
CREATE INDEX idx_invoices_pharmacy ON invoices(pharmacy_id);
