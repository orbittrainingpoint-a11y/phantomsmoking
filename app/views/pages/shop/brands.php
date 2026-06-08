<div class="shop-header"><div class="container"><h1 class="shop-header-title">All Brands</h1><p style="color:var(--color-text-muted)">Explore our premium brand collection</p></div></div>
<div class="container section">
  <div class="grid grid-5">
    <?php foreach ($brands as $brand): ?>
    <a href="/brand/<?= e($brand['slug']) ?>" class="card" style="text-align:center;padding:20px;transition:all var(--transition)">
      <?php if ($brand['logo']): ?>
      <img src="<?= e($brand['logo']) ?>" alt="<?= e($brand['name']) ?>" style="max-height:60px;max-width:120px;object-fit:contain;margin:0 auto 12px;filter:grayscale(1);opacity:0.7;transition:all var(--transition)">
      <?php else: ?>
      <div style="height:60px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;color:var(--color-primary)"><?= e($brand['name']) ?></div>
      <?php endif; ?>
      <div style="font-size:0.82rem;font-weight:600;color:var(--color-text-muted)"><?= e($brand['name']) ?></div>
      <?php if ($brand['country_of_origin']): ?><div style="font-size:0.75rem;color:var(--color-text-muted)"><?= e($brand['country_of_origin']) ?></div><?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
