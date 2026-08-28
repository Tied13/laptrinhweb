<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Tính tổng số lượng sản phẩm trong giỏ hàng
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += is_array($item) ? ($item['quantity'] ?? 1) : (int)$item;
    }
}

// 2. Lấy thông tin tìm kiếm hiện tại
$search_val = $_GET['search'] ?? ($_GET['keyword'] ?? '');
?>

<!-- Header & Thanh Tìm Kiếm -->
<header>
    <div class="container header-content">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-heart"></i> Gấu Bông Store
        </a>

        <form action="products.php" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Nhập tên sản phẩm cần tìm..."
                value="<?php echo htmlspecialchars($search_val); ?>">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
        </form>

        <div class="header-right">
            <!-- Biểu tượng Giỏ hàng -->
            <a href="cart.php" class="cart-icon-btn" title="Xem giỏ hàng">
                <i class="fa-solid fa-bag-shopping"></i>
                <span class="cart-badge-num"><?php echo $cart_count; ?></span>
            </a>

            <!-- Trạng thái Đăng nhập / Tài khoản -->
            <?php if (isset($_SESSION['username'])): ?>
            <div class="user-logged-box" style="display: flex; align-items: center; gap: 8px;">
                <a href="<?php echo ((int)($_SESSION['role'] ?? 0) === 1) ? 'admin/products.php' : '#'; ?>"
                    class="user-account-btn" title="Trang tài khoản">
                    <i class="fa-regular fa-user"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>
                <a href="controllers/AuthController.php?action=logout"
                    style="color: #888; font-size: 13px; text-decoration: none;" title="Đăng xuất">(Thoát)</a>
            </div>
            <?php else: ?>
            <a href="login.php" class="user-account-btn">
                <i class="fa-regular fa-user"></i>
                <span>Đăng nhập</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Navigation Bar -->
<nav>
    <div class="container nav-content">
        <div class="category-btn">
            <i class="fa-solid fa-bars"></i> DANH MỤC
        </div>
        <ul class="main-menu">
            <li><a href="index.php">Trang chủ</a></li>
            <li><a href="products.php">Sản phẩm</a></li>
            <li><a href="#">Tin tức</a></li>
            <li><a href="#">Giới thiệu</a></li>
            <li><a href="contact.php">Liên hệ</a></li>
        </ul>
    </div>
</nav>