<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tìm user theo Email
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Tạo tài khoản mới (Đăng ký)
    public function register($fullname, $email, $password) {
        $query = "INSERT INTO " . $this->table_name . " (fullname, email, password, role, status) 
                  VALUES (:fullname, :email, :password, 0, 1)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        return $stmt->execute();
    }

    // Lấy tất cả danh sách User (cho trang Admin)
    public function getAllUsers() {
        $query = "SELECT id, fullname, email, role, status, created_at FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Đổi trạng thái Khóa / Mở tài khoản
    public function toggleStatus($id, $current_status) {
        $new_status = ($current_status == 1) ? 0 : 1;
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $new_status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
