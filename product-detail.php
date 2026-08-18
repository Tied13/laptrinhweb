<?php
session_start();
include 'db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$sql = "SELECT * FROM san_pham WHERE id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Sản Phẩm</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container detail-container">
        <div class="gallery">
            <img id="mainImage" class="main-img" src="images/<?php echo $product['hinh_anh'] ?? 'default.jpg'; ?>">
            <div class="thumb-list">
                <img class="thumb-img active" src="images/<?php echo $product['hinh_anh'] ?? 'default.jpg'; ?>">
                <img class="thumb-img" src="https://images.unsplash.com/photo-1563241527-3004b7be0ffd?q=80&w=200">
            </div>
        </div>

        <div>
            <h2><?php echo $product['ten_san_pham'] ?? 'Gấu Bông Cao Cấp'; ?></h2>
            <div class="product-price"><?php echo number_format($product['gia'] ?? 200000); ?> VNĐ</div>

            <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                <div class="quantity-control">
                    <button type="button" class="btn-qty" id="btnMinus">-</button>
                    <input type="text" name="quantity" id="inputQty" class="input-qty" value="1">
                    <button type="button" class="btn-qty" id="btnPlus">+</button>
                </div>
                <button type="submit" name="add_to_cart" class="btn btn-purple">Thêm giỏ hàng</button>
            </form>
            <div class="product-description" style="margin-top: 20px;">
                <?php echo $product['mo_ta'] ?? ''; ?>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
