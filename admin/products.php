<form action="../index.php?controller=product&action=store" method="POST" enctype="multipart/form-data">
    
    // phần be 2
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

    <div class="form-group">
        <label>Ảnh đại diện sản phẩm:</label>
        <input type="file" name="image" class="form-control">
    </div>

    <div class="form-group">
        <label>Mô tả chi tiết:</label>
        <textarea name="description" id="editor" class="form-control"><?php echo isset($product_edit['description']) ? $product_edit['description'] : ''; ?></textarea>
    </div>


    // be 3
    <div class="form-group">
        <label>Chọn nhiều ảnh phụ:</label>
        <input type="file" name="images[]" multiple required>
    </div>

    <button type="submit">Lưu Sản Phẩm</button>
</form>
