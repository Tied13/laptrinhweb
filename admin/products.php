<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    $_SESSION['error'] = 'Bạn không có quyền truy cập!';
    header('Location: ../login.php');
    exit();
}
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// 1. Tự động kết nối DB nếu chưa đi qua Controller
if (empty($products) || empty($categories)) {
    $dbPath = file_exists(__DIR__ . '/../config/database.php') 
        ? __DIR__ . '/../config/database.php' 
        : __DIR__ . '/config/database.php';

    if (file_exists($dbPath)) {
        require_once $dbPath;
        $db = new Database();
        $conn = $db->getConnection();

        $sqlProducts = "SELECT p.*, c.name AS category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.id DESC";
        $stmt = $conn->prepare($sqlProducts);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtCat = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY id ASC");
        $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    }
}

$categories = $categories ?? [];
$products = $products ?? [];
$product_edit = $product_edit ?? null;
$gallery = [];

if (isset($conn) && ($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $stmtEditProduct = $conn->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
    $stmtEditProduct->bindValue(':id', $editId, PDO::PARAM_INT);
    $stmtEditProduct->execute();
    $product_edit = $stmtEditProduct->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($product_edit) {
        $stmtGallery = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = :id ORDER BY id");
        $stmtGallery->execute([':id' => $editId]);
        $gallery = $stmtGallery->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Thêm CDN Bootstrap Icons để hiển thị icon sidebar & icon bảng (Fix Lỗi 1.1) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body>

    <?php include '../includes/navbar_admin.php'; ?>

    <div class="admin-content">
        <?php if ($success): ?><p class="message"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="message"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <div class="admin-page-header">
            <h2>Quản lý sản phẩm</h2>
            <button type="button" class="btn btn-primary" id="btn-add-product">
                + Thêm sản phẩm
            </button>
        </div>

        <div class="admin-form-box product-form-box<?php echo isset($product_edit['id']) ? ' show' : ''; ?>"
            id="product-form">
            <div class="product-form-header">
                <h3>
                    <?php echo isset($product_edit['id']) ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới'; ?>
                </h3>
                <button type="button" class="form-close-btn" id="btn-close-product">×</button>
            </div>

            <form
                action="../controllers/ProductController.php?action=store"
                method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="id"
                    value="<?php echo isset($product_edit['id']) ? (int)$product_edit['id'] : ''; ?>">

                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo isset($product_edit['name']) ? htmlspecialchars($product_edit['name']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Danh mục:</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id']; ?>"
                            <?php echo (isset($product_edit['category_id']) && $product_edit['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Giá sản phẩm:</label>
                    <input type="number" name="price" class="form-control"
                        value="<?php echo isset($product_edit['price']) ? htmlspecialchars($product_edit['price']) : ''; ?>"
                        required>
                </div>
                <div class="form-group">
                    <label>Số lượng:</label>
                    <input type="number" min="0" name="quantity" class="form-control"
                        value="<?php echo (int)($product_edit['quantity'] ?? 0); ?>" required>
                </div>

                <div class="form-group">
                    <label>Ảnh đại diện sản phẩm:</label>
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif"
                        <?php echo $product_edit ? '' : 'required'; ?>>
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết:</label>
                    <textarea name="description" id="editor"
                        class="form-control"><?php echo isset($product_edit['description']) ? htmlspecialchars($product_edit['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Chọn nhiều ảnh phụ:</label>
                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif">
                    <?php foreach ($gallery as $photo): ?>
                        <img src="../<?php echo htmlspecialchars($photo['image_url']); ?>" width="70" alt="Ảnh phụ hiện tại">
                    <?php endforeach; ?>
                </div>

                <div class="product-form-actions">
                    <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                    <button type="button" class="btn btn-cancel" id="btn-cancel-product">Hủy</button>
                </div>
            </form>
        </div>

        <div class="product-list-section">
            <h3 class="section-title">Danh sách sản phẩm</h3>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                    <?php
                        $thumb = $p['thumbnail'] ?? '';
                        $thumbUrl = preg_match('~^(https?://|assets/)~i', $thumb)
                            ? $thumb : 'assets/uploads/products/' . basename($thumb);
                    ?>
                    <tr>
                        <td><?php echo (int)$p['id']; ?></td>

                        <td>
                            <img src="<?php echo preg_match('~^https?://~i', $thumbUrl) ? '' : '../'; ?><?php echo htmlspecialchars($thumbUrl); ?>"
                                class="admin-thumb" onerror="this.src='https://placehold.co/100x100?text=No+Image';"
                                alt="<?php echo htmlspecialchars($p['name'] ?? ''); ?>">
                        </td>

                        <td><?php echo htmlspecialchars($p['name'] ?? ''); ?></td>

                        <td><?php echo htmlspecialchars($p['category_name'] ?? 'Chưa phân loại'); ?></td>

                        <td><?php echo number_format((float)($p['price'] ?? 0), 0, ',', '.'); ?>đ</td>

                        <td class="admin-actions">
                            <a href="products.php?action=edit&id=<?php echo (int)$p['id']; ?>"
                                class="btn btn-edit">Sửa</a>
                            <form action="../controllers/ProductController.php?action=delete" method="post" style="display:inline"
                                onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                <button type="submit" class="btn btn-delete">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-product">
                                <i class="bi bi-box-seam"></i>
                                <strong>🧸 Chưa có sản phẩm</strong>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
    <script src="../assets/js/admin.js"></script>

</body>

</html>
