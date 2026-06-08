<?php
namespace App\Models;

use App\Core\Model;

class Banner extends Model
{
    protected string $table = 'banners';

    public function getActive(string $position = 'hero'): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->fetchAll(
            'SELECT * FROM banners WHERE position = ? AND is_active = 1
             AND (start_date IS NULL OR start_date <= ?)
             AND (end_date IS NULL OR end_date >= ?)
             ORDER BY sort_order ASC',
            [$position, $now, $now]
        );
    }
}
