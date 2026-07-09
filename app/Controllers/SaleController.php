<?php
require_once base_path('app/Controllers/Controller.php');
require_once base_path('app/Models/Product.php');
require_once base_path('app/Models/Sale.php');
require_once base_path('app/Models/SaleItem.php');

class SaleController extends Controller {
    public function pos(): void {
        $this->requireLogin();
        $this->requireRole(['admin', 'cashier', 'seller']);
        $productModel = new Product();
        $filters = [
            'search' => sanitize($_GET['search'] ?? ''),
            'status' => 'Active',
        ];
        $products = $productModel->findAll($filters, 20, 0);
        $holds = $this->loadHolds();
        $this->render('sales/pos', [
            'products' => $products,
            'cart' => $_SESSION['cart'] ?? [],
            'filters' => $filters,
            'holds' => $holds,
            'csrf_token' => csrf_token(),
        ]);
    }

    public function addToCart(): void {
        $this->requireRole(['admin', 'cashier', 'seller']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            $this->redirect(url('sales/pos'));
        }

        $product = (new Product())->findById((int)($_POST['product_id'] ?? 0));
        if (!$product || $product['deleted_at'] !== null || $product['status'] !== 'Active') {
            flash('error', 'Product is unavailable.');
            $this->redirect(url('sales/pos'));
        }

        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        if ($product['stock'] < $qty) {
            flash('error', 'Insufficient stock.');
            $this->redirect(url('sales/pos'));
        }

        $cart = $_SESSION['cart'] ?? [];
        $key = $product['id'];
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'product_id' => (int)$product['id'],
                'name' => $product['name'],
                'sku' => $product['sku'],
                'quantity' => $qty,
                'unit_price' => (float)$product['selling_price'],
                'buying_price' => (float)$product['buying_price'],
                'stock' => (int)$product['stock'],
            ];
        }
        $_SESSION['cart'] = $cart;
        flash('success', 'Item added to cart.');
        $this->redirect(url('sales/pos'));
    }

    public function updateCart(): void {
        $this->requireRole(['admin', 'cashier', 'seller']);
        $cart = $_SESSION['cart'] ?? [];
        foreach ($cart as $key => $item) {
            $qty = max(1, (int)($_POST['quantity'][$key] ?? 1));
            $product = (new Product())->findById($item['product_id']);
            if ($product && $product['stock'] < $qty) {
                flash('error', 'One or more items exceed available stock.');
                $this->redirect(url('sales/pos'));
            }
            $cart[$key]['quantity'] = $qty;
        }
        $_SESSION['cart'] = $cart;
        flash('success', 'Cart updated.');
        $this->redirect(url('sales/pos'));
    }

    public function removeItem(): void {
        $this->requireRole(['admin', 'cashier', 'seller']);
        $id = (int)($_GET['id'] ?? 0);
        $cart = $_SESSION['cart'] ?? [];
        unset($cart[$id]);
        $_SESSION['cart'] = $cart;
        flash('success', 'Item removed.');
        $this->redirect(url('sales/pos'));
    }

    public function checkout(): void {
        $this->requireRole(['admin', 'cashier', 'seller']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            $this->redirect(url('sales/pos'));
        }

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            flash('error', 'Cart is empty.');
            $this->redirect(url('sales/pos'));
        }

        $discount = (float)($_POST['discount'] ?? 0);
        $tax = (float)($_POST['tax'] ?? 0);
        $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }
        if ($discount > $subtotal) {
            flash('error', 'Discount cannot exceed subtotal.');
            $this->redirect(url('sales/pos'));
        }
        $total = max(0, $subtotal - $discount + $tax);
        $profit = 0;
        $productModel = new Product();
        $saleModel = new Sale();
        $saleItemModel = new SaleItem();

        $pdo = getDb();
        $pdo->beginTransaction();
        try {
            foreach ($cart as $item) {
                $product = $productModel->findById($item['product_id']);
                if (!$product || (int)$product['stock'] < (int)$item['quantity']) {
                    throw new Exception('Insufficient stock for ' . $item['name']);
                }
                $profit += ((float)$item['unit_price'] - (float)$item['buying_price']) * (int)$item['quantity'];
            }

            $invoiceNumber = $this->nextInvoiceNumber();
            $saleId = $saleModel->create([
                'invoice_number' => $invoiceNumber,
                'cashier_id' => $this->currentUser()['id'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'revenue' => $total,
                'profit' => $profit,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
            ]);

            foreach ($cart as $item) {
                $saleItemModel->create([
                    'sale_id' => $saleId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'buying_price' => $item['buying_price'],
                    'profit' => ((float)$item['unit_price'] - (float)$item['buying_price']) * (int)$item['quantity'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
                $productModel->deductStock($item['product_id'], $item['quantity']);
            }
            $pdo->commit();
            unset($_SESSION['cart']);
            flash('success', 'Sale completed successfully. Invoice ' . $invoiceNumber);
            $this->redirect(url('sales/history'));
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', $e->getMessage());
            $this->redirect(url('sales/pos'));
        }
    }

    public function cancel(): void {
        $this->requireRole(['admin', 'cashier', 'seller']);
        unset($_SESSION['cart']);
        flash('success', 'Sale canceled.');
        $this->redirect(url('sales/pos'));
    }

    public function hold(): void {
        $this->requireRole(['admin', 'cashier', 'seller']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            $this->redirect(url('sales/pos'));
        }

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            flash('error', 'Cart is empty.');
            $this->redirect(url('sales/pos'));
        }

        $stmt = getDb()->prepare('INSERT INTO sale_holds (cart_json, discount, tax, payment_method) VALUES (:cart_json, :discount, :tax, :payment_method)');
        $stmt->execute([
            'cart_json' => json_encode($cart),
            'discount' => (float)($_POST['discount'] ?? 0),
            'tax' => (float)($_POST['tax'] ?? 0),
            'payment_method' => sanitize($_POST['payment_method'] ?? 'cash'),
        ]);
        unset($_SESSION['cart']);
        flash('success', 'Sale held successfully.');
        $this->redirect(url('sales/pos'));
    }

    public function resume(): void {
        $this->requireRole(['admin', 'cashier', 'seller']);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = getDb()->prepare('SELECT * FROM sale_holds WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $hold = $stmt->fetch();
        if ($hold) {
            $_SESSION['cart'] = json_decode($hold['cart_json'], true) ?: [];
            getDb()->prepare('DELETE FROM sale_holds WHERE id = :id')->execute(['id' => $id]);
            flash('success', 'Sale resumed.');
        }
        $this->redirect(url('sales/pos'));
    }

    public function history(): void {
        $this->requireLogin();
        $saleModel = new Sale();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'search' => sanitize($_GET['search'] ?? ''),
            'cashier_id' => !empty($_GET['cashier_id']) ? (int)$_GET['cashier_id'] : '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];
        $perPage = $this->config['items_per_page'];
        $total = $saleModel->countAll($filters);
        $sales = $saleModel->findAll($filters, $perPage, ($page - 1) * $perPage);
        $pagination = $this->pagination($total, $page, $perPage);
        $this->render('sales/history', [
            'sales' => $sales,
            'filters' => $filters,
            'pagination' => $pagination,
            'cashiers' => getDb()->query('SELECT id, name FROM users WHERE role IN (\'admin\', \'cashier\') ORDER BY name')->fetchAll(),
        ]);
    }

    public function show(): void {
        $this->requireLogin();
        $sale = (new Sale())->findById((int)($_GET['id'] ?? 0));
        if (!$sale) {
            flash('error', 'Sale not found.');
            $this->redirect(url('sales/history'));
        }
        $items = (new SaleItem())->findBySale((int)$sale['id']);
        $this->render('sales/show', ['sale' => $sale, 'items' => $items]);
    }

    public function receipt(): void {
        $this->requireLogin();
        $sale = (new Sale())->findById((int)($_GET['id'] ?? 0));
        if (!$sale) {
            flash('error', 'Sale not found.');
            $this->redirect(url('sales/history'));
        }
        $items = (new SaleItem())->findBySale((int)$sale['id']);
        $this->render('sales/receipt', ['sale' => $sale, 'items' => $items]);
    }

    public function invoice(): void {
        $this->requireLogin();
        $sale = (new Sale())->findById((int)($_GET['id'] ?? 0));
        if (!$sale) {
            flash('error', 'Sale not found.');
            $this->redirect(url('sales/history'));
        }
        $items = (new SaleItem())->findBySale((int)$sale['id']);
        $this->render('sales/invoice', ['sale' => $sale, 'items' => $items]);
    }

    private function loadHolds(): array {
        $stmt = getDb()->query('SELECT * FROM sale_holds ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    private function nextInvoiceNumber(): string {
        $stmt = getDb()->query('SELECT MAX(id) FROM sales');
        $lastId = (int)$stmt->fetchColumn();
        return 'INV-' . date('Ymd') . '-' . str_pad((string)($lastId + 1), 4, '0', STR_PAD_LEFT);
    }
}
