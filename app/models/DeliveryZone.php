<?php
namespace App\Models;

use App\Core\Model;

class DeliveryZone extends Model
{
    protected string $table = 'delivery_zones';

    public function getByEmirate(string $emirate): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM delivery_zones WHERE emirate = ? AND is_active = 1 ORDER BY zone_name LIMIT 1',
            [$emirate]
        );
    }

    public function calculateFee(string $emirate, string $type, float $subtotal): float
    {
        $zone = $this->getByEmirate($emirate);

        // Fallback to global settings if no zone found
        if (!$zone) {
            $defaultFee        = (float)$this->getSetting('default_shipping_fee', '15');
            $defaultExpress    = (float)$this->getSetting('default_express_fee', '25');
            $freeThreshold     = (float)$this->getSetting('free_shipping_threshold', '100');
            if ($type === 'standard' && $freeThreshold > 0 && $subtotal >= $freeThreshold) return 0.00;
            return $type === 'express_1hr' ? $defaultExpress : $defaultFee;
        }

        // Free shipping check
        $threshold = (float)$zone['free_shipping_threshold'];
        if ($type === 'standard' && $threshold > 0 && $subtotal >= $threshold) return 0.00;

        return $type === 'express_1hr'
            ? (float)$zone['express_delivery_fee']
            : (float)$zone['standard_delivery_fee'];
    }

    public function getShippingInfo(string $emirate, string $type, float $subtotal): array
    {
        $fee  = $this->calculateFee($emirate, $type, $subtotal);
        $zone = $this->getByEmirate($emirate);
        $threshold = $zone ? (float)$zone['free_shipping_threshold'] : (float)$this->getSetting('free_shipping_threshold', '100');

        return [
            'fee'       => $fee,
            'is_free'   => $fee === 0.00,
            'label'     => $fee === 0.00 ? 'FREE' : 'AED ' . number_format($fee, 2),
            'eta'       => $type === 'express_1hr'
                ? ($zone['express_hours'] ?? '1 Hour')
                : ($zone['standard_days'] ?? '1-2 Days'),
            'threshold' => $threshold,
            'remaining' => max(0, $threshold - $subtotal),
        ];
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM delivery_zones WHERE is_active = 1 ORDER BY emirate, zone_name'
        );
    }

    private function getSetting(string $key, string $default = ''): string
    {
        $row = $this->db->fetch('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);
        return $row['setting_value'] ?? $default;
    }
}
