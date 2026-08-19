<?php
session_start();
include 'database.php';

// 1. Xử lý logic Giỏ hàng
if (isset($_GET['action']) && $_GET['action'] == 'add_to_cart') {
    $product_id = intval($_GET['id']);
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    header('Location: index.php');
    exit();
}

// 2. Kiểm tra từ khóa tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $search_clean = mysqli_real_escape_string($conn, $search);
    $sql = "SELECT * FROM san_pham WHERE ten_san_pham LIKE '%$search_clean%' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM san_pham ORDER BY id DESC";
}
$result = mysqli_query($conn, $sql);

// Tính tổng số lượng mặt hàng trong giỏ
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gấu Bông Store - Cửa Hàng Gấu Bông Cao Cấp</title>
    <!-- CSS External -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Top -->
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-store"></i> Gấu Bông Store
            </a>

            <form class="search-box" action="index.php" method="GET">
                <input type="text" name="search" placeholder="Nhập tên sản phẩm cần tìm..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
            </form>

            <div class="header-icons">
                <!-- Nút thêm sản phẩm mới (Dành cho Admin) -->
                <a href="add.php" class="btn-add-product">
                    <i class="fa-solid fa-plus"></i> Thêm sản phẩm
                </a>

                <!-- Giỏ hàng người dùng -->
                <a href="cart.php" class="cart-icon-wrapper" title="Giỏ hàng">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>

                <!-- Tài khoản người dùng -->
                <div class="user-menu">
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <span><i class="fa-solid fa-user-check"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="logout.php" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket"></i></a>
                    <?php else: ?>
                        <a href="login.php" title="Đăng nhập"><i class="fa-solid fa-user"></i> Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
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

    <!-- Main Container -->
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <aside class="sidebar">
                <ul>
                    <li><a href="products.php">Tất cả sản phẩm <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="products.php">Gấu Bông Teddy <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="products.php">Gấu Bông Capybara <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="products.php">Thỏ Bông <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="products.php">Phụ kiện gấu bông <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="products.php">Sản phẩm khuyến mãi <i class="fa-solid fa-chevron-right"></i></a></li>
                </ul>
            </aside>

            <!-- Slider Banner Động -->
            <div class="slider-container">
                <div class="slide active" style="background-image: url('images/4.jpg');">
                    <div class="banner-text">
                        <h2>ƯU ĐÃI LỚN!</h2>
                        <p>Giảm giá tới 50% cho các dòng gấu bông cao cấp</p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('images/4.jpg');">
                    <div class="banner-text">
                        <h2>BỘ SƯU TẬP MỚI</h2>
                        <p>Khám phá mẫu Capybara siêu đáng yêu năm 2026</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products List - Grid 4 Cột -->
        <section class="products-section">
            <h3 class="section-title">
                <?php echo !empty($search) ? 'Kết quả tìm kiếm cho: "' . htmlspecialchars($search) . '"' : 'Sản Phẩm Mới / Bán Chạy'; ?>
            </h3>

            <div class="product-grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <div class="product-card">
                            <span class="badge-sale">Sale 20%</span>
                            <img src="images/<?php echo htmlspecialchars($row['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($row['ten_san_pham']); ?>">
                            
                            <div>
                                <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="product-title">
                                    <?php echo htmlspecialchars($row['ten_san_pham']); ?>
                                </a>
                                <div class="product-price"><?php echo number_format($row['gia']); ?> VNĐ</div>
                                <div class="product-desc"><?php echo htmlspecialchars($row['mo_ta']); ?></div>
                            </div>

                            <div class="product-actions">
                                <!-- Thêm vào giỏ hàng dành cho Người mua -->
                                <a href="index.php?action=add_to_cart&id=<?php echo $row['id']; ?>" class="btn-action btn-user-cart">
                                    <i class="fa-solid fa-cart-plus"></i> Thêm giỏ hàng
                                </a>

                                <!-- Chức năng Quản trị (Admin) -->
                                <div class="admin-controls">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit">
                                        <i class="fa-solid fa-pen-to-square"></i> Sửa
                                    </a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')" class="btn-action btn-delete">
                                        <i class="fa-solid fa-trash"></i> Xóa
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; padding: 20px;">Không tìm thấy sản phẩm nào!</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
