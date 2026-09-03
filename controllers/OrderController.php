<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

// 1. Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = 'checkout.php';
    header('Location: login.php?msg=require_login');
    exit();
}

// 2. Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? null);

$cart_products = [];
$total_all = 0;
$success_msg = '';
$error_msg = '';

// 3. Lấy dữ liệu sản phẩm trong giỏ & tính tổng tiền
if (!empty($_SESSION['cart']) && $conn) {
    $cart_ids = array_map('intval', array_keys($_SESSION['cart']));
    $ids = implode(',', $cart_ids);

    if (!empty($ids)) {
        $stmt = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
        $cart_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cart_products as $item) {
            $qty = $_SESSION['cart'][$item['id']] ?? 1;
            $total_all += ($item['price'] * $qty);
        }
    }
}

// 4. Xử lý đặt hàng (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_order'])) {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
    $dia_chi = trim($_POST['dia_chi'] ?? '');
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');

    if (!empty($ho_ten) && !empty($so_dien_thoai) && !empty($dia_chi)) {
        try {
            $conn->beginTransaction();

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

// 5. Nạp view hiển thị
require_once __DIR__ . '/views/checkout.php';