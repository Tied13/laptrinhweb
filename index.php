<?php
session_start();

if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
} else {
    include 'config/database.php';
}

// 1. Lấy tham số lọc
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 2. Lấy danh sách danh mục từ bảng `categories`
$categories = [];
if (isset($conn) && $conn instanceof PDO) {
    $stmt_cat = $conn->prepare("SELECT * FROM categories WHERE status = 1 ORDER BY id ASC");
    $stmt_cat->execute();
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} else {
    $res_cat = mysqli_query($conn, "SELECT * FROM categories WHERE status = 1 ORDER BY id ASC");
    while ($c = mysqli_fetch_assoc($res_cat)) { $categories[] = $c; }
}

// 3. Xây dựng truy vấn lấy sản phẩm từ bảng `products`
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "name LIKE :search";
    $params[':search'] = "%{$search}%";
}

if ($category_id > 0) {
    $where_clauses[] = "category_id = :category_id";
    $params[':category_id'] = $category_id;
}

$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(' AND ', $where_clauses) : "";

// Lấy tối đa 8 sản phẩm cho Trang chủ
$products = [];
if ($conn instanceof PDO) {
    $sql_prod = "SELECT * FROM products" . $where_sql . " ORDER BY id DESC LIMIT 8";
    $stmt_prod = $conn->prepare($sql_prod);
    $stmt_prod->execute($params);
    $products = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);
} else {
    $where_mysqli = "";
    if (!empty($search)) $where_mysqli .= " AND name LIKE '%".mysqli_real_escape_string($conn, $search)."%'";
    if ($category_id > 0) $where_mysqli .= " AND category_id = $category_id";
    if (!empty($where_mysqli)) $where_mysqli = " WHERE " . substr($where_mysqli, 5);

    $sql_prod = "SELECT * FROM products" . $where_mysqli . " ORDER BY id DESC LIMIT 8";
    $res_prod = mysqli_query($conn, $sql_prod);
    while ($p = mysqli_fetch_assoc($res_prod)) { $products[] = $p; }
}

// Đếm tổng số lượng sản phẩm trong Giỏ hàng
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
                <input type="text" name="search" placeholder="Nhập tên sản phẩm cần tìm..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
            </form>

            <div class="header-right">
                <!-- Nút Thêm sản phẩm -->
                <a href="admin/product-add.php" class="btn-add-product">
                    <i class="fa-solid fa-plus"></i> Thêm sản phẩm
                </a>

                <!-- Biểu tượng Giỏ hàng -->
                <a href="cart.php" class="cart-icon-btn">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-badge-num"><?php echo $cart_count; ?></span>
                </a>

                <!-- Nút Đăng nhập / Tài khoản -->
                <a href="login.php" class="user-account-btn">
                    <i class="fa-regular fa-user"></i>
                    <span>Đăng nhập</span>
                </a>
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
                    <a href="index.php" class="<?php echo $category_id == 0 ? 'active' : ''; ?>">
                        <span>Tất cả sản phẩm</span> <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
                <?php foreach ($categories as $cat_item): ?>
                    <li>
                        <a href="index.php?category=<?php echo $cat_item['id']; ?>" 
                           class="<?php echo $category_id == $cat_item['id'] ? 'active' : ''; ?>">
                            <span><?php echo htmlspecialchars($cat_item['name']); ?></span> <i class="fa-solid fa-chevron-right"></i>
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
                                <img src="<?php echo htmlspecialchars($row['thumbnail']); ?>" 
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
</body>
</html>
