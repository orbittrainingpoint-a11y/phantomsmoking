<?php
namespace App\Middleware;

use App\Core\Auth;

class AdminMiddleware
{
    public function handle(): void
    {
        if (!Auth::check() || !Auth::isAdmin()) {
            header('Location: /login');
            exit;
        }
    }
}
