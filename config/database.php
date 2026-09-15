<?php

class Database
{
    private $host = "127.0.0.1";
    private $port = "3306";
    private $db_name = "bangaubong_db";
    private $username = "root";
    private $password = "";

    public $conn = null;

    public function getConnection()
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            // Tạo / cập nhật tài khoản admin
            $this->seedAdmin();

            return $this->conn;

        } catch (PDOException $e) {
            die("Lỗi kết nối CSDL: " . $e->getMessage());
        }
    }

    private function seedAdmin()
    {
        if (!$this->conn) {
            return;
        }

        try {
            // Mật khẩu admin mặc định
            $passwordHash = password_hash(
                'password123',
                PASSWORD_DEFAULT
            );

            // Kiểm tra admin đã tồn tại chưa
            $stmt = $this->conn->prepare(
                "SELECT id FROM users WHERE username = :username LIMIT 1"
            );

            $stmt->execute([
                ':username' => 'admin'
            ]);

            $admin = $stmt->fetch();

            if (!$admin) {

                // Chưa có admin -> tạo mới
                $insert = $this->conn->prepare("
                    INSERT INTO users
                        (fullname, username, password, email, role, status)
                    VALUES
                        (:fullname, :username, :password, :email, 1, 1)
                ");

                $insert->execute([
                    ':fullname' => 'Quản Trị Viên',
                    ':username' => 'admin',
                    ':password' => $passwordHash,
                    ':email'    => 'admin@toanbangau.com'
                ]);

            } else {

                // Đã có admin -> reset mật khẩu
                $update = $this->conn->prepare("
                    UPDATE users
                    SET password = :password,
                        role = 1,
                        status = 1
                    WHERE username = :username
                ");

                $update->execute([
                    ':password' => $passwordHash,
                    ':username' => 'admin'
                ]);
            }

        } catch (PDOException $e) {
            // Bỏ qua lỗi nếu bảng users chưa tồn tại
        }
    }
}
?>