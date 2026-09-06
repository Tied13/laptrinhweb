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
//1.CHECKOUT - Khách đặt hàng
//INSERT orders -> INSERT từng order_details -> xóa giỏ hàng
//Form checkout.php gửi: ho_ten, so_dien_thoai, dia_chi
if ($action === 'checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = 'checkout.php';
        $_SESSION['error'] = "Vui lòng đăng nhập để đặt hàng!";
        header("Location: ../login.php");
        exit();
    }
    $user_id = (int)$_SESSION['user_id'];

    $customer_name    = trim($_POST['ho_ten'] ?? '');
    $customer_phone   = trim($_POST['so_dien_thoai'] ?? '');
    $customer_address = trim($_POST['dia_chi'] ?? '');

// Giỏ hàng trống -> không có gì để đặt
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        $_SESSION['error'] = "Giỏ hàng của bạn đang trống!";
        header("Location: ../cart.php");
        exit();
    }

// Thiếu thông tin bắt buộc -> quay lại trang checkout
    if ($customer_name === '' || $customer_phone === '' || $customer_address === '') {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ Họ tên, Số điện thoại và Địa chỉ!";
        header("Location: ../checkout.php");
        exit();
    }

 // Lấy giá từ CSDL (không tin giá gửi từ client) và tính tổng tiền
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
        $product = $stmt->fetch();

        if (!$product) {
            continue; // sản phẩm đã bị xóa khỏi shop -> bỏ qua
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
        header("Location: ../cart.php");
        exit();
    }

 // Ghi đơn hàng (user_id đã lấy từ session ở đầu action, luôn có vì bắt buộc đăng nhập)
    $order_id = $orderModel->createOrder($user_id, $customer_name, $customer_phone, $customer_address, $total);
    if (!$order_id) {
        $_SESSION['error'] = "Đặt hàng thất bại, vui lòng thử lại!";
        header("Location: ../checkout.php");
        exit();
    }

// Ghi từng sản phẩm vào chi tiết đơn hàng
    foreach ($items as $item) {
        $orderModel->addOrderDetail($order_id, $item['product_id'], $item['quantity'], $item['price']);
    }

// Xóa giỏ hàng, báo thành công
    unset($_SESSION['cart']);
    $_SESSION['success'] = "Đặt hàng thành công! Mã đơn hàng của bạn là #" . $order_id;
    header("Location: ../index.php");
    exit();
}

//2.UPDATE STATUS (POST) - Admin đổi trạng thái đơn hàng
//0: Chờ xử lý, 1: Đang giao, 2: Hoàn thành, 3: Đã hủy
//Form admin/orders.php gửi: id, status
if ($action === 'updateStatus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Chặn user thường gọi thẳng URL này
    if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
        header("Location: ../login.php");
        exit();
    }

    $id     = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? -1);

    if ($id <= 0 || $status < 0 || $status > 3) {
        $_SESSION['error'] = "Dữ liệu cập nhật không hợp lệ!";
        header("Location: ../admin/orders.php");
        exit();
    }

    if ($orderModel->updateStatus($id, $status)) {
        $_SESSION['success'] = "Đã cập nhật trạng thái đơn hàng #" . $id;
    } else {
        $_SESSION['error'] = "Cập nhật trạng thái thất bại!";
    }
    header("Location: ../admin/orders.php");
    exit();
}

//3.DELETE (GET) - Admin xóa đơn hàng
//Link admin/orders.php: ../controllers/OrderController.php?action=delete&id=X
//Cần 2 method getOrderById() và deleteOrder() trong models/Order.php
if ($action === 'delete') {
    // Chặn user thường gọi thẳng URL này
    if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
        header("Location: ../login.php");
        exit();
    }

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = "Mã đơn hàng không hợp lệ!";
        header("Location: ../admin/orders.php");
        exit();
    }

    if (!$orderModel->getOrderById($id)) {
        $_SESSION['error'] = "Đơn hàng #" . $id . " không tồn tại!";
        header("Location: ../admin/orders.php");
        exit();
    }

    if ($orderModel->deleteOrder($id)) {
        $_SESSION['success'] = "Đã xóa đơn hàng #" . $id;
    } else {
        $_SESSION['error'] = "Xóa đơn hàng thất bại!";
    }
    header("Location: ../admin/orders.php");
    exit();
}

// Không khớp action nào -> về trang chủ
header("Location: ../index.php");
exit();
