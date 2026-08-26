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
</head>
<body>
<?php include __DIR__ . '/../includes/navbar_admin.php'; ?>

<div class="admin-content">
    <div class="admin-page-header">
        <h2>Quản lý danh mục</h2>
        <button type="button" class="btn btn-primary" id="btn-show-category-form">
            <i class="bi bi-plus-lg"></i>
            + Thêm danh mục
        </button>
    </div>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div
        class="admin-form-box category-form-box"
        id="category-form"
        style="<?= $editCategory ? '' : 'display: none;' ?>"
    >
        <h3>
            <?= $editCategory ? 'Sửa danh mục' : 'Thêm danh mục mới' ?>
        </h3>

        <form
            method="POST"
            action="../controllers/ProductController.php?action=<?= $editCategory ? 'category_update' : 'category_store' ?>"
        >
            <?php if ($editCategory): ?>
                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$editCategory['id'] ?>"
                >
            <?php endif; ?>

            <div class="form-group">
                <label for="category-name">Tên danh mục</label>
                <input
                    type="text"
                    id="category-name"
                    name="name"
                    class="form-control"
                    placeholder="Nhập tên danh mục"
                    value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group form-check">
                <label>
                    <input
                        type="checkbox"
                        name="status"
                        value="1"
                        <?= (
                            !$editCategory ||
                            (int)$editCategory['status'] === 1
                        ) ? 'checked' : '' ?>
                    >
                    Hoạt động
                </label>
            </div>

            <div class="category-form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    <?= $editCategory ? 'Cập nhật' : 'Thêm danh mục' ?>
                </button>
                <?php if ($editCategory): ?>
                    <a href="categories.php" class="btn btn-cancel">
                        <i class="bi bi-x-lg"></i>
                        Hủy
                    </a>
                <?php else: ?>
                    <button
                        type="button"
                        class="btn btn-cancel"
                        id="btn-cancel-category"
                    >
                        <i class="bi bi-x-lg"></i>
                        Hủy
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="admin-section-title">
        <h3>Danh sách danh mục</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
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
                            <?= htmlspecialchars($category['name']) ?>
                        </td>
                        <td>
                            <?php if ((int)$category['status'] === 1): ?>

                                <span class="badge badge-success">
                                    Hoạt động
                                </span>
                            <?php else: ?>

                                <span class="badge badge-danger">
                                    Ẩn
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a
                                href="categories.php?edit=<?= (int)$category['id'] ?>"
                                class="btn btn-edit"
                            >
                                <i class="bi bi-pencil"></i>
                                Sửa
                            </a>
                            <a
                                href="../controllers/ProductController.php?action=category_delete&id=<?= (int)$category['id'] ?>"
                                class="btn btn-delete btn-delete-confirm"
                            >
                                <i class="bi bi-trash"></i>
                                Xóa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">
                        <div class="empty-category">
                            <i class="bi bi-tags"></i>
                            <strong>🏷️ Chưa có danh mục</strong>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="../assets/js/admin.js"></script>
</body>
</html>