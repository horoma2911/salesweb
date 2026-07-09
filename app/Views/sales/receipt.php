<div class="card" style="max-width:560px;margin:20px auto;">
    <h2>Receipt</h2>
    <p><strong>Invoice:</strong> <?= htmlspecialchars($sale['invoice_number']) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars(substr($sale['created_at'], 0, 16)) ?></p>
    <table class="table">
        <thead><tr><th>Item</th><th>Qty</th><th>Amount</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= formatCurrency((float)$item['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong>Discount:</strong> <?= formatCurrency((float)$sale['discount']) ?></p>
    <p><strong>Total:</strong> <?= formatCurrency((float)$sale['total']) ?></p>
    <div class="right">
        <a class="btn btn-secondary" href="<?= url('sales/history') ?>">Back</a>
    </div>
</div>
