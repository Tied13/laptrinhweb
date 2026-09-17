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

if ((int)($_SESSION['role'] ?? 0) !== 1) {
    http_response_code(403);
    exit('Không có quyền truy cập.');
}

function uploadProductPhoto($file) {
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
    return $path;
}

function saveProductPhotos($productModel, $id) {
    if (!isset($_FILES['images']['name']) || !is_array($_FILES['images']['name'])) return;
    foreach ($_FILES['images']['name'] as $index => $unused) {
        $file = [];
        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $key) $file[$key] = $_FILES['images'][$key][$index] ?? null;
        $path = uploadProductPhoto($file);
        if ($path) $productModel->insertProductImage($id, $path);
    }
}

function cleanProductHtml($html) {
    $doc = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="content">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $root = $doc->getElementById('content');
    if (!$root) return '';
    $allowed = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'a', 'img'];
    $clean = function ($node) use (&$clean, $allowed) {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (!$child instanceof DOMElement) continue;
            $tag = strtolower($child->tagName);
            if (!in_array($tag, $allowed, true)) { $child->parentNode->removeChild($child); continue; }
            foreach (iterator_to_array($child->attributes) as $attribute) {
                $key = strtolower($attribute->name);
                $value = trim($attribute->value);
                $safeUrl = preg_match('~^(https?://|assets/uploads/products/)~i', $value);
                if (!(($tag === 'a' && $key === 'href' && $safeUrl) || ($tag === 'img' && $key === 'src' && $safeUrl))) {
                    $child->removeAttributeNode($attribute);
                }
            }
            $clean($child);
        }
    };
    $clean($root);
    $output = '';
    foreach ($root->childNodes as $node) $output .= $doc->saveHTML($node);
    return $output;
}

function saveProduct($productModel, $db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        throw new RuntimeException('Yêu cầu không hợp lệ.');
    }
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = (int)($_POST['category_id'] ?? 0);
    $priceInput = $_POST['price'] ?? '';
    $quantityInput = $_POST['quantity'] ?? '';
    if ($name === '' || $category < 1 || !is_numeric($priceInput) || (float)$priceInput < 0 ||
        filter_var($quantityInput, FILTER_VALIDATE_INT) === false || (int)$quantityInput < 0) {
        throw new RuntimeException('Thông tin sản phẩm không hợp lệ.');
    }
    $price = (float)$priceInput;
    $quantity = (int)$quantityInput;
    $categoryStmt = $db->prepare('SELECT id FROM categories WHERE id = :id AND status = 1');
    $categoryStmt->execute([':id' => $category]);
    if (!$categoryStmt->fetchColumn()) throw new RuntimeException('Danh mục không hợp lệ.');
    $description = cleanProductHtml($_POST['description'] ?? '');
    $thumbnail = uploadProductPhoto($_FILES['image'] ?? []);
    if ($id) {
        if (!$productModel->getProductById($id)) throw new RuntimeException('Không tìm thấy sản phẩm.');
        $productModel->update($id, $name, $category, $price, $description, $thumbnail, $quantity);
    } else {
        if (!$thumbnail) throw new RuntimeException('Vui lòng chọn ảnh đại diện.');
        $id = $productModel->create($name, $category, $price, $description, $thumbnail, $quantity);
    }
    saveProductPhotos($productModel, $id);
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

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ.');
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            if (method_exists($productModel, 'delete') && $productModel->delete($id)) {
                $_SESSION['success'] = "Đã xóa sản phẩm #$id!";
            } else {
                $_SESSION['error'] = "Không thể xóa sản phẩm (đang dính đơn hàng)!";
            }
        }
        header('Location: ../admin/products.php');
        exit();

    default:
        header('Location: ../admin/products.php');
        exit();
}
