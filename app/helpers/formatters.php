<?php
if (!function_exists('format_date')) {
    function format_date(string $datetime, string $format = 'd M Y'): string
    {
        $ts = strtotime($datetime);
        return $ts !== false ? date($format, $ts) : '';
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(string $datetime): string
    {
        $ts = strtotime($datetime);
        return $ts !== false ? date('d M Y, h:i A', $ts) : '';
    }
}

if (!function_exists('format_order_number')) {
    function format_order_number(): string
    {
        return 'SS-' . date('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('format_phone')) {
    function format_phone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) {
            $phone = '+971' . substr($phone, 1);
        }
        return $phone;
    }
}

if (!function_exists('format_weight')) {
    function format_weight(int $grams): string
    {
        if ($grams >= 1000) return number_format($grams / 1000, 1) . ' kg';
        return $grams . ' g';
    }
}

if (!function_exists('payment_method_label')) {
    function payment_method_label(string $method): string
    {
        return match($method) {
            'cod'                       => 'Cash on Delivery',
            'card_on_delivery'          => 'Card on Delivery',
            'payment_link_on_delivery'  => 'Payment Link on Delivery',
            'stripe'                    => 'Card (Stripe)',
            'telr'                      => 'Card (Telr)',
            'tabby'                     => 'Tabby — Pay in 4',
            'tamara'                    => 'Tamara — Pay in 3',
            default                     => ucwords(str_replace('_', ' ', $method)),
        };
    }
}
