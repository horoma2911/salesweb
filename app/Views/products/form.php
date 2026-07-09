<div class="card">
    <h2><?= !empty($product) ? 'Edit Product' : 'Add Product' ?></h2>
    <form method="post" action="<?= url('products/save') ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <?php if (!empty($product)): ?><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><?php endif; ?>
        <div class="two-col">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>SKU</label>
                <input type="text" name="sku" value="<?= htmlspecialchars($product['sku'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Barcode</label>
                <input type="text" name="barcode" value="<?= htmlspecialchars($product['barcode'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" value="<?= htmlspecialchars($product['category'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand" value="<?= htmlspecialchars($product['brand'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="Active" <?= (($product['status'] ?? 'Active') === 'Active') ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= (($product['status'] ?? 'Active') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label>Buying Price</label>
                <input type="number" step="0.01" name="buying_price" value="<?= htmlspecialchars($product['buying_price'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Selling Price</label>
                <input type="number" step="0.01" name="selling_price" value="<?= htmlspecialchars($product['selling_price'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Current Stock</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($product['stock'] ?? '0') ?>" required>
            </div>
            <div class="form-group">
                <label>Minimum Stock</label>
                <input type="number" name="minimum_stock" value="<?= htmlspecialchars($product['minimum_stock'] ?? '0') ?>" required>
            </div>
            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>
        <div class="right">
            <a class="btn btn-secondary" href="<?= url('products') ?>">Cancel</a>
            <button class="btn btn-primary" type="submit">Save Product</button>
        </div>
    </form>
</div>
