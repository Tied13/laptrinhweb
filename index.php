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

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Gấu Bông Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .product-card {
        position: relative;
    }

    /* Ô phủ ảo trong suốt trùm lên toàn bộ vùng ảnh và thông tin */
    .product-click-overlay {
        position: absolute;
        inset: 0;
        bottom: 60px;
        /* Chừa lại phần đáy cho nút thêm giỏ hàng */
        z-index: 1;
        cursor: pointer;
    }

    /* Nổi nút giỏ hàng lên trên ô ảo để click độc lập */
    .btn-add-cart-grid {
        position: relative;
        z-index: 2;
    }
    </style>
</head>

<body>

    <?php include("includes/header.php"); ?>

    <!-- Hero Section (Sidebar & Banner Slide) -->
    <div class="container hero-section">

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
                <?php foreach ($products as $row): 
                    $thumb = $row['thumbnail'] ?? ($row['image'] ?? '');
                    $detailLink = "product-detail.php?id=" . $row['id'];
                    $isLoggedIn = isset($_SESSION['user']) || isset($_SESSION['user_id']);
                    $cartLink = $isLoggedIn 
                        ? "cart.php?action=add&id=" . $row['id'] 
                        : "login.php?redirect=" . urlencode("cart.php?action=add&id=" . $row['id']);
                ?>
                <div class="product-card">
                    <!-- Ô phủ ảo trong suốt dẫn tới trang chi tiết -->
                    <a href="<?php echo $detailLink; ?>" class="product-click-overlay"
                        aria-label="<?php echo htmlspecialchars($row['name']); ?>"></a>

                    <div class="product-img-wrapper">
                        <img src="assets/uploads/products/<?php echo basename(htmlspecialchars($thumb)); ?>"
                            onerror="this.onerror=null; this.src='https://placehold.co/300x300?text=No+Image';"
                            alt="<?php echo htmlspecialchars($row['name']); ?>">
                    </div>

                    <div class="product-title">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </div>

                    <div class="product-price">
                        <?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ
                    </div>

                    <a href="<?php echo $cartLink; ?>" class="btn-add-cart-grid">
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