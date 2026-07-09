<div class="card">
    <h2><?= htmlspecialchars($product['name']) ?></h2>
    <div class="two-col">
        <div>
            <p><strong>SKU:</strong> <?= htmlspecialchars($product['sku']) ?></p>
            <p><strong>Barcode:</strong> <?= htmlspecialchars($product['barcode']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
            <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($product['status']) ?></p>
        </div>
        <div>
            <p><strong>Buying Price:</strong> <?= formatCurrency((float)$product['buying_price']) ?></p>
            <p><strong>Selling Price:</strong> <?= formatCurrency((float)$product['selling_price']) ?></p>
            <p><strong>Current Stock:</strong> <?= (int)$product['stock'] ?></p>
            <p><strong>Minimum Stock:</strong> <?= (int)$product['minimum_stock'] ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($product['description']) ?></p>
        </div>
    </div>
    <div class="right">
        <a class="btn btn-primary" href="<?= url('products/form&id=' . $product['id']) ?>">Edit</a>
        <a class="btn btn-secondary" href="<?= url('products') ?>">Back</a>
    </div>
</div>
