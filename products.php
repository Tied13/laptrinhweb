<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kết nối CSDL & Nạp Model
require_once __DIR__ . '/config/database.php';

// Tự động kiểm tra vị trí file Product.php
if (file_exists(__DIR__ . '/models/Product.php')) {
    require_once __DIR__ . '/models/Product.php';
} else {
    require_once __DIR__ . '/Product.php';
}

$database = new Database();
$conn = $database->getConnection();
$productModel = new Product($conn);

// 2. Cấu hình phân trang
$limit = 8;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// 3. Lấy tham số lọc & tìm kiếm
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 4. Lấy danh mục
$categories = method_exists($productModel, 'getCategories')
    ? $productModel->getCategories()
    : $conn->query(
        "SELECT * FROM categories WHERE status = 1 ORDER BY id ASC"
      )->fetchAll(PDO::FETCH_ASSOC);

// 5. Đếm tổng số và phân trang
$total_products = $productModel->countAll($search, $category_id);
$total_pages = ceil($total_products / $limit);

if ($total_pages < 1) {
    $total_pages = 1;
}

// 6. Lấy danh sách sản phẩm
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

    <title>Cửa Hàng - Gấu Bông Store</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    /*
         * SỬA VỊ TRÍ:
         * Sidebar bên trái, sản phẩm bên phải,
         * cả hai nằm cùng một hàng.
         */

    .hero-section {
        display: grid !important;
        grid-template-columns: 240px minmax(0, 1fr) !important;
        align-items: start !important;
        gap: 24px !important;
        width: 92% !important;
        max-width: 1200px !important;
        margin: 26px auto 0 !important;
    }

    .hero-section .sidebar {
        grid-column: 1 !important;
        grid-row: 1 !important;
        width: 240px !important;
        min-width: 0 !important;
        margin: 0 !important;
    }

    .hero-section .products-section {
        grid-column: 2 !important;
        grid-row: 1 !important;
        width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .hero-section .product-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 16px !important;
        width: 100% !important;
        margin: 0 !important;
        align-items: start !important;
    }

    .hero-section .product-card {
        position: relative;
        min-width: 0;
        width: 100%;
    }

    .hero-section .product-img-wrapper {
        width: 100%;
        overflow: hidden;
    }

    .hero-section .product-img-wrapper img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        display: block;
    }

    /* Ô phủ dẫn tới trang chi tiết */
    .product-click-overlay {
        position: absolute;
        inset: 0;
        bottom: 60px;
        z-index: 1;
        cursor: pointer;
    }

    /* Nút thêm giỏ hàng nằm trên ô phủ */
    .product-actions {
        position: relative;
        z-index: 2;
    }


    /* ================= PHÂN TRANG ================= */

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

    <div class="container hero-section">

        <!-- Sidebar Danh Mục -->
        <div class="sidebar">

            <div class="sidebar-title">
                <i class="fa-solid fa-list-ul"></i> DANH MỤC
            </div>

            <ul>
                <li>
                    <a href="products.php" class="<?php echo ($category_id == 0) ? 'active' : ''; ?>">
                        Tất cả sản phẩm
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>

                <?php foreach ($categories as $cat_item): ?>
                <li>
                    <a href="products.php?category=<?php echo $cat_item['id']; ?>"
                        class="<?php echo ($category_id == $cat_item['id']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat_item['name']); ?>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

        </div>

        <!-- Danh sách Sản Phẩm -->
        <div class="products-section">

            <div class="product-grid">

                <?php if (!empty($products)): ?>

                <?php foreach ($products as $row):

                        $thumb = $row['thumbnail'] ?? ($row['image'] ?? '');

                        $isLoggedIn =
                            isset($_SESSION['user']) ||
                            isset($_SESSION['user_id']);

                        $cartLink = $isLoggedIn
                            ? "cart.php?action=add&id=" . $row['id']
                            : "login.php?redirect=" . urlencode(
                                "cart.php?action=add&id=" . $row['id']
                            );

                        $detailLink = "product-detail.php?id=" . $row['id'];

                    ?>

                <div class="product-card">

                    <!-- Ô phủ ảo trong suốt dẫn tới trang chi tiết -->
                    <a href="<?php echo $detailLink; ?>" class="product-click-overlay"
                        aria-label="<?php echo htmlspecialchars($row['name']); ?>"></a>

                    <span class="badge-sale">Sale 20%</span>

                    <div class="product-img-wrapper">
                        <img src="assets/uploads/products/<?php echo basename($thumb); ?>"
                            onerror="this.onerror=null; this.src='https://placehold.co/300x300?text=No+Image';"
                            alt="<?php echo htmlspecialchars($row['name']); ?>">
                    </div>

                    <div class="product-title">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </div>

                    <div class="product-price">
                        <?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ
                    </div>

                    <div class="product-actions">
                        <a href="<?php echo $cartLink; ?>" class="btn-action btn-user-cart">
                            <i class="fa-solid fa-cart-shopping"></i>
                            Thêm giỏ hàng
                        </a>
                    </div>

                </div>

                <?php endforeach; ?>

                <?php else: ?>

                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;">
                    Không tìm thấy sản phẩm nào!
                </div>

                <?php endif; ?>

            </div>

            <!-- Phân trang -->
            <div class="pagination">

                <a href="products.php?page=<?php echo max(1, $page - 1) . $pagination_query; ?>"
                    class="pagination-link prev-next">
                    &laquo; Trang trước
                </a>

                <?php for ($i = 1; $i <= max(1, $total_pages); $i++): ?>

                <a href="products.php?page=<?php echo $i . $pagination_query; ?>"
                    class="pagination-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>

                <?php endfor; ?>

                <a href="products.php?page=<?php echo min($total_pages, $page + 1) . $pagination_query; ?>"
                    class="pagination-link prev-next">
                    Trang sau &raquo;
                </a>

            </div>

        </div>

    </div>

    <?php include("includes/footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

    <script src="assets/js/main.js"></script>

</body>

</html>