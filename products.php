<?php
session_start();
include 'db.php';

// 1. Cấu hình phân trang
$limit = 6; // Số sản phẩm hiển thị trên 1 trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// 2. Lấy tham số lọc
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 3. Xây dựng điều kiện lọc
$where_clauses = array();

if (!empty($search)) {
    $search_clean = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "ten_san_pham LIKE '%$search_clean%'";
}

if ($category != 'all' && !empty($category)) {
    $category_clean = mysqli_real_escape_string($conn, $category);
    $where_clauses[] = "danh_muc = '$category_clean'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $where_clauses);
}

// 4. Đếm tổng số sản phẩm để tính $total_pages
$sql_count = "SELECT COUNT(*) as total FROM san_pham" . $where_sql;
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_products = $row_count['total'];

// Tính tổng số trang 
$total_pages = ceil($total_products / $limit);
if ($total_pages < 1) {
    $total_pages = 1;
}

// 5. Truy vấn danh sách sản phẩm theo trang
$sql = "SELECT * FROM san_pham" . $where_sql . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cửa Hàng - Gấu Bông Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container hero-section">
       <?php $cat = isset($_GET['category']) ? $_GET['category'] : 'all'; ?>

<aside class="sidebar">
    <div class="sidebar-title"><i class="fa-solid fa-list-ul"></i> Danh Mục</div>
    <ul>
        <li><a href="index.php" class="<?php echo $cat == 'all' ? 'active' : ''; ?>">Tất cả sản phẩm</a></li>
        <li><a href="index.php?category=teddy" class="<?php echo $cat == 'teddy' ? 'active' : ''; ?>">Gấu Teddy</a></li>
        <li><a href="index.php?category=capybara" class="<?php echo $cat == 'capybara' ? 'active' : ''; ?>">Capybara</a></li>
        <li><a href="index.php?category=tho" class="<?php echo $cat == 'tho' ? 'active' : ''; ?>">Thỏ Bông</a></li>
        <li><a href="index.php?category=phukien" class="<?php echo $cat == 'phukien' ? 'active' : ''; ?>">Phụ kiện</a></li>
    </ul>
</aside>

        <div class="products-section" style="flex: 1; margin-top: 0;">
            <div class="product-grid">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <img src="images/<?php echo $row['hinh_anh']; ?>">
                        <div class="product-title"><?php echo $row['ten_san_pham']; ?></div>
                        <div class="product-price"><?php echo number_format($row['gia']); ?> VNĐ</div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- UI Phân trang -->
            <div class="pagination">
                <a href="products.php?page=<?php echo max(1, $page-1); ?>">&laquo; Trang trước</a>
                    <?php for($i = 1; $i <= max(1, $total_pages); $i++): ?>
                <a href="products.php?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                    <?php endfor; ?>
    
                <a href="products.php?page=<?php echo min($total_pages, $page+1); ?>">Trang sau &raquo;</a>
            </div>
        </div>
    </div>
</body>
</html>
