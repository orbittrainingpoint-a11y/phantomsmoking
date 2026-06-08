<div class="shop-header">
  <div class="container">
    <h1 class="shop-header-title"><?= e($heading ?? ($query ? "Results for \"$query\"" : 'Search Products')) ?></h1>
    <p style="color:var(--color-text-muted)"><?= number_format($products['total']) ?> products found</p>
  </div>
</div>
<div class="container section">
  <?php if (empty($products['items'])): ?>
  <div class="empty-state"><i class="fas fa-search"></i><h3>No results found</h3><p>Try different keywords or browse our categories</p><a href="/" class="btn btn-primary" style="margin-top:12px">Browse All Products</a></div>
  <?php else: ?>
  <?php $componentPath = dirname(__DIR__, 2) . '/components'; ?>
  <div class="grid grid-4">
    <?php foreach ($products['items'] as $product): ?>
    <?php include $componentPath . '/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?php if ($products['total_pages'] > 1): ?>
  <div class="pagination">
    <?php for ($p = max(1, $products['current_page'] - 2); $p <= min($products['total_pages'], $products['current_page'] + 2); $p++): ?>
    <?php if ($p == $products['current_page']): ?><span class="active"><?= $p ?></span><?php else: ?><a href="?q=<?= urlencode($query) ?>&page=<?= $p ?>"><?= $p ?></a><?php endif; ?>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
