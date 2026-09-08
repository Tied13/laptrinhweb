<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng ký - Cửa hàng bán gấu bông</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 70vh;
        padding: 40px 15px;
    }

    .login {
        background: #ffffff;
        padding: 35px 30px;
        border-radius: 16px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 420px;
    }

    .login h2 {
        text-align: center;
        margin-bottom: 24px;
        color: #333;
        font-size: 24px;
    }

    .alert-box {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: center;
    }

    .alert-error {
        background-color: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }

    .login form input {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.2s;
    }

    .login form input:focus {
        border-color: #a855f7;
    }

    .login form button {
        width: 100%;
        padding: 13px;
        background: linear-gradient(135deg, #a855f7, #ec4899);
        border: none;
        color: #fff;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.2s;
        margin-top: 6px;
    }

    .login form button:hover {
        opacity: 0.95;
        transform: translateY(-1px);
    }

    .login p {
        text-align: center;
        margin-top: 18px;
        font-size: 14px;
        color: #6b7280;
    }

    .login p a {
        color: #a855f7;
        text-decoration: none;
        font-weight: 600;
    }

    .login p a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <?php include("includes/header.php"); ?>

    <section class="auth-container">
        <div class="login">
            <h2>Đăng ký tài khoản</h2>

            <!-- Hiển thị thông báo lỗi từ Session -->
            <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-box alert-error">
                <?php 
                        echo htmlspecialchars($_SESSION['error']); 
                        unset($_SESSION['error']); 
                    ?>
            </div>
            <?php endif; ?>

            <!-- Action kèm query param ?action=register để Controller nhận diện -->
            <form action="controllers/AuthController.php?action=register" method="POST" id="registerForm">
                <input type="text" name="fullname" placeholder="Họ và tên" required />
                <input type="text" name="username" placeholder="Tên đăng nhập" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" id="password" placeholder="Mật khẩu" required />
                <input type="password" name="confirm" id="confirm" placeholder="Nhập lại mật khẩu" required />
                <button type="submit">Đăng ký</button>
            </form>

            <p>Bạn đã có tài khoản?
                <a href="login.php">Đăng nhập</a>
            </p>
        </div>
    </section>

    <?php include("includes/footer.php"); ?>

    <script>
    // Kiểm tra khớp mật khẩu ngay trên trình duyệt trước khi gửi request
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('confirm').value;
        if (pass !== confirm) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp! Vui lòng nhập lại.');
        }
    });
    </script>
</body>

</html>