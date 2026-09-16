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

    // Lấy thông tin 1 đơn hàng theo ID
    public function getOrderById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Lấy chi tiết đơn hàng kèm tên sản phẩm
    public function getOrderDetails($order_id) {
        $query = "SELECT order_details.*, products.name AS product_name
                  FROM order_details
                  INNER JOIN products ON order_details.product_id = products.id
                  WHERE order_details.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecentOrders($limit) {
        $limit = (int)$limit;
        $query = "SELECT id, created_at, total_price AS total FROM " . $this->table . " ORDER BY id DESC LIMIT $limit";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Xóa đơn hàng cùng chi tiết đơn hàng
    public function deleteOrder($id) {
        try {
            $this->conn->beginTransaction();

            $queryDetails = "DELETE FROM order_details WHERE order_id = :order_id";
            $stmtDetails = $this->conn->prepare($queryDetails);
            $stmtDetails->bindParam(":order_id", $id);
            $stmtDetails->execute();

            $queryOrder = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmtOrder = $this->conn->prepare($queryOrder);
            $stmtOrder->bindParam(":id", $id);
            $stmtOrder->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // Cập nhật trạng thái đơn hàng
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Tính tổng doanh thu cho Dashboard
    public function getTotalRevenue() {
        $query = "SELECT SUM(total_price) AS total_revenue FROM " . $this->table . " WHERE status != 3";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total_revenue'] ?? 0;
    }

    // Đếm tổng số đơn hàng
    public function countOrders() {
        $query = "SELECT COUNT(id) AS total_orders FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total_orders'] ?? 0;
    }

    // Lấy lịch sử đơn hàng của user
    public function getOrdersByUserId($userId) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
