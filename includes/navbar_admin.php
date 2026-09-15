<!-- includes/navbar_admin.php -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .admin-menu a i,
    .admin-logout a i {
        font-size: 1.15rem;
        transition: transform 0.2s ease, color 0.2s ease;
        display: inline-block;
    }
    .admin-menu a:hover i {
        transform: scale(1.2);
    }
    .admin-logout a:hover i {
        transform: scale(1.2);
    }
</style>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <h2>La Beaute Store</h2>
    </div>
    <!-- Menu Admin -->
    <nav class="admin-menu">
        <a href="index.php">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="products.php">
            <i class="bi bi-box-seam"></i>
            <span>Quản lý sản phẩm</span>
        </a>
        <a href="categories.php">
            <i class="bi bi-tags"></i>
            <span>Quản lý danh mục</span>
        </a>
        <a href="orders.php">
            <i class="bi bi-receipt"></i>
            <span>Quản lý đơn hàng</span>
        </a>
        <a href="users.php">
            <i class="bi bi-people"></i>
            <span>Quản lý người dùng</span>
        </a>
        <a href="../index.php">
            <i class="bi bi-house"></i>
            <span>Về cửa hàng</span>
        </a>
    </nav>
    <!-- Đăng xuất -->
    <div class="admin-logout">
        <a href="../logout.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>Đăng xuất</span>
        </a>
    </div>
</aside>
