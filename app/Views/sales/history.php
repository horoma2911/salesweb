<div class="card">
    <div class="flex" style="justify-content:space-between;align-items:center;">
        <div>
            <h2>Sales History</h2>
            <p class="muted">Search, filter by date or cashier, and inspect every completed sale.</p>
        </div>
        <a class="btn btn-primary" href="<?= url('sales/pos') ?>">New Sale</a>
    </div>
</div>

<div class="card">
    <form method="get" action="<?= url('sales/history') ?>" class="grid grid-3">
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Date From</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Date To</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Cashier</label>
            <select name="cashier_id">
                <option value="">All</option>
                <?php foreach ($cashiers as $cashier): ?>
                    <option value="<?= (int)$cashier['id'] ?>" <?= ($filters['cashier_id'] ?? '') == $cashier['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cashier['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="right"><button class="btn btn-primary" type="submit">Filter</button></div>
    </form>
</div>

<div class="card">
    <table class="table">
        <thead><tr><th>Invoice</th><th>Cashier</th><th>Total</th><th>Profit</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($sales as $sale): ?>
            <tr>
                <td><?= htmlspecialchars($sale['invoice_number']) ?></td>
                <td><?= htmlspecialchars($sale['cashier_name'] ?? 'N/A') ?></td>
                <td><?= formatCurrency((float)$sale['total']) ?></td>
                <td><?= formatCurrency((float)$sale['profit']) ?></td>
                <td><?= htmlspecialchars(substr($sale['created_at'], 0, 16)) ?></td>
                <td>
                    <a class="btn btn-secondary" href="<?= url('sales/show&id=' . $sale['id']) ?>">View</a>
                    <a class="btn btn-primary" href="<?= url('sales/receipt&id=' . $sale['id']) ?>">Receipt</a>
                    <a class="btn btn-success" href="<?= url('sales/invoice&id=' . $sale['id']) ?>">Invoice</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
            <a class="page <?= $i === $pagination['page'] ? 'active' : '' ?>" href="<?= url('sales/history&page=' . $i . '&search=' . urlencode($filters['search'] ?? '') . '&date_from=' . urlencode($filters['date_from'] ?? '') . '&date_to=' . urlencode($filters['date_to'] ?? '') . '&cashier_id=' . urlencode($filters['cashier_id'] ?? '')) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>
