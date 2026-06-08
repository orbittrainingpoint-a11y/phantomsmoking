<?php
namespace App\Gateways;

use App\Core\PaymentGateway;

class TamaraGateway extends PaymentGateway
{
    private string $apiToken;
    private string $notificationKey;
    private bool   $testMode;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiToken         = $this->getSetting('tamara_api_token');
        $this->notificationKey  = $this->getSetting('tamara_notification_key');
        $this->testMode         = (bool)$this->getSetting('tamara_test_mode');
        $this->baseUrl          = $this->testMode
            ? 'https://api-sandbox.tamara.co'
            : 'https://api.tamara.co';
    }

    public function createPayment(array $order): array
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        $nameParts = explode(' ', $order['shipping_name'], 2);

        $payload = [
            'order_reference_id' => $order['order_number'],
            'total_amount'       => ['amount' => number_format($order['total_amount'], 2, '.', ''), 'currency' => 'AED'],
            'description'        => 'Order ' . $order['order_number'],
            'country_code'       => 'AE',
            'payment_type'       => 'PAY_BY_INSTALMENTS',
            'instalments'        => 3,
            'locale'             => 'en_US',
            'items'              => array_map(fn($i) => [
                'reference_id' => (string)$i['product_id'],
                'type'         => 'Digital',
                'name'         => $i['product_name'],
                'sku'          => $i['product_sku'] ?? '',
                'quantity'     => $i['quantity'],
                'unit_price'   => ['amount' => number_format($i['unit_price'], 2, '.', ''), 'currency' => 'AED'],
                'total_amount' => ['amount' => number_format($i['unit_price'] * $i['quantity'], 2, '.', ''), 'currency' => 'AED'],
            ], $order['items'] ?? []),
            'consumer'           => [
                'first_name'   => $nameParts[0] ?? 'Customer',
                'last_name'    => $nameParts[1] ?? 'Name',
                'phone_number' => $order['shipping_phone'] ?? '',
                'email'        => $order['email'] ?? '',
            ],
            'billing_address'    => [
                'first_name'   => $nameParts[0] ?? 'Customer',
                'last_name'    => $nameParts[1] ?? 'Name',
                'line1'        => $order['shipping_address_line1'] ?? '',
                'city'         => $order['shipping_city'] ?? 'Dubai',
                'country_code' => 'AE',
                'phone_number' => $order['shipping_phone'] ?? '',
            ],
            'shipping_address'   => [
                'first_name'   => $nameParts[0] ?? 'Customer',
                'last_name'    => $nameParts[1] ?? 'Name',
                'line1'        => $order['shipping_address_line1'] ?? '',
                'city'         => $order['shipping_city'] ?? 'Dubai',
                'country_code' => 'AE',
                'phone_number' => $order['shipping_phone'] ?? '',
            ],
            'discount'           => ['amount' => number_format($order['discount_amount'] ?? 0, 2, '.', ''), 'currency' => 'AED'],
            'tax_amount'         => ['amount' => number_format($order['tax_amount'] ?? 0, 2, '.', ''), 'currency' => 'AED'],
            'shipping_amount'    => ['amount' => number_format($order['shipping_cost'] ?? 0, 2, '.', ''), 'currency' => 'AED'],
            'merchant_url'       => [
                'success'      => "{$appUrl}/payment/tamara/success?order_id={$order['id']}",
                'failure'      => "{$appUrl}/payment/tamara/failed?order_id={$order['id']}",
                'cancel'       => "{$appUrl}/checkout?cancelled=1",
                'notification' => "{$appUrl}/payment/tamara/webhook",
            ],
        ];

        $response = $this->httpPost(
            "{$this->baseUrl}/checkout",
            $payload,
            ["Authorization: Bearer {$this->apiToken}"]
        );

        if (!empty($response['checkout_url'])) {
            return ['success' => true, 'redirect_url' => $response['checkout_url'], 'transaction_id' => $response['checkout_id'] ?? ''];
        }
        return ['success' => false, 'error' => $response['message'] ?? 'Tamara error'];
    }

    public function verifyPayment(array $params): array
    {
        $orderId = $params['tamara_order_id'] ?? '';
        $response = $this->httpGet(
            "{$this->baseUrl}/orders/{$orderId}",
            ["Authorization: Bearer {$this->apiToken}"]
        );
        if (in_array($response['status'] ?? '', ['approved', 'captured'])) {
            return ['success' => true, 'transaction_id' => $orderId, 'status' => 'paid'];
        }
        return ['success' => false, 'error' => 'Tamara payment not approved', 'status' => $response['status'] ?? 'unknown'];
    }
}
