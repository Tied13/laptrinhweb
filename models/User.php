<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Đăng ký tài khoản
    public function register($fullname, $username, $password, $email, $phone, $address) {
        $query = "INSERT INTO " . $this->table . " (fullname, username, password, email, phone, address, role, status) 
                  VALUES (:fullname, :username, :password, :email, :phone, :address, 0, 1)";
        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);

        return $stmt->execute();
    }

    // Tìm user theo username
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Lấy toàn bộ danh sách User cho trang Admin
    public function getAllUsers() {
        $query = "SELECT id, fullname, username, email, phone, address, role, status, created_at FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Đổi trạng thái khóa/mở tài khoản
    public function toggleStatus($id, $current_status) {
        $new_status = ($current_status == 1) ? 0 : 1;
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $new_status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}