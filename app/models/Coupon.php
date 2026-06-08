<?php
namespace App\Models;

use App\Core\Model;

class Coupon extends Model
{
    protected string $table = 'coupons';

    public function findByCode(string $code): ?array
    {
        return $this->db->fetch('SELECT * FROM coupons WHERE code = ? AND is_active = 1', [strtoupper($code)]);
    }

    public function validate(string $code, float $cartTotal, ?int $userId = null): array
    {
        $coupon = $this->findByCode($code);
        if (!$coupon) return ['valid' => false, 'error' => 'Invalid coupon code'];
        if ($coupon['start_date'] && strtotime($coupon['start_date']) > time()) return ['valid' => false, 'error' => 'Coupon not yet active'];
        if ($coupon['end_date'] && strtotime($coupon['end_date']) < time()) return ['valid' => false, 'error' => 'Coupon has expired'];
        if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) return ['valid' => false, 'error' => 'Coupon usage limit reached'];
        if ($cartTotal < $coupon['min_order_amount']) return ['valid' => false, 'error' => 'Minimum order amount is ' . format_price($coupon['min_order_amount'])];
        if ($userId) {
            $used = $this->db->fetch('SELECT COUNT(*) as cnt FROM coupon_usage WHERE coupon_id = ? AND user_id = ?', [$coupon['id'], $userId]);
            if ((int)$used['cnt'] >= $coupon['usage_per_user']) return ['valid' => false, 'error' => 'You have already used this coupon'];
        }
        return ['valid' => true, 'coupon' => $coupon];
    }

    public function markUsed(int $couponId, ?int $userId, int $orderId): void
    {
        $this->db->query('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?', [$couponId]);
        $this->db->insert('coupon_usage', ['coupon_id' => $couponId, 'user_id' => $userId, 'order_id' => $orderId]);
    }
}
