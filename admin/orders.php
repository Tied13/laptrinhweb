<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

//BE4
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);

// Lấy toàn bộ đơn hàng đổ ra bảng danh sách
$orders = $orderModel->getAllOrders();

// Đọc thông báo sau khi xử lý (flash message) rồi xóa khỏi session
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$statusMap = [
    0 => ['label' => 'Chờ xử lý', 'class' => 'badge-pending'],
    1 => ['label' => 'Đang giao', 'class' => 'badge-shipping'],
    2 => ['label' => 'Hoàn thành', 'class' => 'badge-done'],
    3 => ['label' => 'Đã hủy',    'class' => 'badge-cancel'],
];

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Quản lý đơn hàng</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<?php include '../includes/navbar_admin.php'; ?>

<div class="admin-content">
    <div class="admin-page-header">
        <h2>Quản lý đơn hàng</h2>
        <button type="button" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>
            + Thêm đơn hàng
        </button>
    </div>
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
                $currentStatus = (int)$o['status'];
                $badge = $statusMap[$currentStatus]
                    ?? [
                        'label' => 'Không rõ',
                        'class' => 'badge-default'
                    ];
            ?>
                <tr>
                    <td>
                        #<?php echo (int)$o['id']; ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($o['customer_name']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($o['customer_phone']); ?>
                    </td>
                    <td>
                        <?php
                        echo htmlspecialchars(
                            date(
                                'd/m/Y H:i',
                                strtotime($o['created_at'])
                            )
                        );
                        ?>
                    </td>
                    <td>
                        <?php
                        echo number_format(
                            $o['total_price'],
                            0,
                            ',',
                            '.'
                        );
                        ?>đ
                    </td>

                    <td>
                        <span
                            class="badge <?php echo $badge['class']; ?>"
                            id="badge-<?php echo (int)$o['id']; ?>"
                        >
                            <?php echo $badge['label']; ?>
                        </span>
                        <form
                            action="?controller=order&action=updateStatus"
                            method="POST"
                            class="status-form"
                        >
                            <input
                                type="hidden"
                                name="id"
                                value="<?php echo (int)$o['id']; ?>"
                            >
                            <select
                                name="status"
                                class="status-select"
                                data-badge-target="badge-<?php echo (int)$o['id']; ?>"
                                onchange="this.form.requestSubmit()"
                            >
                                <?php foreach ($statusMap as $val => $info): ?>
                                    <option
                                        value="<?php echo $val; ?>"
                                        <?php echo $currentStatus === $val ? 'selected' : ''; ?>
                                    >
                                        <?php echo $info['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>

                    <td class="admin-actions">
                        <a
                            href="?controller=order&action=detail&id=<?php echo (int)$o['id']; ?>"
                            class="btn btn-edit"
                        >
                            <i class="bi bi-eye"></i>
                            Xem
                        </a>
                        <a
                            href="?controller=order&action=delete&id=<?php echo (int)$o['id']; ?>"
                            class="btn btn-delete"
                            data-confirm="Bạn có chắc muốn xóa đơn hàng #<?php echo (int)$o['id']; ?> không?"
                        >
                            <i class="bi bi-trash"></i>
                            Xóa
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
</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>
