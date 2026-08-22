<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Cửa hàng bán gấu bông</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include("includes/header.php"); ?>
<!-- Liên hệ -->
<section class="contact-section">
    <div class="contact-container">
        <h2 class="contact-title">
            Liên Hệ
        </h2>
        <p class="contact-intro">
            Cửa hàng bán gấu bông luôn sẵn sàng hỗ trợ và giải đáp mọi thắc mắc của bạn.
            Hãy để lại thông tin, chúng tôi sẽ liên hệ với bạn sớm nhất.
        </p>
        <div class="contact-form">
            <form action="#" method="post">
                <div class="form-group">
                    <label for="name">Họ và tên</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Nhập họ và tên"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Nhập email"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="number">Số điện thoại</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="Nhập số điện thoại"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="message">Nội dung liên hệ</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="5"
                        placeholder="Nhập nội dung bạn muốn liên hệ..."
                        required
                    ></textarea>
                </div>
                <button type="submit">
                    Gửi liên hệ
                </button>
            </form>
        </div>
    </div>
</section>
<?php include("includes/footer.php"); ?>
</body>
</html>