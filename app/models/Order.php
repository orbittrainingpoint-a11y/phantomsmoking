<?php
namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected string $table = 'orders';

    public function createOrder(array $data, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $orderId = $this->db->insert('orders', $data);
            foreach ($items as $item) {
                $this->db->insert('order_items', array_merge($item, ['order_id' => $orderId]));
            }
            $this->db->insert('order_status_history', [
                'order_id' => $orderId,
                'status'   => 'pending',
                'note'     => 'Order placed',
            ]);
            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function getOrderWithItems(int $orderId): ?array
    {
        $order = $this->find($orderId);
        if (!$order) return null;
        $items = $this->db->fetchAll(
            'SELECT oi.*, pi.image_path AS product_image
             FROM order_items oi
             LEFT JOIN product_images pi ON pi.product_id = oi.product_id AND pi.is_primary = 1
             WHERE oi.order_id = ?', [$orderId]
        );
        // Resolve variant label: prefer stored variant_name, fall back to selected_flavours
        foreach ($items as &$item) {
            if (empty($item['variant_name']) && !empty($item['selected_flavours'])) {
                $item['variant_name'] = $item['selected_flavours'];
            }
        }
        unset($item);
        $order['items'] = $items;
        $order['status_history'] = $this->db->fetchAll(
            'SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC', [$orderId]
        );
        return $order;
    }

    public function getByOrderNumber(string $orderNumber): ?array
    {
        return $this->db->fetch('SELECT * FROM orders WHERE order_number = ?', [$orderNumber]);
    }

    public function getUserOrders(int $userId, int $page = 1, int $perPage = 10): array
    {
        $total = (int)$this->db->fetch('SELECT COUNT(*) as cnt FROM orders WHERE user_id = ?', [$userId])['cnt'];
        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $perPage, $offset]
        );
        return ['items' => $items, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'total_pages' => (int)ceil($total / $perPage)];
    }

    public function updateStatus(int $orderId, string $status, string $note = '', ?int $adminId = null): void
    {
        $this->update($orderId, ['order_status' => $status]);
        $this->db->insert('order_status_history', [
            'order_id'            => $orderId,
            'status'              => $status,
            'note'                => $note,
            'created_by_user_id'  => $adminId,
        ]);
        if ($status === 'delivered') {
            $this->update($orderId, ['delivered_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function getPaginated(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) { $where[] = 'order_status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['payment_status'])) { $where[] = 'payment_status = ?'; $params[] = $filters['payment_status']; }
        if (!empty($filters['search'])) {
            $where[] = '(order_number LIKE ? OR shipping_name LIKE ? OR shipping_phone LIKE ?)';
            $params = array_merge($params, ["%{$filters['search']}%", "%{$filters['search']}%", "%{$filters['search']}%"]);
        }
        if (!empty($filters['date_from'])) { $where[] = 'created_at >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $where[] = 'created_at <= ?'; $params[] = $filters['date_to'] . ' 23:59:59'; }

        $whereStr = implode(' AND ', $where);
        $total = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM orders WHERE $whereStr", $params)['cnt'];
        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            "SELECT * FROM orders WHERE $whereStr ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );
        return ['items' => $items, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'total_pages' => (int)ceil($total / $perPage)];
    }

    public function getDashboardStats(): array
    {
        $today = date('Y-m-d');
        $todayRevenue = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) as rev, COUNT(*) as cnt FROM orders WHERE DATE(created_at) = ? AND payment_status = 'paid'",
            [$today]
        );
        $pending = $this->db->fetch("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'pending'", []);
        $newCustomers = $this->db->fetch("SELECT COUNT(*) as cnt FROM users WHERE DATE(created_at) = ? AND role = 'customer'", [$today]);
        $monthRevenue = $this->db->fetch(
            "SELECT COALESCE(SUM(total_amount),0) as rev FROM orders WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) AND payment_status = 'paid'",
            []
        );
        return [
            'today_revenue'  => (float)$todayRevenue['rev'],
            'today_orders'   => (int)$todayRevenue['cnt'],
            'pending_orders' => (int)$pending['cnt'],
            'new_customers'  => (int)$newCustomers['cnt'],
            'month_revenue'  => (float)$monthRevenue['rev'],
        ];
    }

    public function getSalesReport(string $from, string $to, string $group = 'day'): array
    {
        $groupBy = match ($group) {
            'week'  => 'YEARWEEK(created_at)',
            'month' => 'DATE_FORMAT(created_at, "%Y-%m")',
            default => 'DATE(created_at)',
        };
        $label = match ($group) {
            'week'  => 'YEARWEEK(created_at)',
            'month' => 'DATE_FORMAT(created_at, "%Y-%m")',
            default => 'DATE(created_at)',
        };
        return $this->db->fetchAll(
            "SELECT $label as period, COUNT(*) as orders, SUM(total_amount) as revenue
             FROM orders WHERE created_at BETWEEN ? AND ? AND payment_status = 'paid'
             GROUP BY $groupBy ORDER BY period",
            [$from, $to . ' 23:59:59']
        );
    }
}
