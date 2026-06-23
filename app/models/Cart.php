<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Auth;
use App\Core\Session;

class Cart extends Model
{
    protected string $table = 'carts';

    public function getOrCreate(): array
    {
        $userId = Auth::id();
        $sessionId = Session::id();

        if ($userId) {
            $cart = $this->db->fetch('SELECT * FROM carts WHERE user_id = ?', [$userId]);
        } else {
            $cart = $this->db->fetch('SELECT * FROM carts WHERE session_id = ? AND user_id IS NULL', [$sessionId]);
        }

        if (!$cart) {
            $id = $this->db->insert('carts', [
                'user_id'    => $userId,
                'session_id' => $sessionId,
                'expires_at' => $userId ? null : date('Y-m-d H:i:s', strtotime('+30 days')),
            ]);
            $cart = $this->find($id);
        }
        return $cart ?? throw new \RuntimeException('Failed to create cart');
    }

    public function getCartWithItems(?int $cartId = null): array
    {
        $cart = $cartId ? $this->find($cartId) : $this->getOrCreate();
        if (!$cart) return ['items' => [], 'subtotal' => 0, 'discount' => 0, 'shipping' => 0, 'tax' => 0, 'total' => 0, 'count' => 0, 'coupon_id' => null];

        $items = $this->db->fetchAll(
            'SELECT ci.*, p.name, p.slug, p.stock_quantity, p.allow_backorder, p.price AS base_price,
                    pv.variant_name, pv.stock_quantity AS variant_stock,
                    pi.image_path AS product_image
             FROM cart_items ci
             JOIN products p ON ci.product_id = p.id
             LEFT JOIN product_variants pv ON ci.variant_id = pv.id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE ci.cart_id = ?',
            [$cart['id']]
        );

        // Resolve variant option labels for new variant system
        foreach ($items as &$item) {
            if (!empty($item['variant_option_ids'])) {
                $optIds = json_decode($item['variant_option_ids'], true) ?? [];
                if ($optIds) {
                    $placeholders = implode(',', array_fill(0, count($optIds), '?'));
                    $opts = $this->db->fetchAll(
                        "SELECT vto.option_label, vt.label as type_label
                         FROM variant_type_options vto
                         JOIN product_variant_types vt ON vto.variant_type_id = vt.id
                         WHERE vto.id IN ($placeholders) ORDER BY vt.position",
                        $optIds
                    );
                    if ($opts) {
                        $item['variant_name'] = implode(', ', array_map(
                            fn($o) => $o['type_label'] . ': ' . $o['option_label'], $opts
                        ));
                    }
                }
            }
        }
        unset($item);

        $subtotal = array_sum(array_map(fn($i) => $i['unit_price'] * $i['quantity'], $items));
        $discount = (float)($cart['discount_amount'] ?? 0);
        $shipping = 0;
        $tax = round(($subtotal - $discount) * 0.05, 2);
        $total = $subtotal - $discount + $shipping + $tax;

        return [
            'id'       => $cart['id'],
            'items'    => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'tax'      => $tax,
            'total'    => max(0, $total),
            'count'    => array_sum(array_column($items, 'quantity')),
            'coupon_id'=> $cart['coupon_id'],
        ];
    }

    public function addItem(int $cartId, int $productId, ?int $variantId, int $qty, float $price, string $flavourNames = '', ?string $variantOptionIds = null, ?int $combinationId = null): void
    {
        $flavour = $flavourNames ?: null;
        $existing = $this->db->fetch(
            'SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?
             AND (combination_id = ? OR (combination_id IS NULL AND ? IS NULL))
             AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))
             AND (selected_flavours = ? OR (selected_flavours IS NULL AND ? IS NULL))',
            [$cartId, $productId, $combinationId, $combinationId, $variantId, $variantId, $flavour, $flavour]
        );
        if ($existing) {
            $this->db->update('cart_items', ['quantity' => $existing['quantity'] + $qty], 'id = ?', [$existing['id']]);
        } else {
            $this->db->insert('cart_items', [
                'cart_id'           => $cartId,
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'combination_id'    => $combinationId,
                'quantity'          => $qty,
                'unit_price'        => $price,
                'selected_flavours' => $flavour,
            ]);
        }
    }

    public function updateItem(int $itemId, int $cartId, int $qty): void
    {
        if ($qty <= 0) {
            $this->db->delete('cart_items', 'id = ? AND cart_id = ?', [$itemId, $cartId]);
        } else {
            $this->db->update('cart_items', ['quantity' => $qty], 'id = ? AND cart_id = ?', [$itemId, $cartId]);
        }
    }

    public function removeItem(int $itemId, int $cartId): void
    {
        $this->db->delete('cart_items', 'id = ? AND cart_id = ?', [$itemId, $cartId]);
    }

    public function clearCart(int $cartId): void
    {
        $this->db->delete('cart_items', 'cart_id = ?', [$cartId]);
        $this->db->update('carts', ['coupon_id' => null, 'discount_amount' => 0], 'id = ?', [$cartId]);
    }

    public function applyCoupon(int $cartId, array $coupon, float $subtotal): float
    {
        $discount = 0;
        if ($coupon['type'] === 'percentage') {
            $discount = $subtotal * ($coupon['value'] / 100);
            if ($coupon['max_discount_amount']) $discount = min($discount, $coupon['max_discount_amount']);
        } elseif ($coupon['type'] === 'fixed_amount') {
            $discount = min($coupon['value'], $subtotal);
        }
        $this->db->update('carts', ['coupon_id' => $coupon['id'], 'discount_amount' => $discount], 'id = ?', [$cartId]);
        return $discount;
    }

    public function mergeGuestCart(int $userId): void
    {
        $sessionId = Session::id();
        $guestCart = $this->db->fetch('SELECT * FROM carts WHERE session_id = ? AND user_id IS NULL', [$sessionId]);
        if (!$guestCart) return;

        $userCart = $this->db->fetch('SELECT * FROM carts WHERE user_id = ?', [$userId]);
        if (!$userCart) {
            $this->db->update('carts', ['user_id' => $userId], 'id = ?', [$guestCart['id']]);
            return;
        }

        $guestItems = $this->db->fetchAll('SELECT * FROM cart_items WHERE cart_id = ?', [$guestCart['id']]);
        foreach ($guestItems as $item) {
            $this->addItem($userCart['id'], $item['product_id'], $item['variant_id'], $item['quantity'], $item['unit_price']);
        }
        $this->db->delete('carts', 'id = ?', [$guestCart['id']]);
    }
}
