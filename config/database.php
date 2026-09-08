<?php
class Database {
    private $host = "localhost";
    private $db_name = "bangaubong_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Tự động kiểm tra và khởi tạo tài khoản Admin mặc định
            $this->seedAdmin();

        } catch (PDOException $exception) {
            echo "Lỗi kết nối CSDL: " . $exception->getMessage();
        }
        return $this->conn;
    }

    private function seedAdmin() {
        if (!$this->conn) return;

        try {
            // Kiểm tra xem username 'admin' đã tồn tại hay chưa
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch();

            // Nếu chưa có tài khoản admin nào thì tự động tạo mới
            if (!$admin) {
                $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
                $insert = $this->conn->prepare("
                    INSERT INTO users (fullname, username, password, email, role, status)
                    VALUES ('Quản Trị Viên', 'admin', :password, 'admin@toanbangau.com', 1, 1)
                ");
                $insert->execute([':password' => $passwordHash]);
            }
        } catch (PDOException $e) {
            // Bỏ qua lỗi nếu bảng users chưa được import (tránh crash trang khi setup ban đầu)
        }
    }
}
?>