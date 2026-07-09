<?php
require_once base_path('app/Controllers/Controller.php');
require_once base_path('app/Models/Product.php');
require_once base_path('app/Models/Sale.php');

class DashboardController extends Controller {
    public function index(): void {
        $this->requireLogin();
        $productModel = new Product();
        $saleModel = new Sale();

        $today = $saleModel->summary('today');
        $week = $saleModel->summary('week');
        $month = $saleModel->summary('month');
        $totals = $saleModel->totals();
        $recentSales = $saleModel->recent(5);
        $topProducts = $productModel->topSelling(5);
        $lowStock = $productModel->lowStock();
        $outOfStock = $productModel->outOfStock();
        $totalProducts = (int)$productModel->countAll();
        $this->render('dashboard/index', [
            'today' => $today,
            'week' => $week,
            'month' => $month,
            'totals' => $totals,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'totalProducts' => $totalProducts,
        ]);
    }
}
