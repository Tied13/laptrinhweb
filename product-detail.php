<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

// Tự động kiểm tra file Model Product
if (file_exists(__DIR__ . '/models/Product.php')) {
    require_once __DIR__ . '/models/Product.php';
} elseif (file_exists(__DIR__ . '/Product.php')) {
    require_once __DIR__ . '/Product.php';
}

$database = new Database();
$conn = $database->getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$gallery_images = [];

if ($conn && $id > 0) {
    if (class_exists('Product')) {
        $productModel = new Product($conn);
        $product = $productModel->getProductById($id);
        if (method_exists($productModel, 'getProductImages')) {
            $gallery_images = $productModel->getProductImages($id);
        }
    } else {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!$product) {
    header("Location: products.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user']) || isset($_SESSION['user_id']);
$thumb = $product['thumbnail'] ?? ($product['image'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Gấu Bông Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .detail-card {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        background: #fff;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin: 40px auto;
    }

    .main-img-box img {
        width: 100%;
        height: 420px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #f3e8ff;
        transition: all 0.3s ease;
    }

    .gallery-thumbnails {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }

    .gallery-item {
        width: 70px;
        height: 70px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid transparent;
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .gallery-item:hover,
    .gallery-item.active {
        border-color: #a855f7;
    }

    .product-info h1 {
        font-size: 26px;
        color: #333;
        margin-bottom: 10px;
    }

    .category-badge {
        display: inline-block;
        background: #f3e8ff;
        color: #9333ea;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .detail-price {
        font-size: 24px;
        font-weight: 800;
        color: #ec4899;
        margin-bottom: 20px;
    }

    .quantity-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 25px;
    }

    .btn-qty {
        width: 36px;
        height: 36px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-qty:hover {
        background: #f3e8ff;
        border-color: #a855f7;
    }

    .input-qty {
        width: 60px;
        height: 36px;
        text-align: center;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-weight: 600;
    }

    .btn-add-cart-detail {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: linear-gradient(135deg, #a855f7, #ec4899);
        color: #fff;
        border: none;
        padding: 14px 30px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 30px;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(236, 72, 153, 0.3);
        transition: all 0.3s;
    }

    .btn-add-cart-detail:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(236, 72, 153, 0.4);
    }

    .desc-box {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #f3e8ff;
        color: #4b5563;
        line-height: 1.7;
    }

    @media (max-width: 768px) {
        .detail-card {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>

    <?php include("includes/header.php"); ?>

    <div class="container">
        <div class="detail-card">
            <!-- Cột hình ảnh & Gallery -->
            <div class="main-img-box">
                <img id="mainImg" src="assets/uploads/products/<?php echo htmlspecialchars($thumb); ?>"
                    onerror="this.src='https://via.placeholder.com/450x450?text=No+Image';"
                    alt="<?php echo htmlspecialchars($product['name']); ?>">

                <?php if (!empty($gallery_images)): ?>
                <div class="gallery-thumbnails">
                    <!-- Ảnh chính -->
                    <img src="assets/uploads/products/<?php echo htmlspecialchars($thumb); ?>"
                        class="gallery-item active" onclick="changeImage(this)"
                        onerror="this.src='https://via.placeholder.com/70x70?text=No+Image';" alt="Thumbnail Chính">

                    <!-- Các ảnh phụ -->
                    <?php foreach ($gallery_images as $img): ?>
                    <img src="assets/uploads/products/<?php echo htmlspecialchars($img['image_url']); ?>"
                        class="gallery-item" onclick="changeImage(this)"
                        onerror="this.src='https://via.placeholder.com/70x70?text=No+Image';" alt="Ảnh phụ">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Cột thông tin sản phẩm -->
            <div class="product-info">
                <?php if (!empty($product['category_name'])): ?>
                <span class="category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <?php endif; ?>

                <h1><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="detail-price">
                    <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                </div>

                <!-- Form gửi sang cart.php có kèm ?action=add để Controller nhận dạng ngay -->
                <form action="cart.php?action=add" method="POST" id="cartForm">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

                    <div class="quantity-wrapper">
                        <label style="font-weight: 600; margin-right: 10px;">Số lượng:</label>
                        <button type="button" class="btn-qty" id="btnMinus">-</button>
                        <input type="number" name="quantity" id="inputQty" class="input-qty" value="1" min="1">
                        <button type="button" class="btn-qty" id="btnPlus">+</button>
                    </div>

                    <?php if ($isLoggedIn): ?>
                    <button type="submit" name="add_to_cart" class="btn-add-cart-detail">
                        <i class="fa-solid fa-cart-shopping"></i> Thêm vào giỏ hàng
                    </button>
                    <?php else: ?>
                    <a href="login.php?redirect=<?php echo urlencode('product-detail.php?id=' . $product['id']); ?>"
                        onclick="alert('Vui lòng đăng nhập trước khi thêm sản phẩm vào giỏ!');"
                        class="btn-add-cart-detail">
                        <i class="fa-solid fa-cart-shopping"></i> Thêm vào giỏ hàng
                    </a>
                    <?php endif; ?>
                </form>

                <div class="desc-box">
                    <h3 style="color: #9333ea; margin-bottom: 10px;">Mô tả sản phẩm</h3>
                    <div>
                        <?php echo nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả cho sản phẩm này.')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
    // Tăng giảm số lượng
    const inputQty = document.getElementById('inputQty');
    const btnMinus = document.getElementById('btnMinus');
    const btnPlus = document.getElementById('btnPlus');

    if (btnMinus && btnPlus && inputQty) {
        btnMinus.addEventListener('click', () => {
            let current = parseInt(inputQty.value) || 1;
            if (current > 1) {
                inputQty.value = current - 1;
            }
        });

        btnPlus.addEventListener('click', () => {
            let current = parseInt(inputQty.value) || 1;
            inputQty.value = current + 1;
        });
    }

    // Đổi ảnh khi nhấn vào danh sách ảnh Gallery
    function changeImage(el) {
        const mainImg = document.getElementById('mainImg');
        if (mainImg && el) {
            mainImg.src = el.src;
            document.querySelectorAll('.gallery-item').forEach(item => item.classList.remove('active'));
            el.classList.add('active');
        }
    }
    </script>
</body>

</html>