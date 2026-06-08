<?php
namespace App\Core;

class Auth
{
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
        Session::set('user_email', $user['email']);
    }

    public static function logout(): void
    {
        Session::delete('user_id');
        Session::delete('user_role');
        Session::delete('user_name');
        Session::delete('user_email');
        Session::regenerate();
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        $db = Database::getInstance();
        return $db->fetch('SELECT * FROM users WHERE id = ? AND is_active = 1', [self::id()]);
    }

    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    public static function isAdmin(): bool
    {
        return in_array(self::role(), ['admin', 'manager']);
    }

    public static function isStaff(): bool
    {
        return in_array(self::role(), ['admin', 'manager', 'staff']);
    }

    public static function attempt(string $email, string $password): array|false
    {
        $db = Database::getInstance();
        $user = $db->fetch('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        $db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ], 'id = ?', [$user['id']]);
        return $user;
    }

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
