<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if ((int)($_SESSION['role'] ?? 0) !== 1) {
    http_response_code(403);
    exit('Không có quyền truy cập.');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
$db = (new Database())->getConnection();
$productModel = new Product($db);
$id = (int)($_GET['id'] ?? 0);
$product = $id > 0 ? $productModel->getProductById($id) : null;
if (!$product) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm.');
}
$images = $productModel->getProductImages($id);
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

function galleryEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function galleryImageUrl($value) {
    if (preg_match('~^https?://~i', $value)) return $value;
    if (str_starts_with($value, 'assets/')) return '../' . $value;
    return '../assets/uploads/products/' . basename($value);
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ảnh phụ - <?= galleryEscape($product['name']) ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .be3-gallery-page { padding: 24px; }
        .be3-gallery-page h1 { margin-bottom: 8px; }
        .be3-gallery-page .notice { padding: 12px 16px; border-radius: 8px; margin: 16px 0; }
        .be3-gallery-page .success { background: #e7f7eb; color: #17662a; }
        .be3-gallery-page .error { background: #fde8e8; color: #9b1c1c; }
        .be3-gallery-page .gallery-form,
        .be3-gallery-page .gallery-card { background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 16px; }
        .be3-gallery-page .gallery-form { margin: 20px 0; }
        .be3-gallery-page .gallery-form input { display: block; margin: 12px 0; max-width: 100%; }
        .be3-gallery-page .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 16px; }
        .be3-gallery-page .gallery-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 6px; }
        .be3-gallery-page .gallery-card button,
        .be3-gallery-page .gallery-form button { padding: 8px 14px; cursor: pointer; }
        .be3-gallery-page .gallery-card button { margin-top: 10px; }
        .be3-gallery-page .gallery-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .be3-gallery-page .gallery-actions form { display: inline; }
        .be3-gallery-page .gallery-actions button:disabled { cursor: not-allowed; opacity: .5; }
        .be3-gallery-page .current-cover { display: flex; align-items: center; gap: 14px; margin: 16px 0; }
        .be3-gallery-page .current-cover img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar_admin.php'; ?>
    <main class="admin-content be3-gallery-page">
        <p><a href="products.php">← Danh sách sản phẩm</a> ·
            <a href="../product-detail.php?id=<?= $id ?>">Xem trang chi tiết</a></p>
        <h1>Ảnh phụ: <?= galleryEscape($product['name']) ?></h1>
        <div class="current-cover">
            <img src="<?= galleryEscape(galleryImageUrl($product['thumbnail'])) ?>" alt="Ảnh đại diện hiện tại">
            <span>Ảnh đại diện hiện tại</span>
        </div>
        <p>Chọn một ảnh phụ làm ảnh đại diện nếu cần. Ảnh đại diện cũ sẽ được giữ trong gallery.</p>

        <?php if ($success): ?><p class="notice success"><?= galleryEscape($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="notice error"><?= galleryEscape($error) ?></p><?php endif; ?>

        <form class="gallery-form" action="../controllers/ProductController.php?action=gallery_upload"
            method="post" enctype="multipart/form-data">
            <h2>Thêm ảnh phụ</h2>
            <p>Chọn nhiều ảnh JPG, PNG, WebP hoặc GIF. Tối đa 20 ảnh mỗi lần, mỗi ảnh không quá 5 MB.</p>
            <input type="hidden" name="csrf_token" value="<?= galleryEscape($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required>
            <button type="submit">Tải ảnh lên</button>
        </form>

        <h2>Gallery hiện tại (<?= count($images) ?> ảnh)</h2>
        <p>Dùng nút lên và xuống để chọn thứ tự ảnh hiển thị trong trang chi tiết.</p>
        <?php if (!$images): ?>
            <p>Sản phẩm chưa có ảnh phụ.</p>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($images as $index => $image): ?>
                    <div class="gallery-card">
                        <img src="<?= galleryEscape(galleryImageUrl($image['image_url'])) ?>"
                            alt="Ảnh phụ <?= $index + 1 ?> của <?= galleryEscape($product['name']) ?>">
                        <div class="gallery-actions">
                            <form action="../controllers/ProductController.php?action=gallery_cover" method="post">
                                <input type="hidden" name="csrf_token" value="<?= galleryEscape($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <input type="hidden" name="image_id" value="<?= (int)$image['id'] ?>">
                                <button type="submit">Đặt làm ảnh đại diện</button>
                            </form>
                            <?php foreach (['up' => '↑ Lên', 'down' => '↓ Xuống'] as $direction => $label): ?>
                                <form action="../controllers/ProductController.php?action=gallery_move" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= galleryEscape($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <input type="hidden" name="image_id" value="<?= (int)$image['id'] ?>">
                                    <input type="hidden" name="direction" value="<?= $direction ?>">
                                    <button type="submit" <?= ($direction === 'up' && $index === 0) ||
                                        ($direction === 'down' && $index === count($images) - 1) ? 'disabled' : '' ?>><?= $label ?></button>
                                </form>
                            <?php endforeach; ?>
                            <form action="../controllers/ProductController.php?action=gallery_delete"
                                method="post" onsubmit="return confirm('Xóa ảnh phụ này?')">
                                <input type="hidden" name="csrf_token" value="<?= galleryEscape($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <input type="hidden" name="image_id" value="<?= (int)$image['id'] ?>">
                                <button type="submit">Xóa ảnh</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
