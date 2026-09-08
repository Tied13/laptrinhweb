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

// 1. Thống kê 4 ô trên cùng
$totalRevenue = $orderModel->getTotalRevenue();
$totalOrders  = $orderModel->countOrders();

$stmt = $db->query("SELECT COUNT(id) AS total FROM products");
$totalProducts = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT COUNT(id) AS total FROM users");
$totalUsers = $stmt->fetchColumn() ?: 0;

// 2. Lấy 5 đơn hàng gần đây nhất
$stmt = $db->query("SELECT id, customer_name, total_price, status, created_at 
                    FROM orders 
                    ORDER BY id DESC 
                    LIMIT 5");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Lấy 5 sản phẩm mới cập nhật nhất (thay cho tồn kho vì bảng products không có stock)
$stmt = $db->query("SELECT id, name, price, thumbnail, created_at 
                    FROM products 
                    ORDER BY id DESC 
                    LIMIT 5");
$latestProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Doanh thu hôm nay
$stmt = $db->query("SELECT SUM(total_price) 
                    FROM orders 
                    WHERE DATE(created_at) = CURDATE() AND status != 3");
$todayRevenue = (float)($stmt->fetchColumn() ?: 0);

// 5. Thống kê doanh thu 7 ngày gần nhất để vẽ biểu đồ Chart.js
$stmt = $db->query("SELECT DATE_FORMAT(created_at, '%d/%m') AS order_date, 
                           SUM(total_price) AS daily_total
                    FROM orders
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND status != 3
                    GROUP BY DATE(created_at)
                    ORDER BY DATE(created_at) ASC");
$weeklyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <div class="admin-page-header">
            <h2>Dashboard</h2>
        </div>

        <!-- 4 Khối thống kê -->
        <div class="dashboard-stats">
            <div class="stat-box stat-revenue">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <span class="stat-label">Tổng doanh thu</span>
                    <span class="stat-value"><?= number_format((float)($totalRevenue ?? 0), 0, ',', '.') ?>đ</span>
                </div>
            </div>
            <div class="stat-box stat-orders">
                <div class="stat-icon">🧾</div>
                <div class="stat-info">
                    <span class="stat-label">Tổng đơn hàng</span>
                    <span class="stat-value"><?= (int)($totalOrders ?? 0) ?></span>
                </div>
            </div>
            <div class="stat-box stat-products">
                <div class="stat-icon">🧸</div>
                <div class="stat-info">
                    <span class="stat-label">Sản phẩm</span>
                    <span class="stat-value"><?= (int)($totalProducts ?? 0) ?></span>
                </div>
            </div>
            <div class="stat-box stat-users">
                <div class="stat-icon">👤</div>
                <div class="stat-info">
                    <span class="stat-label">Khách hàng</span>
                    <span class="stat-value"><?= (int)($totalUsers ?? 0) ?></span>
                </div>
            </div>
        </div>

        <div class="dashboard-main">
            <!-- Đơn hàng gần đây -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>🧾 Đơn hàng gần đây</h3>
                </div>
                <?php if (!empty($recentOrders)): ?>
                <ul class="dashboard-list">
                    <?php foreach ($recentOrders as $order): ?>
                    <li>
                        <div>
                            <strong>#<?= (int)$order['id'] ?> -
                                <?= htmlspecialchars($order['customer_name']) ?></strong>
                            <br>
                            <small><?= date('H:i d/m/Y', strtotime($order['created_at'])) ?></small>
                        </div>
                        <span style="font-weight: bold; color: #10B981;">
                            <?= number_format((float)$order['total_price'], 0, ',', '.') ?>đ
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="dashboard-empty">Chưa có đơn hàng nào</p>
                <?php endif; ?>
            </div>

            <!-- Sản phẩm mới cập nhật -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3>🧸 Sản phẩm mới cập nhật</h3>
                </div>
                <?php if (!empty($latestProducts)): ?>
                <ul class="dashboard-list">
                    <?php foreach ($latestProducts as $prod): ?>
                    <li>
                        <div>
                            <strong><?= htmlspecialchars($prod['name']) ?></strong>
                            <br>
                            <small>Giá bán: <?= number_format((float)$prod['price'], 0, ',', '.') ?>đ</small>
                        </div>
                        <span class="stock-warning" style="color: #6366F1;">
                            <?= date('d/m/Y', strtotime($prod['created_at'])) ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="dashboard-empty">Chưa có dữ liệu sản phẩm</p>
                <?php endif; ?>
            </div>

            <!-- Biểu đồ doanh thu -->
            <div class="dashboard-card chart-card">
                <div class="dashboard-card-header">
                    <h3>📈 Doanh thu các ngày gần đây</h3>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="today-revenue" style="margin-top: 20px;">
            <h3>💰 Doanh thu hôm nay</h3>
            <div class="today-revenue-value">
                <?= number_format($todayRevenue, 0, ',', '.') ?>đ
            </div>
            <p class="today-update">Cập nhật lúc <?= date('H:i d/m/Y') ?></p>
        </div>
    </div>

    <script>
    // Dữ liệu biểu đồ chuyển từ PHP sang JS
    window.weeklyRevenue = <?= json_encode($weeklyRevenue, JSON_UNESCAPED_UNICODE) ?>;

    // Tự động khởi tạo Chart nếu file admin.js chưa cấu hình
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueChart');
        if (ctx && window.weeklyRevenue) {
            const labels = window.weeklyRevenue.map(item => item.order_date);
            const data = window.weeklyRevenue.map(item => Number(item.daily_total));

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.length ? labels : ['Hôm nay'],
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: data.length ? data : [<?= (float)$todayRevenue ?>],
                        backgroundColor: '#9333EA',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
    </script>

    <script src="../assets/js/admin.js"></script>
</body>

</html>