<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-cart-plus me-2"></i>Bán hàng</h2>
    <div>
        <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#createOrderModal">
            <i class="bi bi-file-earmark-plus me-2"></i>Tạo đơn hàng
        </button>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Danh sách thuốc</h5></div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchMedicine" placeholder="Tìm thuốc...">
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search me-1"></i>Tìm
                    </button>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Đơn vị</th>
                                <th>Giá</th>
                                <th>Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody id="medicineList">
                            <?php foreach ($medicines as $med): ?>
                                <?php $inventory = $this->medicineModel->getTotalInventory($med['medicine_id']); ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($med['medicine_name']); ?></td>
                                    <td><?php echo htmlspecialchars($med['unit_name'] ?? 'Viên'); ?></td>
                                    <td><?php echo number_format($med['price']); ?>đ</td>
                                    <td><span class="badge bg-info"><?php echo $inventory; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo đơn hàng -->
<div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createOrderModalLabel">
                    <i class="bi bi-file-earmark-plus me-2"></i>Tạo đơn hàng mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Danh sách thuốc -->
                    <div class="col-md-7">
                        <h6 class="mb-3">Chọn thuốc</h6>
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="modalSearchMedicine" placeholder="Tìm thuốc...">
                            <button class="btn btn-primary" type="button" id="modalSearchButton">
                                <i class="bi bi-search me-1"></i>Tìm
                            </button>
                        </div>
                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Tên thuốc</th>
                                        <th>Giá</th>
                                        <th>Tồn kho</th>
                                        <th width="100">Số lượng</th>
                                        <th width="80">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="modalMedicineList">
                                    <?php foreach ($medicines as $med): ?>
                                        <?php $inventory = $this->medicineModel->getTotalInventory($med['medicine_id']); ?>
                                        <tr data-medicine-id="<?php echo $med['medicine_id']; ?>">
                                            <td>
                                                <?php echo htmlspecialchars($med['medicine_name']); ?>
                                                <br><small class="text-muted">Đơn vị: <?php echo htmlspecialchars($med['unit_name'] ?? 'Viên'); ?></small>
                                            </td>
                                            <td><?php echo number_format($med['price']); ?>đ</td>
                                            <td><span class="badge bg-info"><?php echo $inventory; ?></span></td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm medicine-quantity" 
                                                       min="0" max="<?php echo $inventory; ?>" value="0"
                                                       data-id="<?php echo $med['medicine_id']; ?>"
                                                       style="width: 70px;">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success add-to-modal-cart" 
                                                        data-id="<?php echo $med['medicine_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($med['medicine_name']); ?>"
                                                        data-price="<?php echo $med['price']; ?>"
                                                        data-unit="<?php echo htmlspecialchars($med['unit_name'] ?? 'Viên'); ?>"
                                                        data-inventory="<?php echo $inventory; ?>">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Giỏ hàng tạm -->
                    <div class="col-md-5">
                        <h6 class="mb-3">Đơn hàng</h6>
                        <div id="modalCart" style="max-height: 300px; overflow-y: auto; min-height: 200px; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px;">
                            <div class="text-center py-5 text-muted" id="emptyCartMessage">
                                <i class="bi bi-cart3" style="font-size: 3rem;"></i>
                                <p class="mt-3">Chưa có sản phẩm nào</p>
                                <small>Chọn thuốc bên trái để thêm vào đơn</small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Tạm tính:</span>
                                <strong id="modalSubtotal">0đ</strong>
                            </div>
                            <div class="mb-2">
                                <label class="form-label mb-1">Giảm giá (VNĐ)</label>
                                <input type="number" class="form-control form-control-sm" id="modalDiscount" 
                                       value="0" min="0" max="0">
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Thành tiền:</strong>
                                <h5 class="mb-0 text-success" id="modalTotalAmount">0đ</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Hủy
                </button>
                <button type="button" class="btn btn-success" id="saveOrderBtn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Lưu đơn hàng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
console.log('Script started');

// Modal cart data
let modalCartItems = [];

// Format number
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Update modal cart display
function updateModalCart() {
    console.log('updateModalCart called, items:', modalCartItems);
    
    const cartDiv = document.getElementById('modalCart');
    const saveBtn = document.getElementById('saveOrderBtn');
    const discountInput = document.getElementById('modalDiscount');
    const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;
    
    if (modalCartItems.length === 0) {
        cartDiv.innerHTML = `
            <div class="text-center py-5 text-muted" id="emptyCartMessage">
                <i class="bi bi-cart3" style="font-size: 3rem;"></i>
                <p class="mt-3">Chưa có sản phẩm nào</p>
                <small>Chọn thuốc bên trái để thêm vào đơn</small>
            </div>
        `;
        saveBtn.disabled = true;
        document.getElementById('modalSubtotal').textContent = '0đ';
        document.getElementById('modalTotalAmount').textContent = '0đ';
        return;
    }
    
    saveBtn.disabled = false;
    
    let html = '';
    let subtotal = 0;
    
    modalCartItems.forEach((item, index) => {
        const itemSubtotal = item.price * item.quantity;
        subtotal += itemSubtotal;
        
        html += '<div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">';
        html += '<div class="flex-grow-1">';
        html += '<strong>' + item.name + '</strong><br>';
        html += '<small class="text-muted">' + formatNumber(item.price) + 'đ × ' + item.quantity + ' ' + item.unit + '</small>';
        html += '</div>';
        html += '<div class="text-end">';
        html += '<strong class="text-success">' + formatNumber(itemSubtotal) + 'đ</strong><br>';
        html += '<div class="btn-group btn-group-sm mt-1">';
        html += '<button class="btn btn-outline-primary btn-edit-cart" data-index="' + index + '" title="Sửa số lượng">';
        html += '<i class="bi bi-pencil"></i>';
        html += '</button>';
        html += '<button class="btn btn-outline-danger btn-remove-cart" data-index="' + index + '" title="Xóa">';
        html += '<i class="bi bi-trash"></i>';
        html += '</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    });
    
    cartDiv.innerHTML = html;
    
    // Cập nhật tính tiền
    const total = subtotal - discount;
    document.getElementById('modalSubtotal').textContent = formatNumber(subtotal) + 'đ';
    document.getElementById('modalTotalAmount').textContent = formatNumber(total) + 'đ';
    
    // Giới hạn giảm giá không vượt quá tổng tiền
    if (discountInput) {
        discountInput.max = subtotal;
    }
}

// Event delegation for all clicks
document.addEventListener('click', function(e) {
    // Add to cart button
    if (e.target.closest('.add-to-modal-cart')) {
        console.log('Add button clicked!');
        
        const btn = e.target.closest('.add-to-modal-cart');
        const id = parseInt(btn.getAttribute('data-id'));
        const name = btn.getAttribute('data-name');
        const price = parseFloat(btn.getAttribute('data-price'));
        const unit = btn.getAttribute('data-unit');
        const inventory = parseInt(btn.getAttribute('data-inventory'));
        
        console.log('Medicine data:', {id, name, price, unit, inventory});
        
        const quantityInput = document.querySelector('input.medicine-quantity[data-id="' + id + '"]');
        let quantity = parseInt(quantityInput.value);
        
        console.log('Quantity:', quantity);
        
        if (isNaN(quantity) || quantity < 1) {
            alert('Vui lòng nhập số lượng lớn hơn 0');
            quantityInput.focus();
            return;
        }
        
        if (quantity > inventory) {
            alert('Số lượng vượt quá tồn kho (' + inventory + ' ' + unit + ')');
            quantityInput.value = 0;
            return;
        }
        
        // Check if already in cart
        const existingIndex = modalCartItems.findIndex(item => item.id === id);
        
        if (existingIndex >= 0) {
            const newQuantity = modalCartItems[existingIndex].quantity + quantity;
            if (newQuantity > inventory) {
                alert('Tổng số lượng vượt quá tồn kho (' + inventory + ' ' + unit + ')');
                return;
            }
            modalCartItems[existingIndex].quantity = newQuantity;
            console.log('Updated existing item');
        } else {
            modalCartItems.push({
                id: id,
                name: name,
                price: price,
                unit: unit,
                quantity: quantity,
                inventory: inventory
            });
            console.log('Added new item');
        }
        
        updateModalCart();
        quantityInput.value = 0;
    }
    
    // Edit cart item
    if (e.target.closest('.btn-edit-cart')) {
        const index = parseInt(e.target.closest('.btn-edit-cart').getAttribute('data-index'));
        const item = modalCartItems[index];
        const newQuantity = prompt('Nhập số lượng mới cho ' + item.name + '\n(Tồn kho: ' + item.inventory + ' ' + item.unit + ')', item.quantity);
        
        if (newQuantity !== null) {
            const qty = parseInt(newQuantity);
            if (qty > 0 && qty <= item.inventory) {
                modalCartItems[index].quantity = qty;
                updateModalCart();
            } else if (qty > item.inventory) {
                alert('Số lượng vượt quá tồn kho (' + item.inventory + ' ' + item.unit + ')');
            } else {
                alert('Số lượng phải lớn hơn 0');
            }
        }
    }
    
    // Remove cart item
    if (e.target.closest('.btn-remove-cart')) {
        const index = parseInt(e.target.closest('.btn-remove-cart').getAttribute('data-index'));
        modalCartItems.splice(index, 1);
        updateModalCart();
    }
});

// Search functionality in modal
function performModalSearch() {
    const searchValue = document.getElementById('modalSearchMedicine').value.toLowerCase();
    const table = document.getElementById('modalMedicineList');
    const rows = table.getElementsByTagName('tr');
    
    console.log('Searching for:', searchValue);
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;
        
        if (text.toLowerCase().indexOf(searchValue) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// Search functionality outside modal
function performOutsideSearch() {
    const searchValue = document.getElementById('searchMedicine').value.toLowerCase();
    const table = document.getElementById('medicineList');
    const rows = table.getElementsByTagName('tr');
    
    console.log('Searching outside for:', searchValue);
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;
        
        if (text.toLowerCase().indexOf(searchValue) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// Outside search - button click
document.getElementById('searchButton').addEventListener('click', performOutsideSearch);

// Outside search - Enter key
document.getElementById('searchMedicine').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performOutsideSearch();
    }
});

// Outside search - real-time
document.getElementById('searchMedicine').addEventListener('keyup', performOutsideSearch);

// Modal search - button click
document.getElementById('modalSearchButton').addEventListener('click', performModalSearch);

// Modal search - Enter key
document.getElementById('modalSearchMedicine').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performModalSearch();
    }
});

// Modal search - real-time
document.getElementById('modalSearchMedicine').addEventListener('keyup', performModalSearch);

// Update total when discount changes
document.getElementById('modalDiscount').addEventListener('input', function() {
    updateModalCart();
});

// Save order button
document.getElementById('saveOrderBtn').addEventListener('click', function() {
    console.log('Save button clicked');
    console.log('Cart items:', modalCartItems);
    
    if (modalCartItems.length === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm');
        return;
    }
    
    const discount = parseFloat(document.getElementById('modalDiscount').value) || 0;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';
    
    const requestData = {
        items: modalCartItems,
        discount: discount
    };
    
    console.log('Sending request:', requestData);
    console.log('URL:', window.location.origin + '/CNM_NHOM32/ajax/create_order_with_items.php');
    
    fetch('ajax/create_order_with_items.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Đọc response as text trước để debug
        return response.text().then(text => {
            console.log('Response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                throw new Error('Server trả về không phải JSON: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Đã tạo đơn hàng thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể tạo đơn hàng'));
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Lưu đơn hàng';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Lỗi: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Lưu đơn hàng';
    });
});

// Reset modal when closed
document.getElementById('createOrderModal').addEventListener('hidden.bs.modal', function() {
    modalCartItems = [];
    document.getElementById('modalDiscount').value = 0;
    updateModalCart();
    document.getElementById('modalSearchMedicine').value = '';
    const quantityInputs = document.querySelectorAll('.medicine-quantity');
    quantityInputs.forEach(input => input.value = 0);
});

console.log('Script loaded successfully');
</script>

<?php require_once 'views/layouts/footer.php'; ?>
