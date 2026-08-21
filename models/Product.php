<?php
class ProductModel {
    private $conn;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // BE 3 Hàm lưu đường dẫn ảnh vào bảng
    public function insertProductImage($product_id, $image_url) {
        $sql = "INSERT INTO product_images (product_id, image_url) VALUES (:product_id, :image_url)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':image_url', $image_url);
        return $stmt->execute();
    }

    // BE 3 Hàm lấy thông tin
    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // BE 3 Hàm lấy mảng ảnh phụ của sản phẩm
    public function getProductImages($product_id) {
        $sql = "SELECT * FROM product_images WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>