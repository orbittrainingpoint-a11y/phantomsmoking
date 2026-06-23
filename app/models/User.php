<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Auth;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function register(array $data): int
    {
        return $this->create([
            'first_name'          => sanitize_string($data['first_name']),
            'last_name'           => sanitize_string($data['last_name']),
            'email'               => strtolower(trim($data['email'])),
            'phone'               => $data['phone'] ?? null,
            'password_hash'       => Auth::hashPassword($data['password']),
            'email_verify_token'  => generate_token(),
            'newsletter_subscribed'=> (int)($data['newsletter'] ?? 0),
        ]);
    }

    public function getAddresses(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC',
            [$userId]
        );
    }

    public function addAddress(int $userId, array $data): int
    {
        if (!empty($data['is_default'])) {
            $this->db->update('user_addresses', ['is_default' => 0], 'user_id = ?', [$userId]);
        }
        return $this->db->insert('user_addresses', array_merge($data, ['user_id' => $userId]));
    }

    public function updateAddress(int $addressId, int $userId, array $data): void
    {
        if (!empty($data['is_default'])) {
            $this->db->update('user_addresses', ['is_default' => 0], 'user_id = ?', [$userId]);
        }
        $this->db->update('user_addresses', $data, 'id = ? AND user_id = ?', [$addressId, $userId]);
    }

    public function deleteAddress(int $addressId, int $userId): void
    {
        $this->db->delete('user_addresses', 'id = ? AND user_id = ?', [$addressId, $userId]);
    }

    public function getDefaultAddress(int $userId): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1',
            [$userId]
        );
    }

    public function addRewardPoints(int $userId, int $points, string $type, string $description, ?int $orderId = null): void
    {
        $user    = $this->find($userId);
        $balance = (int)($user['reward_points'] ?? 0) + $points;
        $this->db->insert('reward_points_log', [
            'user_id'      => $userId,
            'type'         => $type,
            'points'       => $points,
            'balance_after'=> $balance,
            'order_id'     => $orderId,
            'description'  => $description,
            'expires_at'   => date('Y-m-d H:i:s', strtotime('+12 months')),
        ]);
        $this->update($userId, ['reward_points' => $balance]);
    }

    public function deductRewardPoints(int $userId, int $points, string $description, ?int $orderId = null): bool
    {
        $user         = $this->find($userId);
        $rewardPoints = (int)($user['reward_points'] ?? 0);
        if ($rewardPoints < $points) return false;
        $balance = $rewardPoints - $points;
        $this->db->insert('reward_points_log', [
            'user_id'      => $userId,
            'type'         => 'redeemed',
            'points'       => -$points,
            'balance_after'=> $balance,
            'order_id'     => $orderId,
            'description'  => $description,
        ]);
        $this->update($userId, ['reward_points' => $balance]);
        return true;
    }

    public function getRewardHistory(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM reward_points_log WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
    }

    public function getStats(int $userId): array
    {
        $orders = $this->db->fetch(
            'SELECT COUNT(*) as total_orders, SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND order_status != "cancelled"',
            [$userId]
        );
        $wishlist = $this->db->fetch('SELECT COUNT(*) as cnt FROM wishlists WHERE user_id = ?', [$userId]);
        $user = $this->find($userId);
        return [
            'total_orders'  => (int)($orders['total_orders'] ?? 0),
            'total_spent'   => (float)($orders['total_spent'] ?? 0),
            'reward_points' => (int)($user['reward_points'] ?? 0),
            'wishlist_count'=> (int)($wishlist['cnt'] ?? 0),
        ];
    }

    public function getPaginated(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $where = "role = 'customer'";
        $params = [];
        if ($search) {
            $where .= ' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $total = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM users WHERE $where", $params)['cnt'] ?? 0);
        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            "SELECT id, first_name, last_name, email, phone, reward_points, total_orders, total_spent, is_active, created_at FROM users WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );
        return ['items' => $items, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'total_pages' => (int)ceil($total / $perPage)];
    }
}
