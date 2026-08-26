<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

// == PHẦN BE4 ==

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<?php include '../includes/navbar_admin.php'; ?>

<div class="admin-content">
    <!-- Tiêu đề -->
    <div class="admin-page-header">
        <h2>Dashboard</h2>
    </div>
    <div class="dashboard-stats">
        <div class="stat-box stat-revenue">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <span class="stat-label">Tổng doanh thu</span>
                <span class="stat-value">
                    <?= number_format((float)($totalRevenue ?? 0), 0, ',', '.') ?>đ
                </span>
            </div>
        </div>
        <div class="stat-box stat-orders">
            <div class="stat-icon">🧾</div>
            <div class="stat-info">
                <span class="stat-label">Tổng đơn hàng</span>
                <span class="stat-value">
                    <?= (int)($totalOrders ?? 0) ?>
                </span>
            </div>
        </div>
        <div class="stat-box stat-products">
            <div class="stat-icon">🧸</div>
            <div class="stat-info">
                <span class="stat-label">Sản phẩm</span>
                <span class="stat-value">
                    <?= (int)($totalProducts ?? 0) ?>
                </span>
            </div>
        </div>
        <div class="stat-box stat-users">
            <div class="stat-icon">👤</div>
            <div class="stat-info">
                <span class="stat-label">User</span>
                <span class="stat-value">
                    <?= (int)($totalUsers ?? 0) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="dashboard-main">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3>🧾 Đơn hàng gần đây</h3>
            </div>
            <?php if (!empty($recentOrders) && is_iterable($recentOrders)): ?>
                <ul class="dashboard-list">
                    <?php foreach ($recentOrders as $order): ?>
                        <li>
                            <div>
                                <strong>
                                    #<?= htmlspecialchars($order['id'] ?? '') ?>
                                </strong>
                                <small>
                                    <?= htmlspecialchars($order['created_at'] ?? '') ?>
                                </small>
                            </div>
                            <span>
                                <?= number_format(
                                    (float)($order['total'] ?? 0),
                                    0,
                                    ',',
                                    '.'
                                ) ?>đ
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="dashboard-empty">
                    Chưa có dữ liệu đơn hàng
                </p>
            <?php endif; ?>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3>⚠️ Sản phẩm sắp hết hàng</h3>
            </div>
            <?php if (!empty($lowStockProducts) && is_iterable($lowStockProducts)): ?>
                <ul class="dashboard-list">
                    <?php foreach ($lowStockProducts as $product): ?>
                        <li>
                            <div>
                                <strong>
                                    <?= htmlspecialchars($product['name'] ?? '') ?>
                                </strong>
                                <small>
                                    Sản phẩm trong kho
                                </small>
                            </div>
                            <span class="stock-warning">
                                <?= (int)($product['stock_quantity'] ?? 0) ?>
                                sản phẩm
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>

                <p class="dashboard-empty">
                    Chưa có dữ liệu sản phẩm sắp hết hàng
                </p>
            <?php endif; ?>
        </div>

        <div class="dashboard-card chart-card">
            <div class="dashboard-card-header">
                <h3>📈 Doanh thu 7 ngày gần đây</h3>
            </div>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="today-revenue">
        <h3>💰 Doanh thu hôm nay</h3>
        <div class="today-revenue-value">
            <?= number_format(
                (float)($todayRevenue ?? 0),
                0,
                ',',
                '.'
            ) ?>đ
        </div>
        <p class="today-update">
            Cập nhật lúc <?= date('H:i d/m/Y') ?>
        </p>
    </div>
</div>

<script>
    window.weeklyRevenue = <?= json_encode(
        $weeklyRevenue ?? [],
        JSON_UNESCAPED_UNICODE
    ) ?>;
</script>

<script src="../assets/js/admin.js"></script>

</body>
</html>