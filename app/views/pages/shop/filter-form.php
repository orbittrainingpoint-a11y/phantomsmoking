<?php
// Detect if rendered inside drawer or sidebar
$formId     = isset($isDrawer) ? 'filterFormDrawer' : 'filterForm';
$autoSubmit = isset($isDrawer) ? '' : 'onchange="document.getElementById(\'filterForm\').submit()"';
?>
<form id="<?= $formId ?>" method="GET" action="/shop/<?= e($category['slug']) ?>">
  <!-- Price Range -->
  <div class="filter-section">
    <div class="filter-title">Price Range (AED)</div>
    <div class="price-range-inputs">
      <input type="number" name="min_price" placeholder="Min" value="<?= e($filters['min_price'] ?? '') ?>" min="0">
      <input type="number" name="max_price" placeholder="Max" value="<?= e($filters['max_price'] ?? '') ?>" min="0">
    </div>
    <?php if (!isset($isDrawer)): ?>
    <button type="submit" class="btn btn-primary btn-full btn-sm" style="margin-top:10px">Apply Price</button>
    <?php endif; ?>
  </div>

  <!-- Brand -->
  <?php if (!empty($brands)): ?>
  <div class="filter-section">
    <div class="filter-title">Brand</div>
    <div class="filter-options">
      <?php foreach ($brands as $brand): ?>
      <label class="filter-option">
        <input type="radio" name="brand" value="<?= $brand['id'] ?>"
          <?= ($filters['brand_id'] ?? '') == $brand['id'] ? 'checked' : '' ?>
          <?= $autoSubmit ?>>
        <?= e($brand['name']) ?>
      </label>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Availability -->
  <div class="filter-section">
    <div class="filter-title">Availability</div>
    <label class="filter-toggle">
      <span>In Stock Only</span>
      <label class="toggle-switch">
        <input type="checkbox" name="in_stock" value="1"
          <?= !empty($filters['in_stock']) ? 'checked' : '' ?>
          <?= $autoSubmit ?>>
        <span class="toggle-slider"></span>
      </label>
    </label>
  </div>

  <div class="filter-section">
    <label class="filter-toggle">
      <span>On Sale</span>
      <label class="toggle-switch">
        <input type="checkbox" name="on_sale" value="1"
          <?= !empty($filters['on_sale']) ? 'checked' : '' ?>
          <?= $autoSubmit ?>>
        <span class="toggle-slider"></span>
      </label>
    </label>
  </div>

  <div class="filter-section">
    <label class="filter-toggle">
      <span>New Arrivals</span>
      <label class="toggle-switch">
        <input type="checkbox" name="new_arrival" value="1"
          <?= !empty($filters['new_arrival']) ? 'checked' : '' ?>
          <?= $autoSubmit ?>>
        <span class="toggle-slider"></span>
      </label>
    </label>
  </div>

  <!-- Nicotine (vapes/e-liquids/nic-pouches) -->
  <?php if (in_array($category['slug'], ['vapes', 'e-liquids', 'nic-pouches', 'vapes-disposables', 'vapes-pods'])): ?>
  <div class="filter-section">
    <div class="filter-title">Nicotine Strength</div>
    <div class="filter-options">
      <?php foreach (['3', '6', '12', '20'] as $nic): ?>
      <label class="filter-option">
        <input type="radio" name="nicotine" value="<?= $nic ?>"
          <?= ($filters['nicotine'] ?? '') == $nic ? 'checked' : '' ?>
          <?= $autoSubmit ?>>
        <?= $nic ?>mg
      </label>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Cigar Strength -->
  <?php if (in_array($category['slug'], ['cigars', 'cigars-premium', 'cigars-cuban', 'cigars-dominican', 'cigars-honduran'])): ?>
  <div class="filter-section">
    <div class="filter-title">Cigar Strength</div>
    <div class="filter-options">
      <?php foreach (['mild', 'medium', 'medium-full', 'full'] as $str): ?>
      <label class="filter-option">
        <input type="radio" name="cigar_strength" value="<?= $str ?>"
          <?= ($filters['cigar_strength'] ?? '') == $str ? 'checked' : '' ?>
          <?= $autoSubmit ?>>
        <?= ucwords(str_replace('-', ' ', $str)) ?>
      </label>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!isset($isDrawer)): ?>
  <button type="submit" class="btn btn-primary btn-full btn-sm">Apply Filters</button>
  <?php endif; ?>
</form>
