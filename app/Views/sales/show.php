<div class="card">
    <h2>Sale Details</h2>
    <p><strong>Invoice:</strong> <?= htmlspecialchars($sale['invoice_number']) ?></p>
    <p><strong>Cashier:</strong> <?= htmlspecialchars($sale['cashier_name'] ?? 'N/A') ?></p>
    <p><strong>Payment Method:</strong> <?= htmlspecialchars($sale['payment_method']) ?></p>
    <p><strong>Subtotal:</strong> <?= formatCurrency((float)$sale['subtotal']) ?></p>
    <p><strong>Discount:</strong> <?= formatCurrency((float)$sale['discount']) ?></p>
    <p><strong>Tax:</strong> <?= formatCurrency((float)$sale['tax']) ?></p>
    <p><strong>Total:</strong> <?= formatCurrency((float)$sale['total']) ?></p>
    <p><strong>Profit:</strong> <?= formatCurrency((float)$sale['profit']) ?></p>
    <table class="table" style="margin-top:12px;">
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
    <div class="right" style="margin-top:12px;">
        <a class="btn btn-secondary" href="<?= url('sales/history') ?>">Back</a>
        <a class="btn btn-primary" href="<?= url('sales/receipt&id=' . $sale['id']) ?>">Receipt</a>
        <a class="btn btn-success" href="<?= url('sales/invoice&id=' . $sale['id']) ?>">Invoice</a>
    </div>
</div>
