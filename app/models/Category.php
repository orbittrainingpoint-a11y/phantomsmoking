<?php
namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected string $table = 'categories';

    public function getTree(): array
    {
        $all = $this->db->fetchAll('SELECT * FROM categories WHERE is_active = 1 ORDER BY position ASC');
        return $this->buildTree($all);
    }

    public function getMenuCategories(): array
    {
        $parents = $this->db->fetchAll(
            'SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 AND show_in_menu = 1 ORDER BY position'
        );
        foreach ($parents as &$p) {
            $p['children'] = $this->db->fetchAll(
                'SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY position',
                [$p['id']]
            );
        }
        return $parents;
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->db->fetch('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [$slug]);
    }

    private function buildTree(array $items, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $item['children'] = $this->buildTree($items, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
