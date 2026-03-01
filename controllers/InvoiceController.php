<?php
require_once 'models/Invoice.php';

class InvoiceController {
    private $invoiceModel;
    
    public function __construct() {
        $this->invoiceModel = new Invoice();
    }
    
    /**
     * Danh sách đơn hàng
     */
    public function index() {
        $invoices = $this->invoiceModel->getAll();
        $pageTitle = "Lịch sử đơn hàng";
        require_once 'views/invoices/index.php';
    }
    
    /**
     * Xem chi tiết đơn hàng
     */
    public function view() {
        $id = $_GET['id'] ?? 0;
        $invoice = $this->invoiceModel->getById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng";
            header('Location: index.php?page=invoices');
            exit;
        }
        
        $details = $this->invoiceModel->getDetails($id);
        
        $pageTitle = "Đơn hàng #" . $invoice['invoice_number'];
        require_once 'views/invoices/view.php';
    }
    
    /**
     * In hóa đơn
     */
    public function print() {
        $id = $_GET['id'] ?? 0;
        $invoice = $this->invoiceModel->getById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng";
            header('Location: index.php?page=invoices');
            exit;
        }
        
        $details = $this->invoiceModel->getDetails($id);
        
        $pageTitle = "In hóa đơn #" . $invoice['invoice_number'];
        require_once 'views/invoices/print.php';
    }
}
