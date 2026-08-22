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
    <title>Quản lý Người dùng - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/navbar_admin.php'; ?>
        <main class="admin-content">
            <h2>Danh Sách Người Dùng</h2>
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
                                <span class="badge badge-active">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge badge-locked">Đã khóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['role'] != 1): ?>
                                <a href="users.php?toggle_id=<?= $u['id'] ?>&current_status=<?= $u['status'] ?>"
                                   class="btn-action <?= $u['status'] == 1 ? 'btn-lock' : 'btn-unlock' ?>"
                                   onclick="return confirmToggleUser('<?= $u['status'] == 1 ? 'khóa' : 'mở khóa' ?>')">
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
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>