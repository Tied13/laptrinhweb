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

// 3. Chỉ lấy 8 sản phẩm
$limit = 8;
$offset = 0;

// 4. Lấy danh sách sản phẩm
$products = $productModel->getAll(
    $search,
    $category_id,
    $limit,
    $offset
);

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

    <?php include("includes/header.php"); ?>

    <!-- Hero Section -->
    <div class="container hero-section">

        <!-- Slider Banner -->
        <div class="slider-container">

            <div class="slide active" style="background-image: url('assets/uploads/products/banner-gau-bong.jpg');">

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
                <?php
                echo $category_id > 0
                    ? "SẢN PHẨM THEO DANH MỤC"
                    : "SẢN PHẨM MỚI";
                ?>
            </h2>

            <div class="product-grid">

                <?php if (!empty($products)): ?>

                <?php foreach ($products as $row):

                        $thumb = $row['thumbnail'] ?? ($row['image'] ?? '');

                        $detailLink = "product-detail.php?id=" . $row['id'];

                        $isLoggedIn = isset($_SESSION['user']) ||
                                      isset($_SESSION['user_id']);

                        $cartLink = $isLoggedIn
                            ? "cart.php?action=add&id=" . $row['id']
                            : "login.php?redirect=" . urlencode(
                                "cart.php?action=add&id=" . $row['id']
                            );

                    ?>

                <div class="product-card">

                    <!-- Ô phủ ảo dẫn tới trang chi tiết -->
                    <a href="<?php echo $detailLink; ?>" class="product-click-overlay"
                        aria-label="<?php echo htmlspecialchars($row['name']); ?>">
                    </a>

                    <div class="product-img-wrapper">

                        <img src="assets/uploads/products/<?php
                                    echo basename($thumb);
                                ?>" onerror="this.onerror=null; this.src='https://placehold.co/300x300?text=No+Image';"
                            alt="<?php echo htmlspecialchars($row['name']); ?>">

                    </div>

                    <div class="product-title">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </div>

                    <div class="product-price">
                        <?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ
                    </div>

                    <a href="<?php echo $cartLink; ?>" class="btn-add-cart-grid">

                        <i class="fa-solid fa-cart-shopping"></i>
                        Thêm giỏ hàng

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