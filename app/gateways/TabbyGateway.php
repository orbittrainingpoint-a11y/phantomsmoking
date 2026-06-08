<?php
namespace App\Gateways;

use App\Core\PaymentGateway;

class TabbyGateway extends PaymentGateway
{
    private string $publicKey;
    private string $secretKey;
    private string $merchantCode;
    private bool   $testMode;
    private string $baseUrl;

    public function __construct()
    {
        $this->publicKey    = $this->getSetting('tabby_public_key');
        $this->secretKey    = $this->getSetting('tabby_secret_key');
        $this->merchantCode = $this->getSetting('tabby_merchant_code');
        $this->testMode     = (bool)$this->getSetting('tabby_test_mode');
        $this->baseUrl      = $this->testMode
            ? 'https://api.tabby.ai/api/v2'
            : 'https://api.tabby.ai/api/v2';
    }

    public function createPayment(array $order): array
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        $nameParts = explode(' ', $order['shipping_name'], 2);

        $payload = [
            'payment' => [
                'amount'      => number_format($order['total_amount'], 2, '.', ''),
                'currency'    => 'AED',
                'description' => 'Order ' . $order['order_number'],
                'buyer'       => [
                    'phone'      => $order['shipping_phone'] ?? '',
                    'email'      => $order['email'] ?? '',
                    'name'       => $order['shipping_name'],
                    'dob'        => null,
                ],
                'shipping_address' => [
                    'city'    => $order['shipping_city'] ?? 'Dubai',
                    'address' => $order['shipping_address_line1'] ?? '',
                    'zip'     => '00000',
                ],
                'order' => [
                    'tax_amount'      => number_format($order['tax_amount'] ?? 0, 2, '.', ''),
                    'shipping_amount' => number_format($order['shipping_cost'] ?? 0, 2, '.', ''),
                    'discount_amount' => number_format($order['discount_amount'] ?? 0, 2, '.', ''),
                    'updated_at'      => date('c'),
                    'reference_id'    => $order['order_number'],
                    'items'           => array_map(fn($i) => [
                        'title'      => $i['product_name'],
                        'quantity'   => $i['quantity'],
                        'unit_price' => number_format($i['unit_price'], 2, '.', ''),
                        'discount_amount' => '0.00',
                        'reference_id'    => (string)$i['product_id'],
                        'image_url'       => '',
                        'product_url'     => $appUrl,
                        'category'        => 'Tobacco & Vape',
                    ], $order['items'] ?? []),
                ],
                'buyer_history' => ['registered_since' => date('c'), 'loyalty_level' => 0],
                'order_history' => [],
            ],
            'lang'           => 'en',
            'merchant_code'  => $this->merchantCode,
            'merchant_urls'  => [
                'success'  => "{$appUrl}/payment/tabby/success?order_id={$order['id']}",
                'cancel'   => "{$appUrl}/checkout?cancelled=1",
                'failure'  => "{$appUrl}/payment/tabby/failed?order_id={$order['id']}",
            ],
        ];

        $response = $this->httpPost(
            "{$this->baseUrl}/checkout",
            $payload,
            ["Authorization: Bearer {$this->secretKey}"]
        );

        if (!empty($response['configuration']['available_products']['installments'][0]['web_url'])) {
            return [
                'success'        => true,
                'redirect_url'   => $response['configuration']['available_products']['installments'][0]['web_url'],
                'transaction_id' => $response['id'] ?? '',
            ];
        }
        return ['success' => false, 'error' => $response['error'] ?? 'Tabby error'];
    }

    public function verifyPayment(array $params): array
    {
        $paymentId = $params['payment_id'] ?? '';
        $response  = $this->httpGet(
            "{$this->baseUrl}/payments/{$paymentId}",
            ["Authorization: Bearer {$this->secretKey}"]
        );
        if (($response['status'] ?? '') === 'AUTHORIZED') {
            // Capture
            $this->httpPost(
                "{$this->baseUrl}/payments/{$paymentId}/captures",
                [],
                ["Authorization: Bearer {$this->secretKey}"]
            );
            return ['success' => true, 'transaction_id' => $paymentId, 'status' => 'paid'];
        }
        return ['success' => false, 'error' => 'Tabby payment not authorized', 'status' => $response['status'] ?? 'unknown'];
    }

    public function getPublicKey(): string { return $this->publicKey; }
}
