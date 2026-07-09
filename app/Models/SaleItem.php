<?php
require_once base_path('app/Models/Model.php');

class SaleItem extends Model {
    public function create(array $data): int {
        $stmt = $this->db->prepare('INSERT INTO sale_items (sale_id, product_id, product_name, quantity, unit_price, buying_price, profit, total) VALUES (:sale_id, :product_id, :product_name, :quantity, :unit_price, :buying_price, :profit, :total)');
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function findBySale(int $saleId): array {
        $stmt = $this->db->prepare('SELECT * FROM sale_items WHERE sale_id = :sale_id ORDER BY id ASC');
        $stmt->execute(['sale_id' => $saleId]);
        return $stmt->fetchAll();
    }
}
