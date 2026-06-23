<?php
namespace App\Models;

use App\Core\Model;

class Review extends Model
{
    protected string $table = 'product_reviews';

    public function getProductReviews(int $productId, int $page = 1, int $perPage = 10): array
    {
        $total = (int)($this->db->fetch(
            'SELECT COUNT(*) as cnt FROM product_reviews WHERE product_id = ? AND status = "approved"',
            [$productId]
        )['cnt'] ?? 0);
        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            'SELECT r.*, u.first_name, u.last_name, u.avatar FROM product_reviews r
             JOIN users u ON r.user_id = u.id
             WHERE r.product_id = ? AND r.status = "approved"
             ORDER BY r.created_at DESC LIMIT ? OFFSET ?',
            [$productId, $perPage, $offset]
        );
        return ['items' => $items, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'total_pages' => (int)ceil($total / $perPage)];
    }

    public function getRatingDistribution(int $productId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT rating, COUNT(*) as cnt FROM product_reviews WHERE product_id = ? AND status = "approved" GROUP BY rating',
            [$productId]
        );
        $dist = array_fill(1, 5, 0);
        foreach ($rows as $r) $dist[(int)$r['rating']] = (int)$r['cnt'];
        return $dist;
    }

    public function getPending(int $page = 1, int $perPage = 20): array
    {
        $total = (int)($this->db->fetch('SELECT COUNT(*) as cnt FROM product_reviews WHERE status = "pending"', [])['cnt'] ?? 0);
        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            'SELECT r.*, u.first_name, u.last_name, p.name AS product_name FROM product_reviews r
             JOIN users u ON r.user_id = u.id
             JOIN products p ON r.product_id = p.id
             WHERE r.status = "pending" ORDER BY r.created_at DESC LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );
        return ['items' => $items, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'total_pages' => (int)ceil($total / $perPage)];
    }
}
