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

    public function getAll($keyword = '', $category_id = 0, $limit = 10, $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        if (!empty($keyword)) {
            $sql .= " AND p.name LIKE :keyword";
        }
        if ($category_id > 0) {
            $sql .= " AND p.category_id = :category_id";
        }
        
        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($keyword)) {
            $kw = "%{$keyword}%";
            $stmt->bindValue(':keyword', $kw);
        }
        if ($category_id > 0) {
            $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($keyword = '', $category_id = 0) {
        $sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
        
        if (!empty($keyword)) {
            $sql .= " AND name LIKE :keyword";
        }
        if ($category_id > 0) {
            $sql .= " AND category_id = :category_id";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($keyword)) {
            $kw = "%{$keyword}%";
            $stmt->bindValue(':keyword', $kw);
        }
        if ($category_id > 0) {
            $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function create($name, $category_id, $price, $quantity, $description = '', $image = '') {
        $sql = "INSERT INTO products (name, category_id, price, quantity, description, image) 
                VALUES (:name, :category_id, :price, :quantity, :description, :image)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', trim($name));
        $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
        $stmt->bindValue(':price', (float)$price);
        $stmt->bindValue(':quantity', (int)$quantity, PDO::PARAM_INT);
        $stmt->bindValue(':description', trim($description));
        $stmt->bindValue(':image', trim($image));
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $name, $category_id, $price, $quantity, $description = '', $image = null) {
        if ($image !== null) {
            $sql = "UPDATE products 
                    SET name = :name, category_id = :category_id, price = :price, 
                        quantity = :quantity, description = :description, image = :image 
                    WHERE id = :id";
        } else {
            $sql = "UPDATE products 
                    SET name = :name, category_id = :category_id, price = :price, 
                        quantity = :quantity, description = :description 
                    WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', trim($name));
        $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
        $stmt->bindValue(':price', (float)$price);
        $stmt->bindValue(':quantity', (int)$quantity, PDO::PARAM_INT);
        $stmt->bindValue(':description', trim($description));
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        
        if ($image !== null) {
            $stmt->bindValue(':image', trim($image));
        }
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
