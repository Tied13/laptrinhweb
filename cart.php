<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
$database = new Database();
$conn = $database->getConnection();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php?msg=require_login");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. Thêm vào giỏ hàng từ Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    header('Location: cart.php');
    exit();
}

// 2. Thêm nhanh qua URL (?action=add&id=X)
if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    if ($product_id > 0) {
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
    }
    header('Location: cart.php');
    exit();
}

// 3. Cập nhật số lượng
if (isset($_POST['update_cart'])) {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $p_id => $q) {
            $p_id = intval($p_id);
            $q = intval($q);
            if ($q > 0) {
                $_SESSION['cart'][$p_id] = $q;
            } else {
                unset($_SESSION['cart'][$p_id]);
            }
        }
    }
    header('Location: cart.php');
    exit();
}

// 4. Xóa 1 sản phẩm
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $p_id = intval($_GET['id']);
    unset($_SESSION['cart'][$p_id]);
    header('Location: cart.php');
    exit();
}

// 5. Xóa toàn bộ giỏ hàng
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit();
}

// 6. Lấy danh sách sản phẩm từ DB bằng PDO
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
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - Gấu Bông Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="container">
        <div class="cart-card-box">
            <a href="products.php" class="back-to-shop">
                <i class="fa-solid fa-arrow-left"></i> Tiếp tục mua hàng
            </a>
            <h2 class="cart-title">
                <i class="fa-solid fa-cart-shopping"></i> GIỎ HÀNG CỦA BẠN
            </h2>

            <?php if (!empty($cart_products)): ?>
            <form action="cart.php" method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Sản phẩm</th>
                            <th style="width: 15%;">Giá</th>
                            <th style="width: 15%;">Số lượng</th>
                            <th style="width: 15%;">Tổng tiền</th>
                            <th style="width: 15%;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $total_all = 0;
                            foreach ($cart_products as $item): 
                                $qty = $_SESSION['cart'][$item['id']] ?? 1;
                                $subtotal = $item['price'] * $qty;
                                $total_all += $subtotal;
                                $thumb = $item['thumbnail'] ?? ($item['image'] ?? '');
                        ?>
                        <tr>
                            <td>
                                <div class="cart-product-info">
                                    <img src="assets/uploads/products/<?php echo htmlspecialchars($thumb); ?>"
                                        onerror="this.src='https://via.placeholder.com/80?text=No+Image';"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <input type="number" name="qty[<?php echo $item['id']; ?>]" value="<?php echo $qty; ?>"
                                    min="1" class="cart-qty-input">
                            </td>
                            <td class="item-total-price"><?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <a href="cart.php?action=delete&id=<?php echo $item['id']; ?>" class="btn-delete-cart"
                                    onclick="return confirm('Bạn có muốn xóa sản phẩm này?');">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-bottom-actions">
                    <a href="cart.php?action=clear" class="btn-clear-all"
                        onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?');">
                        <i class="fa-solid fa-trash-can"></i> Xóa toàn bộ giỏ hàng
                    </a>

                    <div class="cart-summary-box">
                        <button type="submit" name="update_cart" class="btn-update-cart">
                            <i class="fa-solid fa-rotate"></i> Cập nhật giỏ hàng
                        </button>

                        <div class="cart-total-text">
                            Tổng tiền thanh toán: <span><?php echo number_format($total_all, 0, ',', '.'); ?> VNĐ</span>
                        </div>

                        <a href="checkout.php" class="btn-checkout-gradient">
                            Đến trang thanh toán <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <div style="text-align: center; padding: 50px 0;">
                <i class="fa-solid fa-basket-shopping"
                    style="font-size: 60px; color: #D8B4FE; margin-bottom: 15px;"></i>
                <p style="font-size: 16px; color: #666;">Giỏ hàng của bạn đang trống!</p>
                <a href="products.php" class="btn-checkout-gradient" style="display: inline-flex; margin-top: 15px;">Mua
                    sắm ngay</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>