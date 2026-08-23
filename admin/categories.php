<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    $_SESSION['error'] = 'Bạn không có quyền truy cập!';
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Category.php';

$database = new Database();
$db = $database->getConnection();
$categoryModel = new Category($db);
$categories = $categoryModel->getAll(true);
$editCategory = null;
if (isset($_GET['edit'])) {
    $editCategory = $categoryModel->getById((int)$_GET['edit']);
}
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/navbar_admin.php'; ?>
    <main class="admin-content">
        <div class="page-header">
            <div>
                <h1>Quản lý danh mục</h1>
                <p>Quản lý danh mục sản phẩm của cửa hàng</p>
            </div>
            <a href="#category-form" class="btn-add">
                <i class="bi bi-plus-lg"></i>
                Thêm danh mục
            </a>
        </div>
        <?php if ($success): ?>
            <div class="message message-success">
                <i class="bi bi-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message message-error">
                <i class="bi bi-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <div class="admin-card category-form-card" id="category-form">
            <div class="card-header">
                <h2>
                    <?= $editCategory ? 'Sửa danh mục' : 'Thêm danh mục' ?>
                </h2>
            </div>
            <form
                method="POST"
                action="../controllers/ProductController.php?action=<?= $editCategory ? 'category_update' : 'category_store' ?>"
                class="admin-form"
            >
                <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="category-name">Tên danh mục</label>
                    <input
                        type="text"
                        id="category-name"
                        name="name"
                        placeholder="Nhập tên danh mục"
                        value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input
                            type="checkbox"
                            name="status"
                            value="1"
                            <?= (!$editCategory || (int)$editCategory['status'] === 1) ? 'checked' : '' ?>
                        >
                        <span>Hoạt động</span>
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-lg"></i>
                        <?= $editCategory ? 'Cập nhật' : 'Thêm danh mục' ?>
                    </button>
                    <?php if ($editCategory): ?>
                        <a href="categories.php" class="btn-cancel">
                            Hủy
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="admin-card">
            <div class="card-header">
                <h2>Danh sách danh mục</h2>
            </div>
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên danh mục</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <?= (int)$category['id'] ?>
                                </td>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php if ((int)$category['status'] === 1): ?>

                                        <span class="status-badge status-active">
                                            Hoạt động
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-hidden">
                                            Ẩn
                                        </span>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a
                                            href="categories.php?edit=<?= (int)$category['id'] ?>"
                                            class="btn-edit"
                                            title="Sửa"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a
                                            href="../controllers/ProductController.php?action=category_delete&id=<?= (int)$category['id'] ?>"
                                            class="btn-delete"
                                            title="Xóa"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-message">
                                Chưa có danh mục
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script src="../assets/js/admin.js"></script>
</body>
</html>