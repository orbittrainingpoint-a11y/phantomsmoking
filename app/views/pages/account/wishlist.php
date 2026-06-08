<div class="container section">
  <div class="account-layout">
    <?php include dirname(__DIR__) . '/../components/account-sidebar.php'; ?>
    <div class="account-content">
      <div class="account-page-title">My Wishlist (<?= count($items) ?>)</div>
      <?php if (empty($items)): ?>
      <div class="empty-state"><i class="fas fa-heart"></i><h3>Your wishlist is empty</h3><p>Save products you love for later</p><a href="/" class="btn btn-primary" style="margin-top:12px">Browse Products</a></div>
      <?php else: ?>
      <?php $componentPath = dirname(__DIR__, 2) . '/components'; ?>
      <div class="grid grid-3">
        <?php foreach ($items as $product): ?>
        <?php include $componentPath . '/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
