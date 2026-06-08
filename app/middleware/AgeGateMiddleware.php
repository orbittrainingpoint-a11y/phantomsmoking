<?php
namespace App\Middleware;

use App\Core\Session;

class AgeGateMiddleware
{
    public function handle(): void
    {
        if (!Session::get('age_verified') && !isset($_COOKIE['age_verified'])) {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: /age-verify?redirect=' . urlencode($uri));
            exit;
        }
    }
}
