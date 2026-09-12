--DROP DATABASE IF EXISTS bangaubong_db;
CREATE DATABASE IF NOT EXISTS bangaubong_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bangaubong_db;

-- 1. Bảng Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    role TINYINT DEFAULT 0 COMMENT '0: Khách hàng, 1: Admin',
    status TINYINT DEFAULT 1 COMMENT '0: Bị khóa, 1: Hoạt động',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Bảng Danh mục
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status TINYINT DEFAULT 1
);

-- 3. Bảng Sản phẩm
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    thumbnail VARCHAR(255) NOT NULL,
    description LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- 4. Bảng Nhiều ảnh sản phẩm (Upload Gallery)
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 5. Bảng Đơn hàng
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status TINYINT DEFAULT 0 COMMENT '0: Chờ xử lý, 1: Đang giao, 2: Hoàn thành, 3: Đã hủy',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 6. Bảng Chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tạo sẵn tài khoản Admin mặc định (Username: admin / Password: password123)
INSERT INTO users (fullname, username, password, email, role, status) 
VALUES ('Quản Trị Viên', 'admin', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRps.9cGLcZEiGDMVr5yUP1KUOYTa', 'admin@toanbangau.com', 1, 1)
ON DUPLICATE KEY UPDATE id=id;

-- ==========================================================
-- DỮ LIỆU BAN ĐẦU
-- ==========================================================

-- 1. DANH MỤC SẢN PHẨM MẪU
-- ==========================================================
INSERT INTO categories (id, name, status) VALUES
(1, 'Gấu Teddy', 1),
(2, 'Thú Bông Hoạt Hình', 1),
(3, 'Gối Ôm Trái Cây', 1),
(4, 'Thú Bông', 1),
(5, 'Đồ Ăn & Thức Uống', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), status = VALUES(status);

-- ==========================================================
-- 2. TRIGGERS
-- ==========================================================
DELIMITER $$

DROP TRIGGER IF EXISTS trg_check_product_price_insert$$
CREATE TRIGGER trg_check_product_price_insert
BEFORE INSERT ON products
FOR EACH ROW
BEGIN
    IF NEW.price < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Lỗi: Giá sản phẩm không được âm!';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_after_insert_order_details$$
CREATE TRIGGER trg_after_insert_order_details
AFTER INSERT ON order_details
FOR EACH ROW
BEGIN
    UPDATE orders
    SET total_price = total_price + (NEW.quantity * NEW.price)
    WHERE id = NEW.order_id;
END$$

DROP TRIGGER IF EXISTS trg_after_delete_order_details$$
CREATE TRIGGER trg_after_delete_order_details
AFTER DELETE ON order_details
FOR EACH ROW
BEGIN
    UPDATE orders
    SET total_price = total_price - (OLD.quantity * OLD.price)
    WHERE id = OLD.order_id;
END$$

DELIMITER ;

-- ==========================================================
-- 3. SẢN PHẨM VÀ ẢNH CHI TIẾT
-- ==========================================================

INSERT INTO products (id, category_id, name, price, thumbnail, description) VALUES
(1, 1, 'Gấu Teddy Áo Len', 550000.00, 'assets/uploads/products/teddy1.jpg', 'Gấu bông Teddy size 1m6 chất liệu lông xoắn cao cấp, nhồi bông PP 3D đàn hồi mềm mịn, tặng kèm nơ ôm tim lãng mạn.'),
(2, 1, 'Gấu Teddy Classic', 280000.00, 'assets/uploads/products/teddy2.jpg', 'Gấu bông dáng xinh, sợi lông xù mềm không rụng, phù hợp làm quà tặng sinh nhật.'),
(3, 2, 'Gấu Bông Capybara Đeo Balo Rùa 40cm', 195000.00, 'assets/uploads/products/cabybara1.jpg', 'Bộ trưởng ngoại giao giới động vật Capybara với tạo hình đeo balo rùa xanh siêu đáng yêu, chất vải nhung co giãn 4 chiều.'),
(4, 2, 'Gấu Dâu Lotso Thơm Hương Dâu 50cm', 245000.00, 'assets/uploads/products/gaudau.jpg', 'Gấu bông nhân vật Lotso màu hồng dâu đặc trưng, tích hợp túi hạt lưu hương dâu tây ngọt ngào.'),
(5, 3, 'Trái Dâu', 220000.00, 'assets/uploads/products/traidau.jpg', 'Gấu bông hình quả dâu tây đỏ nhồi bông êm ái, thích hợp làm gối ôm, tựa lưng hoặc trang trí phòng ngủ.'),
(6, 3, 'Trái Bơ', 220000.00, 'assets/uploads/products/traibo.jpg', 'Gấu bông hình quả bơ xanh mềm mại, chất liệu co giãn êm ái, thích hợp làm gối ôm hoặc quà tặng sinh nhật.'),
(7, 4, 'Thỏ Bông Tai Dài StellaLou', 230000.00, 'assets/uploads/products/tho.jpg', 'Thỏ bông dáng đứng tai dài mềm mịn, chất liệu bông xoắn 3 chiều an toàn cho da trẻ nhỏ.'),
(8, 4, 'Thỏ Bông Đeo Tai Nghe Đáng Yêu', 260000.00, 'assets/uploads/products/tho1.jpg', 'Thỏ nhồi bông mặc váy xòe vintage dễ thương, lông thỏ nhân tạo siêu mượt thích hợp làm quà tặng.'),
(9, 2, 'Chó Bông Trắng', 210000.00, 'assets/uploads/products/chobong.jpg', 'Gấu bông hình chú chó Shiba mắt híp siêu đáng yêu, chất vải thun co giãn 4 chiều mềm mịn.'),
(10, 3, 'Dưa Hấu Mơ Mộng', 190000.00, 'assets/uploads/products/Sản phẩm 6 - Dưa Hấu Mơ Mộng.jpg', 'Gấu bông quả dưa hấu đỏ ngọt ngào, chất liệu nhung mềm mịn êm ái.'),
(11, 2, 'Nhân Viên McDonald''s', 250000.00, 'assets/uploads/products/Sản phẩm 7 - Nhân Viên McDonald''s.jpg', 'Gấu bông phong cách nhân viên phục vụ thức ăn nhanh độc đáo, thiết kế cá tính.'),
(12, 5, 'Bánh Mì Croissant', 170000.00, 'assets/uploads/products/Sản phẩm 8 - Bánh Mì Croissant.jpg', 'Gối ôm hình bánh sừng bò vàng ươm, mềm xốp, thích hợp tựa lưng làm việc.'),
(13, 5, 'Ly Nước Mùa Hè', 180000.00, 'assets/uploads/products/Sản phẩm 9 - Ly Nước Mùa Hè.jpg', 'Gấu bông hình ly nước giải khát mùa hè sảng khoái, màu sắc tươi tắn.'),
(14, 5, 'Trứng Ốp La Ngốc Nghếch', 160000.00, 'assets/uploads/products/Sản phẩm 10 - Trứng Ốp La Ngốc Nghếch.jpg', 'Gối ôm hình trứng ốp la lòng đỏ biểu cảm ngộ nghĩnh, vải thun co giãn 4 chiều.'),
(15, 4, 'Cừu Bông Mây Trắng', 210000.00, 'assets/uploads/products/Sản phẩm 11 - Cừu Bông Mây Trắng.jpg', 'Chú cừu lông xù trắng muốt êm ái như đám mây, an toàn cho trẻ nhỏ.'),
(16, 5, 'Cốc Nước Sao Băng', 185000.00, 'assets/uploads/products/Sản phẩm 12 - Cốc Nước Sao Băng.jpg', 'Gấu bông ly nước đính kèm họa tiết sao băng lấp lánh, đáng yêu.'),
(17, 5, 'Bé Cà Phê Macchiato', 195000.00, 'assets/uploads/products/Sản phẩm 13 - Bé Cà Phê Macchiato.jpg', 'Tạo hình ly cà phê bọt kem ngọt ngào, món quà xinh xắn cho bạn bè.'),
(18, 5, 'Bánh Tam Giác Phô Mai', 175000.00, 'assets/uploads/products/Sản phẩm 14 - Bánh Tam Giác Phô Mai.jpg', 'Gối tựa lưng miếng bánh sandwich phô mai tam giác mềm mại, ấm áp.'),
(19, 5, 'Trứng Nướng Tròn Xoe', 165000.00, 'assets/uploads/products/Sản phẩm 15 - Trứng Nướng Tròn Xoay.jpg', 'Thú bông bánh trứng tròn trịa, vải nhung mịn không xơ rụng.'),
(20, 2, 'Quả Cầu Lông', 150000.00, 'assets/uploads/products/Sản phẩm 16 - Quả Cầu Lông.jpg', 'Thú bông hình quả cầu lông thể thao độc lạ, món quà năng động vui nhộn.'),
(21, 2, 'Gấu Ong Mật Béo', 240000.00, 'assets/uploads/products/Sản phẩm 17 - Gấu Ong Mật Béo.jpg', 'Gấu bông tròn ú hóa trang chú ong vàng chăm chỉ, nhồi bông PP 3D căng tròn.'),
(22, 2, 'Cục Bông 4 Màu', 155000.00, 'assets/uploads/products/Sản phẩm 18 - Cục Bông 4 Màu.jpg', 'Cục bông tròn mềm mại kết hợp 4 gam màu pastel dịu mắt.'),
(23, 2, 'Gấu Bông Ăn Lá', 230000.00, 'assets/uploads/products/Sản phẩm 19 - Gấu Bông Ăn Lá.jpg', 'Chú gấu bông cầm nhánh lá xanh xinh xắn, chất vải mịn màng đàn hồi tốt.'),
(24, 4, 'Vịt Vàng Tỏa Nắng', 190000.00, 'assets/uploads/products/Sản phẩm 20 - Vịt Vàng Tỏa Nắng.jpg', 'Vịt bông vàng ươm với nụ cười rạng rỡ, thích hợp làm gối ôm trang trí.'),
(25, 3, 'Cà Chua Tròn Xoe', 160000.00, 'assets/uploads/products/Sản phẩm 21 - Cà Chua Tròn Xoe.jpg', 'Gối bông hình quả cà chua đỏ mọng tròn xoe siêu cưng.'),
(26, 4, 'Cộng Sự Cáo Cam', 225000.00, 'assets/uploads/products/Sản phẩm 22 - Cộng Sự Cáo Cam.jpg', 'Bé cáo lông cam thông minh lanh lợi, dáng ngồi đáng yêu.'),
(27, 1, 'Gấu Nhỏ Mang Nơ', 215000.00, 'assets/uploads/products/Sản phẩm 23 - Gấu Nhỏ Mang Nơ.jpg', 'Gấu teddy size nhỏ đeo nơ cổ thanh lịch, chất lông mềm cao cấp.'),
(28, 4, 'Bé Voi Quần Bông', 235000.00, 'assets/uploads/products/Sản phẩm 24 - Bé Voi Quần Bông.jpg', 'Chú voi con mặc quần yếm bông phồng siêu ngộ nghĩnh.'),
(29, 4, 'Rùa Xanh', 180000.00, 'assets/uploads/products/Sản phẩm 25 - Rùa Xanh.jpg', 'Rùa bông mai xanh tròn trịa, chất liệu mềm mại thích hợp kê đầu ngủ.')
ON DUPLICATE KEY UPDATE 
    category_id = VALUES(category_id),
    name        = VALUES(name),
    thumbnail   = VALUES(thumbnail),
    price       = VALUES(price),
    description = VALUES(description);

-- Thư viện ảnh chi tiết (product_images)
DELETE FROM product_images WHERE product_id BETWEEN 1 AND 29;
INSERT INTO product_images (product_id, image_url) VALUES
(1, 'assets/uploads/products/teddy1.jpg'),
(2, 'assets/uploads/products/teddy2.jpg'),
(3, 'assets/uploads/products/cabybara1.jpg'),
(4, 'assets/uploads/products/gaudau.jpg'),
(5, 'assets/uploads/products/traidau.jpg'),
(6, 'assets/uploads/products/traibo.jpg'),
(7, 'assets/uploads/products/tho.jpg'),
(8, 'assets/uploads/products/tho1.jpg'),
(9, 'assets/uploads/products/chobong.jpg'),
(10, 'assets/uploads/products/Sản phẩm 6 - Dưa Hấu Mơ Mộng.jpg'),
(11, 'assets/uploads/products/Sản phẩm 7 - Nhân Viên McDonald''s.jpg'),
(12, 'assets/uploads/products/Sản phẩm 8 - Bánh Mì Croissant.jpg'),
(13, 'assets/uploads/products/Sản phẩm 9 - Ly Nước Mùa Hè.jpg'),
(14, 'assets/uploads/products/Sản phẩm 10 - Trứng Ốp La Ngốc ....jpg'),
(15, 'assets/uploads/products/Sản phẩm 11 - Cừu Bông Mây Trắng.jpg'),
(16, 'assets/uploads/products/Sản phẩm 12 - Cốc Nước Sao Băng.jpg'),
(17, 'assets/uploads/products/Sản phẩm 13 - Bé Cà Phê Macchiato.jpg'),
(18, 'assets/uploads/products/Sản phẩm 14 - Bánh Tam Giác Phô ....jpg'),
(19, 'assets/uploads/products/Sản phẩm 15 - Trứng Nướng Tròn ....jpg'),
(20, 'assets/uploads/products/Sản phẩm 16 - Quả Cầu Lông.jpg'),
(21, 'assets/uploads/products/Sản phẩm 17 - Gấu Ong Mật Béo.jpg'),
(22, 'assets/uploads/products/Sản phẩm 18 - Cục Bông 4 Màu.jpg'),
(23, 'assets/uploads/products/Sản phẩm 19 - Gấu Bông Ăn Lá.jpg'),
(24, 'assets/uploads/products/Sản phẩm 20 - Vịt Vàng Tỏa Nắng.jpg'),
(25, 'assets/uploads/products/Sản phẩm 21 - Cà Chua Tròn Xoe.jpg'),
(26, 'assets/uploads/products/Sản phẩm 22 - Cộng Sự Cáo Cam.jpg'),
(27, 'assets/uploads/products/Sản phẩm 23 - Gấu Nhỏ Mang Nơ.jpg'),
(28, 'assets/uploads/products/Sản phẩm 24 - Bé Voi Quần Bông.jpg'),
(29, 'assets/uploads/products/Sản phẩm 25 - Rùa Xanh.jpg');