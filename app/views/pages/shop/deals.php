<div class="shop-header">
  <div class="container">
    <h1 class="shop-header-title">🔥 Deals & Offers</h1>
    <p style="color:var(--color-text-muted)">Best prices on premium tobacco, vapes, shisha and more</p>
  </div>
</div>
<div class="container section">
  <?php if (empty($products)): ?>
  <div class="empty-state"><i class="fas fa-tag"></i><h3>No deals right now</h3><p>Check back soon for great offers!</p></div>
  <?php else: ?>
  <?php $componentPath = dirname(__DIR__, 2) . '/components'; ?>
  <div class="grid grid-4">
    <?php foreach ($products as $product): ?>
    <?php include $componentPath . '/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
