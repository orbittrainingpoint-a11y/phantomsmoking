<?php
namespace App\Core;

abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?", [$id]);
    }

    public function findBy(string $col, mixed $val): ?array
    {
        return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `$col` = ? LIMIT 1", [$val]);
    }

    public function findAll(array $conditions = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        $params = [];
        if (!empty($conditions)) {
            $where = implode(' AND ', array_map(fn($k) => "`$k` = ?", array_keys($conditions)));
            $sql .= " WHERE $where";
            $params = array_values($conditions);
        }
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit > 0) $sql .= " LIMIT $limit";
        if ($offset > 0) $sql .= " OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, "`{$this->primaryKey}` = ?", [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]);
    }

    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        $params = [];
        if (!empty($conditions)) {
            $where = implode(' AND ', array_map(fn($k) => "`$k` = ?", array_keys($conditions)));
            $sql .= " WHERE $where";
            $params = array_values($conditions);
        }
        return (int)$this->db->fetch($sql, $params)['COUNT(*)'];
    }

    public function paginate(int $page, int $perPage, array $conditions = [], string $orderBy = ''): array
    {
        $total = $this->count($conditions);
        $offset = ($page - 1) * $perPage;
        $items = $this->findAll($conditions, $orderBy, $perPage, $offset);
        return [
            'items'       => $items,
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }
}
