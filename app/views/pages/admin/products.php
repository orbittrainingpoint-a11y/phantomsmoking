<div class="admin-search-bar">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap">
    <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= e($search) ?>">
    <select name="status" class="form-control" style="max-width:160px">
      <option value="">All Status</option>
      <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
      <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
    <a href="/admin/products/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Product</a>
  </form>
</div>
<div class="admin-card">
  <div class="admin-card-body" style="padding:0">
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th><input type="checkbox" id="selectAll"></th><th>Product</th><th>SKU</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr id="product-row-<?= $p['id'] ?>">
            <td><input type="checkbox" class="row-check" value="<?= $p['id'] ?>"></td>
            <td style="display:flex;align-items:center;gap:10px">
              <?php if ($p['primary_image']): ?><img src="<?= e($p['primary_image']) ?>" class="product-thumb"><?php endif; ?>
              <div><strong><?= e(truncate($p['name'], 40)) ?></strong><div style="font-size:0.75rem;color:var(--color-text-muted)"><?= e($p['brand_name'] ?? '') ?></div></div>
            </td>
            <td style="font-family:var(--font-mono);font-size:0.82rem"><?= e($p['sku']) ?></td>
            <td><?= e($p['category_name'] ?? '') ?></td>
            <td><strong><?= format_price($p['price']) ?></strong><?php if ($p['compare_at_price']): ?><br><span style="text-decoration:line-through;font-size:0.8rem;color:var(--color-text-muted)"><?= format_price($p['compare_at_price']) ?></span><?php endif; ?></td>
            <td><span style="color:<?= $p['stock_quantity'] <= 0 ? 'var(--color-error)' : ($p['stock_quantity'] <= 5 ? 'var(--color-warning)' : 'var(--color-success)') ?>;font-weight:700"><?= $p['stock_quantity'] ?></span></td>
            <td><span class="badge badge-<?= $p['status'] === 'active' ? 'success' : ($p['status'] === 'draft' ? 'warning' : 'secondary') ?>"><?= ucfirst($p['status']) ?></span></td>
            <td style="display:flex;gap:6px">
              <a href="/admin/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
              <?php if ($p['status'] === 'archived'): ?>
                <button onclick="restoreProduct(<?= $p['id'] ?>)" class="btn btn-sm btn-success" title="Restore to Active"><i class="fas fa-undo"></i></button>
                <button onclick="destroyProduct(<?= $p['id'] ?>, '<?= e(addslashes($p['name'])) ?>')" class="btn btn-sm btn-danger" title="Delete Permanently"><i class="fas fa-trash"></i></button>
              <?php else: ?>
                <button onclick="archiveProduct(<?= $p['id'] ?>)" class="btn btn-sm btn-warning" title="Archive"><i class="fas fa-archive"></i></button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px">
  <span style="font-size:0.88rem;color:var(--color-text-muted)"><?= number_format($total) ?> products total</span>
  <div class="pagination">
    <?php $totalPages = (int)ceil($total / 20); for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
    <?php if ($p == $page): ?><span class="active"><?= $p ?></span><?php else: ?><a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a><?php endif; ?>
    <?php endfor; ?>
  </div>
</div>
