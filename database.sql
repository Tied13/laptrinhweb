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

-- ==========================================================
-- DỮ LIỆU BAN ĐẦU
-- ==========================================================

-- 1. DANH MỤC SẢN PHẨM MẪU
-- ==========================================================
INSERT INTO categories (id, name, status) VALUES
(1, 'Gấu Teddy', 1),
(2, 'Thú Bông Hoạt Hình', 1),
(3, 'Gối Ôm Trái Cây', 1),
(4, 'Thú Bông', 1)
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

-- Thêm Sản phẩm mẫu (Bao gồm 3 sản phẩm mới: tho.jpg, tho1.jpg, chobong.jpg)
INSERT INTO products (id, category_id, name, price, thumbnail, description) VALUES
(1, 1, 'Gấu Teddy Áo Len', 550000.00, 'assets/uploads/products/teddy1.jpg', 'Gấu bông Teddy size 1m6 chất liệu lông xoắn cao cấp, nhồi bông PP 3D đàn hồi mềm mịn, tặng kèm nơ ôm tim lãng mạn.'),
(2, 1, 'Gấu Teddy Classic', 280000.00, 'assets/uploads/products/teddy2.jpg', 'Gấu bông dáng xinh, sợi lông xù mềm không rụng, phù hợp làm quà tặng sinh nhật.'),
(3, 2, 'Gấu Bông Capybara Đeo Balo Rùa 40cm', 195000.00, 'assets/uploads/products/cabybara1.jpg', 'Bộ trưởng ngoại giao giới động vật Capybara với tạo hình đeo balo rùa xanh siêu đáng yêu, chất vải nhung co giãn 4 chiều.'),
(4, 2, 'Gấu Dâu Lotso Thơm Hương Dâu 50cm', 245000.00, 'assets/uploads/products/gaudau.jpg', 'Gấu bông nhân vật Lotso màu hồng dâu đặc trưng, tích hợp túi hạt lưu hương dâu tây ngọt ngào.'),
(5, 3, 'Trái Dâu', 220000.00, 'assets/uploads/products/traidau.jpg', 'Gấu bông hình quả dâu tây đỏ nhồi bông êm ái, thích hợp làm gối ôm, tựa lưng hoặc trang trí phòng ngủ.'),
(6, 3, 'Trái Bơ', 220000.00, 'assets/uploads/products/traibo.jpg', 'Gấu bông hình quả bơ xanh mềm mại, chất liệu co giãn êm ái, thích hợp làm gối ôm hoặc quà tặng sinh nhật.'),
(7, 4, 'Thỏ Bông Tai Dài StellaLou', 230000.00, 'assets/uploads/products/tho.jpg', 'Thỏ bông dáng đứng tai dài mềm mịn, chất liệu bông xoắn 3 chiều an toàn cho da trẻ nhỏ.'),
(8, 4, 'Thỏ Bông Đeo Tai Nghe Đáng Yêu', 260000.00, 'assets/uploads/products/tho1.jpg', 'Thỏ nhồi bông mặc váy xòe vintage dễ thương, lông thỏ nhân tạo siêu mượt thích hợp làm quà tặng.'),
(9, 2, 'Chó Bông Trắng', 210000.00, 'assets/uploads/products/chobong.jpg', 'Gấu bông hình chú chó Shiba mắt híp siêu đáng yêu, chất vải thun co giãn 4 chiều mềm mịn.')
ON DUPLICATE KEY UPDATE 
    category_id = VALUES(category_id),
    name = VALUES(name),
    thumbnail = VALUES(thumbnail),
    price = VALUES(price),
    description = VALUES(description);

-- Thêm Thư viện ảnh chi tiết (product_images)
DELETE FROM product_images WHERE product_id BETWEEN 1 AND 9;
INSERT INTO product_images (product_id, image_url) VALUES
(1, 'assets/uploads/products/teddy1.jpg'),
(2, 'assets/uploads/products/teddy2.jpg'),
(3, 'assets/uploads/products/cabybara1.jpg'),
(4, 'assets/uploads/products/gaudau.jpg'),
(5, 'assets/uploads/products/traidau.jpg'),
(6, 'assets/uploads/products/traibo.jpg'),
(7, 'assets/uploads/products/tho.jpg'),
(8, 'assets/uploads/products/tho1.jpg'),
(9, 'assets/uploads/products/chobong.jpg');