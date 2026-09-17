<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../includes/product_html.php';

$database = new Database();
$db = $database->getConnection();

if (class_exists('Product')) {
    $productModel = new Product($db);
} else {
    die("Fatal Error: Không tìm thấy Class Product!");
}

if ((int)($_SESSION['role'] ?? 0) !== 1) {
    http_response_code(403);
    exit('Không có quyền truy cập.');
}

function uploadProductPhoto($file, &$uploadedFiles) {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Không tải được ảnh lên.');
    if ($file['size'] > 5 * 1024 * 1024) throw new RuntimeException('Mỗi ảnh phải nhỏ hơn 5 MB.');
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$mime])) throw new RuntimeException('Định dạng ảnh không hợp lệ.');
    $directory = __DIR__ . '/../assets/uploads/products/';
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('Không tạo được thư mục ảnh.');
    $path = 'assets/uploads/products/' . bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $path)) throw new RuntimeException('Không lưu được ảnh.');
    $uploadedFiles[] = $path;
    return $path;
}

function localProductPhoto($value) {
    if (!is_string($value) || $value === '') return null;
    $prefix = 'assets/uploads/products/';
    $name = str_starts_with($value, $prefix) ? substr($value, strlen($prefix)) : $value;
    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.(?:jpe?g|png|webp|gif)$/i', $name)) return null;
    return __DIR__ . '/../assets/uploads/products/' . $name;
}

function saveProductPhotos($productModel, $id, &$uploadedFiles) {
    if (!isset($_FILES['images']['name']) || !is_array($_FILES['images']['name'])) return;
    foreach ($_FILES['images']['name'] as $index => $unused) {
        $file = [];
        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $key) $file[$key] = $_FILES['images'][$key][$index] ?? null;
        $path = uploadProductPhoto($file, $uploadedFiles);
        if ($path) $productModel->insertProductImage($id, $path);
    }
}

function saveProduct($productModel, $db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        throw new RuntimeException('Yêu cầu không hợp lệ.');
    }
    $id = (int)($_POST['id'] ?? 0);
    $nameInput = $_POST['name'] ?? '';
    $name = is_string($nameInput) ? trim($nameInput) : '';
    $category = (int)($_POST['category_id'] ?? 0);
    $priceInput = $_POST['price'] ?? '';
    $quantityInput = $_POST['quantity'] ?? '';
    if ($name === '' || $category < 1 || !is_numeric($priceInput) || (float)$priceInput < 0 ||
        filter_var($quantityInput, FILTER_VALIDATE_INT) === false || (int)$quantityInput < 0) {
        throw new RuntimeException('Thông tin sản phẩm không hợp lệ.');
    }
    $price = (float)$priceInput;
    $quantity = (int)$quantityInput;
    if (mb_strlen($name, 'UTF-8') > 255) {
        throw new RuntimeException('Tên sản phẩm tối đa 255 ký tự.');
    }
    if ($price > 99999999.99 || $quantity > 2147483647) {
        throw new RuntimeException('Giá hoặc số lượng vượt giới hạn cho phép.');
    }
    $categoryStmt = $db->prepare('SELECT id FROM categories WHERE id = :id AND status = 1');
    $categoryStmt->execute([':id' => $category]);
    if (!$categoryStmt->fetchColumn()) throw new RuntimeException('Danh mục không hợp lệ.');
    $description = cleanProductHtml($_POST['description'] ?? '');
    $uploadedFiles = [];
    $oldThumbnail = null;
    try {
        $thumbnail = uploadProductPhoto($_FILES['image'] ?? [], $uploadedFiles);
        if (!$id && !$thumbnail) throw new RuntimeException('Vui lòng chọn ảnh đại diện.');
        $db->beginTransaction();
        if ($id) {
            $existing = $productModel->getProductById($id);
            if (!$existing) throw new RuntimeException('Không tìm thấy sản phẩm.');
            $oldThumbnail = $existing['thumbnail'] ?? null;
            $productModel->update($id, $name, $category, $price, $description, $thumbnail, $quantity);
        } else {
            $id = $productModel->create($name, $category, $price, $description, $thumbnail, $quantity);
            if (!$id) throw new RuntimeException('Không lưu được sản phẩm.');
        }
        saveProductPhotos($productModel, $id, $uploadedFiles);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        foreach ($uploadedFiles as $path) @unlink(__DIR__ . '/../' . $path);
        throw $e;
    }
    $oldFile = localProductPhoto($oldThumbnail);
    if ($thumbnail && $oldFile) {
        @unlink($oldFile);
    }
    $_SESSION['success'] = 'Đã lưu sản phẩm.';
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'store':
    case 'create':
    case 'update':
        try { saveProduct($productModel, $db); }
        catch (Throwable $e) { $_SESSION['error'] = $e->getMessage(); }
        header('Location: ../admin/products.php');
        exit();

    case 'gallery_upload':
        $id = (int)($_POST['id'] ?? 0);
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
                !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
                throw new RuntimeException('Yêu cầu không hợp lệ.');
            }
            if ($id < 1 || !$productModel->getProductById($id)) {
                throw new RuntimeException('Không tìm thấy sản phẩm.');
            }
            $names = $_FILES['images']['name'] ?? [];
            if (!is_array($names) || !array_filter($names)) {
                throw new RuntimeException('Vui lòng chọn ít nhất một ảnh.');
            }
            if (count($names) > 20) throw new RuntimeException('Mỗi lần tải tối đa 20 ảnh.');
            $uploadedFiles = [];
            try {
                $db->beginTransaction();
                saveProductPhotos($productModel, $id, $uploadedFiles);
                $db->commit();
            } catch (Throwable $e) {
                if ($db->inTransaction()) $db->rollBack();
                foreach ($uploadedFiles as $path) @unlink(__DIR__ . '/../' . $path);
                throw $e;
            }
            $_SESSION['success'] = 'Đã thêm ảnh vào gallery.';
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ../admin/product-images.php?id=' . $id);
        exit();

    case 'gallery_delete':
        $id = (int)($_POST['id'] ?? 0);
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
                !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
                throw new RuntimeException('Yêu cầu không hợp lệ.');
            }
            $imageId = (int)($_POST['image_id'] ?? 0);
            $image = $productModel->getProductImageById($imageId, $id);
            if (!$image) throw new RuntimeException('Không tìm thấy ảnh phụ của sản phẩm.');
            $db->beginTransaction();
            $productModel->deleteProductImage($imageId, $id);
            $db->commit();
            $file = localProductPhoto($image['image_url']);
            if ($file) @unlink($file);
            $_SESSION['success'] = 'Đã xóa ảnh phụ.';
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ../admin/product-images.php?id=' . $id);
        exit();

    case 'gallery_move':
        $id = (int)($_POST['id'] ?? 0);
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
                !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
                throw new RuntimeException('Yêu cầu không hợp lệ.');
            }
            $imageId = (int)($_POST['image_id'] ?? 0);
            $direction = $_POST['direction'] ?? '';
            if (!in_array($direction, ['up', 'down'], true)) {
                throw new RuntimeException('Hướng di chuyển không hợp lệ.');
            }
            $images = $productModel->getProductImages($id);
            $currentIndex = null;
            foreach ($images as $index => $image) {
                if ((int)$image['id'] === $imageId) {
                    $currentIndex = $index;
                    break;
                }
            }
            if ($currentIndex === null) throw new RuntimeException('Không tìm thấy ảnh phụ.');
            $targetIndex = $currentIndex + ($direction === 'up' ? -1 : 1);
            if (!isset($images[$targetIndex])) throw new RuntimeException('Ảnh đã ở vị trí đầu hoặc cuối.');
            $current = $images[$currentIndex];
            $target = $images[$targetIndex];
            $db->beginTransaction();
            if (!$productModel->updateProductImageUrl($current['id'], $id, $target['image_url']) ||
                !$productModel->updateProductImageUrl($target['id'], $id, $current['image_url'])) {
                throw new RuntimeException('Không đổi được thứ tự ảnh.');
            }
            $db->commit();
            $_SESSION['success'] = 'Đã đổi thứ tự ảnh.';
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ../admin/product-images.php?id=' . $id);
        exit();

    case 'gallery_cover':
        $id = (int)($_POST['id'] ?? 0);
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
                !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
                throw new RuntimeException('Yêu cầu không hợp lệ.');
            }
            $imageId = (int)($_POST['image_id'] ?? 0);
            $product = $productModel->getProductById($id);
            $image = $productModel->getProductImageById($imageId, $id);
            if (!$product || !$image) throw new RuntimeException('Không tìm thấy sản phẩm hoặc ảnh phụ.');
            $oldCover = $product['thumbnail'] ?? '';
            $newCover = $image['image_url'];
            if ($oldCover === '' || $oldCover === $newCover) {
                throw new RuntimeException('Không thể đổi ảnh đại diện này.');
            }
            $db->beginTransaction();
            if (!$productModel->updateProductThumbnail($id, $newCover) ||
                !$productModel->updateProductImageUrl($imageId, $id, $oldCover)) {
                throw new RuntimeException('Không cập nhật được ảnh đại diện.');
            }
            $db->commit();
            $_SESSION['success'] = 'Đã đặt ảnh đại diện mới. Ảnh cũ được giữ trong gallery.';
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ../admin/product-images.php?id=' . $id);
        exit();

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $product = $productModel->getProductById($id);
                if (!$product) throw new RuntimeException('Không tìm thấy sản phẩm.');
                $images = $productModel->getProductImages($id);
                $db->beginTransaction();
                if (!$productModel->delete($id)) throw new RuntimeException('Không thể xóa sản phẩm.');
                $db->commit();
                foreach (array_merge([$product['thumbnail']], array_column($images, 'image_url')) as $path) {
                    $file = localProductPhoto($path);
                    if ($file) @unlink($file);
                }
                $_SESSION['success'] = "Đã xóa sản phẩm #$id!";
            } catch (Throwable $e) {
                if ($db->inTransaction()) $db->rollBack();
                $_SESSION['error'] = 'Không thể xóa sản phẩm (có thể đang thuộc đơn hàng).';
            }
        }
        header('Location: ../admin/products.php');
        exit();

    default:
        header('Location: ../admin/products.php');
        exit();
}
