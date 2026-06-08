<div class="container section">
  <div class="account-layout">
    <?php include dirname(__DIR__) . '/../components/account-sidebar.php'; ?>
    <div class="account-content">
      <div class="account-page-title">Welcome back, <?= e($user['first_name']) ?>! 👋</div>
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-value"><?= $stats['total_orders'] ?></div><div class="stat-label">Total Orders</div></div>
        <div class="stat-card"><div class="stat-value"><?= format_price($stats['total_spent']) ?></div><div class="stat-label">Total Spent</div></div>
        <div class="stat-card"><div class="stat-value" style="color:var(--color-secondary)"><?= number_format($stats['reward_points']) ?></div><div class="stat-label">Reward Points</div></div>
        <div class="stat-card"><div class="stat-value"><?= $stats['wishlist_count'] ?></div><div class="stat-label">Wishlist Items</div></div>
      </div>
      <?php if (!empty($recent_orders)): ?>
      <h3 style="font-family:var(--font-heading);margin-bottom:16px">Recent Orders</h3>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($recent_orders as $order): ?>
            <tr>
              <td><strong><?= e($order['order_number']) ?></strong></td>
              <td><?= format_date($order['created_at']) ?></td>
              <td><?= $order['id'] ?></td>
              <td><strong><?= format_price($order['total_amount']) ?></strong></td>
              <td><?= order_status_badge($order['order_status']) ?></td>
              <td><a href="/account/order/<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <a href="/account/orders" class="btn btn-outline btn-sm" style="margin-top:12px">View All Orders</a>
      <?php endif; ?>
    </div>
  </div>
</div>
