<?php
// Khởi tạo session nếu chưa được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Tính tổng số lượng sản phẩm trong giỏ hàng
$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $count += (int)$qty;
    }
}
// Lấy tên người dùng nếu đã đăng nhập
$username = $_SESSION['username'] ?? '';
?>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Thanh thông báo -->
<div class="topbar">
    <div class="topbar-text">
        Chào mừng đến với thế giới gấu bông – Gặp gấu xinh, gặp đúng gu ^^
    </div>
</div>
<!-- Header chính -->
<header class="navbar">
    <div class="nav-left">
        <h1 class="logo">Cửa hàng gấu bông</h1>
    </div>
    <nav class="nav-center">
        <a href="index.php">Trang Chủ</a>
        <a href="products.php">Sản Phẩm</a>
        <a href="contact.php">Liên Hệ</a>
    </nav>
    <div class="icons">
        <form class="search-box" action="products.php" method="get">
            <input
                type="text"
                name="keyword"
                value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>"
                placeholder="Tìm kiếm sản phẩm..."
            >
            <button type="submit" title="Tìm kiếm">
                <i class="bi bi-search"></i>
            </button>
        </form>
        <a href="cart.php" class="cart-icon" title="Giỏ hàng">
            <i class="bi bi-cart3"></i>
            <?php if ($count > 0): ?>
                <span class="cart-count">
                    <?php echo $count; ?>
                </span>
            <?php endif; ?>
        </a>
        <?php if ($username !== ''): ?>
            <span class="welcome-user">
                Xin chào,
                <strong>
                    <?php echo htmlspecialchars($username); ?>
                </strong>
            </span>
        <?php else: ?>
            <a href="login.php" title="Đăng nhập">
                <i class="bi bi-person"></i>
            </a>
        <?php endif; ?>
    </div>
</header>