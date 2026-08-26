<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';

$database = new Database();
$conn = $database->getConnection();

$productModel = new Product($conn);
$categoryModel = new Category($conn);

// 1. Lấy tham số lọc & tìm kiếm
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 2. Lấy danh mục đang kích hoạt
$categories = $categoryModel->getAll();

// 3. Lấy tối đa 8 sản phẩm hiển thị trên Trang chủ
$products = $productModel->getAll($search, $category_id, 8, 0);

// 4. Đếm tổng số lượng sản phẩm trong Giỏ hàng
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += is_array($item) ? ($item['quantity'] ?? 1) : (int)$item;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Gấu Bông Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <!-- Header & Thanh Tìm Kiếm -->
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-heart"></i> Gấu Bông Store
            </a>

            <form action="index.php" method="GET" class="search-box">
                <input type="text" name="search" placeholder="Nhập tên sản phẩm cần tìm..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
            </form>

            <div class="header-right">
                <!-- Biểu tượng Giỏ hàng -->
                <a href="cart.php" class="cart-icon-btn">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-badge-num"><?php echo $cart_count; ?></span>
                </a>

                <!-- Trạng thái Tài khoản -->
                <?php if (isset($_SESSION['username'])): ?>
                <div class="user-logged-box" style="display: flex; align-items: center; gap: 8px;">
                    <a href="<?php echo (isset($_SESSION['role']) && (int)$_SESSION['role'] === 1) ? 'admin/products.php' : '#'; ?>"
                        class="user-account-btn">
                        <i class="fa-regular fa-user"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </a>
                    <a href="controllers/AuthController.php?action=logout"
                        style="color: #888; font-size: 13px; text-decoration: none;">(Thoát)</a>
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
                <li><a href="#">Liên hệ</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section (Sidebar & Banner Slide) -->
    <div class="container hero-section">
        <!-- Sidebar Danh mục sản phẩm -->
        <aside class="sidebar">
            <ul>
                <li>
                    <a href="index.php" class="<?php echo $category_id === 0 ? 'active' : ''; ?>">
                        <span>Tất cả sản phẩm</span> <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
                <?php foreach ($categories as $cat_item): ?>
                <li>
                    <a href="index.php?category=<?php echo $cat_item['id']; ?>"
                        class="<?php echo $category_id === (int)$cat_item['id'] ? 'active' : ''; ?>">
                        <span><?php echo htmlspecialchars($cat_item['name']); ?></span> <i
                            class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Slider Banner -->
        <div class="slider-container">
            <div class="slide active" style="background-image: url('images/4.jpg');">
                <div class="banner-overlay">
                    <h2>ƯU ĐÃI LỚN!</h2>
                    <p>Giảm giá tới 50% cho các dòng gấu bông cao cấp</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh Sách Sản Phẩm Nổi Bật -->
    <div class="container">
        <section class="products-wrapper">
            <h2 class="section-heading">
                <?php echo $category_id > 0 ? "SẢN PHẨM THEO DANH MỤC" : "SẢN PHẨM MỚI / BÁN CHẠY"; ?>
            </h2>

            <div class="product-grid">
                <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <span class="badge-sale">Sale 20%</span>
                        <?php 
                                    $thumb = $row['thumbnail'] ?? ($row['image'] ?? '');
                                ?>
                        <img src="assets/uploads/products/<?php echo htmlspecialchars($thumb); ?>"
                            onerror="this.src='https://via.placeholder.com/300x300?text=No+Image';"
                            alt="<?php echo htmlspecialchars($row['name']); ?>">
                    </div>

                    <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="product-title">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </a>

                    <div class="product-price">
                        <?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ
                    </div>

                    <a href="cart.php?action=add&id=<?php echo $row['id']; ?>" class="btn-add-cart-grid">
                        <i class="fa-solid fa-cart-shopping"></i> Thêm giỏ hàng
                    </a>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;">
                    Không tìm thấy sản phẩm nào!
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- FontAwesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="assets/js/main.js"></script>
    <?php include("includes/footer.php"); ?>
</body>

</html>