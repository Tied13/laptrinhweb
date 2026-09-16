<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);

$orders = $orderModel->getAllOrders();

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$statusMap = [
    0 => ['label' => 'Chờ xử lý', 'class' => 'badge-pending'],
    1 => ['label' => 'Đang giao', 'class' => 'badge-shipping'],
    2 => ['label' => 'Hoàn thành', 'class' => 'badge-done'],
    3 => ['label' => 'Đã hủy',    'class' => 'badge-cancel'],
];

$viewOrder = null;
$viewDetails = [];
if (isset($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    $viewOrder = $orderModel->getOrderById($viewId);
    if ($viewOrder) { 
        $viewDetails = $orderModel->getOrderDetails($viewId); 
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

    <?php if (file_exists('../includes/navbar_admin.php')) include '../includes/navbar_admin.php'; ?>

    <div class="admin-content">
        <div class="admin-page-header">
            <h2>Quản lý đơn hàng</h2>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="admin-section-title">
            <h3>Danh sách đơn hàng</h3>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $o):
                        $currentStatus = (int)($o['status'] ?? 0);
                        $badge = $statusMap[$currentStatus] ?? ['label' => 'Không rõ', 'class' => 'badge-default'];
                    ?>
                    <tr>
                        <td>#<?php echo (int)$o['id']; ?></td>
                        <td><?php echo htmlspecialchars($o['customer_name'] ?? $o['fullname'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($o['customer_phone'] ?? $o['phone'] ?? 'N/A'); ?></td>
                        <td>
                            <?php echo !empty($o['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($o['created_at']))) : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo number_format($o['total_price'] ?? $o['total'] ?? 0, 0, ',', '.'); ?>đ
                        </td>

                        <td>
                            <span class="badge <?php echo $badge['class']; ?>" id="badge-<?php echo (int)$o['id']; ?>">
                                <?php echo $badge['label']; ?>
                            </span>
                            <form action="../controllers/OrderController.php?action=updateStatus" method="POST" class="status-form" style="display:inline-block; margin-left: 5px;">
                                <input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <?php foreach ($statusMap as $val => $info): ?>
                                        <option value="<?php echo $val; ?>" <?php echo $currentStatus === $val ? 'selected' : ''; ?>>
                                            <?php echo $info['label']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>

                        <td class="admin-actions">
                            <a href="order.php?view=<?php echo (int)$o['id']; ?>" class="btn btn-edit">
                                <i class="bi bi-eye"></i> Xem
                            </a>
                            <a href="../controllers/OrderController.php?action=delete&id=<?php echo (int)$o['id']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Bạn có chắc muốn xóa đơn hàng #<?php echo (int)$o['id']; ?> không?');">
                                <i class="bi bi-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-category">
                                <i class="bi bi-receipt"></i>
                                <strong>🧾 Chưa có đơn hàng</strong>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (isset($_GET['view'])): ?>
            <?php if ($viewOrder): ?>
            <div class="dashboard-card" style="margin-top: 20px; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #fff;">
                <h3>Chi tiết đơn hàng #<?php echo (int)$viewOrder['id']; ?></h3>
                <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($viewOrder['customer_name'] ?? $viewOrder['fullname'] ?? 'N/A'); ?></p>
                <p><strong>SĐT:</strong> <?php echo htmlspecialchars($viewOrder['customer_phone'] ?? $viewOrder['phone'] ?? 'N/A'); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($viewOrder['customer_address'] ?? $viewOrder['address'] ?? 'N/A'); ?></p>
                
                <table class="admin-table" style="margin-top: 15px; width: 100%;">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>SL</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($viewDetails)): ?>
                        <?php foreach ($viewDetails as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d['product_name'] ?? ('Sản phẩm #' . ($d['product_id'] ?? ''))); ?></td>
                                <td><?php echo number_format($d['price'] ?? 0, 0, ',', '.'); ?>đ</td>
                                <td><?php echo (int)($d['quantity'] ?? 1); ?></td>
                                <td><?php echo number_format(($d['price'] ?? 0) * ($d['quantity'] ?? 1), 0, ',', '.'); ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Không có chi tiết sản phẩm.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <p style="font-weight:bold; margin-top:15px; font-size: 1.1em;">
                    Tổng tiền: <span style="color: red;"><?php echo number_format($viewOrder['total_price'] ?? $viewOrder['total'] ?? 0, 0, ',', '.'); ?>đ</span>
                </p>
                <a href="order.php" class="btn btn-edit" style="display: inline-block; margin-top: 10px; padding: 6px 15px; text-decoration: none;">Đóng</a>
            </div>
            <?php else: ?>
            <div class="alert alert-danger" style="margin-top: 20px;">Đơn hàng không tồn tại.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="../assets/js/admin.js"></script>

</body>
</html>
