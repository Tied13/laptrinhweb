<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
// 1. Thêm sản phẩm vào giỏ hàng (cộng dồn nếu trùng ID)
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity   = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    if ($product_id > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
    header("Location: ../cart.php");
    exit();
}
// 2. Cập nhật số lượng (form gửi mảng qty[product_id] = so_luong)
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $product_id => $qty) {
            $qty = intval($qty);
            if ($qty > 0) {
                $_SESSION['cart'][$product_id] = $qty;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }
    header("Location: ../cart.php");
    exit();
}
// 3. Xóa 1 sản phẩm khỏi giỏ hàng
if ($action === 'delete') {
    $product_id = intval($_GET['id'] ?? 0);
    unset($_SESSION['cart'][$product_id]);
    header("Location: ../cart.php");
    exit();
}

// 4. Xóa toàn bộ giỏ hàng
if ($action === 'clear') {
    unset($_SESSION['cart']);
    header("Location: ../cart.php");
    exit();
}
// 5. Tính tổng tiền giỏ hàng - hàm dùng chung, cart.php/checkout.php sẽ gọi khi được nối dây
function getCartTotal($db, $cart) {
    $total = 0;
    if (!empty($cart)) {
        $ids = implode(',', array_keys($cart));
        $stmt = $db->query("SELECT id, price FROM products WHERE id IN ($ids)");
        $products = $stmt->fetchAll();
        foreach ($products as $p) {
            $total += $p['price'] * $cart[$p['id']];
        }
    }
    return $total;
}