<?php
require_once base_path('app/Controllers/Controller.php');
require_once base_path('app/Models/Product.php');

class ProductController extends Controller {
    public function index(): void {
        $this->requireLogin();
        $productModel = new Product();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'search' => sanitize($_GET['search'] ?? ''),
            'category' => sanitize($_GET['category'] ?? ''),
            'status' => sanitize($_GET['status'] ?? ''),
            'stock_min' => $_GET['stock_min'] ?? '',
        ];
        $perPage = $this->config['items_per_page'];
        $total = $productModel->countAll($filters);
        $products = $productModel->findAll($filters, $perPage, ($page - 1) * $perPage);
        $pagination = $this->pagination($total, $page, $perPage);
        $this->render('products/index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $productModel->categories(),
            'pagination' => $pagination,
        ]);
    }

    public function form(): void {
        $this->requireRole(['admin', 'seller']);
        $productModel = new Product();
        $product = null;
        if (!empty($_GET['id'])) {
            $product = $productModel->findById((int)$_GET['id']);
        }
        $this->render('products/form', [
            'product' => $product,
            'categories' => $productModel->categories(),
            'csrf_token' => csrf_token(),
        ]);
    }

    public function show(): void {
        $this->requireLogin();
        $product = (new Product())->findById((int)($_GET['id'] ?? 0));
        if (!$product) {
            flash('error', 'Product not found.');
            $this->redirect(url('products'));
        }
        $this->render('products/show', ['product' => $product]);
    }

    public function save(): void {
        $this->requireRole(['admin', 'seller']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            $this->redirect(url('products'));
        }

        $productModel = new Product();
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = sanitize($_POST['name'] ?? '');
        $sku = strtoupper(trim($_POST['sku'] ?? ''));
        $barcode = trim($_POST['barcode'] ?? '');
        $category = sanitize($_POST['category'] ?? '');
        $brand = sanitize($_POST['brand'] ?? '');
        $buyingPrice = (float)($_POST['buying_price'] ?? 0);
        $sellingPrice = (float)($_POST['selling_price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $minimumStock = (int)($_POST['minimum_stock'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        $status = sanitize($_POST['status'] ?? 'Active');

        if ($name === '' || $sku === '' || $barcode === '' || $category === '' || $brand === '' || $buyingPrice <= 0 || $sellingPrice <= 0 || $sellingPrice < $buyingPrice) {
            flash('error', 'Please enter valid product details and ensure selling price is above buying price.');
            $this->redirect(url('products/form'));
        }

        if ($productModel->findBySku($sku, $id) || $productModel->findByBarcode($barcode, $id)) {
            flash('error', 'SKU or barcode already exists.');
            $this->redirect(url('products/form' . ($id ? '&id=' . $id : '')));
        }

        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $targetDir = base_path('public/uploads');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', basename($_FILES['image']['name']));
            $targetFile = $targetDir . '/' . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image = $fileName;
            }
        }

        $data = [
            'name' => $name,
            'sku' => $sku,
            'barcode' => $barcode,
            'category' => $category,
            'brand' => $brand,
            'buying_price' => $buyingPrice,
            'selling_price' => $sellingPrice,
            'stock' => $stock,
            'minimum_stock' => $minimumStock,
            'image' => $image,
            'description' => $description,
            'status' => $status,
            'created_by' => $this->currentUser()['id'],
        ];

        if ($id) {
            $existing = $productModel->findById($id);
            if (!$existing) {
                flash('error', 'Product not found.');
                $this->redirect(url('products'));
            }
            if ($image === '') {
                unset($data['image']);
            }
            $productModel->update($id, $data);
            flash('success', 'Product updated successfully.');
        } else {
            $productModel->create($data);
            flash('success', 'Product added successfully.');
        }

        $this->redirect(url('products'));
    }

    public function delete(): void {
        $this->requireRole(['admin', 'seller']);
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            (new Product())->delete($id);
            flash('success', 'Product deleted successfully.');
        }
        $this->redirect(url('products'));
    }

    public function restore(): void {
        $this->requireRole(['admin', 'seller']);
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            (new Product())->restore($id);
            flash('success', 'Product restored successfully.');
        }
        $this->redirect(url('products'));
    }
}
