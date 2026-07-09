<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($this->config['app_name'] ?? 'Selling Shop') ?></title>
    <link rel="stylesheet" href="<?= asset('styles.css') ?>">
</head>
<body>
<nav class="navbar">
    <div class="brand">Selling Shop</div>
    <div class="nav-links">
        <?php if (isLoggedIn()): ?>
            <a href="<?= url('dashboard') ?>">Dashboard</a>
            <a href="<?= url('products') ?>">Products</a>
            <a href="<?= url('sales/pos') ?>">POS</a>
            <a href="<?= url('sales/history') ?>">Sales</a>
            <a href="<?= url('reports') ?>">Reports</a>
            <a href="<?= url('logout') ?>">Logout</a>
        <?php else: ?>
            <a href="<?= url('login') ?>">Login</a>
        <?php endif; ?>
    </div>
</nav>
<div class="container">
    <?php foreach ($flash ?? [] as $type => $message): ?>
        <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endforeach; ?>
