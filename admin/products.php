<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    $_SESSION['error'] = 'Bạn không có quyền truy cập!';
    header('Location: ../login.php');
    exit();
}

// Khởi tạo mảng rỗng nếu controller chưa truyền biến này
$categories = $categories ?? [];
$products = $products ?? [];
$product_edit = $product_edit ?? null;
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

            <button type="button" class="btn btn-primary" id="btn-add-product">
                + Thêm sản phẩm
            </button>
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

                    <tr>
                        <td>
                            <?php echo (int)$p['id']; ?>
                        </td>

                        <td>
                            <img src="../assets/uploads/products/<?php echo htmlspecialchars($p['thumbnail']); ?>"
                                class="admin-thumb" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        </td>

                        <td>
                            <?php echo htmlspecialchars($p['name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($p['category_name']); ?>
                        </td>

                        <td>
                            <?php echo number_format($p['price'], 0, ',', '.'); ?>đ
                        </td>

                        <td class="admin-actions">
                            <a href="?controller=product&action=edit&id=<?php echo (int)$p['id']; ?>"
                                class="btn btn-edit">
                                Sửa
                            </a>

                            <a href="?controller=product&action=delete&id=<?php echo (int)$p['id']; ?>"
                                class="btn btn-delete btn-delete-confirm">
                                Xóa
                            </a>
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


        <div class="admin-form-box product-form-box" id="product-form">

            <div class="product-form-header">
                <h3>
                    <?php echo isset($product_edit['id'])
                    ? 'Cập nhật sản phẩm'
                    : 'Thêm sản phẩm mới'; ?>
                </h3>

                <button type="button" class="form-close-btn" id="btn-close-product">
                    ×
                </button>
            </div>

            <form action="../index.php?controller=product&action=store" method="POST" enctype="multipart/form-data">

                <!-- PHẦN BE2 -->
                <input type="hidden" name="id" value="<?php echo isset($product_edit['id'])
                    ? (int)$product_edit['id']
                    : ''; ?>">

                <div class="form-group">
                    <label>Tên sản phẩm:</label>

                    <input type="text" name="name" class="form-control" value="<?php echo isset($product_edit['name'])
                        ? htmlspecialchars($product_edit['name'])
                        : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Danh mục:</label>

                    <select name="category_id" class="form-control" required>
                        <option value="">-- Chọn danh mục --</option>

                        <?php foreach ($categories as $cat): ?>

                        <option value="<?php echo (int)$cat['id']; ?>" <?php echo (
                                isset($product_edit['category_id']) &&
                                $product_edit['category_id'] == $cat['id']
                            ) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Giá sản phẩm:</label>

                    <input type="number" name="price" class="form-control" value="<?php echo isset($product_edit['price'])
                        ? htmlspecialchars($product_edit['price'])
                        : ''; ?>" required>
                </div>


                <!-- PHẦN BE3 -->
                <div class="form-group">
                    <label>Ảnh đại diện sản phẩm:</label>

                    <input type="file" name="image" class="form-control">
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết:</label>

                    <textarea name="description" id="editor" class="form-control"><?php echo isset($product_edit['description'])
                    ? htmlspecialchars($product_edit['description'])
                    : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Chọn nhiều ảnh phụ:</label>

                    <input type="file" name="images[]" multiple required>
                </div>

                <div class="product-form-actions">
                    <button type="submit" class="btn btn-primary">
                        Lưu sản phẩm
                    </button>

                    <button type="button" class="btn btn-cancel" id="btn-cancel-product">
                        Hủy
                    </button>
                </div>

            </form>
        </div>

    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
    <script src="../assets/js/admin.js"></script>

</body>

</html>