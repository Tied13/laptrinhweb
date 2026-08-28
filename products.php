<?php
session_start();

// Kết nối CSDL linh hoạt
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
} else {
    include 'db.php';
}

// 1. Cấu hình phân trang
$limit = 8; // Đổi thành 8 để khớp với layout Grid 4 cột x 2 hàng
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// 2. Lấy tham số lọc
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 3. Truy vấn Danh mục từ bảng `categories`
$categories = [];
if (isset($conn) && $conn instanceof PDO) {
    $stmt_cat = $conn->prepare("SELECT * FROM categories WHERE status = 1 ORDER BY id ASC");
    $stmt_cat->execute();
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} else {
    $res_cat = mysqli_query($conn, "SELECT * FROM categories WHERE status = 1 ORDER BY id ASC");
    while ($c = mysqli_fetch_assoc($res_cat)) { $categories[] = $c; }
}

// 4. Xây dựng điều kiện SQL dựa trên bảng `products`
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

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $where_clauses);
}

// 5. Tính tổng số sản phẩm để phân trang
if ($conn instanceof PDO) {
    $sql_count = "SELECT COUNT(*) as total FROM products" . $where_sql;
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute($params);
    $total_products = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
} else {
    // Dành cho mysqli
    $where_mysqli = "";
    if (!empty($search)) $where_mysqli .= " AND name LIKE '%".mysqli_real_escape_string($conn, $search)."%'";
    if ($category_id > 0) $where_mysqli .= " AND category_id = $category_id";
    if (!empty($where_mysqli)) $where_mysqli = " WHERE " . substr($where_mysqli, 5);

    $res_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM products" . $where_mysqli);
    $total_products = mysqli_fetch_assoc($res_count)['total'];
}

$total_pages = ceil($total_products / $limit);
if ($total_pages < 1) $total_pages = 1;

// 6. Lấy danh sách sản phẩm theo trang
$products = [];
if ($conn instanceof PDO) {
    $sql_prod = "SELECT * FROM products" . $where_sql . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $stmt_prod = $conn->prepare($sql_prod);
    foreach ($params as $k => $v) { $stmt_prod->bindValue($k, $v); }
    $stmt_prod->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_prod->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt_prod->execute();
    $products = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql_prod = "SELECT * FROM products" . $where_mysqli . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $res_prod = mysqli_query($conn, $sql_prod);
    while ($p = mysqli_fetch_assoc($res_prod)) { $products[] = $p; }
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
</head>
<body>
    <?php include("includes/header.php"); ?>
    <div class="container hero-section">
    <div class="sidebar">
    <div class="sidebar-title"><i class="fa-solid fa-list-ul"></i> DANH MỤC</div>
    <ul>
        <!-- Click Tất cả sản phẩm -> Quay về Trang chủ -->
        <li>
            <a href="index.php" class="<?php echo (!isset($_GET['category']) || $_GET['category'] == 0) ? 'active' : ''; ?>">
                Tất cả sản phẩm <i class="fa-solid fa-chevron-right"></i>
            </a>
        </li>

        <!-- Click từng danh mục -> Quay về Trang chủ kèm id danh mục -->
        <?php foreach ($categories as $cat_item): ?>
            <li>
                <a href="index.php?category=<?php echo $cat_item['id']; ?>" 
                class="<?php echo (isset($_GET['category']) && $_GET['category'] == $cat_item['id']) ? 'active' : ''; ?>">
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
                    <?php foreach ($products as $row): ?>
                        <div class="product-card">
                            <span class="badge-sale">Sale 20%</span>
                            <img src="<?php echo htmlspecialchars($row['thumbnail']); ?>" 
                                onerror="this.src='https://via.placeholder.com/300x300?text=No+Image';" 
                                alt="<?php echo htmlspecialchars($row['name']); ?>">
                            
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="product-title">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                            
                            <div class="product-price">
                                <?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ
                            </div>

                            <div class="product-actions">
                                <a href="cart.php?action=add&id=<?php echo $row['id']; ?>" class="btn-action btn-user-cart">
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

            <!-- UI Phân trang giữ nguyên thiết kế -->
            <div class="pagination" style="margin-top: 25px; display: flex; justify-content: center; gap: 8px;">
                <a href="products.php?page=<?php echo max(1, $page-1); ?><?php echo $category_id ? "&category=$category_id" : ""; ?>">&laquo; Trang trước</a>
                <?php for($i = 1; $i <= max(1, $total_pages); $i++): ?>
                    <a href="products.php?page=<?php echo $i; ?><?php echo $category_id ? "&category=$category_id" : ""; ?>" 
                    class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <a href="products.php?page=<?php echo min($total_pages, $page+1); ?><?php echo $category_id ? "&category=$category_id" : ""; ?>">Trang sau &raquo;</a>
            </div>
        </div>
    </div>
</body>
</html>
