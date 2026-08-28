<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
$database = new Database();
$conn = $database->getConnection();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = 'checkout.php';
    header('Location: login.php?msg=require_login');
    exit();
}

// Nếu giỏ hàng trống chuyển về giỏ hàng
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? null);
$success_msg = '';
$error_msg = '';

// Lấy danh sách sản phẩm trong giỏ
$cart_products = [];
$total_all = 0;

if (!empty($_SESSION['cart']) && $conn) {
    $cart_ids = array_map('intval', array_keys($_SESSION['cart']));
    $ids = implode(',', $cart_ids);

    if (!empty($ids)) {
        $stmt = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
        $cart_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Tính tổng giá trị đơn hàng
foreach ($cart_products as $item) {
    $qty = $_SESSION['cart'][$item['id']] ?? 1;
    $total_all += ($item['price'] * $qty);
}

// Xử lý khi nhấn nút Đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_order'])) {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
    $dia_chi = trim($_POST['dia_chi'] ?? '');
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');

    if (!empty($ho_ten) && !empty($so_dien_thoai) && !empty($dia_chi)) {
        try {
            $conn->beginTransaction();

            // Lưu bảng orders
            $sql_order = "INSERT INTO orders (user_id, customer_name, phone, address, note, total_money, status, created_at) 
                          VALUES (:user_id, :name, :phone, :address, :note, :total, 'pending', NOW())";
            $stmt = $conn->prepare($sql_order);
            $stmt->execute([
                ':user_id' => $user_id,
                ':name'    => $ho_ten,
                ':phone'   => $so_dien_thoai,
                ':address' => $dia_chi,
                ':note'    => $ghi_chu,
                ':total'   => $total_all
            ]);
            $order_id = $conn->lastInsertId();

            // Lưu bảng order_details
            $sql_detail = "INSERT INTO order_details (order_id, product_id, price, quantity, total_price) 
                           VALUES (:order_id, :product_id, :price, :quantity, :total_price)";
            $stmt_detail = $conn->prepare($sql_detail);

            foreach ($cart_products as $p) {
                $qty = $_SESSION['cart'][$p['id']] ?? 1;
                $stmt_detail->execute([
                    ':order_id'    => $order_id,
                    ':product_id'  => $p['id'],
                    ':price'       => $p['price'],
                    ':quantity'    => $qty,
                    ':total_price' => $p['price'] * $qty
                ]);
            }

            $conn->commit();
            unset($_SESSION['cart']);
            $success_msg = "Đặt hàng thành công! Đơn hàng của bạn đang được xử lý. Cảm ơn bạn đã ủng hộ Gấu Bông Store.";
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error_msg = "Có lỗi xảy ra: " . $e->getMessage();
        }
    } else {
        $error_msg = "Vui lòng điền đầy đủ các thông tin bắt buộc (*).";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Gấu Bông Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    body {
        background-color: #FFF5F7;
        font-family: 'Montserrat', Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    .checkout-wrapper {
        background: #ffffff;
        border-radius: 16px;
        padding: 30px;
        margin-top: 30px;
        margin-bottom: 50px;
        box-shadow: 0 4px 20px rgba(244, 114, 182, 0.15);
        border: 1px solid #FCE7F3;
    }

    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .section-heading {
        font-size: 20px;
        color: #9333EA;
        margin-bottom: 20px;
        border-bottom: 2px solid #F3E8FF;
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-group {
        margin-bottom: 18px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-weight: 600;
        color: #4A5568;
        font-size: 14px;
    }

    .form-group label span {
        color: #EC4899;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #E9D5FF;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #A855F7;
        box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.2);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 90px;
    }

    .btn-submit-order {
        background: linear-gradient(135deg, #A855F7, #EC4899);
        color: #ffffff;
        border: none;
        padding: 14px 20px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
        box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
        transition: all 0.3s ease;
    }

    .btn-submit-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(236, 72, 153, 0.4);
    }

    .summary-card {
        background: #FAF5FF;
        border: 1px solid #F3E8FF;
        border-radius: 12px;
        padding: 20px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px dashed #E9D5FF;
        font-size: 14px;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        font-size: 18px;
        font-weight: 700;
        color: #374151;
    }

    .summary-total .price {
        color: #EC4899;
        font-size: 22px;
    }

    .alert-success {
        background: #DEF7EC;
        color: #03543F;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background: #FDE8E8;
        color: #9B1C1C;
        padding: 15px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .checkout-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
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
                <?php echo htmlspecialchars($success_msg); ?>
                <div style="margin-top: 20px;">
                    <a href="index.php" class="btn-submit-order"
                        style="display: inline-block; width: auto; text-decoration: none; padding: 10px 25px;">Trở về
                        trang chủ</a>
                </div>
            </div>
            <?php else: ?>
            <?php if (!empty($error_msg)): ?>
            <div class="alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
            <?php endif; ?>

            <div class="checkout-grid">
                <div>
                    <h2 class="section-heading"><i class="fa-solid fa-truck"></i> THÔNG TIN GIAO HÀNG</h2>
                    <form action="checkout.php" method="POST" id="checkoutForm">
                        <div class="form-group">
                            <label for="ho_ten">Họ và tên <span>(*)</span></label>
                            <input type="text" id="ho_ten" name="ho_ten" class="form-control"
                                value="<?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? ''); ?>"
                                placeholder="Nhập họ và tên..." required>
                        </div>
                        <div class="form-group">
                            <label for="so_dien_thoai">Số điện thoại <span>(*)</span></label>
                            <input type="tel" id="so_dien_thoai" name="so_dien_thoai" class="form-control"
                                value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>"
                                placeholder="Nhập số điện thoại..." required>
                        </div>
                        <div class="form-group">
                            <label for="dia_chi">Địa chỉ nhận hàng <span>(*)</span></label>
                            <textarea id="dia_chi" name="dia_chi" class="form-control"
                                placeholder="Nhập địa chỉ chi tiết..."
                                required><?php echo htmlspecialchars($_SESSION['user']['address'] ?? ''); ?></textarea>
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
                        <?php foreach ($cart_products as $row): 
                                $qty = $_SESSION['cart'][$row['id']] ?? 1;
                                $subtotal = $row['price'] * $qty;
                            ?>
                        <div class="summary-item">
                            <div>
                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                <div style="color: #6B7280; font-size: 13px;">x <?php echo $qty; ?></div>
                            </div>
                            <div style="font-weight: 600; color: #374151;">
                                <?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div class="summary-total">
                            <span>Tổng cộng:</span>
                            <span class="price"><?php echo number_format($total_all, 0, ',', '.'); ?> VNĐ</span>
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