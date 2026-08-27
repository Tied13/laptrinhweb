<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);

$action = $_GET['action'] ?? '';

// 1. Đặt hàng (Checkout): INSERT orders -> INSERT từng order_details -> xóa giỏ hàng
if ($action === 'checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten        = trim($_POST['ho_ten'] ?? '');
    $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
    $dia_chi       = trim($_POST['dia_chi'] ?? '');

    // Giỏ hàng trống -> không có gì để đặt
    if (empty($_SESSION['cart'])) {
        header("Location: ../cart.php");
        exit();
    }

    // Thiếu thông tin giao hàng -> quay lại checkout
    if ($ho_ten === '' || $so_dien_thoai === '' || $dia_chi === '') {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ Họ tên, SĐT và Địa chỉ!";
        header("Location: ../checkout.php");
        exit();
    }

    // Lấy giá thật từ DB để tính tổng tiền (không tin dữ liệu phía client)
    $items = [];
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $db->prepare("SELECT id, price FROM products WHERE id = :id");
        $stmt->bindValue(':id', (int)$product_id, PDO::PARAM_INT);
        $stmt->execute();
        $p = $stmt->fetch();
        if ($p) {
            $qty = max(1, (int)$quantity);
            $items[] = [
                'product_id' => (int)$p['id'],
                'quantity'   => $qty,
                'price'      => $p['price']
            ];
            $total += $p['price'] * $qty;
        }
    }

    if (empty($items)) {
        header("Location: ../cart.php");
        exit();
    }

    // INSERT vào bảng orders, nhận về ID đơn vừa tạo
    $user_id  = $_SESSION['user_id'] ?? null; // khách chưa đăng nhập -> NULL
    $order_id = $orderModel->createOrder($user_id, $ho_ten, $so_dien_thoai, $dia_chi, $total);

    if ($order_id) {
        // Duyệt mảng giỏ hàng, INSERT từng item vào order_details
        foreach ($items as $item) {
            $orderModel->addOrderDetail($order_id, $item['product_id'], $item['quantity'], $item['price']);
        }
        // Xóa giỏ hàng sau khi đặt thành công
        unset($_SESSION['cart']);
        $_SESSION['success'] = "Đặt hàng thành công! Mã đơn hàng của bạn là #" . $order_id;
        header("Location: ../index.php");
        exit();
    }

    $_SESSION['error'] = "Đặt hàng thất bại, vui lòng thử lại!";
    header("Location: ../checkout.php");
    exit();
}

// 2. Admin cập nhật trạng thái đơn (0: Chờ xử lý, 1: Đang giao, 2: Hoàn thành, 3: Đã hủy)
if ($action === 'updateStatus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Chặn người không phải Admin gọi thẳng URL này
    if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
        $_SESSION['error'] = "Bạn không có quyền truy cập!";
        header("Location: ../login.php");
        exit();
    }

    $id     = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? -1);

    if ($id > 0 && $status >= 0 && $status <= 3) {
        $orderModel->updateStatus($id, $status);
        $_SESSION['success'] = "Đã cập nhật trạng thái đơn hàng #" . $id;
    } else {
        $_SESSION['error'] = "Dữ liệu không hợp lệ!";
    }
    header("Location: ../admin/orders.php");
    exit();
}
