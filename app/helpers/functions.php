<?php
if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}

if (!function_exists('format_price')) {
    function format_price(float $amount, string $currency = 'AED'): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) return $text;
        return mb_substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('generate_token')) {
    function generate_token(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}

if (!function_exists('e')) {
    function e(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        $parsed  = parse_url($url);
        $appHost = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_HOST);
        if (!empty($parsed['host']) && $parsed['host'] !== $appHost) {
            $url = '/';
        }
        header("Location: $url");
        exit;
    }
}

if (!function_exists('flash_get')) {
    function flash_get(string $key): mixed
    {
        return \App\Core\Session::flash($key);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        if (!\App\Core\Session::has('csrf_token')) {
            \App\Core\Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        $token = \App\Core\Session::get('csrf_token');
        return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return \App\Core\Auth::check();
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        return \App\Core\Auth::user();
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return \App\Core\Auth::isAdmin();
    }
}

if (!function_exists('age_verified')) {
    function age_verified(): bool
    {
        return (bool)(\App\Core\Session::get('age_verified') || isset($_COOKIE['age_verified']));
    }
}

if (!function_exists('discount_percent')) {
    function discount_percent(float $price, float $comparePrice): int
    {
        if ($comparePrice <= 0) return 0;
        return (int)round((($comparePrice - $price) / $comparePrice) * 100);
    }
}

if (!function_exists('star_rating')) {
    function star_rating(float $rating): string
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) $html .= '<i class="fas fa-star"></i>';
            elseif ($i - 0.5 <= $rating) $html .= '<i class="fas fa-star-half-alt"></i>';
            else $html .= '<i class="far fa-star"></i>';
        }
        return $html;
    }
}

if (!function_exists('time_ago')) {
    function time_ago(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return (int)($diff / 60) . ' min ago';
        if ($diff < 86400) return (int)($diff / 3600) . ' hours ago';
        if ($diff < 604800) return (int)($diff / 86400) . ' days ago';
        return date('d M Y', strtotime($datetime));
    }
}

if (!function_exists('order_status_badge')) {
    function order_status_badge(string $status): string
    {
        $map = [
            'pending'          => 'badge-warning',
            'confirmed'        => 'badge-info',
            'processing'       => 'badge-info',
            'packed'           => 'badge-primary',
            'out_for_delivery' => 'badge-primary',
            'delivered'        => 'badge-success',
            'cancelled'        => 'badge-danger',
            'returned'         => 'badge-secondary',
        ];
        $class = $map[$status] ?? 'badge-secondary';
        return '<span class="badge ' . $class . '">' . ucwords(str_replace('_', ' ', $status)) . '</span>';
    }
}
