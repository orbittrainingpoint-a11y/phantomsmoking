<?php
namespace App\Middleware;

use App\Core\Session;

class CsrfMiddleware
{
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            $stored = Session::get('csrf_token', '');
            if (empty($stored) || !hash_equals($stored, $token)) {
                http_response_code(403);
                // Regenerate token after failed attempt
                Session::set('csrf_token', bin2hex(random_bytes(32)));
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    header('Content-Type: application/json');
                    die(json_encode(['error' => 'Invalid CSRF token']));
                }
                die('403 Forbidden — Invalid CSRF token');
            }
            // Rotate CSRF token after each POST
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
    }
}
