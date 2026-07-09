<?php
require_once __DIR__ . '/../config/bootstrap.php';
$pdo = getDb();
$pdo->exec('DELETE FROM sale_items WHERE sale_id IN (SELECT id FROM sales WHERE invoice_number = "INV-TEST-0001")');
$pdo->exec('DELETE FROM sales WHERE invoice_number = "INV-TEST-0001"');
$pdo->exec('DELETE FROM products WHERE sku = "TEST-SKU-1"');
echo "Cleanup complete\n";
