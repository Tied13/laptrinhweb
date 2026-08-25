\<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra quyền Admin (Middleware)
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    $_SESSION['error'] = 'Bạn không có quyền truy cập!';
    header('Location: ../login.php');
    exit();
}

// 2. Nạp cấu hình CSDL và Models
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

$database = new Database();
$db = $database->getConnection();

$productModel = new Product($db);
$categoryModel = new Category($db);

// 3. Khởi tạo dữ liệu hiển thị
$products = $productModel->getAll();
$categories = $categoryModel->getAll();

// Xử lý lấy thông tin khi bấm nút "Sửa"
$product_edit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $product_edit = $productModel->getProductById($edit_id);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <?php include '../includes/navbar_admin.php'; ?>

    <div class="admin-content">
        <div class="admin-page-header">
            <h2>Quản lý sản phẩm</h2>
            <a href="products.php" class="btn btn-primary">+ Thêm sản phẩm</a>
        </div>

        <!-- Bảng danh sách sản phẩm -->
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo (int)$p['id']; ?></td>
                    <td>
                        <?php 
                            $thumb = $p['thumbnail']  ?? ($p['image'] ?? '');;
                        ?>
                        <img src="../assets/uploads/products/<?php echo htmlspecialchars($thumb); ?>"
                            class="admin-thumb" width="60" height="60" style="object-fit: cover; border-radius: 4px;"
                            onerror="this.src='https://via.placeholder.com/60?text=No+Image';">
                    </td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name'] ?? 'Chưa phân loại'); ?></td>
                    <td><?php echo number_format($p['price'], 0, ',', '.'); ?>đ</td>
                    <td class="admin-actions">
                        <a href="products.php?action=edit&id=<?php echo (int)$p['id']; ?>" class="btn btn-edit">Sửa</a>
                        <a href="../controllers/ProductController.php?action=delete&id=<?php echo (int)$p['id']; ?>"
                            class="btn btn-delete btn-delete-confirm"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Chưa có sản phẩm nào.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Form Thêm / Cập nhật sản phẩm -->
        <div class="admin-form-box" style="margin-top: 30px;">
            <h3><?php echo isset($product_edit['id']) ? 'Cập nhật sản phẩm #' . $product_edit['id'] : 'Thêm sản phẩm mới'; ?>
            </h3>

            <form
                action="../controllers/ProductController.php?action=<?php echo isset($product_edit['id']) ? 'update' : 'store'; ?>"
                method="POST" enctype="multipart/form-data">
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
                    <label>Giá sản phẩm (VNĐ):</label>
                    <input type="number" name="price" class="form-control"
                        value="<?php echo isset($product_edit['price']) ? htmlspecialchars($product_edit['price']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Ảnh đại diện sản phẩm:</label>
                    <input type="file" name="image" class="form-control"
                        <?php echo isset($product_edit['id']) ? '' : 'required'; ?>>
                    <?php if (!empty($product_edit['thumbnail'])): ?>
                    <small>Ảnh hiện tại: <?php echo htmlspecialchars($product_edit['thumbnail']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết:</label>
                    <textarea name="description" id="editor"
                        class="form-control"><?php echo isset($product_edit['description']) ? htmlspecialchars($product_edit['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Chọn nhiều ảnh phụ (Gallery):</label>
                    <!-- Đã bỏ thuộc tính required để không bắt buộc -->
                    <input type="file" name="images[]" multiple class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                <?php if (isset($product_edit['id'])): ?>
                <a href="products.php" class="btn btn-secondary">Hủy bỏ</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>

    <script src="../assets/js/admin.js"></script>
</body>

</html>