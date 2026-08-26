<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// --- BẢO MẬT VÀ PHÂN QUYỀN ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    $_SESSION['error'] = "Bạn không có quyền truy cập trang quản trị!";
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
// Xử lý đổi trạng thái khóa tài khoản
if (isset($_GET['toggle_id']) && isset($_GET['current_status'])) {
    $userModel->toggleStatus($_GET['toggle_id'], $_GET['current_status']);
    header("Location: users.php");
    exit();
}
$users = $userModel->getAllUsers();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include '../includes/navbar_admin.php'; ?>
<div class="admin-content">
    <div class="admin-page-header">
        <h2>Danh sách người dùng</h2>
        <a href="?controller=user&action=create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>
            + Thêm người dùng
        </a>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Username</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['fullname']) ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= ($u['role'] == 1) ? '<strong>Admin</strong>' : 'Khách hàng' ?></td>
                <td>
                    <?php if ($u['status'] == 1): ?>
                        <span class="badge badge-success">Hoạt động</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Đã khóa</span>
                    <?php endif; ?>
                </td>
                <td class="admin-actions">
                    <?php if ($u['role'] != 1): ?>
                        <a href="users.php?toggle_id=<?= $u['id'] ?>&current_status=<?= $u['status'] ?>"
                           class="btn <?= $u['status'] == 1 ? 'btn-delete' : 'btn-edit' ?> btn-toggle-confirm"
                           data-message="Bạn có chắc chắn muốn <?= $u['status'] == 1 ? 'khóa' : 'mở khóa' ?> tài khoản này không?">
                            <?= ($u['status'] == 1) ? 'Khóa' : 'Mở khóa' ?>
                        </a>
                    <?php else: ?>
                        <em>Không thể khóa</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="../assets/js/admin.js"></script>
</body>
</html>