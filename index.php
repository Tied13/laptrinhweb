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

// 3. Cấu hình phân trang
$limit = 8;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// 4. Đếm tổng số và phân trang
$total_products = $productModel->countAll($search, $category_id);
$total_pages = ceil($total_products / $limit);

if ($total_pages < 1) {
    $total_pages = 1;
}

// 5. Lấy danh sách sản phẩm
$products = $productModel->getAll(
    $search,
    $category_id,
    $limit,
    $offset
);

// Tham số giữ lại khi bấm chuyển trang
$pagination_query = "";

if ($category_id > 0) {
    $pagination_query .= "&category=" . $category_id;
}

if (!empty($search)) {
    $pagination_query .= "&search=" . urlencode($search);
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

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 25px;
        margin-bottom: 25px;
    }

    .pagination-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        border: 1px solid #e9d5ff;
        border-radius: 8px;
        background: #ffffff;
        color: #9333ea;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .pagination-link:hover {
        background: #f3e8ff;
        border-color: #a855f7;
        color: #7e22ce;
    }

    .pagination-link.active {
        background: linear-gradient(135deg, #a855f7, #ec4899);
        border-color: transparent;
        color: #ffffff;
    }

    .pagination-link.prev-next {
        padding: 0 16px;
    }

    @media (max-width: 768px) {
        .pagination {
            gap: 6px;
        }

        .pagination-link {
            min-width: 34px;
            height: 34px;
            padding: 0 9px;
            font-size: 12px;
        }

        .pagination-link.prev-next {
            padding: 0 10px;
        }
    }

    /* Màn hình vừa */
    @media (max-width: 1200px) {
        .hero-section {
            grid-template-columns: 220px minmax(0, 1fr) !important;
            gap: 16px !important;
        }

        .hero-section .sidebar {
            width: 220px !important;
        }

        .hero-section .product-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    /* Điện thoại */
    @media (max-width: 768px) {
        .hero-section {
            display: flex !important;
            flex-direction: column !important;
            width: 92% !important;
        }

        .hero-section .sidebar,
        .hero-section .products-section {
            width: 100% !important;
        }

        .hero-section .product-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
    </style>
</head>

<body>

    <?php include("includes/header.php"); ?>

    <!-- Hero Section (Sidebar & Banner Slide) -->
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
                <?php echo $category_id > 0 ? "SẢN PHẨM THEO DANH MỤC" : "SẢN PHẨM MỚI "; ?>
            </h2>

            <div class="product-grid">

                <?php if (!empty($products)): ?>

                <?php foreach ($products as $row): 

            $thumb = $row['thumbnail'] ?? ($row['image'] ?? '');

            $detailLink = "product-detail.php?id=" . $row['id'];

            $isLoggedIn = isset($_SESSION['user']) || isset($_SESSION['user_id']);

            $cartLink = $isLoggedIn 
                ? "cart.php?action=add&id=" . $row['id'] 
                : "login.php?redirect=" . urlencode(
                    "cart.php?action=add&id=" . $row['id']
                );

        ?>

                <div class="product-card">

                    <!-- Ô phủ ảo trong suốt dẫn tới trang chi tiết -->
                    <a href="<?php echo $detailLink; ?>" class="product-click-overlay"
                        aria-label="<?php echo htmlspecialchars($row['name']); ?>">
                    </a>

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


            <!-- =========================
     PHÂN TRANG
     ĐẶT NGOÀI product-grid
     ========================= -->

            <div class="pagination">

                <!-- Trang trước -->
                <a href="index.php?page=<?php echo max(1, $page - 1) . $pagination_query; ?>"
                    class="pagination-link prev-next">
                    &laquo; Trang trước
                </a>


                <!-- Số trang -->
                <?php for ($i = 1; $i <= max(1, $total_pages); $i++): ?>

                <a href="index.php?page=<?php echo $i . $pagination_query; ?>"
                    class="pagination-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>

                <?php endfor; ?>


                <!-- Trang sau -->
                <a href="index.php?page=<?php echo min($total_pages, $page + 1) . $pagination_query; ?>"
                    class="pagination-link prev-next">
                    Trang sau &raquo;
                </a>

            </div>
        </section>

    </div>

    <!-- FontAwesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="assets/js/main.js"></script>
    <?php include("includes/footer.php"); ?>
</body>

</html>