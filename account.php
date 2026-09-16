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

// Lấy lịch sử đơn hàng của user
$orders = [];

if ($userId > 0) {
    $orders = $orderModel->getOrdersByUserId($userId);
}

$statusMap = [
    0 => ['label' => 'Chờ xử lý', 'class' => 'badge-pending'],
    1 => ['label' => 'Đang giao', 'class' => 'badge-shipping'],
    2 => ['label' => 'Hoàn thành', 'class' => 'badge-done'],
    3 => ['label' => 'Đã hủy', 'class' => 'badge-cancel'],
];

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lịch sử đơn hàng - La Beaute Store</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    .account-page {
        padding: 40px 0;
    }

    .account-title {
        color: #bc02ad;
        margin-bottom: 25px;
    }

    .order-history {
        width: 100%;
        overflow-x: auto;
    }

    .order-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .order-table th,
    .order-table td {
        padding: 14px;
        border-bottom: 1px solid #eee;
        text-align: left;
    }

    .order-table th {
        background: #fdf0fa;
        color: #bc02ad;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 13px;
        display: inline-block;
    }

    .badge-pending {
        background: #fff3cd;
        color: #856404;
    }

    .badge-shipping {
        background: #cfe2ff;
        color: #084298;
    }

    .badge-done {
        background: #d1e7dd;
        color: #0f5132;
    }

    .badge-cancel {
        background: #f8d7da;
        color: #842029;
    }

    .empty-order {
        text-align: center;
        padding: 40px;
        color: #888;
    }

    .btn-view-order {
        color: #bc02ad;
        text-decoration: none;
        font-weight: 600;
    }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main class="account-page">

        <div class="container">

            <h2 class="account-title">
                <i class="fa-solid fa-receipt"></i>
                Lịch sử đơn hàng
            </h2>

            <p>
                Xin chào,
                <strong>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </strong>
            </p>

            <div class="order-history">

                <table class="order-table">

                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($orders)): ?>

                        <?php foreach ($orders as $o): ?>

                        <?php
                                $currentStatus = (int)$o['status'];

                                $badge = $statusMap[$currentStatus]
                                    ?? [
                                        'label' => 'Không rõ',
                                        'class' => 'badge-pending'
                                    ];
                                ?>

                        <tr>

                            <td>
                                #<?php echo (int)$o['id']; ?>
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
                                <span class="badge <?php echo $badge['class']; ?>">
                                    <?php echo $badge['label']; ?>
                                </span>
                            </td>

                            <td>
                                <a href="order.php?id=<?php echo (int)$o['id']; ?>" class="btn-view-order">
                                    Xem đơn
                                </a>
                            </td>

                        </tr>

                        <?php endforeach; ?>

                        <?php else: ?>

                        <tr>
                            <td colspan="5">
                                <div class="empty-order">
                                    <i class="fa-solid fa-box-open"></i>
                                    <p>Bạn chưa có đơn hàng nào.</p>
                                    <a href="products.php">
                                        Mua sắm ngay
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>

</html>