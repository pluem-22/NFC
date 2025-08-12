<?php
// nfc_simulate.php
require_once 'db_config.php';

$message = '';

// Handle Add Transaction from NFC simulation or manual entry
if (isset($_POST['add_transaction'])) {
    $transaction_id = 'TXN' . uniqid();
    $amount = $_POST['amount'];
    $customer_name = $_POST['customer_name'];
    $type = $_POST['transaction_type']; // 'buy' or 'topup'

    $stmt = $conn->prepare("INSERT INTO transactions (transaction_id, amount, customer_name, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdds", $transaction_id, $amount, $customer_name, $type);

    if ($stmt->execute()) {
        $message = "บันทึกการทำธุรกรรมเรียบร้อยแล้ว!";

        // --- เพิ่ม Logic สำหรับบันทึกรายการสินค้าที่ซื้อ (Order Items) เมื่อเป็นการ "ซื้อสินค้า" ---
        if ($type == 'buy' && isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
            $insert_order_item_stmt = $conn->prepare("INSERT INTO order_items (transaction_id, product_id, quantity, price_per_unit, total_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['selected_products'] as $product_data) {
                // สมมติว่า product_data มาในรูปแบบ "product_id|quantity|price_per_unit"
                // ในการจำลอง NFC จริงๆ ข้อมูลนี้อาจมาจาก ESP32 หรือการเลือกสินค้าบนหน้าจอ
                list($product_id, $quantity, $price_per_unit) = explode('|', $product_data);
                $total_item_price = $quantity * $price_per_unit;

                $insert_order_item_stmt->bind_param("siidd", $transaction_id, $product_id, $quantity, $price_per_unit, $total_item_price);
                $insert_order_item_stmt->execute();

                // ลดสต็อกสินค้า
                $update_stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $update_stock_stmt->bind_param("ii", $quantity, $product_id);
                $update_stock_stmt->execute();
                $update_stock_stmt->close();
            }
            $insert_order_item_stmt->close();
        }
        // --- สิ้นสุด Logic สำหรับบันทึกรายการสินค้าที่ซื้อ ---

    } else {
        $message = "เกิดข้อผิดพลาดในการบันทึก: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch products for manual transaction selection
$products_for_selection = [];
$product_sql = "SELECT id, product_name, price, stock FROM products ORDER BY product_name ASC";
$product_result = $conn->query($product_sql);
if ($product_result->num_rows > 0) {
    while($row = $product_result->fetch_assoc()) {
        $products_for_selection[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> NFC - จำลอง NFC</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <div class="sidebar custom-sidebar" id="sidebar-wrapper">
            <div class="sidebar-heading text-center">
                <i class="fas fa-wallet me-2"></i> ระบบชำระไร้เงินสด
            </div>
            <div class="list-group list-group-flush custom-list-group">
                <a href="index.php" class="list-group-item list-group-item-action custom-list-item">
                    <i class="fas fa-tachometer-alt me-2"></i> แดชบอร์ด
                </a>
              
                
                <a href="nfc_simulate.php" class="list-group-item list-group-item-action custom-list-item active">
                    <i class="fas fa-mobile-alt me-2"></i> จำลอง NFC
                </a>
            </div>
        </div>
        <div id="page-content-wrapper" class="flex-grow-1">
            <nav class="navbar navbar-expand-lg navbar-light custom-topbar">
                <div class="container-fluid">
                    <button class="btn btn-primary custom-toggle-btn" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a class="navbar-brand ms-3 d-none d-md-block" href="#">
                        <i class="fas fa-mobile-alt me-2"></i> จำลอง NFC
                    </a>
                    <div class="ms-auto me-3">
                        <span class="badge bg-success custom-badge-esp32">
                            <i class="fas fa-wifi me-1"></i> เชื่อมต่อ ESP32
                        </span>
                    </div>
                </div>
            </nav>
            <div class="container-fluid py-4">
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <h3 class="mb-4 dashboard-title">จำลองการแตะ NFC</h3>

                <div class="card custom-card mb-5">
                    <div class="card-header custom-card-header">
                        <h4 class="mb-0">จำลองการแตะ NFC / เพิ่มธุรกรรม</h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted">คลิกปุ่มด้านล่างเพื่อจำลองการแตะ NFC และบันทึกธุรกรรมโดยอัตโนมัติ หรือกรอกข้อมูลเพื่อเพิ่มธุรกรรมด้วยตนเอง</p>
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            <button type="button" class="btn btn-primary custom-btn-primary" id="simulateNFCBuy">
                                <i class="fas fa-shopping-bag me-2"></i> จำลองซื้อสินค้า (฿100)
                            </button>
                            <button type="button" class="btn btn-success custom-btn-success" id="simulateNFCTopup">
                                <i class="fas fa-wallet me-2"></i> จำลองเติมเงิน (฿500)
                            </button>
                        </div>

                        <hr class="my-4">

                        <h5>เพิ่มธุรกรรมด้วยตนเอง</h5>
                        <form method="POST" id="manualTransactionForm">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">ชื่อผู้ทำรายการ</label>
                                <input type="text" class="form-control custom-form-control" id="customer_name" name="customer_name" required value="ผู้ใช้งานทั่วไป">
                            </div>
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">ประเภทธุรกรรม</label>
                                <select class="form-select custom-form-control" id="transaction_type" name="transaction_type" required>
                                    <option value="buy">ซื้อสินค้า</option>
                                    <option value="topup">เติมเงิน</option>
                                </select>
                            </div>

                            <div id="productSelection" style="display: none;">
                                <h6 class="mt-4 mb-3">เลือกสินค้า (เฉพาะประเภท "ซื้อสินค้า")</h6>
                                <?php if (!empty($products_for_selection)): ?>
                                    <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                                        <?php foreach ($products_for_selection as $product): ?>
                                            <div class="col">
                                                <div class="card h-100 p-3 shadow-sm product-card"
                                                     data-product-id="<?php echo $product['id']; ?>"
                                                     data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                                                     data-product-price="<?php echo $product['price']; ?>"
                                                     data-product-stock="<?php echo $product['stock']; ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1 text-primary"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                                            <p class="mb-0 text-muted small">ราคา: ฿<?php echo number_format($product['price'], 2); ?> | สต็อก: <?php echo $product['stock']; ?></p>
                                                        </div>
                                                        <div class="input-group input-group-sm w-auto">
                                                            <button type="button" class="btn btn-outline-danger btn-sm decrease-quantity">-</button>
                                                            <input type="text" class="form-control text-center quantity-input" value="0" readonly style="width: 40px;">
                                                            <button type="button" class="btn btn-outline-success btn-sm increase-quantity">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">ยังไม่มีสินค้าให้เลือก กรุณาเพิ่มสินค้าในหน้า "จัดการสินค้า" ก่อน</p>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="amount" class="form-label">รวมยอดเงินที่ต้องชำระ (บาท)</label>
                                    <input type="number" step="0.01" class="form-control custom-form-control" id="amount" name="amount" required value="0.00" readonly>
                                </div>
                                <div id="selectedProductInputs">
                                    </div>
                            </div>

                            <div id="topupAmount" style="display: none;">
                                <div class="mb-3">
                                    <label for="topup_amount" class="form-label">จำนวนเงินเติม (บาท)</label>
                                    <input type="number" step="0.01" class="form-control custom-form-control" id="topup_amount" name="amount" required value="0.00">
                                </div>
                            </div>

                            <button type="submit" name="add_transaction" class="btn btn-success custom-btn-success">
                                <i class="fas fa-plus-circle me-2"></i> เพิ่มธุรกรรม
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        </div>
    <footer class="footer mt-5 py-4 text-center">
        <div class="container">
            <p class="mb-0 text-muted">&copy; 2025 NFC Cashless Institute. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const transactionTypeSelect = document.getElementById('transaction_type');
            const productSelectionDiv = document.getElementById('productSelection');
            const topupAmountDiv = document.getElementById('topupAmount');
            const manualAmountInput = document.getElementById('amount'); // Total amount for manual buy
            const topupAmountInput = document.getElementById('topup_amount'); // Amount for topup
            const selectedProductInputsDiv = document.getElementById('selectedProductInputs');

            // Function to update total amount and hidden inputs for selected products
            function updateManualAmountAndProducts() {
                let totalAmount = 0;
                // Clear existing hidden inputs
                selectedProductInputsDiv.innerHTML = '';

                document.querySelectorAll('.product-card').forEach(card => {
                    const productId = card.dataset.productId;
                    const productName = card.dataset.productName;
                    const productPrice = parseFloat(card.dataset.productPrice);
                    const quantityInput = card.querySelector('.quantity-input');
                    let quantity = parseInt(quantityInput.value);

                    if (quantity > 0) {
                        totalAmount += productPrice * quantity;

                        // Add hidden input for each selected product
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'selected_products[]';
                        // Format: product_id|quantity|price_per_unit
                        hiddenInput.value = `${productId}|${quantity}|${productPrice}`;
                        selectedProductInputsDiv.appendChild(hiddenInput);
                    }
                });
                manualAmountInput.value = totalAmount.toFixed(2);
            }

            // Show/hide product selection or top-up amount based on transaction type
            function toggleTransactionFields() {
                if (transactionTypeSelect.value === 'buy') {
                    productSelectionDiv.style.display = 'block';
                    topupAmountDiv.style.display = 'none';
                    manualAmountInput.setAttribute('required', 'required'); // Manual buy needs amount
                    topupAmountInput.removeAttribute('required'); // Topup amount not needed for buy
                    topupAmountInput.value = '0.00'; // Reset topup amount
                    updateManualAmountAndProducts(); // Recalculate total for buy
                } else if (transactionTypeSelect.value === 'topup') {
                    productSelectionDiv.style.display = 'none';
                    topupAmountDiv.style.display = 'block';
                    topupAmountInput.setAttribute('required', 'required'); // Topup needs amount
                    manualAmountInput.removeAttribute('required'); // Manual amount not needed for topup
                    manualAmountInput.value = '0.00'; // Reset manual amount
                    // Reset product quantities
                    document.querySelectorAll('.product-card .quantity-input').forEach(input => input.value = '0');
                    updateManualAmountAndProducts(); // Clear hidden product inputs
                }
            }

            // Initial toggle when page loads
            toggleTransactionFields();

            // Event listener for transaction type change
            transactionTypeSelect.addEventListener('change', toggleTransactionFields);

            // Quantity controls for products
            document.querySelectorAll('.product-card').forEach(card => {
                const decreaseBtn = card.querySelector('.decrease-quantity');
                const increaseBtn = card.querySelector('.increase-quantity');
                const quantityInput = card.querySelector('.quantity-input');
                const maxStock = parseInt(card.dataset.productStock);

                decreaseBtn.addEventListener('click', () => {
                    let quantity = parseInt(quantityInput.value);
                    if (quantity > 0) {
                        quantityInput.value = quantity - 1;
                        updateManualAmountAndProducts();
                    }
                });

                increaseBtn.addEventListener('click', () => {
                    let quantity = parseInt(quantityInput.value);
                    if (quantity < maxStock) {
                        quantityInput.value = quantity + 1;
                        updateManualAmountAndProducts();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'สต็อกไม่พอ',
                            text: `สินค้า ${card.dataset.productName} มีในสต็อกแค่ ${maxStock} ชิ้นเท่านั้น!`,
                            confirmButtonText: 'เข้าใจแล้ว'
                        });
                    }
                });
            });


            // Simulate NFC Tap for Buying (unchanged, but action points to nfc_simulate.php)
            const simulateNFCBuyBtn = document.getElementById('simulateNFCBuy');
            if (simulateNFCBuyBtn) {
                simulateNFCBuyBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'จำลองการแตะ NFC (ซื้อสินค้า)',
                        text: 'กำลังจำลองการแตะ... ระบบกำลังประมวลผลการซื้อสินค้า 100.00 บาท',
                        icon: 'info',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.timer) {
                            Swal.fire({
                                title: 'สำเร็จ!',
                                text: 'จำลองการซื้อสินค้า 100.00 บาท สำเร็จแล้ว! ระบบกำลังบันทึกข้อมูล.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = 'nfc_simulate.php';

                                const amountInput = document.createElement('input');
                                amountInput.type = 'hidden';
                                amountInput.name = 'amount';
                                amountInput.value = '100.00';
                                form.appendChild(amountInput);

                                const customerNameInput = document.createElement('input');
                                customerNameInput.type = 'hidden';
                                customerNameInput.name = 'customer_name';
                                customerNameInput.value = 'ลูกค้า NFC';
                                form.appendChild(customerNameInput);

                                const typeInput = document.createElement('input');
                                typeInput.type = 'hidden';
                                typeInput.name = 'transaction_type';
                                typeInput.value = 'buy';
                                form.appendChild(typeInput);

                                // Add a dummy product for simulated NFC buy
                                const dummyProductInput = document.createElement('input');
                                dummyProductInput.type = 'hidden';
                                dummyProductInput.name = 'selected_products[]';
                                // Assuming product ID 1 exists with price 100
                                dummyProductInput.value = '1|1|100.00'; // product_id|quantity|price_per_unit
                                form.appendChild(dummyProductInput);


                                const addButton = document.createElement('input');
                                addButton.type = 'hidden';
                                addButton.name = 'add_transaction';
                                addButton.value = '1';
                                form.appendChild(addButton);

                                document.body.appendChild(form);
                                form.submit();
                            });
                        }
                    });
                });
            }

            // Simulate NFC Tap for Topup (unchanged, but action points to nfc_simulate.php)
            const simulateNFCTopupBtn = document.getElementById('simulateNFCTopup');
            if (simulateNFCTopupBtn) {
                simulateNFCTopupBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'จำลองการแตะ NFC (เติมเงิน)',
                        text: 'กำลังจำลองการแตะ... ระบบกำลังประมวลผลการเติมเงิน 500.00 บาท',
                        icon: 'info',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.timer) {
                            Swal.fire({
                                title: 'สำเร็จ!',
                                text: 'จำลองการเติมเงิน 500.00 บาท สำเร็จแล้ว! ระบบกำลังบันทึกข้อมูล.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = 'nfc_simulate.php';

                                const amountInput = document.createElement('input');
                                amountInput.type = 'hidden';
                                amountInput.name = 'amount';
                                amountInput.value = '500.00';
                                form.appendChild(amountInput);

                                const customerNameInput = document.createElement('input');
                                customerNameInput.type = 'hidden';
                                customerNameInput.name = 'customer_name';
                                customerNameInput.value = 'ลูกค้า NFC (เติมเงิน)';
                                form.appendChild(customerNameInput);

                                const typeInput = document.createElement('input');
                                typeInput.type = 'hidden';
                                typeInput.name = 'transaction_type';
                                typeInput.value = 'topup';
                                form.appendChild(typeInput);

                                const addButton = document.createElement('input');
                                addButton.type = 'hidden';
                                addButton.name = 'add_transaction';
                                addButton.value = '1';
                                form.appendChild(addButton);

                                document.body.appendChild(form);
                                form.submit();
                            });
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>