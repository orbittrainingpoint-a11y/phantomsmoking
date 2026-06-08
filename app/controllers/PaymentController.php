<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Order;

class PaymentController extends Controller
{
    // ── Stripe ────────────────────────────────────────────────────────────────
    public function stripeSuccess(): void
    {
        $orderId   = (int)$this->request->get('order_id');
        $sessionId = $this->request->get('session_id', '');
        $order     = (new Order())->find($orderId);
        if (!$order) { $this->redirect('/'); }

        $gateway  = new \App\Gateways\StripeGateway();
        $result   = $gateway->verifyPayment(['session_id' => $sessionId]);
        $this->finalisePayment($order, $result, 'stripe');
    }

    public function stripeFailed(): void
    {
        $this->flash('error', 'Stripe payment failed. Please try again.');
        $this->redirect('/checkout');
    }

    // ── Telr ──────────────────────────────────────────────────────────────────
    public function telrSuccess(): void
    {
        $orderId = (int)$this->request->get('order_id');
        $ref     = $this->request->get('ref', '');
        $order   = (new Order())->find($orderId);
        if (!$order) { $this->redirect('/'); }

        $gateway = new \App\Gateways\TelrGateway();
        $result  = $gateway->verifyPayment(['ref' => $ref]);
        $this->finalisePayment($order, $result, 'telr');
    }

    public function telrFailed(): void
    {
        $this->flash('error', 'Card payment failed. Please try again or choose another method.');
        $this->redirect('/checkout');
    }

    // ── Tabby ─────────────────────────────────────────────────────────────────
    public function tabbySuccess(): void
    {
        $orderId   = (int)$this->request->get('order_id');
        $paymentId = $this->request->get('paymentId', '');
        $order     = (new Order())->find($orderId);
        if (!$order) { $this->redirect('/'); }

        $gateway = new \App\Gateways\TabbyGateway();
        $result  = $gateway->verifyPayment(['payment_id' => $paymentId]);
        $this->finalisePayment($order, $result, 'tabby');
    }

    public function tabbyFailed(): void
    {
        $this->flash('error', 'Tabby payment was not approved. Please try another method.');
        $this->redirect('/checkout');
    }

    // ── Tamara ────────────────────────────────────────────────────────────────
    public function tamaraSuccess(): void
    {
        $orderId       = (int)$this->request->get('order_id');
        $tamaraOrderId = $this->request->get('orderId', '');
        $order         = (new Order())->find($orderId);
        if (!$order) { $this->redirect('/'); }

        $gateway = new \App\Gateways\TamaraGateway();
        $result  = $gateway->verifyPayment(['tamara_order_id' => $tamaraOrderId]);
        $this->finalisePayment($order, $result, 'tamara');
    }

    public function tamaraFailed(): void
    {
        $this->flash('error', 'Tamara payment was not approved. Please try another method.');
        $this->redirect('/checkout');
    }

    // ── Tamara webhook ────────────────────────────────────────────────────────
    public function tamaraWebhook(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!empty($payload['order_id']) && ($payload['event_type'] ?? '') === 'order_approved') {
            $order = $this->db->fetch('SELECT * FROM orders WHERE order_number = ?', [$payload['order_reference_id'] ?? '']);
            if ($order && $order['payment_status'] !== 'paid') {
                $this->db->update('orders', ['payment_status' => 'paid', 'order_status' => 'confirmed'], 'id = ?', [$order['id']]);
            }
        }
        http_response_code(200);
        echo 'OK';
        exit;
    }

    // ── Shared finalise ───────────────────────────────────────────────────────
    private function finalisePayment(array $order, array $result, string $gateway): void
    {
        if ($result['success']) {
            $this->db->update('orders', [
                'payment_status'         => 'paid',
                'order_status'           => 'confirmed',
                'payment_transaction_id' => $result['transaction_id'] ?? '',
            ], 'id = ?', [$order['id']]);
            $this->redirect('/order/confirm/' . $order['id']);
        } else {
            error_log("Payment failed [{$gateway}] order #{$order['order_number']}: " . ($result['error'] ?? ''));
            $this->flash('error', 'Payment could not be verified. Please contact support with order #' . $order['order_number']);
            $this->redirect('/checkout');
        }
    }
}
