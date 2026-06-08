<div style="display:grid;grid-template-columns:1fr 320px;gap:24px">
  <div>
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title"><?= e($customer['first_name'] . ' ' . $customer['last_name']) ?></div>
        <span class="badge badge-<?= $customer['is_active'] ? 'success' : 'danger' ?>"><?= $customer['is_active'] ? 'Active' : 'Banned' ?></span>
      </div>
      <div class="admin-card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:0.9rem">
          <div><strong>Email:</strong> <?= e($customer['email']) ?></div>
          <div><strong>Phone:</strong> <?= e($customer['phone'] ?? '—') ?></div>
          <div><strong>Joined:</strong> <?= format_date($customer['created_at']) ?></div>
          <div><strong>Last Login:</strong> <?= $customer['last_login_at'] ? format_datetime($customer['last_login_at']) : 'Never' ?></div>
          <div><strong>Total Orders:</strong> <?= $customer['total_orders'] ?></div>
          <div><strong>Total Spent:</strong> <?= format_price($customer['total_spent']) ?></div>
          <div><strong>Reward Points:</strong> <span style="color:var(--color-secondary);font-weight:700"><?= number_format($customer['reward_points']) ?></span></div>
          <div><strong>Age Verified:</strong> <?= $customer['age_verified'] ? '✅ Yes' : '❌ No' ?></div>
        </div>
        <?php if ($customer['banned_reason']): ?><div class="alert alert-error" style="margin-top:16px"><strong>Ban Reason:</strong> <?= e($customer['banned_reason']) ?></div><?php endif; ?>
      </div>
    </div>
    <div class="admin-card">
      <div class="admin-card-header"><div class="admin-card-title">Recent Orders</div></div>
      <div class="admin-card-body" style="padding:0">
        <div class="table-wrap">
          <table class="admin-table">
            <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
              <tr>
                <td><strong><?= e($order['order_number']) ?></strong></td>
                <td><?= format_date($order['created_at']) ?></td>
                <td><?= format_price($order['total_amount']) ?></td>
                <td><?= order_status_badge($order['order_status']) ?></td>
                <td><a href="/admin/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($orders)): ?><tr><td colspan="5" style="text-align:center;padding:20px;color:var(--color-text-muted)">No orders yet</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div>
    <div class="admin-card">
      <div class="admin-card-header"><div class="admin-card-title">Actions</div></div>
      <div class="admin-card-body">
        <form method="POST" action="/admin/customers/<?= $customer['id'] ?>/ban">
          <?= csrf_field() ?>
          <?php if ($customer['is_active']): ?>
          <div class="form-group"><label class="form-label">Ban Reason</label><textarea name="reason" class="form-control" rows="2" placeholder="Reason for ban..."></textarea></div>
          <button type="submit" class="btn btn-danger btn-full">Ban Customer</button>
          <?php else: ?>
          <button type="submit" class="btn btn-success btn-full">Unban Customer</button>
          <?php endif; ?>
        </form>
        <hr class="divider">
        <a href="/admin/customers" class="btn btn-outline btn-full btn-sm">← Back to Customers</a>
      </div>
    </div>
  </div>
</div>
