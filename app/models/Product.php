<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected string $table = 'products';

    public function getProductById(int $id): ?array
    {
        $product = $this->db->fetch(
            'SELECT p.*, b.name AS brand_name, b.slug AS brand_slug, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.id = ? AND p.status = "active"',
            [$id]
        );
        if (!$product) return null;
        $product['images']     = $this->getImages($id);
        $product['variants']   = $this->getProductVariants($id);
        $product['attributes'] = $this->getAttributes($id);
        return $product;
    }

    public function getProductBySlug(string $slug): ?array
    {
        $product = $this->db->fetch(
            'SELECT p.*, b.name AS brand_name, b.slug AS brand_slug, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ? AND p.status = "active"',
            [$slug]
        );
        if (!$product) return null;
        $product['images']     = $this->getImages($product['id']);
        $product['variants']   = $this->getProductVariants($product['id']);
        $product['attributes'] = $this->getAttributes($product['id']);
        return $product;
    }

    public function getProductsByCategory(int $catId, array $filters = [], string $sort = 'featured', int $page = 1, int $perPage = 12): array
    {
        $where  = ['p.status = "active"'];
        $params = [];

        $catIds       = $this->getCategoryWithChildren($catId);
        $placeholders = implode(',', array_fill(0, count($catIds), '?'));
        $where[]      = "p.category_id IN ($placeholders)";
        $params       = array_merge($params, $catIds);

        if (!empty($filters['brand_id']))       { $where[] = 'p.brand_id = ?';              $params[] = $filters['brand_id']; }
        if (!empty($filters['min_price']))       { $where[] = 'p.price >= ?';                $params[] = $filters['min_price']; }
        if (!empty($filters['max_price']))       { $where[] = 'p.price <= ?';                $params[] = $filters['max_price']; }
        if (!empty($filters['in_stock']))        { $where[] = 'p.stock_quantity > 0'; }
        if (!empty($filters['on_sale']))         { $where[] = 'p.compare_at_price > p.price'; }
        if (!empty($filters['new_arrival']))     { $where[] = 'p.is_new_arrival = 1'; }
        if (!empty($filters['nicotine']))        { $where[] = 'p.nicotine_content_mg = ?';   $params[] = $filters['nicotine']; }
        if (!empty($filters['cigar_strength']))  { $where[] = 'p.cigar_strength = ?';        $params[] = $filters['cigar_strength']; }
        if (!empty($filters['rating']))          { $where[] = 'p.average_rating >= ?';       $params[] = $filters['rating']; }

        $orderBy = match ($sort) {
            'price_asc'    => 'p.price ASC',
            'price_desc'   => 'p.price DESC',
            'newest'       => 'p.created_at DESC',
            'best_sellers' => 'p.total_sold DESC',
            'rating'       => 'p.average_rating DESC',
            default        => 'p.is_featured DESC, p.total_sold DESC',
        };

        $whereStr = implode(' AND ', $where);
        $total    = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM products p WHERE $whereStr", $params)['cnt'] ?? 0);
        $offset   = ($page - 1) * $perPage;

        $items = $this->db->fetchAll(
            "SELECT p.*, b.name AS brand_name, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE $whereStr ORDER BY $orderBy LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['items' => $items, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'total_pages' => (int)ceil($total / $perPage)];
    }

    public function getFeaturedProducts(int $limit = 8): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, b.name AS brand_name, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.is_featured = 1 AND p.status = "active"
             ORDER BY p.total_sold DESC LIMIT ?',
            [$limit]
        );
    }

    public function getNewArrivals(int $limit = 8): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, b.name AS brand_name, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.is_new_arrival = 1 AND p.status = "active"
             ORDER BY p.created_at DESC LIMIT ?',
            [$limit]
        );
    }

    public function getBestSellers(int $limit = 8): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, b.name AS brand_name, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.is_best_seller = 1 AND p.status = "active"
             ORDER BY p.total_sold DESC LIMIT ?',
            [$limit]
        );
    }

    public function getOnSaleProducts(int $limit = 8): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, b.name AS brand_name, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.compare_at_price > p.price AND p.status = "active"
             ORDER BY p.created_at DESC LIMIT ?',
            [$limit]
        );
    }

    public function searchProducts(string $query, array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where  = ['p.status = "active"'];
        $params = [];

        if (strlen($query) >= 3) {
            $where[]  = 'MATCH(p.name, p.sku, p.flavor_profile) AGAINST(? IN BOOLEAN MODE)';
            $params[] = '+' . implode('* +', explode(' ', $query)) . '*';
        } else {
            $where[]  = '(p.name LIKE ? OR p.sku LIKE ?)';
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        if (!empty($filters['category_id'])) { $where[] = 'p.category_id = ?'; $params[] = $filters['category_id']; }

        $whereStr = implode(' AND ', $where);
        $total    = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM products p WHERE $whereStr", $params)['cnt'] ?? 0);
        $offset   = ($page - 1) * $perPage;

        $items = $this->db->fetchAll(
            "SELECT p.*, b.name AS brand_name, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE $whereStr ORDER BY p.is_featured DESC, p.total_sold DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['items' => $items, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'total_pages' => (int)ceil($total / $perPage)];
    }

    public function getFilteredProducts(array $filters = [], string $sort = 'newest', int $page = 1, int $perPage = 12): array
    {
        $where  = ['p.status = "active"'];
        $params = [];

        if (!empty($filters['new_arrival'])) { $where[] = 'p.is_new_arrival = 1'; }
        if (!empty($filters['on_sale']))     { $where[] = 'p.compare_at_price > p.price'; }
        if (!empty($filters['best_seller'])) { $where[] = 'p.is_best_seller = 1'; }
        if (!empty($filters['featured']))    { $where[] = 'p.is_featured = 1'; }

        $orderBy = match ($sort) {
            'price_asc'    => 'p.price ASC',
            'price_desc'   => 'p.price DESC',
            'best_sellers' => 'p.total_sold DESC',
            'rating'       => 'p.average_rating DESC',
            default        => 'p.created_at DESC',
        };

        $whereStr = implode(' AND ', $where);
        $total    = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM products p WHERE $whereStr", $params)['cnt'] ?? 0);
        $offset   = ($page - 1) * $perPage;

        $items = $this->db->fetchAll(
            "SELECT p.*, b.name AS brand_name, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE $whereStr ORDER BY $orderBy LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['items' => $items, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'total_pages' => (int)ceil($total / $perPage)];
    }

    public function getRelatedProducts(int $productId, int $limit = 6): array
    {
        $product = $this->find($productId);
        if (!$product) return [];
        return $this->db->fetchAll(
            'SELECT p.*, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.category_id = ? AND p.id != ? AND p.status = "active"
             ORDER BY p.total_sold DESC LIMIT ?',
            [$product['category_id'], $productId, $limit]
        );
    }

    // Returns variants with their options (used by getProductById/Slug)
    public function getProductVariants(int $productId): array
    {
        $variants = $this->db->fetchAll(
            'SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY position',
            [$productId]
        );
        foreach ($variants as &$v) {
            $v['options'] = $this->db->fetchAll(
                'SELECT * FROM product_variant_options WHERE variant_id = ?',
                [$v['id']]
            );
        }
        return $variants;
    }

    // Returns variants grouped by type label — used by admin edit form
    public function getVariantTypes(int $productId): array
    {
        $variants = $this->db->fetchAll(
            'SELECT pv.*, pvo.option_name, pvo.option_value
             FROM product_variants pv
             LEFT JOIN product_variant_options pvo ON pvo.variant_id = pv.id
             WHERE pv.product_id = ? AND pv.is_active = 1
             ORDER BY pv.position',
            [$productId]
        );
        $types = [];
        foreach ($variants as $v) {
            $label = $v['option_name'] ?? 'Option';
            if (!isset($types[$label])) {
                $types[$label] = ['label' => $label, 'options' => []];
            }
            $types[$label]['options'][] = [
                'option_label'   => $v['option_value'] ?? $v['variant_name'],
                'price_override' => $v['price'] > 0 ? $v['price'] : '',
                'price_modifier' => '',
                'sku'            => $v['sku'],
                'stock_qty'      => $v['stock_quantity'],
            ];
        }
        return array_values($types);
    }

    // Saves variant types from admin form into product_variants + product_variant_options
    public function saveVariantTypes(int $productId, array $types): void
    {
        $existing = $this->db->fetchAll('SELECT id FROM product_variants WHERE product_id = ?', [$productId]);
        foreach ($existing as $v) {
            $this->db->delete('product_variant_options', 'variant_id = ?', [$v['id']]);
        }
        $this->db->delete('product_variants', 'product_id = ?', [$productId]);

        $position = 0;
        foreach ($types as $type) {
            $label = sanitize_string($type['label'] ?? '');
            if (empty($label)) continue;
            foreach ($type['options'] ?? [] as $opt) {
                $optLabel = sanitize_string($opt['option_label'] ?? '');
                if (empty($optLabel)) continue;

                $price = ($opt['price_override'] !== '' && $opt['price_override'] !== null)
                    ? (float)$opt['price_override']
                    : 0;

                $sku = strtoupper(sanitize_string($opt['sku'] ?? ''));
                if (empty($sku)) {
                    $sku = strtoupper($productId . '-' . slugify($label) . '-' . slugify($optLabel));
                }
                // Ensure SKU uniqueness
                if ($this->db->fetch('SELECT id FROM product_variants WHERE sku = ?', [$sku])) {
                    $sku .= '-' . $position;
                }

                $variantId = $this->db->insert('product_variants', [
                    'product_id'     => $productId,
                    'variant_name'   => $label . ' — ' . $optLabel,
                    'sku'            => $sku,
                    'price'          => $price,
                    'stock_quantity' => (int)($opt['stock_qty'] ?? 0),
                    'is_active'      => 1,
                    'position'       => $position++,
                ]);
                $this->db->insert('product_variant_options', [
                    'variant_id'   => $variantId,
                    'option_name'  => $label,
                    'option_value' => $optLabel,
                ]);
            }
        }
    }

    public function getImages(int $productId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, position ASC',
            [$productId]
        );
    }

    public function getAttributes(int $productId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM product_attributes WHERE product_id = ? ORDER BY position',
            [$productId]
        );
    }

    public function updateStock(int $productId, ?int $variantId, int $qty, string $operation = 'decrement'): bool
    {
        if ($operation === 'decrement') {
            // Atomic decrement — WHERE clause prevents negative stock (race condition safe)
            $rows = $this->db->query(
                'UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?',
                [$qty, $productId, $qty]
            )->rowCount();
            if ($variantId) {
                $this->db->query(
                    'UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?',
                    [$qty, $variantId, $qty]
                );
            }
            return $rows > 0;
        }
        $this->db->query('UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?', [$qty, $productId]);
        if ($variantId) {
            $this->db->query('UPDATE product_variants SET stock_quantity = stock_quantity + ? WHERE id = ?', [$qty, $variantId]);
        }
        return true;
    }

    public function updateProductRating(int $productId): void
    {
        $result = $this->db->fetch(
            'SELECT AVG(rating) as avg_rating, COUNT(*) as cnt FROM product_reviews WHERE product_id = ? AND status = "approved"',
            [$productId]
        );
        $this->update($productId, [
            'average_rating' => round((float)($result['avg_rating'] ?? 0), 2),
            'review_count'   => (int)($result['cnt'] ?? 0),
        ]);
    }

    public function getLowStockProducts(int $threshold = 5): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, pi.image_path AS primary_image FROM products p
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.stock_quantity <= ? AND p.status = "active" AND p.track_inventory = 1
             ORDER BY p.stock_quantity ASC',
            [$threshold]
        );
    }

    private function getCategoryWithChildren(int $catId): array
    {
        $ids      = [$catId];
        $children = $this->db->fetchAll('SELECT id FROM categories WHERE parent_id = ?', [$catId]);
        foreach ($children as $child) $ids[] = $child['id'];
        return $ids;
    }
}
