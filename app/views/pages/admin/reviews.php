<div class="admin-card">
  <div class="admin-card-header"><div class="admin-card-title">Reviews Pending Approval</div></div>
  <div class="admin-card-body" style="padding:0">
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Review</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($reviews['items'] as $r): ?>
          <tr>
            <td><?= e(truncate($r['product_name'], 30)) ?></td>
            <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
            <td><div class="stars"><?= star_rating($r['rating']) ?></div></td>
            <td><?php if ($r['title']): ?><strong><?= e($r['title']) ?></strong><br><?php endif; ?><?= e(truncate($r['body'] ?? '', 80)) ?></td>
            <td><?= format_date($r['created_at']) ?></td>
            <td style="display:flex;gap:6px">
              <form method="POST" action="/admin/reviews/<?= $r['id'] ?>/approve"><?= csrf_field() ?><button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button></form>
              <form method="POST" action="/admin/reviews/<?= $r['id'] ?>/reject"><?= csrf_field() ?><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button></form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($reviews['items'])): ?>
          <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--color-text-muted)"><i class="fas fa-check-circle" style="color:var(--color-success)"></i> No pending reviews</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
