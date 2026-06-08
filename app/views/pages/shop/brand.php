<div class="shop-header">
  <div class="container">
    <div class="breadcrumb"><a href="/">Home</a><span class="breadcrumb-sep">/</span><a href="/brands">Brands</a><span class="breadcrumb-sep">/</span><span><?= e($brand['name']) ?></span></div>
    <div style="display:flex;align-items:center;gap:20px">
      <?php if ($brand['logo']): ?><img src="<?= e($brand['logo']) ?>" alt="<?= e($brand['name']) ?>" style="height:60px;object-fit:contain"><?php endif; ?>
      <div>
        <h1 class="shop-header-title"><?= e($brand['name']) ?></h1>
        <?php if ($brand['country_of_origin']): ?><p style="color:var(--color-text-muted)">Origin: <?= e($brand['country_of_origin']) ?></p><?php endif; ?>
      </div>
    </div>
    <?php if ($brand['description']): ?><p style="color:var(--color-text-muted);max-width:600px;margin-top:8px"><?= e($brand['description']) ?></p><?php endif; ?>
  </div>
</div>
<div class="container section">
  <?php if (empty($products)): ?>
  <div class="empty-state"><i class="fas fa-box"></i><h3>No products found</h3><p>No products available for this brand yet.</p></div>
  <?php else: ?>
  <?php $componentPath = dirname(__DIR__, 2) . '/components'; ?>
  <div class="grid grid-4">
    <?php foreach ($products as $product): ?>
    <?php include $componentPath . '/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
