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

switch ($action) {
    // 1. Cập nhật trạng thái đơn hàng từ Admin
    case 'updateStatus':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;

            if ($id > 0) {
                if ($orderModel->updateStatus($id, $status)) {
                    $_SESSION['success'] = "Cập nhật trạng thái đơn hàng #$id thành công!";
                } else {
                    $_SESSION['error'] = "Lỗi: Không thể cập nhật trạng thái đơn hàng!";
                }
            }
        }
        header('Location: ../admin/order.php');
        exit();

    // 2. Xóa đơn hàng từ Admin (Xóa chi tiết sản phẩm trước, xóa đơn sau)
    case 'delete':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $result = method_exists($orderModel, 'deleteOrder') 
                ? $orderModel->deleteOrder($id) 
                : $orderModel->delete($id);

            if ($result) {
                $_SESSION['success'] = "Đã xóa thành công đơn hàng #$id!";
            } else {
                $_SESSION['error'] = "Lỗi: Không thể xóa đơn hàng #$id!";
            }
        }
        header('Location: ../admin/order.php');
        exit();

    // 3. Xử lý đặt hàng từ phía khách hàng (Checkout)
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id          = $_SESSION['user_id'] ?? null;
            $customer_name    = $_POST['customer_name'] ?? $_POST['fullname'] ?? '';
            $customer_phone   = $_POST['customer_phone'] ?? $_POST['phone'] ?? '';
            $customer_address = $_POST['customer_address'] ?? $_POST['address'] ?? '';
            $total_price      = $_POST['total_price'] ?? 0;

            $order_id = $orderModel->createOrder($user_id, $customer_name, $customer_phone, $customer_address, $total_price);

            if ($order_id) {
                // Thêm danh sách sản phẩm từ giỏ hàng vào bảng order_details
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $p_id  = $item['id'];
                        $qty   = $item['quantity'];
                        $price = $item['price'];

                        if (method_exists($orderModel, 'addOrderDetail')) {
                            $orderModel->addOrderDetail($order_id, $p_id, $qty, $price);
                        } else {
                            $orderModel->addOrderItem($order_id, $p_id, $qty, $price);
                        }
                    }
                    unset($_SESSION['cart']); // Xóa giỏ hàng sau khi lưu đơn thành công
                }
                $_SESSION['success'] = "Đặt hàng thành công! Mã đơn của bạn là #$order_id";
                header('Location: ../index.php');
                exit();
            } else {
                $_SESSION['error'] = "Tạo đơn hàng thất bại. Vui lòng thử lại!";
                header('Location: ../checkout.php');
                exit();
            }
        }
        break;

    default:
        header('Location: ../admin/order.php');
        exit();
}
