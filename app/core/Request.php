<?php
namespace App\Core;

class Request
{
    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function ip(): string
    {
        // Trust X-Forwarded-For only from known proxies to prevent IP spoofing
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($forwarded, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $ip = $forwarded;
            }
        }
        return $ip;
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function uri(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return is_string($path) ? $path : '/';
    }

    public function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            $ruleList = explode('|', $rule);
            foreach ($ruleList as $r) {
                if ($r === 'required' && empty($value)) {
                    $errors[$field] = ucfirst($field) . ' is required';
                } elseif (str_starts_with($r, 'min:') && strlen((string)$value) < (int)substr($r, 4)) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . substr($r, 4) . ' characters';
                } elseif (str_starts_with($r, 'max:') && strlen((string)$value) > (int)substr($r, 4)) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . substr($r, 4) . ' characters';
                } elseif ($r === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Invalid email address';
                } elseif ($r === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = ucfirst($field) . ' must be a number';
                }
            }
        }
        return $errors;
    }

    public function csrfToken(): string
    {
        return $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    public function json(): array
    {
        $body = $_SERVER['_RAW_BODY'] ?? file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }
}
