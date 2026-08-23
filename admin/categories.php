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
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar_admin.php'; ?>

<div class="admin-content">
    <div class="admin-page-header">
        <h2>Quản lý danh mục</h2>
    </div>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="admin-form-box">
        <h3><?= $editCategory ? 'Sửa danh mục' : 'Thêm danh mục' ?></h3>
        <form method="POST" action="../controllers/ProductController.php?action=<?= $editCategory ? 'category_update' : 'category_store' ?>">
            <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Tên danh mục:</label>
                <input type="text" name="name" class="form-control" placeholder="Nhập tên danh mục" value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>" required>
            </div>
            <div class="form-group form-check">
                <label>
                    <input type="checkbox" name="status" value="1" <?= (!$editCategory || (int)$editCategory['status'] === 1) ? 'checked' : '' ?>>
                    Hoạt động
                </label>
            </div>
            <button type="submit" class="btn btn-primary"><?= $editCategory ? 'Cập nhật' : 'Thêm danh mục' ?></button>
            <?php if ($editCategory): ?>
                <a href="categories.php" class="btn btn-cancel">Hủy</a>
            <?php endif; ?>
        </form>
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
                        <td><?= (int)$category['id'] ?></td>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td>
                            <?php if ((int)$category['status'] === 1): ?>
                                <span class="badge badge-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="categories.php?edit=<?= (int)$category['id'] ?>" class="btn btn-edit">Sửa</a>
                            <a href="../controllers/ProductController.php?action=category_delete&id=<?= (int)$category['id'] ?>" class="btn btn-delete btn-delete-confirm">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Chưa có danh mục.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>