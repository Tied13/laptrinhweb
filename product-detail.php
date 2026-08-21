<?php
session_start();

if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
} else {
    include 'db.php';
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

if ($conn instanceof PDO) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Sản Phẩm</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container detail-container" style="display: flex; gap: 30px; margin-top: 30px; background: #fff; padding: 25px; border-radius: 16px;">
        <div class="gallery" style="flex: 1;">
            <img id="mainImage" class="main-img"
                src="<?php echo htmlspecialchars($product['thumbnail'] ?? 'https://via.placeholder.com/400'); ?>" 
                style="width: 100%; height: 350px; object-fit: cover; border-radius: 12px;">
            <div class="thumb-list" style="display: flex; gap: 10px; margin-top: 10px;">
                <img class="thumb-img active" src="<?php echo htmlspecialchars($product['thumbnail'] ?? 'https://via.placeholder.com/400'); ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; cursor: pointer;">
                <img class="thumb-img" src="https://images.unsplash.com/photo-1563241527-3004b7be0ffd?q=80&w=200" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; cursor: pointer;">
            </div>
        </div>

        <div style="flex: 1;">
            <h2><?php echo htmlspecialchars($product['name'] ?? 'Gấu Bông Cao Cấp'); ?></h2>
            <div class="product-price" style="font-size: 24px; color: var(--price-color); font-weight: 800; margin: 15px 0;">
                <?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?> VNĐ
            </div>

            <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                <div class="quantity-control" style="display: flex; align-items: center; gap: 5px; margin-bottom: 20px;">
                    <button type="button" class="btn-qty" id="btnMinus" style="width: 35px; height: 35px; border: 1px solid #ddd; background: #f8f8f8; cursor: pointer;">-</button>
                    <input type="text" name="quantity" id="inputQty" class="input-qty" value="1" style="width: 50px; height: 35px; text-align: center; border: 1px solid #ddd;">
                    <button type="button" class="btn-qty" id="btnPlus" style="width: 35px; height: 35px; border: 1px solid #ddd; background: #f8f8f8; cursor: pointer;">+</button>
                </div>
                <button type="submit" name="add_to_cart" class="btn-checkout" style="border: none; cursor: pointer;">Thêm giỏ hàng</button>
            </form>
            
            <div class="product-description" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                <?php echo htmlspecialchars($product['description'] ?? ''); ?>
            </div>

            // be 3
            <?php require_once 'includes/header.php'; ?>

<div class="container product-detail-wrapper" style="margin-top: 50px;">
    <h1><?= htmlspecialchars($product['name'] ?? 'Tên sản phẩm') ?></h1>
    
    <div class="gallery-container" style="margin: 20px 0;">
        <h3>Ảnh sản phẩm</h3>
        <div class="gallery-images" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if (!empty($gallery)): ?>
                <?php foreach ($gallery as $image): ?>
                    <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                        alt="Thumbnail" 
                        style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc; border-radius: 5px;">
                <?php endforeach; ?>
            <?php else: ?>
                <p>Sản phẩm này chưa có ảnh phụ.</p>
            <?php endif; ?>
        </div>
    </div>

    <hr>

    <div class="product-description">
        <h3>Bài viết Review chi tiết</h3>
        <div class="html-content" style="line-height: 1.6;">
            <?= $product['description'] ?? 'Chưa có bài viết đánh giá.' ?> 
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
