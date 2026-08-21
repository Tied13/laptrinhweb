<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đăng ký - Cửa hàng bán gấu bông</title>
  <link rel="stylesheet" href="assets/css/admin.css"/>
</head>
<body>
    <?php include("includes/header.php"); ?>
    <section class="register">
        <h2>Đăng ký tài khoản</h2>
        <form action="controllers/AuthController.php" method="post">
        <input type="text" name="fullname" placeholder="Họ và tên" required />
        <input type="text" name="username" placeholder="Tên đăng nhập" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Mật khẩu" required />
        <input type="password" name="confirm" placeholder="Nhập lại mật khẩu" required />
        <button type="submit">Đăng ký</button>
    </form>
    <p>Bạn đã có tài khoản? 
        <a href="login.php">Đăng nhập</a>
    </p>
    </section>
<?php include("includes/footer.php"); ?>
</body>
</html>