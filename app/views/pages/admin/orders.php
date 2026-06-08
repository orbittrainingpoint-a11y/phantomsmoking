<div class="admin-search-bar">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap">
    <input type="text" name="search" class="form-control" placeholder="Order # or customer name..." value="<?= e($filters['search'] ?? '') ?>">
    <select name="status" class="form-control" style="max-width:160px">
      <option value="">All Status</option>
      <?php foreach (['pending','confirmed','processing','packed','out_for_delivery','delivered','cancelled','returned'] as $s): ?>
      <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" class="form-control" style="max-width:160px" value="<?= e($filters['date_from'] ?? '') ?>">
    <input type="date" name="date_to" class="form-control" style="max-width:160px" value="<?= e($filters['date_to'] ?? '') ?>">
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  </form>
</div>
<div class="admin-card">
  <div class="admin-card-body" style="padding:0">
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Order #</th><th>Customer</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($orders['items'] as $order): ?>
          <tr>
            <td><a href="/admin/orders/<?= $order['id'] ?>" style="color:var(--color-secondary);font-weight:700"><?= e($order['order_number']) ?></a></td>
            <td><?= e($order['shipping_name']) ?><br><span style="font-size:0.78rem;color:var(--color-text-muted)"><?= e($order['shipping_phone']) ?></span></td>
            <td><?= format_datetime($order['created_at']) ?></td>
            <td><?= e($order['id']) ?></td>
            <td><strong><?= format_price($order['total_amount']) ?></strong></td>
            <td>
              <span class="badge badge-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($order['payment_status']) ?></span><br>
              <span style="font-size:0.75rem;color:var(--color-text-muted)"><?= payment_method_label($order['payment_method']) ?></span>
            </td>
            <td><?= order_status_badge($order['order_status']) ?></td>
            <td><a href="/admin/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div style="display:flex;justify-content:space-between;margin-top:16px;align-items:center">
  <span style="font-size:0.88rem;color:var(--color-text-muted)"><?= number_format($orders['total']) ?> orders</span>
  <div class="pagination">
    <?php for ($p = max(1, $orders['current_page'] - 2); $p <= min($orders['total_pages'], $orders['current_page'] + 2); $p++): ?>
    <?php if ($p == $orders['current_page']): ?><span class="active"><?= $p ?></span><?php else: ?><a href="?page=<?= $p ?>"><?= $p ?></a><?php endif; ?>
    <?php endfor; ?>
  </div>
</div>
