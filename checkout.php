<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Gấu Bông Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="container header-content"
            style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0;">
            <a href="index.php" class="logo"
                style="text-decoration: none; font-size: 22px; font-weight: 700; color: #9333EA;">
                <i class="fa-solid fa-store"></i> Gấu Bông Store
            </a>
            <a href="cart.php" style="color: #9333EA; text-decoration: none; font-weight: 600;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại giỏ hàng
            </a>
        </div>
    </header>

    <div class="container">
        <div class="checkout-wrapper">
            <?php if (!empty($success_msg)): ?>
            <div class="alert-success">
                <i class="fa-solid fa-circle-check" style="font-size: 36px; display: block; margin-bottom: 10px;"></i>
                <?= htmlspecialchars($success_msg); ?>
                <div style="margin-top: 20px;">
                    <a href="index.php" class="btn-submit-order"
                        style="display: inline-block; width: auto; text-decoration: none; padding: 10px 25px;">
                        Trở về trang chủ
                    </a>
                </div>
            </div>
            <?php else: ?>
            <?php if (!empty($error_msg)): ?>
            <div class="alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_msg); ?>
            </div>
            <?php endif; ?>

            <div class="checkout-grid">
                <div>
                    <h2 class="section-heading"><i class="fa-solid fa-truck"></i> THÔNG TIN GIAO HÀNG</h2>
                    <form action="checkout.php" method="POST" id="checkoutForm">
                        <div class="form-group">
                            <label for="ho_ten">Họ và tên <span>(*)</span></label>
                            <input type="text" id="ho_ten" name="ho_ten" class="form-control"
                                value="<?= htmlspecialchars($_SESSION['user']['fullname'] ?? ''); ?>"
                                placeholder="Nhập họ và tên..." required>
                        </div>
                        <div class="form-group">
                            <label for="so_dien_thoai">Số điện thoại <span>(*)</span></label>
                            <input type="tel" id="so_dien_thoai" name="so_dien_thoai" class="form-control"
                                value="<?= htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>"
                                placeholder="Nhập số điện thoại..." required>
                        </div>
                        <div class="form-group">
                            <label for="dia_chi">Địa chỉ nhận hàng <span>(*)</span></label>
                            <textarea id="dia_chi" name="dia_chi" class="form-control"
                                placeholder="Nhập địa chỉ chi tiết..."
                                required><?= htmlspecialchars($_SESSION['user']['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="ghi_chu">Ghi chú đơn hàng</label>
                            <textarea id="ghi_chu" name="ghi_chu" class="form-control"
                                placeholder="Nhập ghi chú thêm (nếu có)..."></textarea>
                        </div>
                        <button type="submit" name="btn_order" class="btn-submit-order">
                            <i class="fa-solid fa-check"></i> XÁC NHẬN ĐẶT HÀNG
                        </button>
                    </form>
                </div>

                <div>
                    <h2 class="section-heading"><i class="fa-solid fa-receipt"></i> ĐƠN HÀNG CỦA BẠN</h2>
                    <div class="summary-card">

                        <?php
    $total = 0;

    foreach (($cart_products ?? []) as $row):
        $qty = $_SESSION['cart'][$row['id']] ?? 1;
        $subtotal = $row['price'] * $qty;
        $total += $subtotal;
    ?>

                        <div class="summary-item">
                            <div>
                                <strong><?= htmlspecialchars($row['name']); ?></strong>
                                <div style="color: #6B7280; font-size: 13px;">
                                    x <?= $qty; ?>
                                </div>
                            </div>

                            <div style="font-weight: 600; color: #374151;">
                                <?= number_format($subtotal, 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>

                        <?php endforeach; ?>

                        <div class="summary-total">
                            <span>Tổng cộng:</span>
                            <span class="price">
                                <?= number_format($total, 0, ',', '.'); ?> VNĐ
                            </span>
                        </div>

                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>

</html>