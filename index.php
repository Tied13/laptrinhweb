<?php
include 'db.php';

// Kiểm tra xem có từ khóa tìm kiếm không
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    // Lọc sản phẩm theo tên nhập vào
    $sql = "SELECT * FROM san_pham WHERE ten_san_pham LIKE '%$search%' ORDER BY id DESC";
} else {
    // Nếu không tìm kiếm, lấy toàn bộ sản phẩm
    $sql = "SELECT * FROM san_pham ORDER BY id DESC";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gấu Bông Store - Cửa Hàng Gấu Bông Cao Cấp</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome hỗ trợ icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #110138;
            --secondary-color: #c1b4fd;
            --price-color: #d9534f;
            --bg-light: #f8f9fa;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', Arial, sans-serif;
        }

        body {
            background-color: #FFF5F7 !important;
            color: #4A5568;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        /* Header Top & Search Bar */
        header {
            background: #ffffff !important;
            padding: 15px 0;
            border-bottom: 2px solid #F3E8FF;
            box-shadow: 0 2px 10px rgba(255, 182, 193, 0.3);
        }

/* Nền phần danh mục & banner */
        .main-content, .container {
            background-color: transparent !important;
        }

        .sidebar, .product-section {
            background: #ffffff !important;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(255, 182, 193, 0.25);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #C084FC !important;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-box {
            display: flex;
            flex: 1;
            max-width: 500px;
            border: 2px solid #D8B4FE !important; 
            border-radius: 25px;                   
            overflow: hidden;                       
            background: #fff;
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            box-shadow: 0 0 8px rgba(140, 115, 85, 0.4);
        }

        .search-box input {
            width: 100%;
            padding: 10px 18px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .search-box button {
            padding: 10px 22px;
            background: #C084FC !important;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .search-box button:hover {
            background: #735d43;
        }

        .header-icons {
            display: flex;
            gap: 15px;
            font-size: 20px;
            color: var(--secondary-color);
        }

        /* Navigation Bar */
        nav {
            background: var(--secondary-color);
        }

        .nav-content {
            display: flex;
        }
        .navbar, nav {
            background-color: #E9D5FF !important; 
        }

        .nav-link, nav a {
            color: #6B21A8 !important; 
            font-weight: 600;
        }
        .category-btn {
            background: var(--primary-color);
            color: #fff;
            padding: 12px 20px;
            font-weight: bold;
            width: 250px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .main-menu {
            display: flex;
            list-style: none;
        }

        .main-menu a {
            color: #000000;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            font-size: 14px;
        }

        .main-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* 3. Hero Section (Sidebar + Banner) */
        .hero-section {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .sidebar {
            background: #FFFFFF !important;
            border: 1px solid #F3E8FF;
            box-shadow: 0 4px 15px rgba(216, 180, 254, 0.2) !important;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li a {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            color: #555;
            text-decoration: none;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .sidebar li a:hover {
            color: var(--primary-color);
            background: var(--bg-light);
        }

        .banner {
            flex: 1;
            background-image: url('images/4.jpg'); 
            background-size: cover;
            background-position: center;
            min-height: 350px;
            display: flex;
            align-items: center;
            padding: 40px;
            color: #fff;
            border-radius: 20px;
        }

        .banner-text {
            background: rgba(26, 30, 40, 0.65) !important;
            backdrop-filter: blur(5px) !important;
            padding: 30px 35px !important;
            border-radius: 15px !important;
            border: 1px solid rgba(186, 112, 222, 0.4) !important;
            max-width: 420px !important;
            box-shadow: 0 10px 30px rgba(137, 137, 137, 0.3) !important;
}

        .banner-text h2 {
            font-family: 'Montserrat', sans-serif !important;
            font-size: 32px !important;
            font-weight: 800 !important;
            color: #ffffff !important;
            letter-spacing: 1.5px !important;
            margin-bottom: 10px !important;
            text-transform: uppercase !important;
            text-align: center;
}

        .banner-text p {
            font-size: 18px !important;
            color: #e2e8f0 !important;
            margin-bottom: 10px !important;
            text-align: center;
}

        .banner-btn {
            display: inline-block !important;
            padding: 10px 24px !important;
            background: linear-gradient(135deg, #d4af37 0%, #aa7c11 100%) !important;
            color: #fff !important;
            text-decoration: none !important;
            font-weight: bold !important;
            font-size: 13px !important;
            border-radius: 25px !important;
            text-transform: uppercase !important;
}
        /* 4. Products Section */
        .products-section {
            background: #FFFFFF !important; 
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(244, 114, 182, 0.15) !important;
            border: 1px solid #FCE7F3;
            margin-top: 25px !important; 
        }

        .product-section h2 {
            color: #9333EA !important; 
            border-bottom: 2px solid #F3E8FF !important;
        }
        .section-title {
            font-size: 18px;
            color: #9333EA !important; 
            border-bottom: 2px solid #F3E8FF !important;
            padding-bottom: 8px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .product-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important;
            gap: 20px !important;
            width: 100% !important;
        }

      .product-card {
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            background: #FFFFFF !important;
            border: 1px solid #FCE7F3 !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 20px rgba(244, 114, 182, 0.2) !important;
            border-color: #F472B6 !important;
        }
        
        .product-card img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .badge-sale {
            position: absolute;
            top: 18px;
            right: 18px;
            background: linear-gradient(135deg, #FF85A1, #F472B6) !important;
            color: #e1bffc;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 20px;
            box-shadow: 0 2px 6px rgba(255, 65, 108, 0.4);
            z-index: 1;
        }

        .product-title {
            font-size: 15px;
            font-weight: 700;
            color: #020203;
            margin-bottom: 6px;
            line-height: 1.3;
            height: 38px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            color: #EC4899 !important;
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .product-desc {
            font-size: 12px;
            color: #8c98a4;
            height: 32px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .product-actions {
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px dashed #e9ecef;
            display: flex;
            gap: 8px;
        }

        .btn-action {
            flex: 1;
            padding: 6px 0;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .btn-edit {
            background-color: #fff8e6;
            color: #f39c12;
            border: 1px solid #ffe8b3;
        }

        .btn-edit:hover {
            background-color: #f39c12;
            color: #fff;
        }

        .btn-delete {
            background-color: #ffeef0;
            color: #e74c3c;
            border: 1px solid #ffccd1;
        }

        .btn-delete:hover {
            background-color: #e74c3c;
            color: #fff;
        }

        .btn-add-product, a[href="add.php"] {
            background: linear-gradient(135deg, #F472B6 0%, #C084FC 100%) !important; 
            border: none !important;
            box-shadow: 0 4px 10px rgba(244, 114, 182, 0.3) !important;
}

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .header-content {
                flex-direction: column;
            }
            .search-box {
                width: 100%;
            }
            .main-menu {
                display: none; 
            }
            .category-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Header Top -->
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-store"></i> Gấu Bông Store
            </a>
                <form class="search-box" action="index.php" method="GET">
                    <input type="text" name="search" placeholder="Nhập tên sản phẩm cần tìm..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
                </form>
            <div class="header-icons">
    <!-- Nút thêm sản phẩm mới -->
    <a href="add.php" style="background: #28a745; color: #fff; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; display: flex; align-items: center; gap: 5px;">
        <i class="fa-solid fa-plus"></i> Thêm sản phẩm
    </a>

    <a href="#" style="color: inherit;"><i class="fa-solid fa-cart-shopping"></i></a>
    <a href="#" style="color: inherit;"><i class="fa-solid fa-user"></i></a>
</div>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <div class="container nav-content">
            <div class="category-btn">
                <i class="fa-solid fa-bars"></i> DANH MỤC
            </div>
            <ul class="main-menu">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="#">Sản phẩm</a></li>
                <li><a href="#">Tin tức</a></li>
                <li><a href="#">Giới thiệu</a></li>
                <li><a href="#">Liên hệ</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <aside class="sidebar">
                <ul>
                    <li><a href="#">Tất cả sản phẩm <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="#">Gấu Bông Teddy <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="#">Gấu Bông Capybara <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="#">Thỏ Bông <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="#">Phụ kiện gấu bông <i class="fa-solid fa-chevron-right"></i></a></li>
                    <li><a href="#">Sản phẩm khuyến mãi <i class="fa-solid fa-chevron-right"></i></a></li>
                </ul>
            </aside>
            <div class="banner">
                <div class="banner-text">
                    <h2>ƯU ĐÃI LỚN!</h2>
                    <p>Giảm giá tới 50% cho các dòng gấu bông cao cấp</p>
                </div>
            </div>
        </div>

        <!-- Products List -->
        <section class="products-section">
            <h3 class="section-title">Sản Phẩm Khuyến Mãi</h3>
            <div class="product-grid">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="product-card">
                <span class="badge-sale">Sale 20%</span>
                <img src="images/<?php echo $row['hinh_anh']; ?>" alt="<?php echo $row['ten_san_pham']; ?>">
                
                <div>
                    <div class="product-title"><?php echo $row['ten_san_pham']; ?></div>
                    <div class="product-price"><?php echo number_format($row['gia']); ?> VNĐ</div>
                    <div class="product-desc"><?php echo $row['mo_ta']; ?></div>
                </div>
                
                <div class="product-actions">
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit">
                        <i class="fa-solid fa-pen-to-square"></i> Sửa
                    </a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')" class="btn-action btn-delete">
                        <i class="fa-solid fa-trash"></i> Xóa
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Chưa có sản phẩm nào trong cửa hàng!</p>
    <?php endif; ?>
</div>

        </div>
    </section>
</div>

</body>
</html>
