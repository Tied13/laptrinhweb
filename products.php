<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kết nối CSDL chuẩn PDO qua config
require_once __DIR__ . '/config/database.php';
$database = new Database();
$conn = $database->getConnection();

// 2. Cấu hình phân trang
$limit = 8;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// 3. Lấy tham số lọc & tìm kiếm
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 4. Lấy danh sách Danh mục
$stmt_cat = $conn->prepare("SELECT * FROM categories WHERE status = 1 ORDER BY id ASC");
$stmt_cat->execute();
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// 5. Xây dựng điều kiện lọc sản phẩm
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

// 6. Tính tổng số sản phẩm để chia trang
$sql_count = "SELECT COUNT(*) as total FROM products" . $where_sql;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$total_products = (int)($stmt_count->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

$total_pages = ceil($total_products / $limit);
if ($total_pages < 1) $total_pages = 1;

// 7. Lấy danh sách sản phẩm theo trang
$sql_prod = "SELECT * FROM products" . $where_sql . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt_prod = $conn->prepare($sql_prod);

foreach ($params as $k => $v) { 
    $stmt_prod->bindValue($k, $v); 
}
$stmt_prod->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_prod->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_prod->execute();
$products = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);

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
                    ?>
                <div class="product-card">
                    <span class="badge-sale">Sale 20%</span>
                    <div class="product-img-wrapper">
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