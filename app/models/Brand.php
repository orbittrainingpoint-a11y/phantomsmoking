<?php
namespace App\Models;

use App\Core\Model;

class Brand extends Model
{
    protected string $table = 'brands';

    public function getFeatured(int $limit = 12): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM brands WHERE is_featured = 1 AND is_active = 1 ORDER BY position LIMIT ?',
            [$limit]
        );
    }

    public function getAll(): array
    {
        return $this->db->fetchAll('SELECT * FROM brands WHERE is_active = 1 ORDER BY name');
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->db->fetch('SELECT * FROM brands WHERE slug = ? AND is_active = 1', [$slug]);
    }
}
