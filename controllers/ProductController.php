<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. Xử lý Đăng ký
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if (empty($fullname) || empty($username) || empty($password) || empty($email)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ các trường bắt buộc!";
        header("Location: ../register.php");
        exit();
    }

    if ($userModel->findByUsername($username)) {
        $_SESSION['error'] = "Tên đăng nhập đã tồn tại!";
        header("Location: ../register.php");
        exit();
    }

    if ($userModel->register($fullname, $username, $password, $email, $phone, $address)) {
        $_SESSION['success'] = "Đăng ký thành công! Hãy đăng nhập.";
        header("Location: ../login.php");
    } else {
        $_SESSION['error'] = "Đăng ký thất bại, vui lòng thử lại!";
        header("Location: ../register.php");
    }
    exit();
}

// 2. Xử lý Đăng nhập
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $userModel->findByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] == 0) {
            $_SESSION['error'] = "Tài khoản của bạn đã bị khóa!";
            header("Location: ../login.php");
            exit();
        }

        // Lưu thông tin vào Session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['fullname']  = $user['fullname'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = (int)$user['role'];

        if ($_SESSION['role'] === 1) {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../index.php");
        }
    } else {
        $_SESSION['error'] = "Sai tài khoản hoặc mật khẩu!";
        header("Location: ../login.php");
    }
    exit();
}

// 3. Xử lý Đăng xuất
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}