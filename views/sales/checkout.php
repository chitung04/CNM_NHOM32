<?php
if (!defined('DB_HOST')) {
    die('Direct access not permitted');
}

$pageTitle = $pageTitle ?? 'Thanh toán';
require_once 'views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cash-register"></i> Thanh toán đơn hàng
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Thông tin đơn hàng -->
                    <div class="invoice-info mb-4">
                        <h5>Đơn hàng: <?= htmlspecialchars($invoice['invoice_number']) ?></h5>
                        <p class="text-muted">Ngày: <?= $invoice['created_at'] ? date('d/m/Y H:i', strtotime($invoice['created_at'])) : '-' ?></p>
                    </div>

                    <!-- Chi tiết sản phẩm -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>STT</th>
                                    <th>Tên thuốc</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stt = 1;
                                foreach ($details as $item): 
                                ?>
                                <tr>
                                    <td><?= $stt++ ?></td>
                                    <td><?= htmlspecialchars($item['medicine_name']) ?></td>
                                    <td><?= number_format($item['unit_price'], 0, ',', '.') ?>đ</td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= number_format($item['subtotal'], 0, ',', '.') ?>đ</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Tổng tiền:</strong></td>
                                    <td><strong><?= number_format($invoice['total_amount'], 0, ',', '.') ?>đ</strong></td>
                                </tr>
                                <?php if ($invoice['discount'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end">Chiết khấu:</td>
                                    <td class="text-danger">-<?= number_format($invoice['discount'], 0, ',', '.') ?>đ</td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-success">
                                    <td colspan="4" class="text-end"><strong>Thành tiền:</strong></td>
                                    <td><strong class="text-success"><?= number_format($invoice['final_amount'], 0, ',', '.') ?>đ</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Form thanh toán -->
                    <form id="paymentForm" method="POST" action="index.php?page=sales&action=complete">
                        <input type="hidden" name="invoice_id" value="<?= $invoice['invoice_id'] ?>">
                        
                        <!-- Chọn hình thức thanh toán -->
                        <div class="mb-4">
                            <h5 class="mb-3">Chọn hình thức thanh toán:</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="payment-method-card" onclick="selectPaymentMethod('cash', this)">
                                        <input type="radio" name="payment_method" id="payment_cash" value="cash" checked>
                                        <label for="payment_cash" class="payment-label">
                                            <i class="fas fa-money-bill-wave fa-3x text-success mb-2"></i>
                                            <h5>Tiền mặt</h5>
                                            <p class="text-muted">Thanh toán trực tiếp</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="payment-method-card" onclick="selectPaymentMethod('bank_transfer', this)">
                                        <input type="radio" name="payment_method" id="payment_bank" value="bank_transfer">
                                        <label for="payment_bank" class="payment-label">
                                            <i class="fas fa-qrcode fa-3x text-primary mb-2"></i>
                                            <h5>Chuyển khoản</h5>
                                            <p class="text-muted">Quét mã QR ngân hàng</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thanh toán tiền mặt -->
                        <div id="cashPaymentSection" class="payment-section">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Số tiền cần thanh toán:</label>
                                        <input type="text" class="form-control form-control-lg text-end" 
                                               value="<?= number_format($invoice['final_amount'], 0, ',', '.') ?>đ" 
                                               readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Số tiền khách đưa: <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-lg text-end" 
                                               id="amountPaid" name="amount_paid" 
                                               min="<?= $invoice['final_amount'] ?>"
                                               step="1000"
                                               placeholder="Nhập số tiền"
                                               required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info" id="changeDisplay" style="display: none;">
                                        <h5 class="mb-0">
                                            <i class="fas fa-hand-holding-usd"></i> 
                                            Tiền thừa trả khách: <strong id="changeAmount">0đ</strong>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thanh toán chuyển khoản -->
                        <div id="bankTransferSection" class="payment-section" style="display: none;">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> 
                                Vui lòng quét mã QR bên dưới để chuyển khoản
                            </div>
                            
                            <div class="text-center mb-3">
                                <div class="qr-code-container">
                                    <div id="bankQRCode" class="border p-4 d-inline-block bg-white rounded shadow-sm">
                                        <!-- Logo ngân hàng -->
                                        <div class="d-flex justify-content-center align-items-center mb-3">
                                            <div class="me-3">
                                                <span style="color: #ED1C24; font-weight: bold; font-size: 24px;">VIETQR</span>
                                            </div>
                                            <div>
                                                <span style="color: #0066B3; font-weight: bold; font-size: 24px;">MB</span>
                                            </div>
                                        </div>
                                        
                                        <!-- QR Code Image -->
                                        <div class="qr-image mb-3">
                                            <img src="assets/images/image.png" 
                                                 alt="MB Bank VietQR - DO CHI TUNG" 
                                                 style="width: 300px; height: auto; border: 3px solid #ddd; border-radius: 8px;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <!-- Fallback nếu ảnh không load được -->
                                            <div style="width: 280px; height: 280px; border: 3px solid #ddd; display: none; align-items: center; justify-content: center; margin: 0 auto; background: white; border-radius: 8px;">
                                                <div class="text-center">
                                                    <i class="fas fa-qrcode" style="font-size: 200px; color: #000;"></i>
                                                    <p class="mt-2 text-muted">Đang tải mã QR...</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Thông tin tài khoản -->
                                        <div class="bank-info text-center">
                                            <p class="mb-1" style="color: #0066B3; font-weight: bold; font-size: 16px;">DO CHI TUNG</p>
                                            <p class="mb-1" style="font-size: 16px;">SĐT: 0398266899</p>
                                            <p class="mb-0 text-muted" style="font-size: 13px;">MB Bank - VietQR</p>
                                        </div>
                                        
                                        <!-- Logo VietQR services -->
                                        <div class="d-flex justify-content-center align-items-center mt-3 gap-2">
                                            <small class="text-muted">
                                                <span style="color: #ED1C24; font-weight: bold;">VietQR</span>Pay | 
                                                <span style="color: #ED1C24; font-weight: bold;">VietQR</span>Global | 
                                                <span style="color: #0066B3; font-weight: bold;">napas 24/7</span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info text-center">
                                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Thông tin chuyển khoản</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Ngân hàng:</strong></p>
                                        <p class="mb-3">MB Bank (Military Bank)</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Chủ tài khoản:</strong></p>
                                        <p class="mb-3">DO CHI TUNG</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Số điện thoại:</strong></p>
                                        <p class="mb-3">0398266899</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Số tiền:</strong></p>
                                        <p class="mb-3 text-danger"><strong><?= number_format($invoice['final_amount'], 0, ',', '.') ?>đ</strong></p>
                                    </div>
                                </div>
                                <div class="alert alert-warning mb-0">
                                    <strong>Nội dung chuyển khoản:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmBankTransfer" required>
                                <label class="form-check-label" for="confirmBankTransfer">
                                    <strong>Tôi xác nhận khách hàng đã chuyển khoản thành công</strong>
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php?page=sales" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" id="completePaymentBtn">
                                <i class="fas fa-check-circle"></i> Hoàn tất thanh toán
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar thông tin -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin</h5>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <label>Nhân viên:</label>
                        <p><?= htmlspecialchars($_SESSION['full_name']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Thời gian:</label>
                        <p><?= date('d/m/Y H:i:s') ?></p>
                    </div>
                    <div class="info-item">
                        <label>Số lượng sản phẩm:</label>
                        <p><?= count($details) ?> mặt hàng</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Lưu ý</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Kiểm tra kỹ thông tin trước khi hoàn tất</li>
                        <li>Sau khi thanh toán, tồn kho sẽ được cập nhật tự động</li>
                        <li>Hóa đơn có thể in lại sau khi hoàn tất</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-method-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    height: 100%;
}

.payment-method-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.payment-method-card.active {
    border-color: #007bff;
    background-color: #e7f3ff;
}

.payment-method-card input[type="radio"] {
    display: none;
}

.payment-label {
    cursor: pointer;
    margin: 0;
    width: 100%;
}

.qr-code-container {
    margin: 20px 0;
}

.info-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-item label {
    font-weight: bold;
    color: #666;
    margin-bottom: 5px;
}

.info-item p {
    margin: 0;
    font-size: 1.1em;
}

/* Ensure buttons are clickable */
#completePaymentBtn {
    position: relative;
    z-index: 10;
    pointer-events: auto !important;
}

#completePaymentBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Fix form elements */
#paymentForm {
    position: relative;
    z-index: 5;
}

.payment-method-card {
    position: relative;
    z-index: 6;
}
</style>

<script>
const finalAmount = <?= $invoice['final_amount'] ?>;

// Chọn hình thức thanh toán
function selectPaymentMethod(method, element) {
    console.log('Selecting payment method:', method);
    
    // Update radio buttons
    document.getElementById('payment_cash').checked = (method === 'cash');
    document.getElementById('payment_bank').checked = (method === 'bank_transfer');
    
    // Update card styles
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to clicked element
    if (element) {
        element.classList.add('active');
    } else {
        // Fallback: find the correct card
        const targetCard = method === 'cash' ? 
            document.querySelector('.payment-method-card:first-child') :
            document.querySelector('.payment-method-card:last-child');
        if (targetCard) targetCard.classList.add('active');
    }
    
    // Show/hide sections
    const cashSection = document.getElementById('cashPaymentSection');
    const bankSection = document.getElementById('bankTransferSection');
    const amountPaidInput = document.getElementById('amountPaid');
    const confirmBankCheckbox = document.getElementById('confirmBankTransfer');
    
    if (method === 'cash') {
        cashSection.style.display = 'block';
        bankSection.style.display = 'none';
        amountPaidInput.required = true;
        confirmBankCheckbox.required = false;
        amountPaidInput.value = ''; // Clear value for manual input
    } else {
        cashSection.style.display = 'none';
        bankSection.style.display = 'block';
        amountPaidInput.required = false;
        confirmBankCheckbox.required = true;
        
        // Set amount_paid = final_amount cho bank transfer
        amountPaidInput.value = finalAmount;
    }
}

// Tính tiền thừa
document.getElementById('amountPaid')?.addEventListener('input', function() {
    const amountPaid = parseFloat(this.value) || 0;
    const change = amountPaid - finalAmount;
    
    const changeDisplay = document.getElementById('changeDisplay');
    const changeAmount = document.getElementById('changeAmount');
    
    if (change >= 0) {
        changeAmount.textContent = new Intl.NumberFormat('vi-VN').format(change) + 'đ';
        changeDisplay.style.display = 'block';
        changeDisplay.classList.remove('alert-danger');
        changeDisplay.classList.add('alert-info');
    } else {
        changeAmount.textContent = 'Chưa đủ tiền!';
        changeDisplay.style.display = 'block';
        changeDisplay.classList.remove('alert-info');
        changeDisplay.classList.add('alert-danger');
    }
});

// Validate form trước khi submit
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    console.log('Form submit triggered');
    
    const paymentMethodElement = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethodElement) {
        e.preventDefault();
        alert('Vui lòng chọn hình thức thanh toán!');
        return false;
    }
    
    const paymentMethod = paymentMethodElement.value;
    console.log('Payment method:', paymentMethod);
    
    if (paymentMethod === 'cash') {
        const amountPaidInput = document.getElementById('amountPaid');
        const amountPaid = parseFloat(amountPaidInput.value) || 0;
        
        console.log('Amount paid:', amountPaid, 'Final amount:', finalAmount);
        
        if (amountPaid < finalAmount) {
            e.preventDefault();
            alert('Số tiền khách đưa không đủ! Cần: ' + new Intl.NumberFormat('vi-VN').format(finalAmount) + 'đ');
            amountPaidInput.focus();
            return false;
        }
    } else if (paymentMethod === 'bank_transfer') {
        const confirmCheckbox = document.getElementById('confirmBankTransfer');
        
        if (!confirmCheckbox.checked) {
            e.preventDefault();
            alert('Vui lòng xác nhận khách hàng đã chuyển khoản!');
            confirmCheckbox.focus();
            return false;
        }
        
        // Set amount_paid = final_amount cho bank transfer
        document.getElementById('amountPaid').value = finalAmount;
    }
    
    // Confirm trước khi hoàn tất
    const confirmMessage = paymentMethod === 'cash' ? 
        'Xác nhận hoàn tất thanh toán bằng tiền mặt?' :
        'Xác nhận hoàn tất thanh toán bằng chuyển khoản?';
        
    if (!confirm(confirmMessage)) {
        e.preventDefault();
        return false;
    }
    
    // Disable submit button để tránh double submit
    const submitBtn = document.getElementById('completePaymentBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    
    console.log('Form validation passed, submitting...');
    return true;
});

// Set active cho payment method đầu tiên khi trang load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing payment method');
    
    // Set active cho cash payment (default)
    selectPaymentMethod('cash', document.querySelector('.payment-method-card:first-child'));
    
    // Add console log để debug
    console.log('Final amount:', finalAmount);
    console.log('Payment form initialized');
    
    // Test button click
    const btn = document.getElementById('completePaymentBtn');
    if (btn) {
        console.log('Payment button found:', btn);
        btn.addEventListener('click', function(e) {
            console.log('Payment button clicked directly');
        });
    } else {
        console.error('Payment button not found!');
    }
    
    // Test form
    const form = document.getElementById('paymentForm');
    if (form) {
        console.log('Payment form found:', form);
    } else {
        console.error('Payment form not found!');
    }
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
