<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();
$db = $conn; // Đồng bộ cả 2 biến kết nối

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php?msg=require_login");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Hàm tính tổng tiền giỏ hàng (dùng chung cho checkout hoặc nơi khác)
function getCartTotal($db, $cart) {
    $total = 0;
    if (!empty($cart) && $db) {
        $ids = implode(',', array_map('intval', array_keys($cart)));
        if (!empty($ids)) {
            $stmt = $db->query("SELECT id, price FROM products WHERE id IN ($ids)");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($products as $p) {
                $total += $p['price'] * ($cart[$p['id']] ?? 0);
            }
        }
    }
    return $total;
}

$action = $_GET['action'] ?? '';

// 1. Thêm vào giỏ hàng từ Form (POST add_to_cart hoặc ?action=add)
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) || ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST')) {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    if ($product_id > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
    header('Location: cart.php');
    exit();
}

// 2. Thêm nhanh qua URL GET (?action=add&id=X)
if ($action === 'add' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $product_id = intval($_GET['id']);
    if ($product_id > 0) {
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
    }
    header('Location: cart.php');
    exit();
}

// 3. Cập nhật số lượng
if (isset($_POST['update_cart']) || ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST')) {
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
if ($action === 'delete') {
    $p_id = intval($_GET['id'] ?? 0);
    if ($p_id > 0) {
        unset($_SESSION['cart'][$p_id]);
    }
    header('Location: cart.php');
    exit();
}

// 5. Xóa toàn bộ giỏ hàng
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    unset($_SESSION['cart']);
    header('Location: cart.php');
    exit();
}

// 6. Lấy danh sách sản phẩm từ DB để cart.php render
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