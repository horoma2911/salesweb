<div class="card">
    <div class="flex" style="justify-content:space-between;align-items:center;">
        <div>
            <h2>Dashboard</h2>
            <p class="muted">Business overview with sales, revenue, profit, stock, and recent activity.</p>
        </div>
        <a class="btn btn-primary" href="<?= url('sales/pos') ?>">Open POS</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat">
        <div class="muted">Today’s sales</div>
        <h3><?= (int)($today['sales_count'] ?? 0) ?></h3>
    </div>
    <div class="stat success">
        <div class="muted">Today’s revenue</div>
        <h3><?= formatCurrency((float)($today['revenue'] ?? 0)) ?></h3>
    </div>
    <div class="stat warning">
        <div class="muted">Today’s profit</div>
        <h3><?= formatCurrency((float)($today['profit'] ?? 0)) ?></h3>
    </div>
    <div class="stat danger">
        <div class="muted">Low stock</div>
        <h3><?= count($lowStock) ?></h3>
    </div>
</div>

<div class="grid" style="margin-top:18px;">
    <div class="card">
        <h3>Weekly and monthly performance</h3>
        <div class="grid grid-2">
            <div>
                <h4>Weekly</h4>
                <p>Revenue: <strong><?= formatCurrency((float)($week['revenue'] ?? 0)) ?></strong></p>
                <p>Profit: <strong><?= formatCurrency((float)($week['profit'] ?? 0)) ?></strong></p>
            </div>
            <div>
                <h4>Monthly</h4>
                <p>Revenue: <strong><?= formatCurrency((float)($month['revenue'] ?? 0)) ?></strong></p>
                <p>Profit: <strong><?= formatCurrency((float)($month['profit'] ?? 0)) ?></strong></p>
            </div>
        </div>
    </div>
    <div class="card">
        <h3>Inventory alerts</h3>
        <p><strong>Out of stock:</strong> <?= count($outOfStock) ?></p>
        <p><strong>Total products:</strong> <?= (int)$totalProducts ?></p>
    </div>
</div>

<div class="grid grid-2" style="margin-top:18px;">
    <div class="card">
        <h3>Recent sales</h3>
        <table class="table">
            <thead><tr><th>Invoice</th><th>Cashier</th><th>Total</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($recentSales as $sale): ?>
                <tr>
                    <td><a href="<?= url('sales/show&id=' . $sale['id']) ?>"><?= htmlspecialchars($sale['invoice_number']) ?></a></td>
                    <td><?= htmlspecialchars($sale['cashier_name'] ?? 'N/A') ?></td>
                    <td><?= formatCurrency((float)$sale['total']) ?></td>
                    <td><?= htmlspecialchars(substr($sale['created_at'], 0, 16)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3>Top selling products</h3>
        <table class="table">
            <thead><tr><th>Product</th><th>Units sold</th></tr></thead>
            <tbody>
            <?php foreach ($topProducts as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= (int)$product['total_sold'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top:18px;">
    <h3>Charts</h3>
    <div style="display:flex;align-items:flex-end;gap:12px;height:220px;">
        <?php $bars = [($today['revenue'] ?? 0) / 5, ($week['revenue'] ?? 0) / 10, ($month['revenue'] ?? 0) / 20, ($totals['revenue'] ?? 0) / 50]; ?>
        <?php foreach ($bars as $index => $bar): ?>
            <div style="flex:1;background:linear-gradient(180deg,#2563eb,#60a5fa);height:<?= max(30, min(200, (int)$bar)) ?>px;border-radius:8px 8px 0 0;"></div>
        <?php endforeach; ?>
    </div>
</div>
