<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    private Cart $cartModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
    }

    public function index(): void
    {
        $this->requireAgeVerified();
        $cart   = $this->cartModel->getCartWithItems();
        $upsell = (new Product())->getBestSellers(4);
        $this->render('cart/index', [
            'title'  => 'Your Cart — Phantom Smoking',
            'cart'   => $cart,
            'upsell' => $upsell,
            'csrf'   => $this->csrfToken(),
        ]);
    }

    public function add(): void
    {
        $raw  = file_get_contents('php://input');
        $json = $raw ? (json_decode($raw, true) ?? []) : [];
        $data = !empty($json) ? $json : $this->request->all();

        $productId     = (int)($data['product_id'] ?? 0);
        $combinationId = !empty($data['combination_id']) ? (int)$data['combination_id'] : null;
        $variantId     = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;
        $qty           = max(1, (int)($data['qty'] ?? 1));
        $flavourNames  = isset($data['flavour_names']) ? sanitize_string((string)$data['flavour_names']) : '';

        if ($productId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid product'], 400);
        }

        $product = (new Product())->find($productId);
        if (!$product || $product['status'] !== 'active') {
            $this->json(['success' => false, 'error' => 'Product not available'], 400);
        }

        // Resolve price: combination > variant > base
        $price = $this->resolvePrice($productId, $combinationId, $variantId);

        // Resolve display label from combination
        if ($combinationId && empty($flavourNames)) {
            $combo = $this->db->fetch(
                'SELECT c.*,
                    o1.option_value AS v1, o2.option_value AS v2,
                    o3.option_value AS v3, o4.option_value AS v4, o5.option_value AS v5
                 FROM product_variation_combinations c
                 LEFT JOIN product_variation_options o1 ON c.option_id_level1 = o1.id
                 LEFT JOIN product_variation_options o2 ON c.option_id_level2 = o2.id
                 LEFT JOIN product_variation_options o3 ON c.option_id_level3 = o3.id
                 LEFT JOIN product_variation_options o4 ON c.option_id_level4 = o4.id
                 LEFT JOIN product_variation_options o5 ON c.option_id_level5 = o5.id
                 WHERE c.id = ? AND c.product_id = ?',
                [$combinationId, $productId]
            );
            if ($combo) {
                $parts = array_filter([$combo['v1'], $combo['v2'], $combo['v3'], $combo['v4'], $combo['v5']]);
                $flavourNames = implode(' / ', $parts);
            }
        }

        $cart = $this->cartModel->getOrCreate();
        $this->cartModel->addItem($cart['id'], $productId, $variantId, $qty, $price, $flavourNames, null, $combinationId);
        $updated = $this->cartModel->getCartWithItems($cart['id']);

        $this->json(['success' => true, 'cart_count' => $updated['count'], 'cart_total' => format_price($updated['total'])]);
    }

    public function update(): void
    {
        $data   = $this->request->isAjax() ? $this->request->json() : $this->request->all();
        $itemId = (int)($data['cart_item_id'] ?? 0);
        $qty    = (int)($data['qty'] ?? 0);
        $cart   = $this->cartModel->getOrCreate();
        $this->cartModel->updateItem($itemId, $cart['id'], $qty);
        $updated = $this->cartModel->getCartWithItems($cart['id']);
        $this->json(['success' => true, 'cart' => $updated]);
    }

    public function remove(): void
    {
        $data   = $this->request->isAjax() ? $this->request->json() : $this->request->all();
        $itemId = (int)($data['cart_item_id'] ?? 0);
        $cart   = $this->cartModel->getOrCreate();
        $this->cartModel->removeItem($itemId, $cart['id']);
        $updated = $this->cartModel->getCartWithItems($cart['id']);
        $this->json(['success' => true, 'cart' => $updated]);
    }

    public function editItem(): void
    {
        $raw  = file_get_contents('php://input');
        $data = $raw ? (json_decode($raw, true) ?? []) : $this->request->all();

        $cartItemId       = (int)($data['cart_item_id'] ?? 0);
        $qty              = max(1, (int)($data['qty'] ?? 1));
        $variantOptionIds = !empty($data['variant_option_ids']) && is_array($data['variant_option_ids'])
            ? json_encode(array_map('intval', $data['variant_option_ids']))
            : null;
        $flavourNames = isset($data['flavour_names']) ? sanitize_string((string)$data['flavour_names']) : '';

        if ($cartItemId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid item'], 400);
        }

        $cart = $this->cartModel->getOrCreate();
        $item = $this->db->fetch(
            'SELECT ci.*, p.price AS base_price, p.status
             FROM cart_items ci JOIN products p ON ci.product_id = p.id
             WHERE ci.id = ? AND ci.cart_id = ?',
            [$cartItemId, $cart['id']]
        );
        if (!$item) {
            $this->json(['success' => false, 'error' => 'Item not found'], 404);
        }

        $price = $this->resolvePrice($item['product_id'], $variantOptionIds, null);

        $this->db->update('cart_items', [
            'quantity'           => $qty,
            'unit_price'         => $price,
            'variant_option_ids' => $variantOptionIds,
            'selected_flavours'  => $flavourNames ?: null,
        ], 'id = ? AND cart_id = ?', [$cartItemId, $cart['id']]);

        $updated = $this->cartModel->getCartWithItems($cart['id']);
        $this->json(['success' => true, 'cart_count' => $updated['count'], 'cart_total' => format_price($updated['total'])]);
    }

    private function resolvePrice(int $productId, ?int $combinationId, ?int $variantId): float
    {
        // New variation system — combination has its own price
        if ($combinationId) {
            $combo = $this->db->fetch(
                'SELECT price FROM product_variation_combinations WHERE id = ? AND product_id = ? AND is_active = 1',
                [$combinationId, $productId]
            );
            if ($combo) return (float)$combo['price'];
        }

        // Old variant system
        if ($variantId) {
            $v = $this->db->fetch('SELECT price FROM product_variants WHERE id = ?', [$variantId]);
            if ($v) return (float)$v['price'];
        }

        // Base product price
        $row = $this->db->fetch('SELECT price FROM products WHERE id = ?', [$productId]);
        return (float)($row['price'] ?? 0);
    }
}
