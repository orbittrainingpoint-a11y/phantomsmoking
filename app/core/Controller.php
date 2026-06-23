<?php
namespace App\Core;

class Controller
{
    protected Request $request;
    protected Database $db;

    public function __construct()
    {
        $this->request = new Request();
        $this->db = Database::getInstance();
    }

    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url, int $code = 302): never
    {
        // Prevent open redirect attacks — only allow relative or same-host URLs
        $parsed  = parse_url($url);
        $appHost = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_HOST);
        if (!empty($parsed['host']) && $parsed['host'] !== $appHost) {
            $url = '/';
        }
        // Build absolute URL for Hostinger reverse-proxy compatibility
        if (empty($parsed['host'])) {
            $scheme  = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                     || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
            $host    = $_SERVER['HTTP_HOST'] ?? ($appHost ?: 'localhost');
            $url     = $scheme . '://' . $host . $url;
        }
        http_response_code($code);
        header("Location: $url");
        exit;
    }

    protected function flash(string $type, mixed $message): void
    {
        Session::flash($type, $message);
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please login to continue');
            $this->redirect('/login?redirect=' . urlencode($this->request->uri()));
        }
    }

    protected function requireAdmin(): void
    {
        if (!Auth::check() || !Auth::isAdmin()) {
            $this->redirect('/login');
        }
    }

    protected function requireAgeVerified(): void
    {
        if (!Session::get('age_verified') && !isset($_COOKIE['age_verified'])) {
            $this->redirect('/age-verify?redirect=' . urlencode($this->request->uri()));
        }
    }

    protected function csrfToken(): string
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    protected function verifyCsrf(): void
    {
        $token = $this->request->csrfToken();
        if (!hash_equals(Session::get('csrf_token', ''), $token)) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }
    }

    protected function paginate(int $total, int $perPage, int $page): array
    {
        $totalPages = (int)ceil($total / $perPage);
        return [
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'total_pages' => $totalPages,
            'offset'      => ($page - 1) * $perPage,
        ];
    }
}
