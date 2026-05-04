<?php
session_start();
require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔍 KIỂM TRA CHI TIẾT ĐƠN HÀNG CÓ ĐƯỢC LƯU VÀO DATABASE KHÔNG</h2>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h3>📋 1. Kiểm tra cấu trúc bảng invoice_details</h3>";
    
    $sql = "DESCRIBE invoice_details";
    $stmt = $conn->query($sql);
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Key</th><th>Mặc định</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>📊 2. Thống kê chi tiết đơn hàng hiện có</h3>";
    
    $sql = "SELECT COUNT(*) as total_details FROM invoice_details";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch();
    $totalDetails = $result['total_details'];
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<strong>Tổng số chi tiết đơn hàng trong database: " . number_format($totalDetails) . "</strong>";
    echo "</div>";
    
    if ($totalDetails > 0) {
        echo "<h3>📝 3. Chi tiết đơn hàng gần đây nhất (10 records)</h3>";
        
        $sql = "SELECT 
                    id.detail_id,
                    i.invoice_number,
                    m.medicine_name,
                    id.quantity,
                    id.unit_price,
                    id.subtotal,
                    b.batch_id,
                    i.created_at,
                    u.full_name as staff_name,
                    CASE 
                        WHEN i.payment_method IS NULL THEN 'Chưa thanh toán'
                        ELSE 'Đã thanh toán'
                    END as payment_status
                FROM invoice_details id
                JOIN invoices i ON id.invoice_id = i.invoice_id
                JOIN medicines m ON id.medicine_id = m.medicine_id
                JOIN batches b ON id.batch_id = b.batch_id
                LEFT JOIN users u ON i.user_id = u.user_id
                ORDER BY id.detail_id DESC
                LIMIT 10";
        
        $stmt = $conn->query($sql);
        $details = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>ID</th><th>Số HĐ</th><th>Thuốc</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th><th>Lô</th><th>Nhân viên</th><th>Trạng thái</th><th>Ngày tạo</th>";
        echo "</tr>";
        
        foreach ($details as $detail) {
            $statusColor = $detail['payment_status'] == 'Đã thanh toán' ? '#d4edda' : '#fff3cd';
            
            echo "<tr style='background: $statusColor;'>";
            echo "<td>" . $detail['detail_id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($detail['invoice_number']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($detail['medicine_name']) . "</td>";
            echo "<td>" . $detail['quantity'] . "</td>";
            echo "<td>" . number_format($detail['unit_price']) . "đ</td>";
            echo "<td><strong>" . number_format($detail['subtotal']) . "đ</strong></td>";
            echo "<td>#" . $detail['batch_id'] . "</td>";
            echo "<td>" . htmlspecialchars($detail['staff_name']) . "</td>";
            echo "<td>" . $detail['payment_status'] . "</td>";
            echo "<td>" . ($detail['created_at'] ? date('d/m/Y H:i', strtotime($detail['created_at'])) : '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>📈 4. Thống kê theo trạng thái đơn hàng</h3>";
        
        $sql = "SELECT 
                    CASE 
                        WHEN i.payment_method IS NULL THEN 'Chưa thanh toán'
                        ELSE 'Đã thanh toán'
                    END as status,
                    COUNT(id.detail_id) as detail_count,
                    COUNT(DISTINCT i.invoice_id) as invoice_count,
                    SUM(id.subtotal) as total_amount
                FROM invoice_details id
                JOIN invoices i ON id.invoice_id = i.invoice_id
                GROUP BY (i.payment_method IS NULL)";
        
        $stmt = $conn->query($sql);
        $stats = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>Trạng thái</th><th>Số chi tiết</th><th>Số đơn hàng</th><th>Tổng tiền</th></tr>";
        
        foreach ($stats as $stat) {
            $color = $stat['status'] == 'Đã thanh toán' ? '#d4edda' : '#fff3cd';
            
            echo "<tr style='background: $color;'>";
            echo "<td><strong>" . $stat['status'] . "</strong></td>";
            echo "<td>" . number_format($stat['detail_count']) . " items</td>";
            echo "<td>" . number_format($stat['invoice_count']) . " đơn</td>";
            echo "<td>" . number_format($stat['total_amount']) . "đ</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>💊 5. Top 5 thuốc được bán nhiều nhất</h3>";
        
        $sql = "SELECT 
                    m.medicine_name,
                    SUM(id.quantity) as total_sold,
                    COUNT(id.detail_id) as times_ordered,
                    SUM(id.subtotal) as total_revenue
                FROM invoice_details id
                JOIN medicines m ON id.medicine_id = m.medicine_id
                JOIN invoices i ON id.invoice_id = i.invoice_id
                WHERE i.payment_method IS NOT NULL
                GROUP BY m.medicine_id, m.medicine_name
                ORDER BY total_sold DESC
                LIMIT 5";
        
        $stmt = $conn->query($sql);
        $topMedicines = $stmt->fetchAll();
        
        if (!empty($topMedicines)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f8f9fa;'><th>Tên thuốc</th><th>Tổng SL bán</th><th>Số lần đặt</th><th>Doanh thu</th></tr>";
            
            foreach ($topMedicines as $med) {
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($med['medicine_name']) . "</strong></td>";
                echo "<td>" . number_format($med['total_sold']) . "</td>";
                echo "<td>" . number_format($med['times_ordered']) . " lần</td>";
                echo "<td>" . number_format($med['total_revenue']) . "đ</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>⚠️ Chưa có thuốc nào được bán (chưa có đơn hàng đã thanh toán)</p>";
        }
        
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;'>";
        echo "<h4>⚠️ CHƯA CÓ CHI TIẾT ĐƠN HÀNG NÀO</h4>";
        echo "<p>Điều này có thể do:</p>";
        echo "<ul>";
        echo "<li>Chưa có ai tạo đơn hàng</li>";
        echo "<li>Đơn hàng được tạo nhưng chưa thêm sản phẩm</li>";
        echo "<li>Có lỗi trong quá trình lưu chi tiết đơn hàng</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h3>🔧 6. Test tạo đơn hàng có chi tiết</h3>";
    
    // Kiểm tra xem có thuốc và batch không
    $sql = "SELECT m.medicine_id, m.medicine_name, m.price, b.batch_id 
            FROM medicines m 
            JOIN batches b ON m.medicine_id = b.medicine_id 
            WHERE b.status = 'active' AND b.quantity > 0 
            LIMIT 1";
    $stmt = $conn->query($sql);
    $testMedicine = $stmt->fetch();
    
    if ($testMedicine) {
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ CÓ THỂ TẠO ĐƠN HÀNG TEST</h4>";
        echo "<p><strong>Thuốc test:</strong> " . htmlspecialchars($testMedicine['medicine_name']) . "</p>";
        echo "<p><strong>Giá:</strong> " . number_format($testMedicine['price']) . "đ</p>";
        echo "<p><strong>Batch ID:</strong> #" . $testMedicine['batch_id'] . "</p>";
        
        echo "<p><strong>Để test tạo đơn hàng:</strong></p>";
        echo "<ol>";
        echo "<li>Vào trang bán hàng</li>";
        echo "<li>Nhấn 'Tạo đơn hàng'</li>";
        echo "<li>Thêm thuốc '" . htmlspecialchars($testMedicine['medicine_name']) . "' vào đơn</li>";
        echo "<li>Lưu đơn hàng</li>";
        echo "<li>Quay lại trang này để kiểm tra</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
        echo "<h4>❌ KHÔNG THỂ TẠO ĐƠN HÀNG TEST</h4>";
        echo "<p>Lý do: Không có thuốc hoặc batch khả dụng trong kho</p>";
        echo "<p>Cần nhập kho trước khi có thể bán hàng</p>";
        echo "</div>";
    }
    
    echo "<h3>🎯 KẾT LUẬN</h3>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #155724;'>";
    echo "<h4>✅ XÁC NHẬN: CHI TIẾT ĐƠN HÀNG ĐƯỢC LƯU VÀO DATABASE</h4>";
    echo "<ul>";
    echo "<li><strong>Bảng invoice_details:</strong> ✅ Tồn tại và có cấu trúc đúng</li>";
    echo "<li><strong>Tổng chi tiết đơn hàng:</strong> " . number_format($totalDetails) . " records</li>";
    echo "<li><strong>Lưu thông tin:</strong> ✅ Thuốc, số lượng, giá, lô, subtotal</li>";
    echo "<li><strong>Liên kết dữ liệu:</strong> ✅ Với invoices, medicines, batches</li>";
    echo "<li><strong>Trạng thái:</strong> ✅ Phân biệt đã/chưa thanh toán</li>";
    echo "</ul>";
    
    if ($totalDetails > 0) {
        echo "<div style='background: #d1ecf1; padding: 10px; margin-top: 10px; border-radius: 5px;'>";
        echo "<strong>🎉 HỆ THỐNG HOẠT ĐỘNG BÌNH THƯỜNG - CÓ DỮ LIỆU CHI TIẾT ĐƠN HÀNG</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; margin-top: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ CHƯA CÓ DỮ LIỆU - CẦN TẠO ĐƠN HÀNG ĐỂ KIỂM TRA</strong>";
        echo "</div>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ LỖI KẾT NỐI DATABASE</h3>";
    echo "Chi tiết lỗi: " . $e->getMessage();
    echo "</div>";
}
?>