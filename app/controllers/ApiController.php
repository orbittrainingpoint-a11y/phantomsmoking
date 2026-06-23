<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Wishlist;
use App\Models\Review;
use App\Models\DeliveryZone;
use App\Models\Notification;
use App\Models\Order;

class ApiController extends Controller
{
    private function apiRateLimit(int $max = 60, int $minutes = 1): void
    {
        $ip    = $this->request->ip();
        $key   = 'api_rl_' . md5($ip);
        $ts    = strtotime("-{$minutes} minutes");
        $since = date('Y-m-d H:i:s', $ts !== false ? $ts : time());
        $count = (int)($this->db->fetch(
            'SELECT COUNT(*) as cnt FROM login_attempts WHERE ip_address = ? AND attempted_at >= ?',
            [$ip . '_api', $since]
        )['cnt'] ?? 0);
        if ($count >= $max) {
            http_response_code(429);
            header('Retry-After: ' . ($minutes * 60));
            $this->json(['error' => 'Rate limit exceeded'], 429);
        }
        $this->db->insert('login_attempts', ['ip_address' => $ip . '_api', 'email' => null]);
    }

    public function cartAdd(): void    { (new CartController())->add(); }
    public function cartUpdate(): void { (new CartController())->update(); }
    public function cartRemove(): void { (new CartController())->remove(); }
    public function cartEditItem(): void { (new CartController())->editItem(); }

    public function cartGet(): void
    {
        $cart = (new Cart())->getCartWithItems();
        $this->json(['success' => true, 'cart' => $cart]);
    }

    public function cartCoupon(): void
    {
        $data  = $this->request->json();
        $code  = strtoupper(trim($data['code'] ?? ''));
        $cartModel = new Cart();
        $cart  = $cartModel->getOrCreate();
        $cartData = $cartModel->getCartWithItems($cart['id']);
        $result = (new Coupon())->validate($code, $cartData['subtotal'], Auth::id());
        if (!$result['valid']) { $this->json(['success' => false, 'error' => $result['error']], 400); }
        $discount = $cartModel->applyCoupon($cart['id'], $result['coupon'], $cartData['subtotal']);
        $this->json(['success' => true, 'discount' => format_price($discount), 'message' => 'Coupon applied!']);
    }

    public function cartCouponRemove(): void
    {
        $cart = (new Cart())->getOrCreate();
        $this->db->update('carts', ['coupon_id' => null, 'discount_amount' => 0], 'id = ?', [$cart['id']]);
        $this->json(['success' => true]);
    }

    public function productSearch(): void
    {
        $this->apiRateLimit(30, 1);
        $q     = sanitize_string($this->request->get('q', ''));
        $limit = min(8, (int)$this->request->get('limit', 8));
        if (strlen($q) < 2) { $this->json(['results' => []]); }
        $result = (new Product())->searchProducts($q, [], 1, $limit);
        $this->json(['results' => $result['items']]);
    }

    // ── GET /api/products/:id/variations ─────────────────────────────────────
    // Returns variation types with their options + all combinations for a product
    public function productVariations(string $id): void
    {
        $pid = (int)$id;

        // Verify product exists
        if (!$this->db->fetch('SELECT id FROM products WHERE id = ?', [$pid])) {
            $this->json(['success' => false, 'error' => 'Product not found'], 404);
        }

        // Load types ordered by display_order
        $types = $this->db->fetchAll(
            'SELECT id, type_name, display_order
             FROM product_variation_types
             WHERE product_id = ?
             ORDER BY display_order ASC',
            [$pid]
        );

        // Attach options to each type
        foreach ($types as &$t) {
            $t['options'] = $this->db->fetchAll(
                'SELECT id, option_value, display_order
                 FROM product_variation_options
                 WHERE variation_type_id = ?
                 ORDER BY display_order ASC',
                [$t['id']]
            );
        }
        unset($t);

        // Load all active combinations with resolved option values
        $combinations = $this->db->fetchAll(
            'SELECT
                c.id, c.sku, c.price, c.stock, c.is_active,
                c.option_id_level1, c.option_id_level2, c.option_id_level3,
                c.option_id_level4, c.option_id_level5,
                o1.option_value AS val_level1,
                o2.option_value AS val_level2,
                o3.option_value AS val_level3,
                o4.option_value AS val_level4,
                o5.option_value AS val_level5
             FROM product_variation_combinations c
             LEFT JOIN product_variation_options o1 ON c.option_id_level1 = o1.id
             LEFT JOIN product_variation_options o2 ON c.option_id_level2 = o2.id
             LEFT JOIN product_variation_options o3 ON c.option_id_level3 = o3.id
             LEFT JOIN product_variation_options o4 ON c.option_id_level4 = o4.id
             LEFT JOIN product_variation_options o5 ON c.option_id_level5 = o5.id
             WHERE c.product_id = ? AND c.is_active = 1
             ORDER BY c.id ASC',
            [$pid]
        );

        $this->json([
            'success'         => true,
            'variation_types' => $types,
            'combinations'    => $combinations,
        ]);
    }

    // ── POST /api/products/:id/variations ─────────────────────────────────────
    // Replaces all variation data for a product (admin only)
    // Body: { variation_types: [{type_name, options:[{option_value}]}], combinations: [{options:[val1,val2,...], price, stock, sku}] }
    public function productVariationsSave(string $id): void
    {
        $this->requireAdmin();
        $pid  = (int)$id;
        $data = $this->request->json();

        // Validate product exists
        if (!$this->db->fetch('SELECT id FROM products WHERE id = ?', [$pid])) {
            $this->json(['success' => false, 'error' => 'Product not found'], 404);
        }

        $types  = $data['variation_types'] ?? [];
        $combos = $data['combinations']    ?? [];

        // Validate: at least one type with at least one option
        if (empty($types)) {
            $this->json(['success' => false, 'error' => 'At least one variation type is required'], 400);
        }
        foreach ($types as $i => $type) {
            if (empty(trim($type['type_name'] ?? ''))) {
                $this->json(['success' => false, 'error' => "Variation type #" . ($i + 1) . " has no name"], 400);
            }
            if (empty($type['options'])) {
                $this->json(['success' => false, 'error' => "Variation type '{$type['type_name']}' has no options"], 400);
            }
        }

        // Validate: no duplicate combinations (same level values)
        $seen = [];
        foreach ($combos as $i => $combo) {
            $key = implode('|', array_map('strval', $combo['options'] ?? []));
            if (isset($seen[$key])) {
                $this->json(['success' => false, 'error' => 'Duplicate combination found: ' . implode(' + ', $combo['options'] ?? [])], 400);
            }
            $seen[$key] = true;
        }

        // Validate: SKU uniqueness across all products (excluding this product's own combos)
        foreach ($combos as $combo) {
            $sku = trim($combo['sku'] ?? '');
            if ($sku === '') continue;
            $existing = $this->db->fetch(
                'SELECT id FROM product_variation_combinations WHERE sku = ? AND product_id != ?',
                [$sku, $pid]
            );
            if ($existing) {
                $this->json(['success' => false, 'error' => "SKU '{$sku}' is already used by another product"], 400);
            }
        }

        // All valid — save inside a transaction
        $this->db->beginTransaction();
        try {
            // Wipe existing variation data for this product
            // Cascade deletes handle options and combinations automatically
            $this->db->delete('product_variation_types', 'product_id = ?', [$pid]);

            // Insert types and build option value → DB id map
            $typeIdMap = []; // typeIndex => ['tid' => int, 'optMap' => [value => id]]
            foreach ($types as $order => $type) {
                $tid = $this->db->insert('product_variation_types', [
                    'product_id'    => $pid,
                    'type_name'     => sanitize_string($type['type_name']),
                    'display_order' => (int)$order,
                ]);
                $optMap = [];
                foreach ($type['options'] as $oOrder => $opt) {
                    $val = sanitize_string($opt['option_value'] ?? '');
                    if ($val === '') continue;
                    $oid = $this->db->insert('product_variation_options', [
                        'variation_type_id' => $tid,
                        'product_id'        => $pid,
                        'option_value'      => $val,
                        'display_order'     => (int)$oOrder,
                    ]);
                    $optMap[$val] = $oid;
                }
                $typeIdMap[$order] = ['tid' => $tid, 'optMap' => $optMap];
            }

            // Insert combinations
            foreach ($combos as $combo) {
                $options = $combo['options'] ?? []; // array of values, index = level-1
                // Resolve level 1 (required)
                $l1val = $options[0] ?? null;
                if ($l1val === null || !isset($typeIdMap[0]['optMap'][$l1val])) continue;

                $row = [
                    'product_id'       => $pid,
                    'option_id_level1' => $typeIdMap[0]['optMap'][$l1val],
                    'option_id_level2' => isset($options[1], $typeIdMap[1]['optMap'][$options[1]]) ? $typeIdMap[1]['optMap'][$options[1]] : null,
                    'option_id_level3' => isset($options[2], $typeIdMap[2]['optMap'][$options[2]]) ? $typeIdMap[2]['optMap'][$options[2]] : null,
                    'option_id_level4' => isset($options[3], $typeIdMap[3]['optMap'][$options[3]]) ? $typeIdMap[3]['optMap'][$options[3]] : null,
                    'option_id_level5' => isset($options[4], $typeIdMap[4]['optMap'][$options[4]]) ? $typeIdMap[4]['optMap'][$options[4]] : null,
                    'price'            => (float)($combo['price'] ?? 0),
                    'stock'            => (int)($combo['stock']   ?? 0),
                    'sku'              => sanitize_string($combo['sku'] ?? '') ?: null,
                    'is_active'        => 1,
                ];
                $this->db->insert('product_variation_combinations', $row);
            }

            $this->db->commit();
            $this->json(['success' => true, 'message' => 'Variations saved successfully']);
        } catch (\Throwable $e) {
            $this->db->rollback();
            error_log('[productVariationsSave] ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }

    // ── POST /api/products/:id/variations/:combinationId/update ───────────────
    // Updates price, stock, sku for a single combination (admin only)
    // Tunnelled as POST since Hostinger blocks PUT
    public function productVariationCombinationUpdate(string $id, string $combinationId): void
    {
        $this->requireAdmin();
        $pid   = (int)$id;
        $cid   = (int)$combinationId;
        $data  = $this->request->json();

        // Verify combination belongs to this product
        $combo = $this->db->fetch(
            'SELECT id, sku FROM product_variation_combinations WHERE id = ? AND product_id = ?',
            [$cid, $pid]
        );
        if (!$combo) {
            $this->json(['success' => false, 'error' => 'Combination not found'], 404);
        }

        $update = [];

        // Price
        if (isset($data['price'])) {
            $price = (float)$data['price'];
            if ($price < 0) {
                $this->json(['success' => false, 'error' => 'Price cannot be negative'], 400);
            }
            $update['price'] = $price;
        }

        // Stock
        if (isset($data['stock'])) {
            $stock = (int)$data['stock'];
            if ($stock < 0) {
                $this->json(['success' => false, 'error' => 'Stock cannot be negative'], 400);
            }
            $update['stock'] = $stock;
        }

        // SKU — must be unique across all products
        if (isset($data['sku'])) {
            $sku = sanitize_string(trim($data['sku']));
            if ($sku !== '' && $sku !== $combo['sku']) {
                $conflict = $this->db->fetch(
                    'SELECT id FROM product_variation_combinations WHERE sku = ? AND id != ?',
                    [$sku, $cid]
                );
                if ($conflict) {
                    $this->json(['success' => false, 'error' => "SKU '{$sku}' is already in use"], 400);
                }
            }
            $update['sku'] = $sku ?: null;
        }

        if (empty($update)) {
            $this->json(['success' => false, 'error' => 'Nothing to update — send price, stock, or sku'], 400);
        }

        $this->db->update('product_variation_combinations', $update, 'id = ? AND product_id = ?', [$cid, $pid]);
        $this->json(['success' => true, 'message' => 'Combination updated']);
    }

    public function productVariants(string $id): void
    {
        $variants = $this->db->fetchAll(
            'SELECT pv.id, pv.variant_name, pv.price, pv.stock_quantity,
                    pvo.option_name, pvo.option_value
             FROM product_variants pv
             LEFT JOIN product_variant_options pvo ON pvo.variant_id = pv.id
             WHERE pv.product_id = ? AND pv.is_active = 1
             ORDER BY pvo.option_name, pv.position',
            [(int)$id]
        );
        $grouped = [];
        foreach ($variants as $v) {
            $type = $v['option_name'] ?? 'Option';
            if (!isset($grouped[$type])) $grouped[$type] = ['type' => $type, 'options' => []];
            $grouped[$type]['options'][] = [
                'id'    => $v['id'],
                'label' => $v['option_value'] ?? $v['variant_name'],
                'price' => (float)$v['price'],
                'stock' => (int)$v['stock_quantity'],
            ];
        }
        $this->json(['variants' => array_values($grouped)]);
    }
public function productFlavours(string $id): void
    {
        try {
            $flavours = $this->db->fetchAll(
                'SELECT f.id, f.name, f.category FROM flavours f
                 JOIN product_flavours pf ON f.id = pf.flavour_id
                 WHERE pf.product_id = ? AND f.is_active = 1
                 ORDER BY f.name',
                [(int)$id]
            );
        } catch (\Throwable $e) {
            // Table may not exist yet — return empty
            $flavours = [];
        }
        $this->json(['flavours' => $flavours]);
    }

    public function wishlistToggle(): void
    {
        if (!Auth::check()) { $this->json(['success' => false, 'error' => 'Login required'], 401); }
        $data      = $this->request->json();
        $productId = (int)($data['product_id'] ?? 0);
        $variantId = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;
        $added     = (new Wishlist())->toggle((int)Auth::id(), $productId, $variantId);
        $this->json(['success' => true, 'added' => $added]);
    }

    public function wishlistGet(): void
    {
        if (!Auth::check()) { $this->json(['items' => []]); }
        $items = (new Wishlist())->getUserWishlist((int)Auth::id());
        $this->json(['items' => $items]);
    }

    public function reviewSubmit(): void
    {
        if (!Auth::check()) { $this->json(['success' => false, 'error' => 'Login required'], 401); }
        $data = $this->request->json();
        $errors = [];
        if (empty($data['product_id'])) $errors[] = 'Product required';
        if (empty($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) $errors[] = 'Rating 1-5 required';
        if (!empty($errors)) { $this->json(['success' => false, 'errors' => $errors], 400); }

        $productId = (int)$data['product_id'];
        $userId    = (int)Auth::id();

        // One review per product per account
        $existing = $this->db->fetch(
            'SELECT id, status FROM product_reviews WHERE product_id = ? AND user_id = ?',
            [$productId, $userId]
        );
        if ($existing) {
            $msg = $existing['status'] === 'pending'
                ? 'You already submitted a review for this product. It is pending approval.'
                : 'You have already reviewed this product.';
            $this->json(['success' => false, 'error' => $msg], 409);
        }

        $this->db->insert('product_reviews', [
            'product_id' => $productId,
            'user_id'    => $userId,
            'rating'     => (int)$data['rating'],
            'title'      => sanitize_string($data['title'] ?? ''),
            'body'       => sanitize_string($data['body'] ?? ''),
            'status'     => 'pending',
        ]);
        $this->json(['success' => true, 'message' => 'Review submitted for approval.']);
    }

    public function reviewHelpful(string $id): void
    {
        $this->db->query('UPDATE product_reviews SET helpful_count = helpful_count + 1 WHERE id = ?', [(int)$id]);
        $this->json(['success' => true]);
    }

    public function deliveryEstimate(): void
    {
        $emirate  = sanitize_string($this->request->get('emirate', 'Dubai'));
        $type     = $this->request->get('type', 'standard');
        $subtotal = (float)$this->request->get('subtotal', 0);
        $info     = (new DeliveryZone())->getShippingInfo($emirate, $type, $subtotal);
        $this->json([
            'fee'       => $info['fee'],
            'label'     => $info['label'],
            'is_free'   => $info['is_free'],
            'eta'       => $info['eta'],
            'remaining' => $info['remaining'],
            'formatted' => $info['is_free'] ? '<span style="color:var(--color-success);font-weight:700">FREE</span>' : 'AED ' . number_format($info['fee'], 2),
        ]);
    }

    public function couponValidate(): void
    {
        $data   = $this->request->json();
        $code   = strtoupper(trim($data['code'] ?? ''));
        $total  = (float)($data['cart_total'] ?? 0);
        $result = (new Coupon())->validate($code, $total, Auth::id());
        $this->json($result);
    }

    public function notifications(): void
    {
        if (!Auth::check()) { $this->json(['count' => 0, 'items' => []]); }
        $notifModel = new Notification();
        $uid = (int)Auth::id();
        $this->json(['count' => $notifModel->getUnreadCount($uid), 'items' => $notifModel->getUserNotifications($uid, 10)]);
    }

    public function notificationsRead(): void
    {
        if (!Auth::check()) { $this->json(['success' => false], 401); }
        $data = $this->request->json();
        (new Notification())->markRead((int)Auth::id(), $data['id'] ?? null);
        $this->json(['success' => true]);
    }

    public function ageVerifyStatus(): void
    {
        $this->json(['verified' => age_verified()]);
    }

    public function newsletterSubscribe(): void
    {
        $this->apiRateLimit(5, 10);
        $data  = $this->request->json();
        $email = strtolower(trim($data['email'] ?? ''));
        if (!validate_email($email)) { $this->json(['success' => false, 'error' => 'Invalid email'], 400); }
        $existing = $this->db->fetch('SELECT id FROM newsletter_subscribers WHERE email = ?', [$email]);
        if (!$existing) $this->db->insert('newsletter_subscribers', ['email' => $email]);
        $this->json(['success' => true, 'message' => 'Subscribed successfully!']);
    }

    // Admin API
    public function adminStats(): void
    {
        $this->requireAdmin();
        $this->json((new Order())->getDashboardStats());
    }

    public function adminOrders(): void
    {
        $this->requireAdmin();
        $page    = max(1, (int)$this->request->get('page', 1));
        $filters = array_filter(['status' => $this->request->get('status'), 'search' => $this->request->get('search')]);
        $this->json((new Order())->getPaginated($page, 20, $filters));
    }

    public function adminOrderStatus(string $id): void
    {
        $this->requireAdmin();
        $data = $this->request->json();
        if (empty($data['status'])) { $data = $this->request->all(); }
        (new Order())->updateStatus((int)$id, $data['status'] ?? '', $data['note'] ?? '', (int)Auth::id());
        $this->json(['success' => true]);
    }

    public function adminProducts(): void
    {
        $this->requireAdmin();
        $page = max(1, (int)$this->request->get('page', 1));
        $search = $this->request->get('search', '');
        $where = '1=1'; $params = [];
        if ($search) { $where .= ' AND (name LIKE ? OR sku LIKE ?)'; $params = ["%$search%", "%$search%"]; }
        $total = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM products WHERE $where", $params)['cnt'] ?? 0);
        $offset = ($page - 1) * 20;
        $items = $this->db->fetchAll("SELECT * FROM products WHERE $where ORDER BY created_at DESC LIMIT 20 OFFSET ?", [...$params, $offset]);
        $this->json(['items' => $items, 'total' => $total]);
    }

    public function adminProductCreate(): void
    {
        $this->requireAdmin();
        $this->json(['success' => true, 'message' => 'Use /admin/products/create form']);
    }

    public function adminProductUpdate(string $id): void
    {
        $this->requireAdmin();
        $data = $this->request->json();
        (new Product())->update((int)$id, $data);
        $this->json(['success' => true]);
    }

    public function adminProductDelete(string $id): void
    {
        $this->requireAdmin();
        (new Product())->update((int)$id, ['status' => 'archived']);
        $this->json(['success' => true, 'action' => 'archived']);
    }

    public function adminProductDestroy(string $id): void
    {
        $this->requireAdmin();
        $pid = (int)$id;
        $images = $this->db->fetchAll('SELECT image_path FROM product_images WHERE product_id = ?', [$pid]);
        foreach ($images as $img) { delete_image($img['image_path']); }
        $this->db->delete('product_images', 'product_id = ?', [$pid]);
        $this->db->delete('product_flavours', 'product_id = ?', [$pid]);
        $this->db->delete('product_variant_types', 'product_id = ?', [$pid]);
        $this->db->delete('product_attributes', 'product_id = ?', [$pid]);
        $this->db->delete('products', 'id = ?', [$pid]);
        $this->json(['success' => true, 'action' => 'deleted']);
    }

    public function adminProductImages(string $id): void
    {
        $this->requireAdmin();
        if (empty($_FILES['image'])) { $this->json(['success' => false, 'error' => 'No file'], 400); }
        $path = save_product_image($_FILES['image'], (int)$id);
        if (!$path) { $this->json(['success' => false, 'error' => 'Upload failed'], 400); }
        $imgId = $this->db->insert('product_images', ['product_id' => (int)$id, 'image_path' => $path, 'is_primary' => 0, 'position' => 99]);
        $this->json(['success' => true, 'image_id' => $imgId, 'path' => $path]);
    }

    public function adminProductImageDelete(string $id): void
    {
        $this->requireAdmin();
        $img = $this->db->fetch('SELECT * FROM product_images WHERE id = ?', [(int)$id]);
        if ($img) {
            delete_image($img['image_path']);
            $this->db->delete('product_images', 'id = ?', [(int)$id]);
        }
        $this->json(['success' => true]);
    }

    public function adminCustomers(): void
    {
        $this->requireAdmin();
        $page = max(1, (int)$this->request->get('page', 1));
        $this->json((new \App\Models\User())->getPaginated($page));
    }

    public function adminCustomerBan(string $id): void
    {
        $this->requireAdmin();
        $data = $this->request->json();
        $user = (new \App\Models\User())->find((int)$id);
        $isActive = $user ? ($user['is_active'] ? 0 : 1) : 0;
        (new \App\Models\User())->update((int)$id, ['is_active' => $isActive, 'banned_reason' => $data['reason'] ?? '']);
        $this->json(['success' => true]);
    }

    public function adminCouponCreate(): void
    {
        $this->requireAdmin();
        $data = $this->request->json();
        $id = $this->db->insert('coupons', [
            'code' => strtoupper($data['code'] ?? ''), 'type' => $data['type'] ?? 'percentage',
            'value' => (float)($data['value'] ?? 0), 'min_order_amount' => (float)($data['min_order_amount'] ?? 0),
            'usage_limit' => $data['usage_limit'] ?? null, 'is_active' => 1,
        ]);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function adminBannersSort(): void
    {
        $this->requireAdmin();
        $data = $this->request->json();
        foreach (($data['ids'] ?? []) as $i => $id) {
            $this->db->update('banners', ['sort_order' => $i], 'id = ?', [(int)$id]);
        }
        $this->json(['success' => true]);
    }

    public function adminReportsSales(): void
    {
        $this->requireAdmin();
        $from  = $this->request->get('from', date('Y-m-01'));
        $to    = $this->request->get('to', date('Y-m-d'));
        $group = $this->request->get('group', 'day');
        $this->json((new Order())->getSalesReport($from, $to, $group));
    }

    public function adminLowStock(): void
    {
        $this->requireAdmin();
        $this->json((new Product())->getLowStockProducts(5));
    }
}
