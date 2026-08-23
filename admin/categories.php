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
    <style>
        .form-box { background: #fff; padding: 20px; margin-bottom: 25px; border-radius: 8px; }
        .form-box input[type="text"] { width: 100%; max-width: 500px; padding: 10px; margin: 8px 0 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { padding: 9px 16px; border: none; border-radius: 5px; cursor: pointer; background: #28a745; color: #fff; }
        .message { padding: 10px 14px; margin-bottom: 15px; border-radius: 5px; background: #f1f1f1; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php
    if (file_exists(__DIR__ . '/../includes/navbar_admin.php')) {
        include __DIR__ . '/../includes/navbar_admin.php';
    }
    ?>

    <main class="admin-content">
        <h2>Quản lý Danh mục</h2>

        <?php if ($success): ?>
            <div class="message" style="background: #d4edda; color: #155724;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message" style="background: #f8d7da; color: #721c24;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h3><?= $editCategory ? 'Sửa danh mục' : 'Thêm danh mục' ?></h3>

            <form method="POST" action="../controllers/ProductController.php?action=<?= $editCategory ? 'category_update' : 'category_store' ?>">
                <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
                <?php endif; ?>

                <label>Tên danh mục</label><br>
                <input type="text" name="name" placeholder="Nhập tên danh mục" value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>" required><br>

                <label>
                    <input type="checkbox" name="status" value="1" <?= (!$editCategory || (int)$editCategory['status'] === 1) ? 'checked' : '' ?>>
                    Hoạt động
                </label><br><br>

                <button type="submit" class="btn-submit"><?= $editCategory ? 'Cập nhật' : 'Thêm danh mục' ?></button>
                <?php if ($editCategory): ?>
                    <a href="categories.php" style="margin-left: 10px; text-decoration: none; color: #666;">Hủy</a>
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
                            <td><?= (int)$category['status'] === 1 ? 'Hoạt động' : 'Ẩn' ?></td>
                            <td>
                                <a href="categories.php?edit=<?= (int)$category['id'] ?>">Sửa</a> |
                                <a href="../controllers/ProductController.php?action=category_delete&id=<?= (int)$category['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa danh mục này?');">Xóa</a>
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
    </main>
</div>
</body>
</html>
