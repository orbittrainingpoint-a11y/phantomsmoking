<?php
namespace App\Core;

abstract class PaymentGateway
{
    abstract public function createPayment(array $order): array;
    // Returns: ['success'=>bool, 'redirect_url'=>string, 'transaction_id'=>string, 'error'=>string]

    abstract public function verifyPayment(array $params): array;
    // Returns: ['success'=>bool, 'transaction_id'=>string, 'status'=>string, 'error'=>string]

    protected function getSetting(string $key): string
    {
        $db = Database::getInstance();
        $row = $db->fetch('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);
        return $row['setting_value'] ?? '';
    }

    protected function httpPost(string $url, array $data, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw   = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) return ['error' => $error];
        return is_string($raw) ? (json_decode($raw, true) ?? ['error' => 'Invalid response']) : ['error' => 'Invalid response'];
    }

    protected function httpGet(string $url, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw   = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) return ['error' => $error];
        return is_string($raw) ? (json_decode($raw, true) ?? ['error' => 'Invalid response']) : ['error' => 'Invalid response'];
    }
}
