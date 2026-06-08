<div class="admin-search-bar">
  <form method="GET" style="display:flex;gap:12px">
    <input type="text" name="search" class="form-control" placeholder="Search customers..." value="<?= e($search) ?>">
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
  </form>
</div>
<div class="admin-card">
  <div class="admin-card-body" style="padding:0">
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Customer</th><th>Email</th><th>Phone</th><th>Orders</th><th>Spent</th><th>Points</th><th>Status</th><th>Joined</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($customers['items'] as $c): ?>
          <tr>
            <td><strong><?= e($c['first_name'] . ' ' . $c['last_name']) ?></strong></td>
            <td><?= e($c['email']) ?></td>
            <td><?= e($c['phone'] ?? '—') ?></td>
            <td><?= $c['total_orders'] ?></td>
            <td><?= format_price($c['total_spent']) ?></td>
            <td><?= number_format($c['reward_points']) ?></td>
            <td><span class="status-dot <?= $c['is_active'] ? 'active' : 'inactive' ?>"></span><?= $c['is_active'] ? 'Active' : 'Banned' ?></td>
            <td><?= format_date($c['created_at']) ?></td>
            <td><a href="/admin/customers/<?= $c['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
