<?php
require_once base_path('app/Controllers/Controller.php');
require_once base_path('app/Models/Product.php');
require_once base_path('app/Models/Sale.php');

class ReportController extends Controller {
    public function index(): void {
        $this->requireRole(['admin', 'cashier']);
        $type = $_GET['type'] ?? 'daily';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $search = sanitize($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        if (!empty($_GET['export'])) {
            $this->export($type, $dateFrom, $dateTo, $search);
        }

        $report = $this->buildReport($type, $dateFrom, $dateTo, $search, $page, $perPage);
        $this->render('reports/index', [
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
            'report' => $report,
            'pagination' => $report['pagination'] ?? ['page' => 1, 'pages' => 1, 'perPage' => $perPage],
        ]);
    }

    private function buildReport(string $type, string $dateFrom, string $dateTo, string $search, int $page, int $perPage): array {
        $productModel = new Product();
        $saleModel = new Sale();
        $offset = ($page - 1) * $perPage;

        switch ($type) {
            case 'weekly':
                $rows = $saleModel->byPeriod('weekly', $dateFrom, $dateTo);
                $totals = $this->sumRows($rows);
                return ['type' => $type, 'rows' => $rows, 'totals' => $totals, 'pagination' => $this->pagination(count($rows), $page, $perPage)];
            case 'monthly':
                $rows = $saleModel->byPeriod('monthly', $dateFrom, $dateTo);
                $totals = $this->sumRows($rows);
                return ['type' => $type, 'rows' => $rows, 'totals' => $totals, 'pagination' => $this->pagination(count($rows), $page, $perPage)];
            case 'yearly':
                $rows = $saleModel->byPeriod('yearly', $dateFrom, $dateTo);
                $totals = $this->sumRows($rows);
                return ['type' => $type, 'rows' => $rows, 'totals' => $totals, 'pagination' => $this->pagination(count($rows), $page, $perPage)];
            case 'revenue':
                $rows = $saleModel->byPeriod('daily', $dateFrom, $dateTo);
                return ['type' => $type, 'rows' => $rows, 'totals' => $this->sumRows($rows), 'pagination' => $this->pagination(count($rows), $page, $perPage)];
            case 'profit':
                $rows = $saleModel->byPeriod('daily', $dateFrom, $dateTo);
                return ['type' => $type, 'rows' => $rows, 'totals' => $this->sumRows($rows), 'pagination' => $this->pagination(count($rows), $page, $perPage)];
            case 'inventory':
                $filters = ['search' => $search];
                $total = $productModel->countAll($filters);
                $products = $productModel->findAll($filters, $perPage, $offset);
                return ['type' => $type, 'products' => $products, 'pagination' => $this->pagination($total, $page, $perPage)];
            case 'product-sales':
                $stmt = getDb()->prepare('SELECT p.name, p.sku, SUM(si.quantity) as total_sold, SUM(si.total) as revenue FROM sale_items si JOIN products p ON p.id = si.product_id WHERE p.name LIKE :search OR p.sku LIKE :search GROUP BY si.product_id ORDER BY total_sold DESC LIMIT :limit OFFSET :offset');
                $searchLike = '%' . $search . '%';
                $stmt->bindValue(':search', $searchLike);
                $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                $countStmt = getDb()->prepare('SELECT COUNT(*) FROM (SELECT si.product_id FROM sale_items si JOIN products p ON p.id = si.product_id WHERE p.name LIKE :search OR p.sku LIKE :search GROUP BY si.product_id)');
                $countStmt->bindValue(':search', $searchLike);
                $countStmt->execute();
                $total = (int)$countStmt->fetchColumn();
                return ['type' => $type, 'rows' => $rows, 'pagination' => $this->pagination($total, $page, $perPage)];
            case 'top-selling':
                $rows = $productModel->topSelling(10);
                return ['type' => $type, 'rows' => $rows];
            case 'low-stock':
                $rows = $productModel->lowStock();
                return ['type' => $type, 'rows' => $rows];
            case 'out-of-stock':
                $rows = $productModel->outOfStock();
                return ['type' => $type, 'rows' => $rows];
            case 'daily':
            default:
                $rows = $saleModel->byPeriod('daily', $dateFrom, $dateTo);
                $totals = $this->sumRows($rows);
                $bestSelling = $this->bestSellingProduct($dateFrom, $dateTo);
                return ['type' => $type, 'rows' => $rows, 'totals' => $totals, 'bestSelling' => $bestSelling, 'pagination' => $this->pagination(count($rows), $page, $perPage)];
        }
    }

    private function sumRows(array $rows): array {
        return [
            'sales_count' => array_sum(array_column($rows, 'sales_count')),
            'revenue' => array_sum(array_map(fn($row) => (float)($row['revenue'] ?? 0), $rows)),
            'profit' => array_sum(array_map(fn($row) => (float)($row['profit'] ?? 0), $rows)),
        ];
    }

    private function bestSellingProduct(string $dateFrom, string $dateTo): array {
        $sql = 'SELECT p.name, SUM(si.quantity) as total_sold FROM sale_items si JOIN products p ON p.id = si.product_id JOIN sales s ON s.id = si.sale_id WHERE 1=1';
        $params = [];
        if ($dateFrom) {
            $sql .= ' AND s.created_at >= :date_from';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= ' AND s.created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }
        $sql .= ' GROUP BY si.product_id ORDER BY total_sold DESC LIMIT 1';
        $stmt = getDb()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $stmt->fetch() ?: ['name' => 'N/A', 'total_sold' => 0];
    }

    private function buildPdfHtml(array $report, string $type, string $dateFrom, string $dateTo, string $search): string {
        $title = htmlspecialchars(ucfirst($type) . ' Report');
        $period = htmlspecialchars(trim($dateFrom . ' - ' . $dateTo));
        $summaryRows = [
            ['Sales Count', (int)($report['totals']['sales_count'] ?? 0)],
            ['Revenue', formatCurrency((float)($report['totals']['revenue'] ?? 0))],
            ['Profit', formatCurrency((float)($report['totals']['profit'] ?? 0))],
        ];

        $detailRows = '';
        if (!empty($report['rows'])) {
            foreach ($report['rows'] as $row) {
                $periodLabel = htmlspecialchars($row['day'] ?? $row['name'] ?? 'N/A');
                $detailRows .= '<tr><td>' . $periodLabel . '</td><td>' . (int)($row['sales_count'] ?? 0) . '</td><td>' . formatCurrency((float)($row['revenue'] ?? 0)) . '</td><td>' . formatCurrency((float)($row['profit'] ?? 0)) . '</td></tr>';
            }
        } elseif (!empty($report['products'])) {
            foreach ($report['products'] as $product) {
                $detailRows .= '<tr><td>' . htmlspecialchars($product['name'] ?? 'N/A') . '</td><td>' . htmlspecialchars($product['sku'] ?? 'N/A') . '</td><td>' . (int)($product['stock'] ?? 0) . '</td><td>' . htmlspecialchars($product['status'] ?? 'N/A') . '</td></tr>';
            }
        }

        $detailTable = !empty($detailRows)
            ? '<h3>Details</h3><table><thead><tr><th>Period</th><th>Sales</th><th>Revenue</th><th>Profit</th></tr></thead><tbody>' . $detailRows . '</tbody></table>'
            : '';

        $html = '<!doctype html><html><head><meta charset="utf-8"><title>' . $title . '</title>';
        $html .= '<style>body{font-family:Arial,Helvetica,sans-serif;padding:24px;color:#111827;}h1{margin-bottom:8px;}p{margin:4px 0 16px;}table{width:100%;border-collapse:collapse;margin-bottom:18px;}th,td{border:1px solid #d1d5db;padding:8px;text-align:left;}th{background:#f3f4f6;}</style>';
        $html .= '</head><body><h1>' . $title . '</h1><p><strong>Period:</strong> ' . $period . '</p><h3>Summary</h3><table><thead><tr><th>Metric</th><th>Value</th></tr></thead><tbody>';
        foreach ($summaryRows as [$metric, $value]) {
            $html .= '<tr><td>' . htmlspecialchars($metric) . '</td><td>' . htmlspecialchars((string)$value) . '</td></tr>';
        }
        $html .= '</tbody></table>' . $detailTable . '</body></html>';
        return $html;
    }

    private function export(string $type, string $dateFrom, string $dateTo, string $search): void {
        $report = $this->buildReport($type, $dateFrom, $dateTo, $search, 1, 1000);
        if ($_GET['export'] === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $type . '_report.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Report Type', 'Type']);
            fputcsv($out, [$type, '']);
            fputcsv($out, ['Summary', '']);
            fputcsv($out, ['Sales Count', $report['totals']['sales_count'] ?? 0]);
            fputcsv($out, ['Revenue', $report['totals']['revenue'] ?? 0]);
            fputcsv($out, ['Profit', $report['totals']['profit'] ?? 0]);
            fclose($out);
            exit;
        }

        if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
            $html = $this->buildPdfHtml($report, $type, $dateFrom, $dateTo, $search);
            if (!class_exists(\Dompdf\Dompdf::class)) {
                header('Content-Type: text/html; charset=utf-8');
                header('Content-Disposition: inline; filename=' . $type . '_report.html');
                echo $html;
                exit;
            }

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream($type . '_report.pdf', ['Attachment' => false]);
            exit;
        }

        // Default: simple HTML
        header('Content-Type: text/html; charset=utf-8');
        echo '<html><body><h1>' . ucfirst($type) . ' Report</h1><table border="1" cellspacing="0" cellpadding="4">';
        echo '<tr><th>Metric</th><th>Value</th></tr>';
        echo '<tr><td>Sales Count</td><td>' . ($report['totals']['sales_count'] ?? 0) . '</td></tr>';
        echo '<tr><td>Revenue</td><td>' . ($report['totals']['revenue'] ?? 0) . '</td></tr>';
        echo '<tr><td>Profit</td><td>' . ($report['totals']['profit'] ?? 0) . '</td></tr>';
        echo '</table></body></html>';
        exit;
    }
}
