<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar_admin.php'; ?>
<div class="admin-content">
    <div class="admin-page-header">
        <h2>Quản lý sản phẩm</h2>
        <a href="?controller=product&action=create" class="btn btn-primary">+ Thêm sản phẩm</a>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><img src="../assets/uploads/products/<?php echo $p['image']; ?>" class="admin-thumb"></td>
                <td><?php echo $p['name']; ?></td>
                <td><?php echo $p['category_name']; ?></td>
                <td><?php echo number_format($p['price']); ?>đ</td>
                <td><?php echo $p['quantity']; ?></td>
                <td>
                    <?php if ($p['quantity'] > 0): ?>
                        <span class="badge badge-success">Còn hàng</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Hết hàng</span>
                    <?php endif; ?>
                </td>
                <td class="admin-actions">
                    <a href="?controller=product&action=edit&id=<?php echo $p['id']; ?>" class="btn btn-edit">Sửa</a>
                    <a href="?controller=product&action=delete&id=<?php echo $p['id']; ?>" class="btn btn-delete btn-delete-confirm">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="admin-form-box">
        <h3><?php echo isset($product_edit['id']) ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới'; ?></h3>
        <form action="../index.php?controller=product&action=store" method="POST" enctype="multipart/form-data">
            // phần be2
            <input type="hidden" name="id" value="<?php echo isset($product_edit['id']) ? $product_edit['id'] : ''; ?>">
            <div class="form-group">
                <label>Tên sản phẩm:</label>
                <input type="text" name="name" class="form-control" value="<?php echo isset($product_edit['name']) ? $product_edit['name'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Danh mục:</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($product_edit['category_id']) && $product_edit['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giá sản phẩm:</label>
                <input type="number" name="price" class="form-control" value="<?php echo isset($product_edit['price']) ? $product_edit['price'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Số lượng:</label>
                <input type="number" name="quantity" class="form-control" value="<?php echo isset($product_edit['quantity']) ? $product_edit['quantity'] : ''; ?>" required>
            </div>
            // be 3
            <div class="form-group">
                <label>Ảnh đại diện sản phẩm:</label>
                <input type="file" name="image" class="form-control">
            </div>
            <div class="form-group">
                <label>Mô tả chi tiết:</label>
                <textarea name="description" id="editor" class="form-control"><?php echo isset($product_edit['description']) ? $product_edit['description'] : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label>Chọn nhiều ảnh phụ:</label>
                <input type="file" name="images[]" multiple required>
            </div>

            <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
        </form>
    </div>

</div>
<?php include '../includes/footer.php'; ?>