<div class="card" style="max-width:720px;margin:20px auto;">
    <h2>Invoice</h2>
    <p><strong>Invoice Number:</strong> <?= htmlspecialchars($sale['invoice_number']) ?></p>
    <p><strong>Cashier:</strong> <?= htmlspecialchars($sale['cashier_name'] ?? 'N/A') ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars(substr($sale['created_at'], 0, 16)) ?></p>
    <table class="table">
        <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= formatCurrency((float)$item['unit_price']) ?></td>
                <td><?= formatCurrency((float)$item['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong>Subtotal:</strong> <?= formatCurrency((float)$sale['subtotal']) ?></p>
    <p><strong>Discount:</strong> <?= formatCurrency((float)$sale['discount']) ?></p>
    <p><strong>Tax:</strong> <?= formatCurrency((float)$sale['tax']) ?></p>
    <p><strong>Total:</strong> <?= formatCurrency((float)$sale['total']) ?></p>
    <p><strong>Profit:</strong> <?= formatCurrency((float)$sale['profit']) ?></p>
    <div class="right">
        <a class="btn btn-secondary" href="<?= url('sales/history') ?>">Back</a>
    </div>
</div>
