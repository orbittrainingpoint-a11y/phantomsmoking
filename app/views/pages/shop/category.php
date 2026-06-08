<?php
$activeFilters = array_filter($filters ?? [], fn($v) => $v !== '' && $v !== null);
$filterCount   = count($activeFilters);
?>
<div class="shop-header">
  <div class="container">
    <div class="breadcrumb"><a href="/">Home</a><span class="breadcrumb-sep">/</span><span><?= e($category['name']) ?></span></div>
    <h1 class="shop-header-title"><?= e($category['name']) ?></h1>
    <?php if ($category['description']): ?><p style="color:var(--color-text-muted);max-width:600px"><?= e($category['description']) ?></p><?php endif; ?>
  </div>
</div>

<div class="container section" style="padding-top:24px">
  <div class="layout-sidebar">

    <!-- Desktop Sidebar Filters -->
    <aside class="filter-sidebar">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <strong>Filters</strong>
        <?php if ($filterCount): ?>
        <a href="/shop/<?= e($category['slug']) ?>" style="font-size:0.82rem;color:var(--color-secondary)">Clear All</a>
        <?php endif; ?>
      </div>
      <?php include __DIR__ . '/filter-form.php'; ?>
    </aside>

    <!-- Products Column -->
    <div>
      <!-- Sort bar with mobile filter button -->
      <div class="sort-bar">
        <div style="display:flex;align-items:center;gap:10px">
          <!-- Mobile filter trigger -->
          <button class="mobile-filter-btn" onclick="openFilterDrawer()">
            <i class="fas fa-sliders-h"></i> Filters
            <?php if ($filterCount): ?><span class="filter-badge"><?= $filterCount ?></span><?php endif; ?>
          </button>
          <div class="sort-bar-left"><?= number_format($products['total']) ?> products</div>
        </div>
        <div class="sort-bar-right">
          <select class="sort-select" onchange="applySort(this.value)">
            <option value="featured"     <?= $sort === 'featured'     ? 'selected' : '' ?>>Featured</option>
            <option value="price_asc"    <?= $sort === 'price_asc'    ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc"   <?= $sort === 'price_desc'   ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="newest"       <?= $sort === 'newest'       ? 'selected' : '' ?>>Newest</option>
            <option value="best_sellers" <?= $sort === 'best_sellers' ? 'selected' : '' ?>>Best Sellers</option>
            <option value="rating"       <?= $sort === 'rating'       ? 'selected' : '' ?>>Top Rated</option>
          </select>
        </div>
      </div>

      <?php if (empty($products['items'])): ?>
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>No products found</h3>
        <p>Try adjusting your filters</p>
        <a href="/shop/<?= e($category['slug']) ?>" class="btn btn-primary btn-sm" style="margin-top:12px">Clear Filters</a>
      </div>
      <?php else: ?>
      <div class="grid grid-3">
        <?php
        $componentPath = dirname(__DIR__, 2) . '/components';
        foreach ($products['items'] as $product): ?>
        <?php include $componentPath . '/product-card.php'; ?>
        <?php endforeach; ?>
      </div>

      <?php if ($products['total_pages'] > 1): ?>
      <div class="pagination">
        <?php if ($products['current_page'] > 1): ?>
        <a href="?page=<?= $products['current_page'] - 1 ?>">‹</a>
        <?php endif; ?>
        <?php for ($p = max(1, $products['current_page'] - 2); $p <= min($products['total_pages'], $products['current_page'] + 2); $p++): ?>
        <?php if ($p == $products['current_page']): ?>
        <span class="active"><?= $p ?></span>
        <?php else: ?>
        <a href="?page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
        <?php endfor; ?>
        <?php if ($products['current_page'] < $products['total_pages']): ?>
        <a href="?page=<?= $products['current_page'] + 1 ?>">›</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Mobile Filter Drawer -->
<div class="filter-drawer-overlay" id="filterOverlay" onclick="closeFilterDrawer()"></div>
<div class="filter-drawer" id="filterDrawer">
  <div class="filter-drawer-handle"></div>
  <div class="filter-drawer-header">
    <strong><i class="fas fa-sliders-h" style="color:var(--color-secondary);margin-right:8px"></i>Filters <?php if ($filterCount): ?><span style="color:var(--color-secondary)">(<?= $filterCount ?>)</span><?php endif; ?></strong>
    <div style="display:flex;align-items:center;gap:12px">
      <?php if ($filterCount): ?>
      <a href="/shop/<?= e($category['slug']) ?>" class="filter-drawer-clear">Clear All</a>
      <?php endif; ?>
      <button class="filter-drawer-close" onclick="closeFilterDrawer()"><i class="fas fa-times"></i></button>
    </div>
  </div>
  <div class="filter-drawer-body">
    <?php $isDrawer = true; include __DIR__ . '/filter-form.php'; unset($isDrawer); ?>
  </div>
  <div class="filter-drawer-footer">
    <a href="/shop/<?= e($category['slug']) ?>" class="btn btn-outline">Reset</a>
    <button class="btn btn-primary" onclick="document.getElementById('filterFormDrawer').submit()">
      <i class="fas fa-check"></i> Apply Filters
    </button>
  </div>
</div>

<script>
function openFilterDrawer() {
  document.getElementById('filterDrawer').classList.add('open');
  document.getElementById('filterOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeFilterDrawer() {
  document.getElementById('filterDrawer').classList.remove('open');
  document.getElementById('filterOverlay').classList.remove('open');
  document.body.style.overflow = '';
}
function applySort(val) {
  const url = new URL(window.location.href);
  url.searchParams.set('sort', val);
  window.location.href = url.toString();
}
// Close drawer on swipe down
(function() {
  const drawer = document.getElementById('filterDrawer');
  let startY = 0;
  drawer.addEventListener('touchstart', e => { startY = e.touches[0].clientY; }, { passive: true });
  drawer.addEventListener('touchend', e => {
    if (e.changedTouches[0].clientY - startY > 80) closeFilterDrawer();
  }, { passive: true });
})();
</script>
