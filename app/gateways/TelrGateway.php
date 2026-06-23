<?php
namespace App\Gateways;

use App\Core\PaymentGateway;

class TelrGateway extends PaymentGateway
{
    private string $storeId;
    private string $authKey;
    private bool   $testMode;
    private string $endpoint = 'https://secure.telr.com/gateway/order.json';

    public function __construct()
    {
        $this->storeId  = $this->getSetting('telr_store_id');
        $this->authKey  = $this->getSetting('telr_auth_key');
        $this->testMode = (bool)$this->getSetting('telr_test_mode');
    }

    public function createPayment(array $order): array
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        $payload = [
            'ivp_method'  => 'create',
            'ivp_store'   => $this->storeId,
            'ivp_authkey' => $this->authKey,
            'ivp_cart'    => $order['order_number'],
            'ivp_test'    => $this->testMode ? 1 : 0,
            'ivp_amount'  => number_format($order['total_amount'], 2, '.', ''),
            'ivp_currency'=> 'AED',
            'ivp_desc'    => 'Order ' . $order['order_number'],
            'return_auth' => "{$appUrl}/payment/telr/success?order_id={$order['id']}",
            'return_decl' => "{$appUrl}/payment/telr/failed?order_id={$order['id']}",
            'return_can'  => "{$appUrl}/checkout?cancelled=1",
            'bill_fname'  => explode(' ', $order['shipping_name'])[0] ?? 'Customer',
            'bill_lname'  => explode(' ', $order['shipping_name'])[1] ?? 'Name',
            'bill_email'  => $order['email'] ?? 'customer@example.com',
            'bill_tel'    => $order['shipping_phone'] ?? '',
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $raw = curl_exec($ch);
        $response = is_string($raw) ? (json_decode($raw, true) ?? []) : [];
        curl_close($ch);

        if (!empty($response['order']['url'])) {
            return ['success' => true, 'redirect_url' => $response['order']['url'], 'transaction_id' => $response['order']['ref'] ?? ''];
        }
        return ['success' => false, 'error' => $response['error']['message'] ?? 'Telr error'];
    }

    public function verifyPayment(array $params): array
    {
        $ref = $params['ref'] ?? '';
        $payload = [
            'ivp_method'  => 'check',
            'ivp_store'   => $this->storeId,
            'ivp_authkey' => $this->authKey,
            'order_ref'   => $ref,
        ];
        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $raw = curl_exec($ch);
        $response = is_string($raw) ? (json_decode($raw, true) ?? []) : [];
        curl_close($ch);

        $status = $response['order']['status']['text'] ?? '';
        if (in_array($status, ['Authorised', 'Captured'])) {
            return ['success' => true, 'transaction_id' => $ref, 'status' => 'paid'];
        }
        return ['success' => false, 'error' => 'Payment ' . $status, 'status' => strtolower($status)];
    }
}
