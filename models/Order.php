<?php
class Order {
    private $conn;
    private $table = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }
    // Tạo đơn hàng mới, trả về ID đơn vừa tạo
    public function createOrder($user_id, $customer_name, $customer_phone, $customer_address, $total_price) {
        $query = "INSERT INTO " . $this->table . " (user_id, customer_name, customer_phone, customer_address, total_price, status)
                  VALUES (:user_id, :customer_name, :customer_phone, :customer_address, :total_price, 0)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":customer_name", $customer_name);
        $stmt->bindParam(":customer_phone", $customer_phone);
        $stmt->bindParam(":customer_address", $customer_address);
        $stmt->bindParam(":total_price", $total_price);

        if ($stmt->execute()) {
            // Lấy ID đơn vừa tạo (SELECT + ORDER BY + LIMIT - đúng cú pháp giáo trình tr.104)
            $query_id = "SELECT id FROM " . $this->table . " ORDER BY id DESC LIMIT 1";
            $stmt_id = $this->conn->prepare($query_id);
            $stmt_id->execute();
            $row = $stmt_id->fetch();
            return $row['id'];
        }
        return false;
    }
        // Thêm 1 sản phẩm vào chi tiết đơn hàng
    public function addOrderDetail($order_id, $product_id, $quantity, $price) {
        $query = "INSERT INTO order_details (order_id, product_id, quantity, price)
                  VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":price", $price);

        return $stmt->execute();
    }
        // Lấy toàn bộ đơn hàng cho trang Admin
    public function getAllOrders() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
        // Cập nhật trạng thái đơn hàng (0: Chờ xử lý, 1: Đang giao, 2: Hoàn thành, 3: Đã hủy)
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
        // Tính tổng doanh thu cho Dashboard (bỏ đơn đã hủy)
    public function getTotalRevenue() {
        $query = "SELECT SUM(total_price) AS total_revenue FROM " . $this->table . " WHERE status != 3";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total_revenue'] ?? 0;
    }

    // Đếm tổng số đơn hàng cho Dashboard
    public function countOrders() {
        $query = "SELECT COUNT(id) AS total_orders FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total_orders'] ?? 0;
    }
}