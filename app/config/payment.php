<?php
return [
    'telr' => [
        'store_id' => $_ENV['TELR_STORE_ID'] ?? '',
        'auth_key' => $_ENV['TELR_AUTH_KEY'] ?? '',
        'test_mode'=> filter_var($_ENV['TELR_TEST_MODE'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'endpoint' => 'https://secure.telr.com/gateway/order.json',
    ],
    'cod_enabled'        => true,
    'card_enabled'       => true,
    'apple_pay_enabled'  => true,
];
