<?php
require_once __DIR__ . '/../config/bootstrap.php';

require_once base_path('app/Controllers/Controller.php');
require_once base_path('app/Controllers/AuthController.php');
require_once base_path('app/Controllers/DashboardController.php');
require_once base_path('app/Controllers/ProductController.php');
require_once base_path('app/Controllers/SaleController.php');
require_once base_path('app/Controllers/ReportController.php');

$action = $_GET['action'] ?? 'dashboard';

$routes = [
    'dashboard' => ['DashboardController', 'index'],
    'login' => ['AuthController', 'loginForm'],
    'do-login' => ['AuthController', 'login'],
    'logout' => ['AuthController', 'logout'],
    'products' => ['ProductController', 'index'],
    'products/form' => ['ProductController', 'form'],
    'products/save' => ['ProductController', 'save'],
    'products/show' => ['ProductController', 'show'],
    'products/delete' => ['ProductController', 'delete'],
    'products/restore' => ['ProductController', 'restore'],
    'sales/pos' => ['SaleController', 'pos'],
    'sales/add-to-cart' => ['SaleController', 'addToCart'],
    'sales/update-cart' => ['SaleController', 'updateCart'],
    'sales/remove-item' => ['SaleController', 'removeItem'],
    'sales/checkout' => ['SaleController', 'checkout'],
    'sales/cancel' => ['SaleController', 'cancel'],
    'sales/hold' => ['SaleController', 'hold'],
    'sales/resume' => ['SaleController', 'resume'],
    'sales/history' => ['SaleController', 'history'],
    'sales/show' => ['SaleController', 'show'],
    'sales/receipt' => ['SaleController', 'receipt'],
    'sales/invoice' => ['SaleController', 'invoice'],
    'reports' => ['ReportController', 'index'],
];

if (!isset($routes[$action])) {
    $action = 'dashboard';
}

[$controllerName, $method] = $routes[$action];
$controller = new $controllerName();
$controller->$method();
