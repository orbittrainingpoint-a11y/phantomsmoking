<?php
namespace App\Middleware;

use App\Core\Database;

class RateLimitMiddleware
{
    public function handle(int $maxAttempts = 5, int $decayMinutes = 15): void
    {
        $ip = $this->getIp();
        $db = Database::getInstance();
        $since = date('Y-m-d H:i:s', strtotime("-{$decayMinutes} minutes"));
        // Clean old attempts
        $db->query('DELETE FROM login_attempts WHERE attempted_at < ?', [$since]);
        $count = (int)$db->fetch(
            'SELECT COUNT(*) as cnt FROM login_attempts WHERE ip_address = ? AND attempted_at >= ?',
            [$ip, $since]
        )['cnt'];
        if ($count >= $maxAttempts) {
            http_response_code(429);
            header('Retry-After: ' . ($decayMinutes * 60));
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                die(json_encode(['error' => 'Too many attempts. Please try again later.']));
            }
            die('429 — Too many attempts. Please try again in ' . $decayMinutes . ' minutes.');
        }
    }

    private function getIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($forwarded, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $ip = $forwarded;
            }
        }
        return $ip;
    }
}
