<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Sale.php';
require_once __DIR__ . '/../app/Models/SaleItem.php';

$pdo = getDb();
$pdo->exec('DELETE FROM sale_items');
$pdo->exec('DELETE FROM sales');
$pdo->exec('DELETE FROM products WHERE sku = "TEST-SKU-1"');

$productModel = new Product();
$productId = $productModel->create([
    'name' => 'Verification Product',
    'sku' => 'TEST-SKU-1',
    'barcode' => 'TEST-BAR-1',
    'category' => 'Testing',
    'brand' => 'TestBrand',
    'buying_price' => 10,
    'selling_price' => 15,
    'stock' => 5,
    'minimum_stock' => 2,
    'image' => '',
    'description' => 'Verification item',
    'status' => 'Active',
    'created_by' => 1,
]);

$product = $productModel->findById($productId);
if ($product['stock'] != 5) {
    throw new Exception('Initial stock failed');
}

$saleModel = new Sale();
$saleItemModel = new SaleItem();
$saleId = $saleModel->create([
    'invoice_number' => 'INV-TEST-0001',
    'cashier_id' => 1,
    'subtotal' => 30,
    'discount' => 0,
    'tax' => 0,
    'total' => 30,
    'revenue' => 30,
    'profit' => 10,
    'payment_method' => 'cash',
    'status' => 'completed',
]);

$saleItemModel->create([
    'sale_id' => $saleId,
    'product_id' => $productId,
    'product_name' => 'Verification Product',
    'quantity' => 2,
    'unit_price' => 15,
    'buying_price' => 10,
    'profit' => 10,
    'total' => 30,
]);

$productModel->deductStock($productId, 2);
$updated = $productModel->findById($productId);
if ($updated['stock'] != 3) {
    throw new Exception('Stock deduction failed');
}

$summary = $saleModel->summary('today');
if ((float)$summary['revenue'] != 30) {
    throw new Exception('Revenue summary failed');
}

echo "Verification passed: product created, sale recorded, stock deducted, revenue and profit calculated.\n";
