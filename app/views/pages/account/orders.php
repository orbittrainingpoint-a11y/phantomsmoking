<div class="container section">
  <div class="account-layout">
    <?php include dirname(__DIR__) . '/../components/account-sidebar.php'; ?>
    <div class="account-content">
      <div class="account-page-title">My Orders</div>
      <?php if (empty($orders['items'])): ?>
      <div class="empty-state"><i class="fas fa-shopping-bag"></i><h3>No orders yet</h3><p>Start shopping to see your orders here</p><a href="/" class="btn btn-primary" style="margin-top:12px">Shop Now</a></div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($orders['items'] as $order): ?>
            <tr>
              <td><strong><?= e($order['order_number']) ?></strong></td>
              <td><?= format_date($order['created_at']) ?></td>
              <td><strong><?= format_price($order['total_amount']) ?></strong></td>
              <td><span class="badge badge-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($order['payment_status']) ?></span></td>
              <td><?= order_status_badge($order['order_status']) ?></td>
              <td><a href="/account/order/<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <?php if ($orders['total_pages'] > 1): ?>
      <div class="pagination">
        <?php for ($p = 1; $p <= $orders['total_pages']; $p++): ?>
        <?php if ($p == $orders['current_page']): ?><span class="active"><?= $p ?></span><?php else: ?><a href="?page=<?= $p ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
