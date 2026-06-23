<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Product;

class CheckoutController extends Controller
{
    public function index(): void
    {
        $this->requireAgeVerified();
        $cartModel = new Cart();
        $cart = $cartModel->getCartWithItems();
        if (empty($cart['items'])) { $this->redirect('/cart'); }

        $addresses = Auth::check() ? (new User())->getAddresses((int)Auth::id()) : [];
        $zones     = (new DeliveryZone())->getAll();

        // Load active payment methods from settings
        $payments = $this->getActivePaymentMethods();

        $this->render('checkout/index', [
            'title'     => 'Checkout — Phantom Smoking',
            'cart'      => $cart,
            'addresses' => $addresses,
            'zones'     => $zones,
            'payments'  => $payments,
            'csrf'      => $this->csrfToken(),
            'step'      => (int)$this->request->get('step', 1),
        ]);
    }

    public function placeOrder(): void
    {
        $this->requireAgeVerified();
        $cartModel = new Cart();
        $cart = $cartModel->getCartWithItems();
        if (empty($cart['items'])) { $this->flash('error', 'Your cart is empty.'); $this->redirect('/cart'); }

        $data   = $this->request->all();
        $errors = [];
        if (empty($data['shipping_name']))          $errors[] = 'Full name is required';
        if (empty($data['shipping_phone']))         $errors[] = 'Phone is required';
        if (empty($data['shipping_address_line1'])) $errors[] = 'Address is required';
        if (empty($data['shipping_emirate']))       $errors[] = 'Emirate is required';
        if (empty($data['payment_method']))         $errors[] = 'Payment method is required';

        if (!empty($errors)) {
            $this->flash('error', implode(', ', $errors));
            $this->redirect('/checkout');
        }

        $paymentMethod = $data['payment_method'];
        $deliveryType  = $data['delivery_type'] ?? 'standard';
        $emirate       = sanitize_string($data['shipping_emirate'] ?? 'Dubai');
        $shippingCost  = (new DeliveryZone())->calculateFee($emirate, $deliveryType, $cart['subtotal']);
        $tax           = round(($cart['subtotal'] - $cart['discount']) * 0.05, 2);
        $total         = $cart['subtotal'] - $cart['discount'] + $shippingCost + $tax;

        // Reward points
        $pointsUsed = 0; $pointsDiscount = 0;
        if (Auth::check() && !empty($data['use_reward_points'])) {
            $user = (new User())->find((int)Auth::id());
            $rewardPoints = (int)($user['reward_points'] ?? 0);
            if ($rewardPoints >= 500) {
                $maxDiscount    = $cart['subtotal'] * 0.30;
                $pointsValue    = min($rewardPoints / 10, $maxDiscount);
                $pointsUsed     = (int)($pointsValue * 10);
                $pointsDiscount = $pointsValue;
                $total         -= $pointsDiscount;
            }
        }

        $orderNumber  = 'PS-' . date('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $pointsEarned = (int)$cart['subtotal'];

        // Get user email
        $email = '';
        if (Auth::check()) {
            $email = Auth::user()['email'] ?? '';
        } else {
            $email = sanitize_string($data['email'] ?? '');
        }

        $orderData = [
            'order_number'           => $orderNumber,
            'user_id'                => Auth::id(),
            'guest_email'            => Auth::check() ? null : $email,
            'shipping_name'          => sanitize_string($data['shipping_name']),
            'shipping_phone'         => sanitize_string($data['shipping_phone']),
            'shipping_address_line1' => sanitize_string($data['shipping_address_line1']),
            'shipping_address_line2' => sanitize_string($data['shipping_address_line2'] ?? ''),
            'shipping_area'          => sanitize_string($data['shipping_area'] ?? ''),
            'shipping_city'          => sanitize_string($data['shipping_city'] ?? 'Dubai'),
            'shipping_emirate'       => $emirate,
            'subtotal'               => $cart['subtotal'],
            'discount_amount'        => $cart['discount'] + $pointsDiscount,
            'shipping_cost'          => $shippingCost,
            'tax_amount'             => $tax,
            'total_amount'           => max(0, $total),
            'reward_points_earned'   => $pointsEarned,
            'reward_points_used'     => $pointsUsed,
            'reward_points_discount' => $pointsDiscount,
            'coupon_id'              => $cart['coupon_id'],
            'payment_method'         => $paymentMethod,
            'payment_status'         => 'pending',
            'delivery_type'          => $deliveryType,
            'delivery_slot'          => $data['delivery_slot'] ?? null,
            'customer_notes'         => sanitize_string($data['notes'] ?? ''),
            'ip_address'             => $this->request->ip(),
        ];

        $items = array_map(fn($i) => [
            'product_id'        => $i['product_id'],
            'variant_id'        => $i['variant_id'],
            'product_name'      => $i['name'],
            'product_sku'       => $i['sku'] ?? '',
            'variant_name'      => $i['variant_name'] ?: ($i['selected_flavours'] ?? null),
            'quantity'          => $i['quantity'],
            'unit_price'        => $i['unit_price'],
            'tax_amount'        => round($i['unit_price'] * $i['quantity'] * 0.05, 2),
            'total_price'       => $i['unit_price'] * $i['quantity'],
            'product_image'     => $i['product_image'] ?? null,
            'selected_flavours' => $i['selected_flavours'] ?? null,
        ], $cart['items']);

        $orderModel = new Order();
        $orderId    = $orderModel->createOrder($orderData, $items);

        // Deduct stock
        $productModel = new Product();
        foreach ($cart['items'] as $item) {
            $productModel->updateStock($item['product_id'], $item['variant_id'], $item['quantity'], 'decrement');
            $this->db->query('UPDATE products SET total_sold = total_sold + ? WHERE id = ?', [$item['quantity'], $item['product_id']]);
        }

        // Reward points
        if (Auth::check()) {
            $uid = (int)Auth::id();
            $userModel = new User();
            if ($pointsUsed > 0) $userModel->deductRewardPoints($uid, $pointsUsed, 'Redeemed on order ' . $orderNumber, $orderId);
            $userModel->addRewardPoints($uid, $pointsEarned, 'earned', 'Earned on order ' . $orderNumber, $orderId);
            $this->db->query('UPDATE users SET total_orders = total_orders + 1, total_spent = total_spent + ? WHERE id = ?', [$total, $uid]);
        }

        if ($cart['coupon_id']) {
            (new Coupon())->markUsed($cart['coupon_id'], Auth::id(), $orderId);
        }

        // COD / Card on Delivery / Payment Link on Delivery — complete immediately
        if (in_array($paymentMethod, ['cod', 'card_on_delivery', 'payment_link_on_delivery'])) {
            $cartModel->clearCart($cart['id']);
            $this->db->update('orders', ['order_status' => 'confirmed'], 'id = ?', [$orderId]);
            $order = $orderModel->getOrderWithItems($orderId);
            if ($order) { send_order_confirmation($order, $order['items']); }
            // Store order ID in session for guest access control
            if (!Auth::check()) {
                $guestOrders = \App\Core\Session::get('guest_order_ids', []);
                $guestOrders[] = $orderId;
                \App\Core\Session::set('guest_order_ids', array_slice($guestOrders, -5));
            }
            $this->redirect('/order/confirm/' . $orderId);
        }

        // Online payment — redirect to gateway
        $order = $orderModel->find($orderId);
        if (!$order) { $this->redirect('/'); }
        $order['email'] = $email;
        $order['items'] = $items;

        $cartModel->clearCart($cart['id']);

        $redirectUrl = $this->initiateGatewayPayment($paymentMethod, $order);
        if ($redirectUrl) {
            $this->redirect($redirectUrl);
        }

        // Gateway init failed — mark order as failed
        $this->db->update('orders', ['payment_status' => 'failed'], 'id = ?', [$orderId]);
        $this->flash('error', 'Could not connect to payment gateway. Please try again.');
        $this->redirect('/checkout');
    }

    private function initiateGatewayPayment(string $method, array $order): ?string
    {
        try {
            $gateway = match($method) {
                'stripe' => new \App\Gateways\StripeGateway(),
                'telr'   => new \App\Gateways\TelrGateway(),
                'tabby'  => new \App\Gateways\TabbyGateway(),
                'tamara' => new \App\Gateways\TamaraGateway(),
                default  => null,
            };
            if (!$gateway) return null;
            $result = $gateway->createPayment($order);
            if ($result['success']) {
                // Store transaction ID
                $this->db->update('orders', ['payment_transaction_id' => $result['transaction_id'] ?? ''], 'id = ?', [$order['id']]);
                return $result['redirect_url'];
            }
            error_log("Gateway [{$method}] error: " . ($result['error'] ?? ''));
        } catch (\Throwable $e) {
            error_log("Gateway [{$method}] exception: " . $e->getMessage());
        }
        return null;
    }

    private function getActivePaymentMethods(): array
    {
        $methods = [];
        $settings = $this->db->fetchAll('SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE "%_enabled"');
        $map = array_column($settings, 'setting_value', 'setting_key');

        if (!empty($map['cod_enabled']))                  $methods[] = ['id' => 'cod',                    'label' => 'Cash on Delivery',          'icon' => 'fa-money-bill-wave',  'desc' => 'Pay cash when your order arrives'];
        if (!empty($map['card_on_delivery_enabled']))      $methods[] = ['id' => 'card_on_delivery',        'label' => 'Card on Delivery',          'icon' => 'fa-credit-card',      'desc' => 'Pay by card when your order arrives'];
        if (!empty($map['payment_link_on_delivery_enabled'])) $methods[] = ['id' => 'payment_link_on_delivery', 'label' => 'Payment Link on Delivery',  'icon' => 'fa-link',             'desc' => 'We will send you a payment link at delivery'];
        if (!empty($map['stripe_enabled']))                $methods[] = ['id' => 'stripe',                  'label' => 'Credit / Debit Card',       'icon' => 'fa-credit-card',      'desc' => 'Visa, Mastercard, Amex — Powered by Stripe'];
        if (!empty($map['telr_enabled']))                  $methods[] = ['id' => 'telr',                    'label' => 'Card Payment (Telr)',        'icon' => 'fa-credit-card',      'desc' => 'Secure card payment via Telr'];
        if (!empty($map['tabby_enabled']))                 $methods[] = ['id' => 'tabby',                   'label' => 'Pay in 4 — Tabby',          'icon' => 'fa-calendar-alt',     'desc' => 'Split into 4 interest-free payments'];
        if (!empty($map['tamara_enabled']))                $methods[] = ['id' => 'tamara',                  'label' => 'Pay in 3 — Tamara',         'icon' => 'fa-calendar-check',   'desc' => 'Split into 3 easy instalments'];

        return $methods;
    }
}
