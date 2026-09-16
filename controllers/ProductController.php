<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';

$database = new Database();
$db = $database->getConnection();

if (class_exists('Product')) {
    $productModel = new Product($db);
} else {
    die("Fatal Error: Không tìm thấy Class Product!");
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = $_POST['name'] ?? '';
            $price       = $_POST['price'] ?? 0;
            $description = $_POST['description'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            $image       = $_POST['image'] ?? '';

            if (method_exists($productModel, 'create') && $productModel->create($name, $price, $description, $category_id, $image)) {
                $_SESSION['success'] = "Thêm sản phẩm thành công!";
            } else {
                $_SESSION['error'] = "Thêm sản phẩm thất bại!";
            }
        }
        header('Location: ../admin/product.php');
        exit();

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = (int)($_POST['id'] ?? 0);
            $name        = $_POST['name'] ?? '';
            $price       = $_POST['price'] ?? 0;
            $description = $_POST['description'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            $image       = $_POST['image'] ?? '';

            if ($id > 0 && method_exists($productModel, 'update') && $productModel->update($id, $name, $price, $description, $category_id, $image)) {
                $_SESSION['success'] = "Cập nhật sản phẩm #$id thành công!";
            } else {
                $_SESSION['error'] = "Cập nhật thất bại!";
            }
        }
        header('Location: ../admin/product.php');
        exit();

    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            if (method_exists($productModel, 'delete') && $productModel->delete($id)) {
                $_SESSION['success'] = "Đã xóa sản phẩm #$id!";
            } else {
                $_SESSION['error'] = "Không thể xóa sản phẩm (đang dính đơn hàng)!";
            }
        }
        header('Location: ../admin/product.php');
        exit();

    default:
        header('Location: ../admin/product.php');
        exit();
}
