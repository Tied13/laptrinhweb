<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra mật khẩu (Giả sử bạn dùng password_hash)
    if ($user && password_verify($password, $user['password'])) {
        
        // Kiểm tra xem tài khoản có bị khóa không
        if (isset($user['status']) && (int)$user['status'] === 0) {
            $_SESSION['error'] = "Tài khoản của bạn đã bị khóa!";
            header("Location: ../login.php");
            exit();
        }

        // Lưu thông tin vào Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = (int)$user['role'];

        // Phân luồng trang đích dựa trên Role
        if ($_SESSION['role'] === 1) {
            header("Location: ../admin/products.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Sai tài khoản hoặc mật khẩu!";
        header("Location: ../login.php");
        exit();
    }
}

// Xử lý Đăng xuất
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}