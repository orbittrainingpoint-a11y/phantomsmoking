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
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    header('Content-Type: application/json');
                    die(json_encode(['error' => 'Invalid CSRF token']));
                }
                die('403 Forbidden');
            }
            // Do NOT rotate per-request — token is per-session, rotated only on login/logout
        }
    }
}
