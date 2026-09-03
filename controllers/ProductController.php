<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
$products = $this->productModel->getAll();
    
    // Bổ sung: Lấy danh sách danh mục để truyền sang view
    $categories = $this->categoryModel->getAll(); 

    require_once '../views/admin/products.php';
$database = new Database();
$db = $database->getConnection();
$productModel = new Product($db);

$action = $_GET['action'] ?? '';

// 1. Thêm mới sản phẩm
if (in_array($action, ['store', 'add']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $price       = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    $upload_dir = __DIR__ . '/../assets/uploads/products/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Nhận ảnh đại diện từ form
    $thumbnail_name = '';
    $file = $_FILES['image'] ?? ($_FILES['thumbnail'] ?? null);
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $thumbnail_name = time() . '_thumb_' . uniqid() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $upload_dir . $thumbnail_name);
    }

    // Gọi đúng 5 tham số (không còn quantity)
    $product_id = $productModel->create($name, $category_id, $price, $description, $thumbnail_name);

    // Lưu bộ ảnh phụ
    if ($product_id && isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $total_files = count($_FILES['images']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $sub_ext  = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                $sub_name = time() . '_gallery_' . $i . '_' . uniqid() . '.' . $sub_ext;
                
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_dir . $sub_name)) {
                    $productModel->insertProductImage($product_id, $sub_name);
                }
            }
        }
    }

    header("Location: ../admin/products.php");
    exit();
}

// 2. Cập nhật sản phẩm
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)$_POST['id'];
    $name        = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $price       = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    $upload_dir = __DIR__ . '/../assets/uploads/products/';
    $thumbnail_name = null;

    $file = $_FILES['image'] ?? ($_FILES['thumbnail'] ?? null);
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $thumbnail_name = time() . '_thumb_' . uniqid() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $upload_dir . $thumbnail_name);
    }

    $productModel->update($id, $name, $category_id, $price, $description, $thumbnail_name);

    header("Location: ../admin/products.php");
    exit();
}

// 3. Xóa sản phẩm
if ($action === 'delete' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $productModel->delete($id);
    header("Location: ../admin/products.php");
    exit();
}