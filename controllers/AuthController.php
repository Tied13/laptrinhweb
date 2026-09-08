<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';

// ==========================================
// 1. XỬ LÝ ĐĂNG KÝ (REGISTER)
// ==========================================
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Kiểm tra các trường bắt buộc
    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ các thông tin bắt buộc!";
        header("Location: ../register.php");
        exit();
    }

    // Kiểm tra khớp mật khẩu
    if ($password !== $confirm) {
        $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
        header("Location: ../register.php");
        exit();
    }

    // Kiểm tra định dạng Email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Địa chỉ email không đúng định dạng!";
        header("Location: ../register.php");
        exit();
    }

    try {
        $database = new Database();
        $db = $database->getConnection();

        // Kiểm tra xem Username hoặc Email đã có người dùng chưa
        $checkStmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
        $checkStmt->execute([
            ':username' => $username,
            ':email'    => $email
        ]);

        if ($checkStmt->fetch()) {
            $_SESSION['error'] = "Tên đăng nhập hoặc Email này đã tồn tại trên hệ thống!";
            header("Location: ../register.php");
            exit();
        }

        // Mã hóa mật khẩu bảo mật chuẩn Bcrypt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Chèn người dùng mới (Mặc định: role = 0 [Khách hàng], status = 1 [Hoạt động])
        $insertStmt = $db->prepare("
            INSERT INTO users (fullname, username, password, email, role, status) 
            VALUES (:fullname, :username, :password, :email, 0, 1)
        ");
        $insertStmt->execute([
            ':fullname' => $fullname,
            ':username' => $username,
            ':password' => $hashed_password,
            ':email'    => $email
        ]);

        $_SESSION['success'] = "Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay.";
        header("Location: ../login.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi kết nối máy chủ: " . $e->getMessage();
        header("Location: ../register.php");
        exit();
    }
}

// ==========================================
// 2. XỬ LÝ ĐĂNG NHẬP (LOGIN)
// ==========================================
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
        header("Location: ../login.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra mật khẩu hash
    if ($user && password_verify($password, $user['password'])) {
        
        // Kiểm tra tài khoản có bị khóa không (status = 0)
        if (isset($user['status']) && (int)$user['status'] === 0) {
            $_SESSION['error'] = "Tài khoản của bạn đã bị vô hiệu hóa!";
            header("Location: ../login.php");
            exit();
        }

        // Lưu thông tin người dùng vào Session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role']     = (int)$user['role'];

        // Điều hướng theo quyền: Admin sang trang admin, Khách về trang chủ
        if ($_SESSION['role'] === 1) {
            header("Location: ../admin/products.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Tên đăng nhập hoặc mật khẩu không chính xác!";
        header("Location: ../login.php");
        exit();
    }
}

// ==========================================
// 3. XỬ LÝ ĐĂNG XUẤT (LOGOUT)
// ==========================================
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Nếu không khớp action nào, chuyển hướng về trang chủ
header("Location: ../index.php");
exit();