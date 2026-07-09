<div class="card">
    <div class="flex" style="justify-content:space-between;align-items:center;">
        <div>
            <h2>Reports</h2>
            <p class="muted">Generate daily, weekly, monthly, yearly, revenue, profit, inventory, and product sales reports.</p>
        </div>
        <div class="flex">
            <a class="btn btn-secondary" href="<?= url('reports&type=daily&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&search=' . urlencode($search)) ?>">Daily</a>
            <a class="btn btn-secondary" href="<?= url('reports&type=weekly&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&search=' . urlencode($search)) ?>">Weekly</a>
            <a class="btn btn-secondary" href="<?= url('reports&type=monthly&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&search=' . urlencode($search)) ?>">Monthly</a>
            <a class="btn btn-secondary" href="<?= url('reports&type=yearly&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&search=' . urlencode($search)) ?>">Yearly</a>
            <a class="btn btn-secondary" href="<?= url('reports&type=inventory&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&search=' . urlencode($search)) ?>">Inventory</a>
            <a class="btn btn-secondary" href="<?= url('reports&type=product-sales&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&search=' . urlencode($search)) ?>">Product Sales</a>
        </div>
    </div>
</div>

<div class="card">
    <form method="get" action="<?= url('reports') ?>" class="grid grid-3">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
        <div class="form-group">
            <label>Date From</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
        </div>
        <div class="form-group">
            <label>Date To</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
        </div>
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="right">
            <button class="btn btn-primary" type="submit">Generate</button>
            <a class="btn btn-success" href="<?= url('reports&type=' . urlencode($type) . '&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&search=' . urlencode($search) . '&export=excel') ?>">Export Excel</a>
            <a class="btn btn-secondary" href="<?= url('reports&type=' . urlencode($type) . '&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&search=' . urlencode($search) . '&export=pdf') ?>">Export PDF</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>Summary</h3>
    <div class="stats-grid">
        <div class="stat"><div class="muted">Total sales</div><h3><?= (int)($report['totals']['sales_count'] ?? 0) ?></h3></div>
        <div class="stat success"><div class="muted">Total revenue</div><h3><?= formatCurrency((float)($report['totals']['revenue'] ?? 0)) ?></h3></div>
        <div class="stat warning"><div class="muted">Total profit</div><h3><?= formatCurrency((float)($report['totals']['profit'] ?? 0)) ?></h3></div>
        <div class="stat danger"><div class="muted">Best selling</div><h3><?= htmlspecialchars($report['bestSelling']['name'] ?? 'N/A') ?></h3></div>
    </div>
</div>

<div class="card">
    <?php if (($report['type'] ?? '') === 'inventory' && !empty($report['products'])): ?>
        <table class="table">
            <thead><tr><th>Product</th><th>SKU</th><th>Stock</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($report['products'] as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['sku']) ?></td>
                    <td><?= (int)$product['stock'] ?></td>
                    <td><?= htmlspecialchars($product['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (!empty($report['rows'])): ?>
        <table class="table">
            <thead><tr><th>Period</th><th>Sales</th><th>Revenue</th><th>Profit</th></tr></thead>
            <tbody>
            <?php foreach ($report['rows'] as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['day'] ?? $row['name'] ?? 'N/A') ?></td>
                    <td><?= (int)($row['sales_count'] ?? 0) ?></td>
                    <td><?= formatCurrency((float)($row['revenue'] ?? 0)) ?></td>
                    <td><?= formatCurrency((float)($row['profit'] ?? 0)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">No data available for the selected criteria.</p>
    <?php endif; ?>
</div>
