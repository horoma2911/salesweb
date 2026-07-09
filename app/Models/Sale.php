<?php
require_once base_path('app/Models/Model.php');

class Sale extends Model {
    public function create(array $data): int {
        $stmt = $this->db->prepare('INSERT INTO sales (invoice_number, cashier_id, subtotal, discount, tax, total, revenue, profit, payment_method, status) VALUES (:invoice_number, :cashier_id, :subtotal, :discount, :tax, :total, :revenue, :profit, :payment_method, :status)');
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function findAll(array $filters = [], int $limit = 10, int $offset = 0): array {
        $sql = 'SELECT s.*, u.name as cashier_name FROM sales s LEFT JOIN users u ON u.id = s.cashier_id WHERE 1=1';
        $params = [];
        if (!empty($filters['search'])) {
            $sql .= ' AND (s.invoice_number LIKE :search OR u.name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['cashier_id'])) {
            $sql .= ' AND s.cashier_id = :cashier_id';
            $params['cashier_id'] = (int)$filters['cashier_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND s.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND s.created_at <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        $sql .= ' ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int {
        $sql = 'SELECT COUNT(*) FROM sales s LEFT JOIN users u ON u.id = s.cashier_id WHERE 1=1';
        $params = [];
        if (!empty($filters['search'])) {
            $sql .= ' AND (s.invoice_number LIKE :search OR u.name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['cashier_id'])) {
            $sql .= ' AND s.cashier_id = :cashier_id';
            $params['cashier_id'] = (int)$filters['cashier_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND s.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND s.created_at <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT s.*, u.name as cashier_name FROM sales s LEFT JOIN users u ON u.id = s.cashier_id WHERE s.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $sale = $stmt->fetch();
        return $sale ?: null;
    }

    public function summary(string $period): array {
        $dateClause = match ($period) {
            'today' => "DATE(created_at) = DATE('now')",
            'week' => "strftime('%Y-%W', created_at) = strftime('%Y-%W', 'now')",
            'month' => "strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')",
            'year' => "strftime('%Y', created_at) = strftime('%Y', 'now')",
            default => '1=1',
        };
        $stmt = $this->db->prepare('SELECT COUNT(*) as sales_count, COALESCE(SUM(total),0) as revenue, COALESCE(SUM(profit),0) as profit FROM sales WHERE ' . $dateClause);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function totals(): array {
        $stmt = $this->db->query('SELECT COUNT(*) as sales_count, COALESCE(SUM(total),0) as revenue, COALESCE(SUM(profit),0) as profit FROM sales');
        return $stmt->fetch();
    }

    public function recent(int $limit = 5): array {
        $stmt = $this->db->prepare('SELECT s.*, u.name as cashier_name FROM sales s LEFT JOIN users u ON u.id = s.cashier_id ORDER BY s.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function byPeriod(string $period, ?string $dateFrom = null, ?string $dateTo = null): array {
        $sql = 'SELECT DATE(created_at) as day, COUNT(*) as sales_count, SUM(total) as revenue, SUM(profit) as profit FROM sales WHERE 1=1';
        $params = [];
        if ($dateFrom) {
            $sql .= ' AND created_at >= :date_from';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= ' AND created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }
        $sql .= match ($period) {
            'daily' => ' GROUP BY DATE(created_at) ORDER BY day DESC',
            'weekly' => ' GROUP BY strftime(\'%Y-%W\', created_at) ORDER BY day DESC',
            'monthly' => ' GROUP BY strftime(\'%Y-%m\', created_at) ORDER BY day DESC',
            'yearly' => ' GROUP BY strftime(\'%Y\', created_at) ORDER BY day DESC',
            default => ' GROUP BY DATE(created_at) ORDER BY day DESC',
        };
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
