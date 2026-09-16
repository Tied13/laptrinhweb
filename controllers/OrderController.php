<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

$database   = new Database();
$db         = $database->getConnection();
$orderModel = new Order($db);

$action = $_GET['action'] ?? '';

// 1. CHECKOUT - Khách đặt hàng
if ($action === 'checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = 'checkout.php';
        $_SESSION['error'] = "Vui lòng đăng nhập để đặt hàng!";
        session_write_close();
        header("Location: ../login.php");
        exit();
    }
    $user_id = (int)$_SESSION['user_id'];

    $customer_name    = trim($_POST['ho_ten'] ?? '');
    $customer_phone   = trim($_POST['so_dien_thoai'] ?? '');
    $customer_address = trim($_POST['dia_chi'] ?? '');

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        $_SESSION['error'] = "Giỏ hàng của bạn đang trống!";
        session_write_close();
        header("Location: ../cart.php");
        exit();
    }

    if ($customer_name === '' || $customer_phone === '' || $customer_address === '') {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ Họ tên, Số điện thoại và Địa chỉ!";
        session_write_close();
        header("Location: ../checkout.php");
        exit();
    }

    $items = [];
    $total = 0;
    foreach ($cart as $product_id => $quantity) {
        $product_id = (int)$product_id;
        $quantity   = (int)$quantity;
        if ($product_id <= 0 || $quantity <= 0) {
            continue;
        }

        $stmt = $db->prepare("SELECT id, price FROM products WHERE id = :id");
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            continue;
        }

        $items[] = [
            'product_id' => $product_id,
            'quantity'   => $quantity,
            'price'      => $product['price'],
        ];
        $total += $product['price'] * $quantity;
    }

    if (empty($items) || $total <= 0) {
        $_SESSION['error'] = "Sản phẩm trong giỏ hàng không hợp lệ!";
        session_write_close();
        header("Location: ../cart.php");
        exit();
    }

    $order_id = $orderModel->createOrder($user_id, $customer_name, $customer_phone, $customer_address, 0);
    
    if (!$order_id) {
        $_SESSION['error'] = "Đặt hàng thất bại, vui lòng thử lại!";
        session_write_close();
        header("Location: ../checkout.php");
        exit();
    }

    foreach ($items as $item) {
        $orderModel->addOrderDetail($order_id, $item['product_id'], $item['quantity'], $item['price']);
    }

    unset($_SESSION['cart']);
    $_SESSION['success'] = "Đặt hàng thành công! Mã đơn hàng của bạn là #" . $order_id;
    session_write_close();
    header("Location: ../checkout.php");
    exit();
}

// 2. UPDATE STATUS - Admin Cập nhật trạng thái đơn hàng
if ($action === 'updateStatus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
        $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này!";
        session_write_close();
        header("Location: ../login.php");
        exit();
    }

    $id     = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? -1);

    if ($id <= 0 || $status < 0 || $status > 3) {
        $_SESSION['error'] = "Dữ liệu cập nhật không hợp lệ!";
        session_write_close();
        header("Location: ../admin/orders.php");
        exit();
    }

    if ($orderModel->updateStatus($id, $status)) {
        $_SESSION['success'] = "Đã cập nhật trạng thái đơn hàng #" . $id;
    } else {
        $_SESSION['error'] = "Cập nhật trạng thái thất bại!";
    }
    session_write_close();
    header("Location: ../admin/orders.php");
    exit();
}

// 3. DELETE - Admin Xóa đơn hàng
if ($action === 'delete') {
    if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
        $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này!";
        session_write_close();
        header("Location: ../login.php");
        exit();
    }

    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if ($id <= 0 || !$orderModel->getOrderById($id)) {
        $_SESSION['error'] = "Đơn hàng không hợp lệ hoặc không tồn tại!";
        session_write_close();
        header("Location: ../admin/orders.php");
        exit();
    }

    if ($orderModel->deleteOrder($id)) {
        $_SESSION['success'] = "Đã xóa đơn hàng #" . $id;
    } else {
        $_SESSION['error'] = "Xóa đơn hàng thất bại!";
    }
    session_write_close();
    header("Location: ../admin/orders.php");
    exit();
}

// 4. CANCEL - Khách hàng tự hủy đơn
if ($action === 'cancel') {
    if (!isset($_SESSION['user_id'])) {
        session_write_close();
        header("Location: ../login.php");
        exit();
    }

    $id = (int)($_GET['id'] ?? 0);
    $user_id = (int)$_SESSION['user_id'];

    if ($id > 0 && method_exists($orderModel, 'cancelOrder')) {
        if ($orderModel->cancelOrder($id, $user_id)) {
            $_SESSION['success'] = "Đã hủy thành công đơn hàng #" . $id;
        } else {
            $_SESSION['error'] = "Không thể hủy đơn hàng này!";
        }
    }
    session_write_close();
    header("Location: ../account.php");
    exit();
}

session_write_close();
header("Location: ../index.php");
exit();
