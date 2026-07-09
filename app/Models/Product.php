<?php
require_once base_path('app/Models/Model.php');

class Product extends Model {
    public function findAll(array $filters = [], int $limit = 10, int $offset = 0): array {
        $sql = 'SELECT * FROM products WHERE deleted_at IS NULL';
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE :search OR sku LIKE :search OR barcode LIKE :search OR category LIKE :search OR brand LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND category = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }
        if (isset($filters['stock_min']) && $filters['stock_min'] !== '') {
            $sql .= ' AND stock <= :stock_min';
            $params['stock_min'] = (int)$filters['stock_min'];
        }

        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
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
        $sql = 'SELECT COUNT(*) FROM products WHERE deleted_at IS NULL';
        $params = [];
        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE :search OR sku LIKE :search OR barcode LIKE :search OR category LIKE :search OR brand LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category'])) {
            $sql .= ' AND category = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }
        if (isset($filters['stock_min']) && $filters['stock_min'] !== '') {
            $sql .= ' AND stock <= :stock_min';
            $params['stock_min'] = (int)$filters['stock_min'];
        }
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function findBySku(string $sku, ?int $excludeId = null): ?array {
        $sql = 'SELECT * FROM products WHERE sku = :sku';
        $params = ['sku' => $sku];
        if ($excludeId) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function findByBarcode(string $barcode, ?int $excludeId = null): ?array {
        $sql = 'SELECT * FROM products WHERE barcode = :barcode';
        $params = ['barcode' => $barcode];
        if ($excludeId) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare('INSERT INTO products (name, sku, barcode, category, brand, buying_price, selling_price, stock, minimum_stock, image, description, status, created_by) VALUES (:name, :sku, :barcode, :category, :brand, :buying_price, :selling_price, :stock, :minimum_stock, :image, :description, :status, :created_by)');
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = $key . ' = :' . $key;
        }
        $stmt = $this->db->prepare('UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $data['id'] = $id;
        $stmt->execute($data);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('UPDATE products SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function restore(int $id): void {
        $stmt = $this->db->prepare('UPDATE products SET deleted_at = NULL WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function deductStock(int $id, int $quantity): bool {
        $product = $this->findById($id);
        if (!$product || $product['stock'] < $quantity) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :id');
        $stmt->execute(['quantity' => $quantity, 'id' => $id]);
        return true;
    }

    public function lowStock(): array {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE deleted_at IS NULL AND stock <= minimum_stock ORDER BY stock ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function outOfStock(): array {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE deleted_at IS NULL AND stock <= 0 ORDER BY stock ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function categories(): array {
        $stmt = $this->db->query('SELECT DISTINCT category FROM products WHERE deleted_at IS NULL ORDER BY category');
        return array_column($stmt->fetchAll(), 'category');
    }

    public function topSelling(int $limit = 5): array {
        $stmt = $this->db->prepare('SELECT p.name, p.sku, SUM(si.quantity) as total_sold FROM sale_items si JOIN products p ON p.id = si.product_id GROUP BY si.product_id ORDER BY total_sold DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
