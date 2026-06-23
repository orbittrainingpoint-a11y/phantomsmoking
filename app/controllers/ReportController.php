<?php
namespace App\Controllers;

use App\Core\Controller;

class ReportController extends Controller
{
    public function __construct() { parent::__construct(); $this->requireAdmin(); }

    private function getFilters(): array
    {
        $tab   = $this->request->get('tab', 'sales');
        $range = $this->request->get('range', 'month');
        $from  = $this->request->get('from', '');
        $to    = $this->request->get('to', '');

        if (!$from || !$to) {
            switch ($range) {
                case 'today':
                    $from = $to = date('Y-m-d'); break;
                case 'week':
                    $from = date('Y-m-d', strtotime('monday this week'));
                    $to   = date('Y-m-d'); break;
                case 'year':
                    $from = date('Y-01-01');
                    $to   = date('Y-m-d'); break;
                default: // month
                    $from = date('Y-m-01');
                    $to   = date('Y-m-d'); break;
            }
        }
        return compact('tab', 'range', 'from', 'to');
    }

    public function index(): void
    {
        $f    = $this->getFilters();
        $data = $this->queryReport($f['tab'], $f['from'], $f['to']);

        $this->render('admin/reports', array_merge($f, $data, [
            'title'       => 'Reports — Admin',
            'last_updated'=> date('d M Y, h:i:s A'),
        ]), 'admin');
    }

    public function export(): void
    {
        $f      = $this->getFilters();
        $format = $this->request->get('format', 'csv');
        $data   = $this->queryReport($f['tab'], $f['from'], $f['to']);

        if ($format === 'csv') {
            $this->exportCsv($f['tab'], $f['from'], $f['to'], $data);
        } else {
            $this->exportPrint($f['tab'], $f['from'], $f['to'], $data);
        }
    }

    public function invoicePdf(string $id): void
    {
        $order = $this->db->fetch('SELECT * FROM orders WHERE id = ?', [(int)$id]);
        if (!$order) { http_response_code(404); echo 'Not found'; exit; }
        $items = $this->db->fetchAll('SELECT * FROM order_items WHERE order_id = ?', [(int)$id]);

        header('Content-Type: text/html; charset=utf-8');
        echo $this->buildInvoiceHtml($order, $items);
        exit;
    }

    // ── Queries ──────────────────────────────────────────────────────────────

    private function queryReport(string $tab, string $from, string $to): array
    {
        $fromDt = $from . ' 00:00:00';
        $toDt   = $to   . ' 23:59:59';

        switch ($tab) {
            case 'inventory': return $this->queryInventory();
            case 'customers': return $this->queryCustomers($fromDt, $toDt);
            case 'invoices':  return $this->queryInvoices($fromDt, $toDt);
            case 'delivery':  return $this->queryDelivery($fromDt, $toDt);
            case 'reviews':   return $this->queryReviews($fromDt, $toDt);
            default:          return $this->querySales($fromDt, $toDt);
        }
    }

    private function querySales(string $from, string $to): array
    {
        $base = 'FROM orders WHERE created_at BETWEEN ? AND ?';
        $p    = [$from, $to];

        $summary = $this->db->fetch("SELECT COUNT(*) as total_orders,
            COALESCE(SUM(total_amount),0) as revenue,
            COALESCE(AVG(total_amount),0) as avg_order,
            COALESCE(SUM(CASE WHEN payment_status='paid' THEN total_amount ELSE 0 END),0) as paid_revenue
            $base", $p);

        $top_products = $this->db->fetchAll("SELECT p.name, p.sku,
            SUM(oi.quantity) as units, SUM(oi.total_price) as revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at BETWEEN ? AND ?
            GROUP BY p.id ORDER BY revenue DESC LIMIT 15", [$from, $to]);

        $payment_breakdown = $this->db->fetchAll("SELECT payment_method,
            COUNT(*) as orders, COALESCE(SUM(total_amount),0) as revenue
            $base GROUP BY payment_method ORDER BY revenue DESC", $p);

        $daily = $this->db->fetchAll("SELECT DATE(created_at) as day,
            COUNT(*) as orders, COALESCE(SUM(total_amount),0) as revenue
            $base GROUP BY DATE(created_at) ORDER BY day", $p);

        return compact('summary', 'top_products', 'payment_breakdown', 'daily');
    }

    private function queryInventory(): array
    {
        $threshold = (int)($this->db->fetch("SELECT setting_value FROM settings WHERE setting_key='low_stock_threshold'")['setting_value'] ?? 5);

        $products = $this->db->fetchAll("SELECT p.id, p.name, p.sku, p.stock_quantity,
            p.low_stock_threshold, c.name as category, b.name as brand
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.status = 'active'
            ORDER BY p.stock_quantity ASC");

        $variants = $this->db->fetchAll("SELECT p.name as product_name, p.sku as product_sku,
            vt.label as variant_type, vto.option_label, vto.stock_qty, vto.sku as variant_sku
            FROM variant_type_options vto
            JOIN product_variant_types vt ON vto.variant_type_id = vt.id
            JOIN products p ON vt.product_id = p.id
            WHERE p.status = 'active'
            ORDER BY vto.stock_qty ASC");

        $low_stock = array_filter($products, fn($p) => $p['stock_quantity'] <= $p['low_stock_threshold']);

        return compact('products', 'variants', 'low_stock', 'threshold');
    }

    private function queryCustomers(string $from, string $to): array
    {
        $summary = $this->db->fetch("SELECT COUNT(*) as total,
            SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as new_customers,
            SUM(CASE WHEN total_orders > 1 THEN 1 ELSE 0 END) as returning_customers
            FROM users WHERE role='customer'", [$from, $to]);

        $top_customers = $this->db->fetchAll("SELECT u.first_name, u.last_name, u.email, u.phone,
            u.total_orders, u.total_spent, u.created_at,
            MAX(o.created_at) as last_order
            FROM users u
            LEFT JOIN orders o ON o.user_id = u.id
            WHERE u.role = 'customer'
            GROUP BY u.id
            ORDER BY u.total_spent DESC LIMIT 50");

        $new_by_day = $this->db->fetchAll("SELECT DATE(created_at) as day, COUNT(*) as count
            FROM users WHERE role='customer' AND created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at) ORDER BY day", [$from, $to]);

        return compact('summary', 'top_customers', 'new_by_day');
    }

    private function queryInvoices(string $from, string $to): array
    {
        $search = sanitize_string($this->request->get('search', ''));
        $status = $this->request->get('payment_status', '');
        $page   = max(1, (int)$this->request->get('page', 1));
        $perPage = 25;

        $where  = ['o.created_at BETWEEN ? AND ?'];
        $params = [$from, $to];

        if ($status) { $where[] = 'o.payment_status = ?'; $params[] = $status; }
        if ($search) {
            $where[] = '(o.order_number LIKE ? OR o.shipping_name LIKE ? OR o.shipping_phone LIKE ?)';
            $params  = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
        }

        $whereStr = implode(' AND ', $where);
        $total    = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM orders o WHERE $whereStr", $params)['cnt'] ?? 0);
        $offset   = ($page - 1) * $perPage;

        $invoices = $this->db->fetchAll("SELECT o.id, o.order_number, o.shipping_name, o.shipping_phone,
            o.shipping_emirate, o.total_amount, o.payment_method, o.payment_status,
            o.order_status, o.created_at
            FROM orders o WHERE $whereStr
            ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset", $params);

        $pagination = ['total' => $total, 'per_page' => $perPage, 'current_page' => $page,
            'total_pages' => (int)ceil($total / $perPage)];

        return compact('invoices', 'pagination', 'search', 'status');
    }

    private function queryDelivery(string $from, string $to): array
    {
        $summary = $this->db->fetchAll("SELECT order_status,
            COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue
            FROM orders WHERE created_at BETWEEN ? AND ?
            GROUP BY order_status ORDER BY count DESC", [$from, $to]);

        $delivery_types = $this->db->fetchAll("SELECT delivery_type,
            COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue
            FROM orders WHERE created_at BETWEEN ? AND ?
            GROUP BY delivery_type ORDER BY count DESC", [$from, $to]);

        $orders = $this->db->fetchAll("SELECT o.order_number, o.shipping_name, o.shipping_phone,
            o.shipping_emirate, o.delivery_type, o.order_status, o.total_amount, o.created_at, o.delivered_at
            FROM orders o WHERE o.created_at BETWEEN ? AND ?
            ORDER BY o.created_at DESC LIMIT 100", [$from, $to]);

        return compact('summary', 'delivery_types', 'orders');
    }

    private function queryReviews(string $from, string $to): array
    {
        $status = $this->request->get('review_status', '');
        $where  = ['r.created_at BETWEEN ? AND ?'];
        $params = [$from, $to];
        if ($status) { $where[] = 'r.status = ?'; $params[] = $status; }

        $reviews = $this->db->fetchAll("SELECT r.id, r.rating, r.title, r.body, r.status,
            r.created_at, r.is_verified_purchase,
            p.name as product_name, p.sku as product_sku,
            u.first_name, u.last_name, u.email
            FROM product_reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY r.created_at DESC", $params);

        $summary = $this->db->fetch("SELECT COUNT(*) as total,
            COALESCE(AVG(rating),0) as avg_rating,
            SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rejected
            FROM product_reviews r WHERE " . implode(' AND ', $where), $params);

        return compact('reviews', 'summary', 'status');
    }

    // ── CSV Export ────────────────────────────────────────────────────────────

    private function exportCsv(string $tab, string $from, string $to, array $data): void
    {
        $filename = "report_{$tab}_{$from}_{$to}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            http_response_code(500);
            echo 'Unable to open output stream';
            return;
        }
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

        switch ($tab) {
            case 'sales':
                fputcsv($out, ['Product', 'SKU', 'Units Sold', 'Revenue (AED)']);
                foreach ($data['top_products'] as $r)
                    fputcsv($out, [$r['name'], $r['sku'], $r['units'], number_format($r['revenue'],2)]);
                fputcsv($out, []);
                fputcsv($out, ['Payment Method', 'Orders', 'Revenue (AED)']);
                foreach ($data['payment_breakdown'] as $r)
                    fputcsv($out, [payment_method_label($r['payment_method']), $r['orders'], number_format($r['revenue'],2)]);
                break;

            case 'inventory':
                fputcsv($out, ['Product', 'SKU', 'Category', 'Brand', 'Stock', 'Low Stock Threshold', 'Status']);
                foreach ($data['products'] as $r)
                    fputcsv($out, [$r['name'], $r['sku'], $r['category'], $r['brand'], $r['stock_quantity'],
                        $r['low_stock_threshold'], $r['stock_quantity'] <= $r['low_stock_threshold'] ? 'LOW STOCK' : 'OK']);
                fputcsv($out, []);
                fputcsv($out, ['Product', 'Product SKU', 'Variant Type', 'Option', 'Variant SKU', 'Stock']);
                foreach ($data['variants'] as $r)
                    fputcsv($out, [$r['product_name'], $r['product_sku'], $r['variant_type'], $r['option_label'], $r['variant_sku'], $r['stock_qty']]);
                break;

            case 'customers':
                fputcsv($out, ['Name', 'Email', 'Phone', 'Total Orders', 'Total Spent (AED)', 'Member Since', 'Last Order']);
                foreach ($data['top_customers'] as $r)
                    fputcsv($out, [$r['first_name'].' '.$r['last_name'], $r['email'], $r['phone'],
                        $r['total_orders'], number_format($r['total_spent'],2), $r['created_at'], $r['last_order']]);
                break;

            case 'invoices':
                fputcsv($out, ['Order #', 'Customer', 'Phone', 'Emirate', 'Total (AED)', 'Payment Method', 'Payment Status', 'Order Status', 'Date']);
                foreach ($data['invoices'] as $r)
                    fputcsv($out, [$r['order_number'], $r['shipping_name'], $r['shipping_phone'],
                        $r['shipping_emirate'], number_format($r['total_amount'],2),
                        payment_method_label($r['payment_method']), $r['payment_status'], $r['order_status'], $r['created_at']]);
                break;

            case 'delivery':
                fputcsv($out, ['Order #', 'Customer', 'Phone', 'Emirate', 'Delivery Type', 'Status', 'Total (AED)', 'Order Date', 'Delivered At']);
                foreach ($data['orders'] as $r)
                    fputcsv($out, [$r['order_number'], $r['shipping_name'], $r['shipping_phone'],
                        $r['shipping_emirate'], $r['delivery_type'], $r['order_status'],
                        number_format($r['total_amount'],2), $r['created_at'], $r['delivered_at'] ?? '—']);
                break;

            case 'reviews':
                fputcsv($out, ['Product', 'SKU', 'Customer', 'Email', 'Rating', 'Title', 'Review', 'Status', 'Verified', 'Date']);
                foreach ($data['reviews'] as $r)
                    fputcsv($out, [$r['product_name'], $r['product_sku'],
                        $r['first_name'].' '.$r['last_name'], $r['email'],
                        $r['rating'], $r['title'], $r['body'], $r['status'],
                        $r['is_verified_purchase'] ? 'Yes' : 'No', $r['created_at']]);
                break;
        }

        fclose($out);
        exit;
    }

    // ── Print/PDF Export ──────────────────────────────────────────────────────

    private function exportPrint(string $tab, string $from, string $to, array $data): void
    {
        $titles = ['sales'=>'Sales Report','inventory'=>'Inventory Report','customers'=>'Customer Report',
                   'invoices'=>'Invoice Report','delivery'=>'Delivery Report','reviews'=>'Reviews Report'];
        $title  = $titles[$tab] ?? 'Report';
        $store  = $this->db->fetch("SELECT setting_value FROM settings WHERE setting_key='store_name'")['setting_value'] ?? 'Phantom Smoking';
        $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

        $rows = '';
        $headers = '';

        switch ($tab) {
            case 'sales':
                $headers = '<tr><th>Product</th><th>SKU</th><th>Units Sold</th><th>Revenue (AED)</th></tr>';
                foreach ($data['top_products'] as $r)
                    $rows .= '<tr><td>'.e($r['name']).'</td><td>'.e($r['sku']).'</td><td>'.intval($r['units']).'</td><td>'.number_format($r['revenue'],2).'</td></tr>';
                break;
            case 'inventory':
                $headers = '<tr><th>Product</th><th>SKU</th><th>Category</th><th>Stock</th><th>Status</th></tr>';
                foreach ($data['products'] as $r)
                    $rows .= '<tr><td>'.e($r['name']).'</td><td>'.e($r['sku']).'</td><td>'.e($r['category']).'</td><td>'.intval($r['stock_quantity']).'</td><td>'.($r['stock_quantity']<=$r['low_stock_threshold']?'<span style="color:red">LOW</span>':'OK').'</td></tr>';
                break;
            case 'customers':
                $headers = '<tr><th>Name</th><th>Email</th><th>Orders</th><th>Total Spent</th><th>Since</th></tr>';
                foreach ($data['top_customers'] as $r)
                    $rows .= '<tr><td>'.e($r['first_name'].' '.$r['last_name']).'</td><td>'.e($r['email']).'</td><td>'.intval($r['total_orders']).'</td><td>AED '.number_format($r['total_spent'],2).'</td><td>'.e($r['created_at']).'</td></tr>';
                break;
            case 'invoices':
                $headers = '<tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr>';
                foreach ($data['invoices'] as $r)
                    $rows .= '<tr><td>'.e($r['order_number']).'</td><td>'.e($r['shipping_name']).'</td><td>AED '.number_format($r['total_amount'],2).'</td><td>'.e(payment_method_label($r['payment_method'])).'</td><td>'.e($r['payment_status']).'</td><td>'.e($r['created_at']).'</td></tr>';
                break;
            case 'delivery':
                $headers = '<tr><th>Order #</th><th>Customer</th><th>Emirate</th><th>Type</th><th>Status</th><th>Total</th><th>Date</th></tr>';
                foreach ($data['orders'] as $r)
                    $rows .= '<tr><td>'.e($r['order_number']).'</td><td>'.e($r['shipping_name']).'</td><td>'.e($r['shipping_emirate']).'</td><td>'.e($r['delivery_type']).'</td><td>'.e($r['order_status']).'</td><td>AED '.number_format($r['total_amount'],2).'</td><td>'.e($r['created_at']).'</td></tr>';
                break;
            case 'reviews':
                $headers = '<tr><th>Product</th><th>Customer</th><th>Rating</th><th>Title</th><th>Status</th><th>Date</th></tr>';
                foreach ($data['reviews'] as $r)
                    $rows .= '<tr><td>'.e($r['product_name']).'</td><td>'.e($r['first_name'].' '.$r['last_name']).'</td><td>'.str_repeat('&#9733;',(int)$r['rating']).'</td><td>'.e($r['title']).'</td><td>'.e($r['status']).'</td><td>'.e($r['created_at']).'</td></tr>';
                break;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>{$title}</title>
<style>
  body{font-family:Arial,sans-serif;font-size:11px;margin:20px;color:#222}
  .header{text-align:center;margin-bottom:20px;border-bottom:2px solid #C8963C;padding-bottom:12px}
  .header img{height:50px}
  .header h1{margin:6px 0 2px;font-size:18px;color:#1A1A2E}
  .header p{margin:2px 0;color:#666;font-size:11px}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th{background:#1A1A2E;color:#fff;padding:7px 8px;text-align:left;font-size:10px}
  td{padding:6px 8px;border-bottom:1px solid #eee;font-size:10px}
  tr:nth-child(even) td{background:#f9f9f9}
  .footer{margin-top:20px;text-align:center;font-size:9px;color:#999;border-top:1px solid #eee;padding-top:8px}
  @media print{@page{size:A4 landscape;margin:10mm} button{display:none}}
</style>
</head><body>
<div class="header">
  <img src="{$baseUrl}/assets/images/logo.webp" alt="{$store}">
  <h1>{$title}</h1>
  <p>{$store} &nbsp;|&nbsp; Period: {$from} to {$to} &nbsp;|&nbsp; Generated: {$this->now()}</p>
</div>
<button onclick="window.print()" style="margin-bottom:12px;padding:8px 20px;background:#C8963C;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px">🖨 Print / Save as PDF</button>
<table><thead>{$headers}</thead><tbody>{$rows}</tbody></table>
<div class="footer">Phantom Smoking &mdash; Confidential Report &mdash; {$this->now()}</div>
</body></html>
HTML;
        exit;
    }

    private function buildInvoiceHtml(array $order, array $items): string
    {
        $store   = e($this->db->fetch("SELECT setting_value FROM settings WHERE setting_key='store_name'")['setting_value'] ?? 'Phantom Smoking');
        $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        $rows    = '';
        foreach ($items as $i)
            $rows .= '<tr><td>'.e($i['product_name']).'</td><td>'.e($i['variant_name'] ?? '').'</td><td>'.intval($i['quantity']).'</td><td>AED '.number_format($i['unit_price'],2).'</td><td>AED '.number_format($i['total_price'],2).'</td></tr>';

        $subtotal   = number_format($order['subtotal'],2);
        $discount   = number_format($order['discount_amount'],2);
        $shipping   = number_format($order['shipping_cost'],2);
        $tax        = number_format($order['tax_amount'],2);
        $total      = number_format($order['total_amount'],2);
        $pmLabel    = e(payment_method_label($order['payment_method']));
        $orderNum   = e($order['order_number']);
        $orderDate  = e($order['created_at']);
        $payStatus  = e($order['payment_status']);
        $shipName   = e($order['shipping_name']);
        $shipPhone  = e($order['shipping_phone']);
        $shipAddr   = e($order['shipping_address_line1']);
        $shipArea   = e($order['shipping_area']);
        $shipEmir   = e($order['shipping_emirate']);
        $delivType  = e($order['delivery_type']);
        $ordStatus  = e($order['order_status']);
        $logoUrl    = e($baseUrl . '/assets/images/logo.webp');

        return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invoice {$orderNum}</title>
<style>
  body{font-family:Arial,sans-serif;font-size:12px;margin:30px;color:#222}
  .top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px}
  .logo img{height:55px}
  .inv-title{text-align:right}
  .inv-title h1{font-size:22px;color:#1A1A2E;margin:0}
  .inv-title p{margin:2px 0;color:#666;font-size:11px}
  .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;padding:14px;background:#f9f9f9;border-radius:6px}
  .info-grid h4{margin:0 0 6px;font-size:11px;text-transform:uppercase;color:#C8963C}
  .info-grid p{margin:2px 0;font-size:11px}
  table{width:100%;border-collapse:collapse;margin-bottom:16px}
  th{background:#1A1A2E;color:#fff;padding:8px;text-align:left;font-size:11px}
  td{padding:7px 8px;border-bottom:1px solid #eee;font-size:11px}
  .totals{width:280px;margin-left:auto}
  .totals tr td{padding:5px 8px}
  .totals .grand td{font-weight:bold;font-size:13px;border-top:2px solid #1A1A2E}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:bold}
  .paid{background:#d4edda;color:#155724} .pending{background:#fff3cd;color:#856404} .failed{background:#f8d7da;color:#721c24}
  @media print{@page{size:A4;margin:10mm} button{display:none}}
</style></head><body>
<button onclick="window.print()" style="margin-bottom:16px;padding:8px 20px;background:#C8963C;color:#fff;border:none;border-radius:4px;cursor:pointer">Print Invoice</button>
<div class="top">
  <div class="logo"><img src="{$logoUrl}" alt="{$store}"><br><small style="color:#666">{$store}</small></div>
  <div class="inv-title"><h1>INVOICE</h1><p><strong>{$orderNum}</strong></p><p>Date: {$orderDate}</p><p>Payment: <span class="badge {$payStatus}">{$payStatus}</span></p></div>
</div>
<div class="info-grid">
  <div><h4>Bill To</h4><p><strong>{$shipName}</strong></p><p>{$shipPhone}</p><p>{$shipAddr}</p><p>{$shipArea}, {$shipEmir}, UAE</p></div>
  <div><h4>Payment Info</h4><p>Method: {$pmLabel}</p><p>Status: {$payStatus}</p><p>Delivery: {$delivType}</p><p>Order Status: {$ordStatus}</p></div>
</div>
<table><thead><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead><tbody>{$rows}</tbody></table>
<table class="totals">
  <tr><td>Subtotal</td><td>AED {$subtotal}</td></tr>
  <tr><td>Discount</td><td>- AED {$discount}</td></tr>
  <tr><td>Shipping</td><td>AED {$shipping}</td></tr>
  <tr><td>VAT (5%)</td><td>AED {$tax}</td></tr>
  <tr class="grand"><td>TOTAL</td><td>AED {$total}</td></tr>
</table>
<p style="font-size:10px;color:#999;margin-top:20px;text-align:center">Thank you for shopping with {$store} &mdash; phantomsmoking.ae</p>
</body></html>
HTML;
    }

    private function now(): string { return date('d M Y, h:i A'); }
}
