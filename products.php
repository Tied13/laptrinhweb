<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kết nối CSDL & Nạp Model
require_once __DIR__ . '/config/database.php';
// Tự động kiểm tra vị trí file Product.php (trong thư mục models/ hoặc cùng thư mục)
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

// 4. Lấy danh mục qua Model (hoặc query nếu hàm chưa có)
$categories = method_exists($productModel, 'getCategories') 
    ? $productModel->getCategories() 
    : $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// 5. Đếm tổng số và phân trang bằng hàm countAll() của Model
$total_products = $productModel->countAll($search, $category_id);
$total_pages = ceil($total_products / $limit);
if ($total_pages < 1) $total_pages = 1;

// 6. Lấy danh sách sản phẩm bằng hàm getAll() của Model
$products = $productModel->getAll($search, $category_id, $limit, $offset);

// Tham số giữ lại khi bấm chuyển trang
$pagination_query = "";
if ($category_id > 0) $pagination_query .= "&category=" . $category_id;
if (!empty($search)) $pagination_query .= "&search=" . urlencode($search);
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
    .product-actions {
        position: relative;
        z-index: 2;
    }
    </style>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <div class="container hero-section">
        <!-- Sidebar Danh Mục -->
        <div class="sidebar">
            <div class="sidebar-title"><i class="fa-solid fa-list-ul"></i> DANH MỤC</div>
            <ul>
                <li>
                    <a href="products.php" class="<?php echo ($category_id == 0) ? 'active' : ''; ?>">
                        Tất cả sản phẩm <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
                <?php foreach ($categories as $cat_item): ?>
                <li>
                    <a href="products.php?category=<?php echo $cat_item['id']; ?>"
                        class="<?php echo ($category_id == $cat_item['id']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat_item['name']); ?> <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Danh sách Sản Phẩm -->
        <div class="products-section" style="flex: 1; margin-top: 0;">
            <div class="product-grid">
                <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): 
                        $thumb = $row['thumbnail'] ?? ($row['image'] ?? '');
                        $isLoggedIn = isset($_SESSION['user']) || isset($_SESSION['user_id']);
                        $cartLink = $isLoggedIn 
                            ? "cart.php?action=add&id=" . $row['id'] 
                            : "login.php?redirect=" . urlencode("cart.php?action=add&id=" . $row['id']);
                        $detailLink = "product-detail.php?id=" . $row['id'];
                    ?>
                <div class="product-card">
                    <!-- Ô phủ ảo trong suốt dẫn tới trang chi tiết -->
                    <a href="<?php echo $detailLink; ?>" class="product-click-overlay"
                        aria-label="<?php echo htmlspecialchars($row['name']); ?>"></a>

                    <span class="badge-sale">Sale 20%</span>
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

                    <div class="product-actions">
                        <a href="<?php echo $cartLink; ?>" class="btn-action btn-user-cart">
                            <i class="fa-solid fa-cart-shopping"></i> Thêm giỏ hàng
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
            <div class="pagination" style="margin-top: 25px; display: flex; justify-content: center; gap: 8px;">
                <a href="products.php?page=<?php echo max(1, $page - 1) . $pagination_query; ?>">&laquo; Trang trước</a>
                <?php for ($i = 1; $i <= max(1, $total_pages); $i++): ?>
                <a href="products.php?page=<?php echo $i . $pagination_query; ?>"
                    class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                <a href="products.php?page=<?php echo min($total_pages, $page + 1) . $pagination_query; ?>">Trang sau
                    &raquo;</a>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>