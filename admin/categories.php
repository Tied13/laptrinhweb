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
$database = new Database();
$db = $database->getConnection();

// ==========================================================
// 1. XỬ LÝ ACTION: THÊM, SỬA, XÓA TRỰC TIẾP
// ==========================================================
$action = $_GET['action'] ?? '';

// Xóa danh mục
if ($action === 'category_delete') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $checkStmt = $db->prepare("SELECT COUNT(id) FROM products WHERE category_id = :id");
        $checkStmt->execute([':id' => $id]);
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['error'] = "Không thể xóa: Danh mục này đang có $count sản phẩm!";
        } else {
            $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Đã xóa danh mục thành công!";
            } else {
                $_SESSION['error'] = "Xóa danh mục thất bại!";
            }
        }
    }
    header('Location: categories.php');
    exit();
}

// Thêm mới hoặc Cập nhật danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['category_store', 'category_update'])) {
    $name   = trim($_POST['name'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($name === '') {
        $_SESSION['error'] = "Tên danh mục không được để trống!";
    } else {
        if ($action === 'category_update' && $id > 0) {
            $stmt = $db->prepare("UPDATE categories SET name = :name, status = :status WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['success'] = "Cập nhật danh mục thành công!";
        } else {
            $stmt = $db->prepare("INSERT INTO categories (name, status) VALUES (:name, :status)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['success'] = "Thêm danh mục mới thành công!";
        }
    }
    header('Location: categories.php');
    exit();
}

// ==========================================================
// 2. LẤY DỮ LIỆU ĐỂ HIỂN THỊ RA VIEW
// ==========================================================
$stmt = $db->query("SELECT c.*, COUNT(p.id) AS product_count 
                    FROM categories c 
                    LEFT JOIN products p ON c.id = p.category_id 
                    GROUP BY c.id 
                    ORDER BY c.id ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmtEdit = $db->prepare("SELECT * FROM categories WHERE id = :id");
    $stmtEdit->execute([':id' => $editId]);
    $editCategory = $stmtEdit->fetch(PDO::FETCH_ASSOC);
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

    <?php include '../includes/navbar_admin.php'; ?>

    <div class="admin-content">
        <div class="admin-page-header">
            <h2>Quản lý danh mục</h2>

            <button type="button" class="btn btn-primary" id="btn-show-category-form">
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

        <!-- FORM THÊM / SỬA DANH MỤC -->
        <div class="admin-form-box category-form-box" id="category-form"
            style="<?= $editCategory ? 'display: block;' : 'display: none;' ?>">
            <div class="product-form-header">
                <h3><?= $editCategory ? 'Cập nhật danh mục' : 'Thêm danh mục mới' ?></h3>
                <button type="button" class="form-close-btn" id="btn-close-category">×</button>
            </div>

            <form method="POST"
                action="categories.php?action=<?= $editCategory ? 'category_update' : 'category_store' ?>">
                <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Tên danh mục:</label>
                    <input type="text" name="name" class="form-control" placeholder="Nhập tên danh mục..."
                        value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label style="cursor: pointer;">
                        <input type="checkbox" name="status" value="1"
                            <?= (!$editCategory || (int)$editCategory['status'] === 1) ? 'checked' : '' ?>>
                        Hoạt động
                    </label>
                </div>

                <div class="product-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $editCategory ? 'Cập nhật' : 'Lưu danh mục' ?>
                    </button>
                    <a href="categories.php" class="btn btn-cancel" id="btn-cancel-category">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH DANH MỤC -->
        <div class="product-list-section">
            <h3 class="section-title">Danh sách danh mục</h3>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Số lượng sản phẩm</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= (int)$category['id'] ?></td>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td><?= (int)$category['product_count'] ?></td>
                        <td>
                            <?php if ((int)$category['status'] === 1): ?>
                            <span class="badge badge-success">Hoạt động</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="categories.php?edit=<?= (int)$category['id'] ?>" class="btn btn-edit">
                                Sửa
                            </a>
                            <a href="categories.php?action=category_delete&id=<?= (int)$category['id'] ?>"
                                class="btn btn-delete btn-delete-confirm"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                Xóa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-product">
                                <strong>🏷️ Chưa có danh mục nào</strong>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    const btnShow = document.getElementById('btn-show-category-form');
    const btnClose = document.getElementById('btn-close-category');
    const btnCancel = document.getElementById('btn-cancel-category');
    const formBox = document.getElementById('category-form');

    if (btnShow && formBox) {
        btnShow.addEventListener('click', function() {
            formBox.style.display = (formBox.style.display === 'none' || formBox.style.display === '') ?
                'block' : 'none';
        });
    }
    if (btnClose && formBox) {
        btnClose.addEventListener('click', function() {
            formBox.style.display = 'none';
        });
    }
    if (btnCancel && formBox) {
        btnCancel.addEventListener('click', function() {
            formBox.style.display = 'none';
        });
    }
    </script>
    <script src="../assets/js/admin.js"></script>
</body>

</html>