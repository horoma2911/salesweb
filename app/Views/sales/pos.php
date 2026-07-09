<div class="pos-grid">
    <div class="card">
        <div class="flex" style="justify-content:space-between;align-items:center;">
            <div>
                <h2>Point of Sale</h2>
                <p class="muted">Search products, add to cart, apply discount, and complete the sale.</p>
            </div>
            <a class="btn btn-secondary" href="<?= url('sales/history') ?>">Sales History</a>
        </div>

        <form method="get" action="<?= url('sales/pos') ?>" class="form-group">
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search products">
        </form>

        <div class="product-list">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                    <span class="muted">SKU <?= htmlspecialchars($product['sku']) ?></span><br>
                    <span class="muted">Stock <?= (int)$product['stock'] ?></span><br>
                    <span class="pill"><?= formatCurrency((float)$product['selling_price']) ?></span>
                    <form method="post" action="<?= url('sales/add-to-cart') ?>" style="margin-top:8px;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                        <input type="number" name="quantity" value="1" min="1" style="margin-bottom:8px;">
                        <button class="btn btn-primary" type="submit">Add</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h3>Current Cart</h3>
        <form method="post" action="<?= url('sales/update-cart') ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <table class="table">
                <thead><tr><th>Item</th><th>Qty</th><th>Price</th></tr></thead>
                <tbody>
                <?php $cartItems = $cart ?? []; foreach ($cartItems as $key => $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><input type="number" name="quantity[<?= $key ?>]" value="<?= (int)$item['quantity'] ?>" min="1"></td>
                        <td><?= formatCurrency((float)$item['unit_price'] * (int)$item['quantity']) ?></td>
                        <td><a class="btn btn-danger" href="<?= url('sales/remove-item&id=' . $key) ?>">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="right" style="margin-top:10px;"><button class="btn btn-secondary" type="submit">Update</button></div>
        </form>

        <div class="form-group">
            <label>Discount</label>
            <input type="number" step="0.01" form="checkout-form" name="discount" value="0">
        </div>
        <div class="form-group">
            <label>Tax</label>
            <input type="number" step="0.01" form="checkout-form" name="tax" value="0">
        </div>
        <div class="form-group">
            <label>Payment Method</label>
            <select form="checkout-form" name="payment_method">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="transfer">Transfer</option>
            </select>
        </div>
        <div class="right">
            <a class="btn btn-secondary" href="<?= url('sales/cancel') ?>">Cancel</a>
            <form method="post" action="<?= url('sales/hold') ?>" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <button class="btn btn-warning" type="submit">Hold</button>
            </form>
            <form id="checkout-form" method="post" action="<?= url('sales/checkout') ?>" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <button class="btn btn-success" type="submit">Complete Sale</button>
            </form>
        </div>

        <div class="card" style="margin-top:12px;">
            <h4>Held Sales</h4>
            <ul>
                <?php foreach ($holds as $hold): ?>
                    <li><?= htmlspecialchars($hold['id']) ?> — <?= htmlspecialchars(substr($hold['created_at'], 0, 16)) ?> <a href="<?= url('sales/resume&id=' . $hold['id']) ?>">Resume</a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
