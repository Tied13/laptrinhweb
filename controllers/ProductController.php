<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';

$database = new Database();
$db = $database->getConnection();
$productModel = new Product($db);

$action = $_GET['action'] ?? '';

// 1. XỬ LÝ CẬP NHẬT SẢN PHẨM (action=update)
if ($action === 'update') {
    $id          = $_POST['id'] ?? 0;
    $name        = $_POST['name'] ?? '';
    $price       = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $image       = $_POST['image'] ?? '';

    try {
        if ($productModel->update($id, $name, $price, $description, $category_id, $image)) {
            $_SESSION['success'] = "Cập nhật sản phẩm thành công!";
        } else {
            $_SESSION['error'] = "Cập nhật sản phẩm thất bại!";
        }
    } catch (PDOException $e) {
        // Bắt lỗi khóa ngoại (Foreign Key 1452) khi chọn ID danh mục không tồn tại
        $_SESSION['error'] = "Lỗi SQL: ID danh mục ($category_id) không tồn tại trong bảng categories!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
    }

    header('Location: ../admin/products.php');
    exit();
}

// 2. XỬ LÝ XÓA SẢN PHẨM (action=delete)
if ($action === 'delete') {
    $id = $_GET['id'] ?? 0;

    try {
        if ($productModel->delete($id)) {
            $_SESSION['success'] = "Xóa sản phẩm thành công!";
        } else {
            $_SESSION['error'] = "Xóa sản phẩm thất bại!";
        }
    } catch (PDOException $e) {
        // Bắt lỗi khi xóa sản phẩm đã có trong đơn hàng (Foreign Key constraint)
        $_SESSION['error'] = "Không thể xóa! Sản phẩm này đã tồn tại trong đơn hàng của khách.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
    }

    header('Location: ../admin/products.php');
    exit();
}

// Mặc định nếu không đúng action thì quay về danh sách sản phẩm
header('Location: ../admin/products.php');
exit();
