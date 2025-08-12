<?php
// transactions.php
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
    } else {
        $message = "เกิดข้อผิดพลาดในการบันทึก: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Update Transaction
if (isset($_POST['update_transaction'])) {
    $id = $_POST['edit_id'];
    $amount = $_POST['edit_amount'];
    $customer_name = $_POST['edit_customer_name'];
    $type = $_POST['edit_type']; // ดึงค่า type มาด้วย

    $stmt = $conn->prepare("UPDATE transactions SET amount = ?, customer_name = ?, type = ? WHERE id = ?");
    $stmt->bind_param("dsi", $amount, $customer_name, $type, $id);

    if ($stmt->execute()) {
        $message = "อัปเดตข้อมูลเรียบร้อยแล้ว!";
    } else {
        $message = "เกิดข้อผิดพลาดในการอัปเดต: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Delete Transaction
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "ลบข้อมูลเรียบร้อยแล้ว!";
    } else {
        $message = "เกิดข้อผิดพลาดในการลบ: " . $stmt->error;
    }
    $stmt->close();
    header("Location: transactions.php"); // Redirect back to transactions page
    exit();
}

// Fetch all transactions for display
$sql = "SELECT * FROM transactions ORDER BY transaction_date DESC";
$result = $conn->query($sql);

$transactions = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NANO - รายการธุรกรรม</title>
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
                <a href="transactions.php" class="list-group-item list-group-item-action custom-list-item active">
                    <i class="fas fa-exchange-alt me-2"></i> รายการธุรกรรม
                </a>
                <a href="products.php" class="list-group-item list-group-item-action custom-list-item">
                    <i class="fas fa-shopping-cart me-2"></i> จัดการสินค้า
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
                        <i class="fas fa-exchange-alt me-2"></i> รายการธุรกรรม
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

                <h3 class="mb-4 dashboard-title">รายการธุรกรรมทั้งหมด</h3>

               
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover custom-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Transaction ID</th>
                                        <th>จำนวนเงิน</th>
                                        <th>ผู้ทำรายการ</th>
                                        <th>ประเภท</th>
                                        <th>วันที่/เวลา</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($transactions)): ?>
                                        <?php foreach ($transactions as $index => $transaction): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                                <td class="<?php echo ($transaction['type'] == 'topup' ? 'text-success' : 'text-danger'); ?>">
                                                    ฿<?php echo number_format($transaction['amount'], 2); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                                <td>
                                                    <?php
                                                        if ($transaction['type'] == 'buy') {
                                                            echo '<span class="badge custom-badge-danger">ซื้อสินค้า</span>';
                                                        } elseif ($transaction['type'] == 'topup') {
                                                            echo '<span class="badge custom-badge-success">เติมเงิน</span>';
                                                        } else {
                                                            echo '<span class="badge bg-secondary">' . htmlspecialchars($transaction['type']) . '</span>';
                                                        }
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                                                <td><span class="badge custom-badge-success"><?php echo htmlspecialchars($transaction['status']); ?></span></td>
                                                <td>
                                                    <button class="btn btn-sm custom-btn-warning edit-btn"
                                                            data-id="<?php echo $transaction['id']; ?>"
                                                            data-amount="<?php echo $transaction['amount']; ?>"
                                                            data-customer_name="<?php echo htmlspecialchars($transaction['customer_name']); ?>"
                                                            data-type="<?php echo htmlspecialchars($transaction['type']); ?>">
                                                        แก้ไข
                                                    </button>
                                                    <a href="transactions.php?delete_id=<?php echo $transaction['id']; ?>" class="btn btn-sm custom-btn-danger delete-btn">ลบ</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">ยังไม่มีข้อมูลการทำธุรกรรม</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <hr class="my-5" id="nfc-simulate-section">

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
                        <form method="POST">
                            <div class="mb-3">
                                <label for="amount" class="form-label">จำนวนเงินที่ต้องการโอน (บาท)</label>
                                <input type="number" step="0.01" class="form-control custom-form-control" id="amount" name="amount" required value="100.00">
                            </div>
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
                            <button type="submit" name="add_transaction" class="btn btn-success custom-btn-success">
                                <i class="fas fa-plus-circle me-2"></i> เพิ่มธุรกรรม
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        </div>
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content custom-card">
                <div class="modal-header custom-card-header-warning">
                    <h5 class="modal-title" id="editTransactionModalLabel">แก้ไขธุรกรรม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST">
                        <input type="hidden" id="edit_id" name="edit_id">
                        <div class="mb-3">
                            <label for="edit_amount" class="form-label">จำนวนเงิน (บาท)</label>
                            <input type="number" step="0.01" class="form-control custom-form-control" id="edit_amount" name="edit_amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_customer_name" class="form-label">ชื่อผู้ทำรายการ</label>
                            <input type="text" class="form-control custom-form-control" id="edit_customer_name" name="edit_customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_type" class="form-label">ประเภทธุรกรรม</label>
                            <select class="form-select custom-form-control" id="edit_type" name="edit_type" required>
                                <option value="buy">ซื้อสินค้า</option>
                                <option value="topup">เติมเงิน</option>
                            </select>
                        </div>
                        <button type="submit" name="update_transaction" class="btn custom-btn-warning">บันทึกการแก้ไข</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-5 py-4 text-center">
        <div class="container">
            <p class="mb-0 text-muted">&copy; 2025 NANO Cashless Institute. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>
</body>
</html>