<?php
class Product {
    private $conn;
    private $table = "products";

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // BE 3: Lưu đường dẫn ảnh vào bảng product_images (Gallery)
    public function insertProductImage($product_id, $image_url) {
        $sql = "INSERT INTO product_images (product_id, image_url) VALUES (:product_id, :image_url)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':image_url', $image_url);
        return $stmt->execute();
    }

    // BE 3: Lấy thông tin 1 sản phẩm theo ID
    public function getProductById($id) {
        $sql = "SELECT p.*, c.name AS category_name 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // BE 3: Lấy mảng ảnh phụ của sản phẩm
    public function getProductImages($product_id) {
        $sql = "SELECT * FROM product_images WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách sản phẩm phân trang & lọc
    public function getAll($keyword = '', $category_id = 0, $limit = 0, $offset = 0) {
        $sql = "SELECT p.*, c.name AS category_name 
                FROM " . $this->table . " p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        if (!empty($keyword)) {
            $sql .= " AND p.name LIKE :keyword";
        }
        if ($category_id > 0) {
            $sql .= " AND p.category_id = :category_id";
        }
        
        $sql .= " ORDER BY p.id DESC";

        // Chỉ áp dụng LIMIT và OFFSET khi có truyền limit > 0
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($keyword)) {
            $kw = "%{$keyword}%";
            $stmt->bindValue(':keyword', $kw);
        }
        if ($category_id > 0) {
            $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
        }
        if ($limit > 0) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm tổng số sản phẩm (dùng cho phân trang)
    public function countAll($keyword = '', $category_id = 0) {
        $sql = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE 1=1";
        
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

    // Tạo mới sản phẩm
    public function create($name, $category_id, $price, $description = '', $thumbnail = '') {
        $sql = "INSERT INTO " . $this->table . " (name, category_id, price, description, thumbnail) 
                VALUES (:name, :category_id, :price, :description, :thumbnail)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', trim($name));
        $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
        $stmt->bindValue(':price', (float)$price);
        $stmt->bindValue(':description', trim($description));
        $stmt->bindValue(':thumbnail', trim($thumbnail));
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật sản phẩm
    public function update($id, $name, $category_id, $price, $description = '', $thumbnail = null) {
        if ($thumbnail !== null && !empty($thumbnail)) {
            $sql = "UPDATE " . $this->table . " 
                    SET name = :name, category_id = :category_id, price = :price, 
                        description = :description, thumbnail = :thumbnail 
                    WHERE id = :id";
        } else {
            $sql = "UPDATE " . $this->table . " 
                    SET name = :name, category_id = :category_id, price = :price, 
                        description = :description 
                    WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', trim($name));
        $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
        $stmt->bindValue(':price', (float)$price);
        $stmt->bindValue(':description', trim($description));
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        
        if ($thumbnail !== null && !empty($thumbnail)) {
            $stmt->bindValue(':thumbnail', trim($thumbnail));
        }
        
        return $stmt->execute();
    }

    // Xóa sản phẩm và dọn sạch bảng gallery liên quan
    public function delete($id) {
        // 1. Xóa ảnh phụ trong bảng product_images trước
        $stmt_img = $this->conn->prepare("DELETE FROM product_images WHERE product_id = :id");
        $stmt_img->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt_img->execute();

        // 2. Xóa sản phẩm chính
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>