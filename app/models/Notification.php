<?php
namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';

    public function getUserNotifications(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        return (int)($this->db->fetch(
            'SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0',
            [$userId]
        )['cnt'] ?? 0);
    }

    public function markRead(int $userId, ?int $notifId = null): void
    {
        if ($notifId) {
            $this->db->update('notifications', ['is_read' => 1], 'id = ? AND user_id = ?', [$notifId, $userId]);
        } else {
            $this->db->update('notifications', ['is_read' => 1], 'user_id = ?', [$userId]);
        }
    }

    public function create(array $data): int
    {
        return $this->db->insert('notifications', $data);
    }
}
