<?php
require_once 'models/Invoice.php';
require_once 'models/Medicine.php';
require_once 'models/Batch.php';

class ReportController {
    private $invoiceModel;
    private $medicineModel;
    private $batchModel;
    
    public function __construct() {
        $this->invoiceModel = new Invoice();
        $this->medicineModel = new Medicine();
        $this->batchModel = new Batch();
    }
    
    /**
     * Báo cáo doanh thu
     */
    public function sales() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $invoices = $this->invoiceModel->getByDateRange($startDate, $endDate);
        $totalRevenue = $this->invoiceModel->getTotalRevenue($startDate, $endDate);
        
        // Nhóm theo ngày
        $revenueByDay = [];
        foreach ($invoices as $invoice) {
            $date = date('Y-m-d', strtotime($invoice['created_at']));
            if (!isset($revenueByDay[$date])) {
                $revenueByDay[$date] = 0;
            }
            $revenueByDay[$date] += $invoice['final_amount'];
        }
        
        $pageTitle = "Báo cáo doanh thu";
        require_once 'views/reports/sales.php';
    }
    
    /**
     * Báo cáo tồn kho
     */
    public function inventory() {
        $medicines = $this->medicineModel->getAll();
        
        // Thêm thông tin tồn kho
        foreach ($medicines as &$medicine) {
            $medicine['inventory'] = $this->medicineModel->getTotalInventory($medicine['medicine_id']);
            $medicine['batches'] = $this->batchModel->getByMedicine($medicine['medicine_id']);
        }
        
        $pageTitle = "Báo cáo tồn kho";
        require_once 'views/reports/inventory.php';
    }
    
    /**
     * Báo cáo thuốc sắp hết hạn
     */
    public function expiry() {
        $days = $_GET['days'] ?? 30;
        $batches = $this->batchModel->getExpiringBatches($days);
        
        $pageTitle = "Thuốc sắp hết hạn";
        require_once 'views/reports/expiry.php';
    }
    
    /**
     * Thống kê thuốc bán chạy
     */
    public function topSelling() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $limit = $_GET['limit'] ?? 10;
        
        $topMedicines = $this->invoiceModel->getTopSellingMedicines($startDate, $endDate, $limit);
        
        // Tính tổng số lượng và doanh thu
        $totalQuantity = 0;
        $totalRevenue = 0;
        foreach ($topMedicines as $medicine) {
            $totalQuantity += $medicine['total_quantity'];
            $totalRevenue += $medicine['total_revenue'];
        }
        
        $pageTitle = "Thuốc bán chạy";
        require_once 'views/reports/top_selling.php';
    }
}
