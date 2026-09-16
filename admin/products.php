<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';

$database = new Database();
$db = $database->getConnection();
$productModel = new Product($db);

$products = method_exists($productModel, 'getAll') ? $productModel->getAll() : [];

$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editProduct = method_exists($productModel, 'getById') ? $productModel->getById($editId) : null;
}

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php if (file_exists('../includes/navbar_admin.php')) include '../includes/navbar_admin.php'; ?>

    <div class="admin-content" style="padding: 20px;">
        <h2>Quản lý sản phẩm</h2>

        <?php if ($success): ?><div class="alert alert-success" style="color: green;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger" style="color: red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form action="../controllers/ProductController.php?action=<?= $editProduct ? 'update' : 'create' ?>" method="POST" style="margin-bottom: 20px;">
            <?php if ($editProduct): ?>
                <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
            <?php endif; ?>
            <input type="text" name="name" placeholder="Tên sản phẩm" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
            <input type="number" name="price" placeholder="Giá" value="<?= $editProduct['price'] ?? '' ?>" required>
            <input type="text" name="image" placeholder="Link hình ảnh" value="<?= htmlspecialchars($editProduct['image'] ?? '') ?>">
            <button type="submit"><?= $editProduct ? 'Cập nhật' : 'Thêm mới' ?></button>
            <?php if ($editProduct): ?><a href="product.php">Hủy</a><?php endif; ?>
        </form>

        <table class="admin-table" border="1" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Hình ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td>#<?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= number_format($p['price'], 0, ',', '.') ?>đ</td>
                        <td><img src="<?= htmlspecialchars($p['image']) ?>" width="50"></td>
                        <td>
                            <a href="product.php?edit=<?= $p['id'] ?>">Sửa</a> | 
                            <a href="../controllers/ProductController.php?action=delete&id=<?= $p['id'] ?>" onclick="return confirm('Xóa sản phẩm này?');">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">Chưa có sản phẩm nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
