<?php
namespace App\Gateways;

use App\Core\PaymentGateway;

class StripeGateway extends PaymentGateway
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secretKey = $this->getSetting('stripe_secret_key');
        $this->publicKey = $this->getSetting('stripe_public_key');
    }

    public function createPayment(array $order): array
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        $params = http_build_query([
            'payment_method_types[]'                               => 'card',
            'mode'                                                 => 'payment',
            'success_url'                                          => "{$appUrl}/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}&order_id={$order['id']}",
            'cancel_url'                                           => "{$appUrl}/checkout?cancelled=1",
            'customer_email'                                       => $order['email'] ?? '',
            'metadata[order_id]'                                   => $order['id'],
            'metadata[order_number]'                               => $order['order_number'],
            'line_items[0][price_data][currency]'                  => 'aed',
            'line_items[0][price_data][unit_amount]'               => (int)round($order['total_amount'] * 100),
            'line_items[0][price_data][product_data][name]'        => 'Order ' . $order['order_number'],
            'line_items[0][quantity]'                              => 1,
        ]);
        $ch = curl_init("{$this->baseUrl}/checkout/sessions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$this->secretKey}"],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!empty($response['url'])) {
            return ['success' => true, 'redirect_url' => $response['url'], 'transaction_id' => $response['id']];
        }
        return ['success' => false, 'error' => $response['error']['message'] ?? 'Stripe error'];
    }

    public function verifyPayment(array $params): array
    {
        $sessionId = $params['session_id'] ?? '';
        $ch = curl_init("{$this->baseUrl}/checkout/sessions/{$sessionId}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$this->secretKey}"],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (($response['payment_status'] ?? '') === 'paid') {
            return ['success' => true, 'transaction_id' => $response['payment_intent'] ?? $sessionId, 'status' => 'paid'];
        }
        return ['success' => false, 'error' => 'Payment not completed', 'status' => $response['payment_status'] ?? 'unknown'];
    }

    public function getPublicKey(): string { return $this->publicKey; }
}
