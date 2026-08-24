<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

// == PHẦN BE4 ==

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
                    $badge = $statusMap[$currentStatus] ?? ['label' => 'Không rõ', 'class' => 'badge-default'];
                ?>
                <tr>
                    <td>#<?php echo (int)$o['id']; ?></td>
                    <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($o['customer_phone']); ?></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($o['created_at']))); ?></td>
                    <td><?php echo number_format($o['total_price'], 0, ',', '.'); ?>đ</td>
                    <td>
                        <span class="badge <?php echo $badge['class']; ?>" id="badge-<?php echo (int)$o['id']; ?>">
                            <?php echo $badge['label']; ?>
                        </span>
                        <form action="?controller=order&action=updateStatus" method="POST" class="status-form">
                            <input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
                            <select name="status" class="status-select" data-badge-target="badge-<?php echo (int)$o['id']; ?>" onchange="this.form.requestSubmit()">
                                <?php foreach ($statusMap as $val => $info): ?>
                                    <option value="<?php echo $val; ?>" <?php echo $currentStatus === $val ? 'selected' : ''; ?>>
                                        <?php echo $info['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td class="admin-actions">
                        <a href="?controller=order&action=detail&id=<?php echo (int)$o['id']; ?>" class="btn btn-edit">Xem</a>
                        <a href="?controller=order&action=delete&id=<?php echo (int)$o['id']; ?>" class="btn btn-delete" data-confirm="Bạn có chắc muốn xóa đơn hàng #<?php echo (int)$o['id']; ?> không?">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty-message">Chưa có đơn hàng</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="../assets/js/admin.js"></script>
</body>
</html>