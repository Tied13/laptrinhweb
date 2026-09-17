<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/product_html.php';

// Tự động nạp file Model Product nếu có
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
        if (method_exists($productModel, 'getProductById')) {
            $product = $productModel->getProductById($id);
        }
        if (method_exists($productModel, 'getProductImages')) {
            $gallery_images = $productModel->getProductImages($id);
        }
    }
    
    // Fallback nếu không có Product model hoặc model không trả dữ liệu
    if (!$product) {
        $stmt = $conn->prepare("
            SELECT p.*, c.name AS category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Lấy danh sách ảnh phụ từ product_images nếu rỗng
        if ($product && empty($gallery_images)) {
            $imgStmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = :id");
            $imgStmt->execute([':id' => $id]);
            $gallery_images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

if (!$product) {
    header("Location: products.php");
    exit();
}

// Hàm chuẩn hóa đường dẫn ảnh (tránh nhân đôi assets/uploads/products/)
function getProductImagePath($path) {
    if (empty($path)) {
        return 'https://via.placeholder.com/450x450?text=No+Image';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'assets/')) {
        return $path;
    }
    return 'assets/uploads/products/' . ltrim($path, '/');
}

$isLoggedIn = isset($_SESSION['user']) || isset($_SESSION['user_id']);
$thumb = $product['thumbnail'] ?? ($product['image'] ?? '');
$mainImageUrl = getProductImagePath($thumb);
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

    .gallery-open {
        display: block;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: zoom-in;
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
        border: 2px solid transparent;
        cursor: pointer;
        padding: 0;
        background: #fff;
        overflow: hidden;
        transition: border-color 0.2s;
    }

    .gallery-item:hover,
    .gallery-item.active,
    .gallery-item:focus-visible {
        border-color: #a855f7;
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gallery-dialog {
        width: min(92vw, 1000px);
        max-height: 92vh;
        padding: 16px;
        border: 0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
    }

    .gallery-dialog::backdrop {
        background: rgba(0, 0, 0, 0.78);
    }

    .gallery-dialog-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 12px;
    }

    .gallery-dialog-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .gallery-dialog-controls img {
        width: min(75vw, 800px);
        max-height: 75vh;
        height: auto;
        object-fit: contain;
    }

    .gallery-dialog button {
        min-width: 36px;
        min-height: 36px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
    }

    .gallery-dialog button:focus-visible,
    .gallery-open:focus-visible {
        outline: 3px solid #a855f7;
        outline-offset: 3px;
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

    .desc-box figure {
        max-width: 100%;
        margin: 1em auto;
        text-align: center;
    }

    .desc-box img {
        max-width: 100%;
        height: auto;
    }

    .desc-box table {
        display: block;
        max-width: 100%;
        overflow-x: auto;
        border-collapse: collapse;
    }

    .desc-box th,
    .desc-box td {
        border: 1px solid #e5e7eb;
        padding: 8px;
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
                <!-- 1. Chỉ 1 ảnh lớn duy nhất để hiển thị -->
                <button type="button" class="gallery-open" id="openGallery"
                    aria-label="Xem ảnh sản phẩm kích thước lớn">
                    <img id="mainImg" src="<?php echo htmlspecialchars($mainImageUrl); ?>"
                        onerror="this.src='https://via.placeholder.com/450x450?text=No+Image';"
                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                </button>

                <?php
    // Gom tất cả ảnh vào 1 danh sách duy nhất
    $allImages = [];

    // Lấy ảnh chính
    if (!empty($mainImageUrl)) {
        $allImages[] = $mainImageUrl;
    }

    // Lấy ảnh từ gallery (nếu có)
    if (!empty($gallery_images) && is_array($gallery_images)) {
        foreach ($gallery_images as $img) {
            $url = is_array($img) ? ($img['image_url'] ?? '') : $img;
            if (!empty($url)) {
                $allImages[] = getProductImagePath($url);
            }
        }
    }

    // Lọc sạch các ảnh trùng nhau
    $uniqueImages = array_values(array_unique(array_filter($allImages)));
    ?>

                <!-- 2. Chỉ hiện danh sách ảnh nhỏ bên dưới KHI VÀ CHỈ KHI có từ 2 ảnh KHÁC NHAU trở lên -->
                <?php if (count($uniqueImages) > 1): ?>
                <div class="gallery-thumbnails">
                    <?php foreach ($uniqueImages as $idx => $imgSrc): ?>
                    <button type="button" class="gallery-item <?php echo $idx === 0 ? 'active' : ''; ?>"
                        aria-label="Xem ảnh sản phẩm <?php echo $idx + 1; ?>"
                        aria-pressed="<?php echo $idx === 0 ? 'true' : 'false'; ?>">
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                            onerror="this.src='https://via.placeholder.com/70x70?text=No+Image';"
                            alt="Ảnh <?php echo $idx + 1; ?>">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <dialog id="galleryDialog" class="gallery-dialog" aria-label="Ảnh sản phẩm kích thước lớn">
                    <div class="gallery-dialog-header">
                        <span id="galleryCount" aria-live="polite"></span>
                        <button type="button" id="closeGallery" aria-label="Đóng ảnh lớn">×</button>
                    </div>
                    <div class="gallery-dialog-controls">
                        <button type="button" id="previousGallery" aria-label="Ảnh trước">‹</button>
                        <img id="galleryFullImage" alt="">
                        <button type="button" id="nextGallery" aria-label="Ảnh tiếp theo">›</button>
                    </div>
                </dialog>
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
                <?php $inStock = (int)($product['quantity'] ?? 0) > 0; ?>
                <p><?php echo $inStock ? 'Còn ' . (int)$product['quantity'] . ' sản phẩm' : 'Sản phẩm đã hết hàng'; ?></p>
                <?php if ((int)($_SESSION['role'] ?? 0) === 1): ?>
                    <p><a href="admin/product-images.php?id=<?php echo (int)$product['id']; ?>">Quản lý ảnh gallery</a></p>
                <?php endif; ?>

                <form action="cart.php?action=add" method="POST" id="cartForm">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

                    <div class="quantity-wrapper">
                        <label style="font-weight: 600; margin-right: 10px;">Số lượng:</label>
                        <button type="button" class="btn-qty" id="btnMinus">-</button>
                        <input type="number" name="quantity" id="inputQty" class="input-qty" value="1" min="1"
                            max="<?php echo max(1, (int)($product['quantity'] ?? 1)); ?>">
                        <button type="button" class="btn-qty" id="btnPlus">+</button>
                    </div>

                    <?php if (!$inStock): ?>
                    <button type="button" class="btn-add-cart-detail" disabled>Hết hàng</button>
                    <?php elseif ($isLoggedIn): ?>
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
                        <?php echo cleanProductHtml($product['description'] ?? '') ?: 'Chưa có mô tả cho sản phẩm này.'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>

    <script src="assets/js/main.js"></script>
    <script>
    const mainImg = document.getElementById('mainImg');
    const thumbnailButtons = Array.from(document.querySelectorAll('.gallery-item'));
    const images = thumbnailButtons.length
        ? thumbnailButtons.map(button => button.querySelector('img'))
        : [mainImg];
    const dialog = document.getElementById('galleryDialog');
    const fullImage = document.getElementById('galleryFullImage');
    const galleryCount = document.getElementById('galleryCount');
    let currentImage = 0;

    function selectImage(index) {
        currentImage = (index + images.length) % images.length;
        const image = images[currentImage];
        mainImg.src = image.src;
        mainImg.alt = image.alt;
        fullImage.src = image.src;
        fullImage.alt = image.alt;
        galleryCount.textContent = `Ảnh ${currentImage + 1} / ${images.length}`;
        thumbnailButtons.forEach((button, position) => {
            const active = position === currentImage;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
        });
    }

    thumbnailButtons.forEach((button, index) => {
        button.addEventListener('click', () => selectImage(index));
    });
    document.getElementById('openGallery').addEventListener('click', () => {
        selectImage(currentImage);
        dialog.showModal();
        document.getElementById('closeGallery').focus();
    });
    document.getElementById('closeGallery').addEventListener('click', () => dialog.close());
    document.getElementById('previousGallery').addEventListener('click', () => selectImage(currentImage - 1));
    document.getElementById('nextGallery').addEventListener('click', () => selectImage(currentImage + 1));
    dialog.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            selectImage(currentImage - 1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            selectImage(currentImage + 1);
        }
    });
    dialog.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
    });
    </script>
</body>

</html>
