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
                <input type="text" name="username" placeholder="Tên đăng nhập" required />
                <input type="password" name="password" placeholder="Mật khẩu" required />
                <button type="submit">Đăng nhập</button>
            </form>
            <p>Bạn chưa có tài khoản?
                <a href="register.php">Đăng ký ngay</a>
            </p>
        </section>
    </main>
    <?php include("includes/footer.php"); ?>
</body>
</html>