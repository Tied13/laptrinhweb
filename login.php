<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Cửa hàng bán Gấu bông</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include("includes/header.php"); ?>
    <main class="auth-container">
        <section class="login">
            <h2>Đăng nhập</h2>
            <form action="controllers/AuthController.php?action=login" method="post">
                <?php if (!empty($_GET['redirect'])): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                <?php endif; ?>
                <input type="text" name="username" placeholder="Tên đăng nhập" required />
                <input type="password" name="password" placeholder="Mật khẩu" required />
                <button type="submit">Đăng nhập</button>
            </form>
            <!-- Hiển thị thông báo lỗi từ Session -->
            <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-box alert-error">
                <?php 
                        echo htmlspecialchars($_SESSION['error']); 
                        unset($_SESSION['error']); 
                    ?>
            </div>
            <?php endif; ?>
            <p>Bạn chưa có tài khoản?
                <a href="register.php">Đăng ký ngay</a>
            </p>
        </section>
    </main>
    <?php include("includes/footer.php"); ?>
</body>

</html>