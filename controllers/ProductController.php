<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->productModel = new ProductModel($db);
        $this->categoryModel = new CategoryModel($db);
    }

    public function index() {
        $keyword = $_GET['search'] ?? '';
        $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $products = $this->productModel->getAll($keyword, $category_id, $limit, $offset);
        $total_items = $this->productModel->countAll($keyword, $category_id);
        $total_pages = ceil($total_items / $limit);
        $categories = $this->categoryModel->getAll();

        return [
            'products' => $products,
            'categories' => $categories,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'keyword' => $keyword,
            'category_id' => $category_id
        ];
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $category_id = $_POST['category_id'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $quantity = $_POST['quantity'] ?? 0;
            $description = $_POST['description'] ?? '';
            
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $target_dir = "../assets/uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $file_name = time() . '_' . uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $file_name;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $file_name;
                }
            }

            if (!empty($_POST['id'])) {
                $id = (int)$_POST['id'];
                $img_update = !empty($image) ? $image : null;
                $this->productModel->update($id, $name, $category_id, $price, $quantity, $description, $img_update);
            } else {
                $this->productModel->create($name, $category_id, $price, $quantity, $description, $image);
            }
            header('Location: products.php');
            exit();
        }
    }

    public function delete() {
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $this->productModel->delete($id);
            header('Location: products.php');
            exit();
        }
    }

    // be3
    public function detail() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id > 0) {
            // Lấy thông tin chi tiết
            $product = $this->productModel->getProductById($id);
            // Lấy mảng ảnh phụ
            $gallery = $this->productModel->getProductImages($id);
            
            if ($product) {
                // Render ra view (File nằm ở thư mục gốc)
                require_once 'product-detail.php'; 
            } else {
                echo "Sản phẩm không tồn tại!";
            }
        } else {
            echo "ID sản phẩm không hợp lệ!";
        }
    }

    // be3
    public function deleteImagesPhysical($product_id) {
        $images = $this->productModel->getProductImages($product_id);
        if (!empty($images)) {
            foreach ($images as $img) {
                if (file_exists($img['image_url'])) {
                    unlink($img['image_url']);
                }
            }
        }
    }
}
?>
