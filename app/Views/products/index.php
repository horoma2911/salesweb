<div class="card">
    <div class="flex" style="justify-content:space-between;align-items:center;">
        <div>
            <h2>Product Management</h2>
            <p class="muted">Search, filter, paginate, and manage your inventory.</p>
        </div>
        <a class="btn btn-primary" href="<?= url('products/form') ?>">Add Product</a>
    </div>
</div>

<div class="card">
    <form method="get" action="<?= url('products') ?>" class="grid grid-3">
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category">
                <option value="">All</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>" <?= ($filters['category'] ?? '') === $category ? 'selected' : '' ?>><?= htmlspecialchars($category) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="">All</option>
                <option value="Active" <?= ($filters['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= ($filters['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label>Low stock threshold</label>
            <input type="number" name="stock_min" value="<?= htmlspecialchars($filters['stock_min'] ?? '') ?>">
        </div>
        <div class="right"><button class="btn btn-primary" type="submit">Filter</button></div>
    </form>
</div>

<div class="card">
    <table class="table">
        <thead>
        <tr>
            <th>Image</th>
            <th>Product</th>
            <th>SKU</th>
            <th>Barcode</th>
            <th>Buying</th>
            <th>Selling</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><img src="<?= !empty($product['image']) ? '/' . 'uploads/' . htmlspecialchars($product['image']) : 'https://via.placeholder.com/48' ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;"></td>
                <td>
                    <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                    <span class="muted"><?= htmlspecialchars($product['category']) ?></span>
                </td>
                <td><?= htmlspecialchars($product['sku']) ?></td>
                <td><?= htmlspecialchars($product['barcode']) ?></td>
                <td><?= formatCurrency((float)$product['buying_price']) ?></td>
                <td><?= formatCurrency((float)$product['selling_price']) ?></td>
                <td><?= (int)$product['stock'] ?></td>
                <td><span class="pill"><?= htmlspecialchars($product['status']) ?></span></td>
                <td>
                    <a class="btn btn-secondary" href="<?= url('products/show&id=' . $product['id']) ?>">View</a>
                    <a class="btn btn-primary" href="<?= url('products/form&id=' . $product['id']) ?>">Edit</a>
                    <?php if (!empty($product['deleted_at'])): ?>
                        <a class="btn btn-success" href="<?= url('products/restore&id=' . $product['id']) ?>">Restore</a>
                    <?php else: ?>
                        <a class="btn btn-danger" href="<?= url('products/delete&id=' . $product['id']) ?>">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
            <a class="page <?= $i === $pagination['page'] ? 'active' : '' ?>" href="<?= url('products&page=' . $i . '&search=' . urlencode($filters['search'] ?? '') . '&category=' . urlencode($filters['category'] ?? '') . '&status=' . urlencode($filters['status'] ?? '') . '&stock_min=' . urlencode($filters['stock_min'] ?? '')) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>
