<?php
namespace App\Models;

use App\Core\Model;

class Wishlist extends Model
{
    protected string $table = 'wishlists';

    public function getUserWishlist(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT w.*, p.name, p.slug, p.price, p.compare_at_price, p.stock_quantity,
                    pi.image_path AS product_image, b.name AS brand_name
             FROM wishlists w
             JOIN products p ON w.product_id = p.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE w.user_id = ? ORDER BY w.added_at DESC',
            [$userId]
        );
    }

    public function toggle(int $userId, int $productId, ?int $variantId = null): bool
    {
        $existing = $this->db->fetch(
            'SELECT id FROM wishlists WHERE user_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))',
            [$userId, $productId, $variantId, $variantId]
        );
        if ($existing) {
            $this->db->delete('wishlists', 'id = ?', [$existing['id']]);
            return false; // removed
        }
        $this->db->insert('wishlists', ['user_id' => $userId, 'product_id' => $productId, 'variant_id' => $variantId]);
        return true; // added
    }

    public function isInWishlist(int $userId, int $productId): bool
    {
        return (bool)$this->db->fetch('SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?', [$userId, $productId]);
    }
}
