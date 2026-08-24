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
</head>
<body>
<?php include '../includes/navbar_admin.php'; ?>
<div class="admin-content">
    <div class="admin-page-header">
        <h2>Tổng quan</h2>
    </div>

    <div class="dashboard-stats">
        <div class="stat-box stat-revenue">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <span class="stat-label">Doanh thu</span>
                <span class="stat-value"><?= number_format((float)($totalRevenue ?? 0), 0, ',', '.') ?>đ</span>
            </div>
        </div>

        <div class="stat-box stat-orders">
            <div class="stat-icon">🧾</div>
            <div class="stat-info">
                <span class="stat-label">Đơn hàng</span>
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
                <span class="stat-label">Người dùng</span>
                <span class="stat-value"><?= (int)($totalUsers ?? 0) ?></span>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/admin.js"></script>
</body>
</html>