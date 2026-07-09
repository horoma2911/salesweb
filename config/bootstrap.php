<?php
session_start();

const APP_ROOT = __DIR__ . '/..';

function base_path(string $path = ''): string {
    return APP_ROOT . ($path ? '/' . ltrim($path, '/') : '');
}

if (file_exists(base_path('vendor/autoload.php'))) {
    require_once base_path('vendor/autoload.php');
}

function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return $path ? 'index.php?action=' . $path : 'index.php';
}

function asset(string $path): string {
    return '/' . ltrim('assets/' . ltrim($path, '/'), '/');
}

function formatCurrency(float $amount): string {
    return 'TZS ' . number_format($amount, 2);
}

function sanitize(string $value): string {
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}

function getFlash(): array {
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function getDb(): PDO {
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }

    $dbFile = getenv('DB_PATH') ?: base_path('storage/app.sqlite');
    $dbDir = dirname($dbFile);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function initializeDatabase(): void {
    $pdo = getDb();

    $pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'cashier',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
SQL
    );

    $pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        sku TEXT NOT NULL UNIQUE,
        barcode TEXT NOT NULL UNIQUE,
        category TEXT NOT NULL,
        brand TEXT NOT NULL,
        buying_price REAL NOT NULL DEFAULT 0,
        selling_price REAL NOT NULL DEFAULT 0,
        stock INTEGER NOT NULL DEFAULT 0,
        minimum_stock INTEGER NOT NULL DEFAULT 0,
        image TEXT,
        description TEXT,
        status TEXT NOT NULL DEFAULT 'Active',
        deleted_at TEXT,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(created_by) REFERENCES users(id)
    );
SQL
    );

    $pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS sales (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_number TEXT NOT NULL UNIQUE,
        cashier_id INTEGER NOT NULL,
        subtotal REAL NOT NULL DEFAULT 0,
        discount REAL NOT NULL DEFAULT 0,
        tax REAL NOT NULL DEFAULT 0,
        total REAL NOT NULL DEFAULT 0,
        revenue REAL NOT NULL DEFAULT 0,
        profit REAL NOT NULL DEFAULT 0,
        payment_method TEXT NOT NULL DEFAULT 'cash',
        status TEXT NOT NULL DEFAULT 'completed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(cashier_id) REFERENCES users(id)
    );
SQL
    );

    $pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS sale_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sale_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        product_name TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        unit_price REAL NOT NULL,
        buying_price REAL NOT NULL,
        profit REAL NOT NULL,
        total REAL NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(sale_id) REFERENCES sales(id),
        FOREIGN KEY(product_id) REFERENCES products(id)
    );
SQL
    );

    $pdo->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS sale_holds (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cart_json TEXT NOT NULL,
        discount REAL NOT NULL DEFAULT 0,
        tax REAL NOT NULL DEFAULT 0,
        payment_method TEXT NOT NULL DEFAULT 'cash',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
SQL
    );

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => 'admin@example.com']);
    if (!$stmt->fetch()) {
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)')->execute([
            'Administrator',
            'admin@example.com',
            $hash,
            'admin'
        ]);
    }

    $productCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($productCount === 0) {
        $pdo->prepare('INSERT INTO products (name, sku, barcode, category, brand, buying_price, selling_price, stock, minimum_stock, image, description, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([
            'Laptop',
            'SKU-1001',
            'BAR-1001',
            'Electronics',
            'BrandX',
            450.00,
            750.00,
            8,
            3,
            'laptop.jpg',
            'Business laptop with 16GB RAM',
            'Active',
            1
        ]);
        $pdo->prepare('INSERT INTO products (name, sku, barcode, category, brand, buying_price, selling_price, stock, minimum_stock, image, description, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([
            'Wireless Mouse',
            'SKU-1002',
            'BAR-1002',
            'Accessories',
            'BrandY',
            12.50,
            24.00,
            2,
            2,
            'mouse.jpg',
            'Ergonomic wireless mouse',
            'Active',
            1
        ]);
        $pdo->prepare('INSERT INTO products (name, sku, barcode, category, brand, buying_price, selling_price, stock, minimum_stock, image, description, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([
            'Office Chair',
            'SKU-1003',
            'BAR-1003',
            'Furniture',
            'BrandZ',
            75.00,
            120.00,
            5,
            2,
            'chair.jpg',
            'Comfortable office chair',
            'Active',
            1
        ]);
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        flash('error', 'Please log in to access this page.');
        redirect(url('login'));
    }
}

function requireRole(array $roles): void {
    requireLogin();
    $user = currentUser();
    if (!in_array($user['role'], $roles, true)) {
        flash('error', 'You are not authorized to access this area.');
        redirect(url('dashboard'));
    }
}

initializeDatabase();
