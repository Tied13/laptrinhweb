<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chưa đăng nhập thì quay về login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Order.php';

$database = new Database();
$db = $database->getConnection();

$orderModel = new Order($db);

// Lấy ID user đang đăng nhập
$userId = (int)($_SESSION['user_id'] ?? 0);

// Lấy ID đơn hàng trên URL
$orderId = (int)($_GET['id'] ?? 0);

// Nếu không có user_id hoặc order_id
if ($userId <= 0 || $orderId <= 0) {
    header('Location: account.php');
    exit();
}

// Lấy đơn hàng
$order = $orderModel->getOrderById($orderId);

// Không tìm thấy đơn hoặc đơn không thuộc user này
if (!$order || (int)$order['user_id'] !== $userId) {
    header('Location: account.php');
    exit();
}

// Lấy chi tiết sản phẩm
$details = $orderModel->getOrderDetails($orderId);

$statusMap = [
    0 => ['label' => 'Chờ xử lý', 'class' => 'badge-pending'],
    1 => ['label' => 'Đang giao', 'class' => 'badge-shipping'],
    2 => ['label' => 'Hoàn thành', 'class' => 'badge-done'],
    3 => ['label' => 'Đã hủy', 'class' => 'badge-cancel'],
];

$currentStatus = (int)$order['status'];

$badge = $statusMap[$currentStatus]
    ?? [
        'label' => 'Không rõ',
        'class' => 'badge-pending'
    ];

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Chi tiết đơn hàng - La Beaute Store</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/order-detail.css">

</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main class="order-detail-page">

        <div class="container">

            <h2 class="order-detail-title">
                <i class="fa-solid fa-receipt"></i>
                Chi tiết đơn hàng #<?php echo (int)$order['id']; ?>
            </h2>

            <!-- Thông tin đơn hàng -->
            <div class="order-info-box">

                <h3>
                    <i class="fa-solid fa-truck"></i>
                    Thông tin giao hàng
                </h3>

                <p>
                    <strong>Họ tên:</strong>
                    <?php echo htmlspecialchars($order['customer_name']); ?>
                </p>

                <p>
                    <strong>Số điện thoại:</strong>
                    <?php echo htmlspecialchars($order['customer_phone']); ?>
                </p>

                <p>
                    <strong>Địa chỉ:</strong>
                    <?php echo htmlspecialchars($order['customer_address']); ?>
                </p>

                <p>
                    <strong>Ngày đặt:</strong>
                    <?php
                    echo htmlspecialchars(
                        date(
                            'd/m/Y H:i',
                            strtotime($order['created_at'])
                        )
                    );
                    ?>
                </p>

                <p>
                    <strong>Trạng thái:</strong>

                    <span class="badge <?php echo $badge['class']; ?>">
                        <?php echo $badge['label']; ?>
                    </span>
                </p>

            </div>

            <!-- Danh sách sản phẩm -->
            <div class="order-info-box">

                <h3>
                    <i class="fa-solid fa-box"></i>
                    Sản phẩm đã đặt
                </h3>

                <div class="order-products">

                    <table class="order-table">

                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($details)): ?>

                            <?php foreach ($details as $d): ?>

                            <tr>

                                <td>
                                    <?php
                                            echo htmlspecialchars(
                                                $d['product_name'] ?? '(đã xóa)'
                                            );
                                            ?>
                                </td>

                                <td>
                                    <?php
                                            echo number_format(
                                                $d['price'],
                                                0,
                                                ',',
                                                '.'
                                            );
                                            ?>đ
                                </td>

                                <td>
                                    <?php echo (int)$d['quantity']; ?>
                                </td>

                                <td>
                                    <?php
                                            echo number_format(
                                                $d['price'] * $d['quantity'],
                                                0,
                                                ',',
                                                '.'
                                            );
                                            ?>đ
                                </td>

                            </tr>

                            <?php endforeach; ?>

                            <?php else: ?>

                            <tr>
                                <td colspan="4">
                                    Không có sản phẩm trong đơn hàng.
                                </td>
                            </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <div class="order-total">
                    Tổng tiền:
                    <?php
                    echo number_format(
                        $order['total_price'],
                        0,
                        ',',
                        '.'
                    );
                    ?>đ
                </div>

            </div>

            <a href="account.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại lịch sử đơn hàng
            </a>

        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>

</html>