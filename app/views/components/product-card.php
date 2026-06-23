<?php
// $product must be in scope
$imgSrc      = $product['primary_image'] ?? $product['product_image'] ?? '/assets/images/placeholder.jpg';
$hasDiscount = !empty($product['compare_at_price']) && $product['compare_at_price'] > $product['price'];
$discountPct = $hasDiscount ? discount_percent($product['price'], $product['compare_at_price']) : 0;
$inStock     = ($product['stock_quantity'] ?? 0) > 0;
$productName = addslashes($product['name']);
$productUrl  = '/product/' . e($product['slug']);
?>
<a href="<?= $productUrl ?>" class="product-card" style="text-decoration:none;color:inherit;display:block">
  <div class="product-card-image">
    <img src="<?= e($imgSrc) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
    <div class="product-card-badges">
      <?php if (!empty($product['is_new_arrival'])): ?><span class="badge badge-new">New</span><?php endif; ?>
      <?php if ($hasDiscount): ?><span class="badge badge-sale">-<?= $discountPct ?>%</span><?php endif; ?>
      <?php if (!$inStock): ?><span class="badge badge-danger">Out of Stock</span><?php endif; ?>
    </div>
    <div class="product-card-actions">
      <button class="product-action-btn wishlist-toggle" data-product-id="<?= $product['id'] ?>" title="Add to Wishlist"
        onclick="event.preventDefault();event.stopPropagation();toggleWishlist(<?= $product['id'] ?>, this)">
        <i class="fas fa-heart"></i>
      </button>
      <span class="product-action-btn" title="Quick View"><i class="fas fa-eye"></i></span>
    </div>
  </div>
  <div class="product-card-body">
    <?php if (!empty($product['brand_name'])): ?>
    <div class="product-brand"><?= e($product['brand_name']) ?></div>
    <?php endif; ?>
    <div class="product-name"><?= e($product['name']) ?></div>
    <?php if (($product['average_rating'] ?? 0) > 0): ?>
    <div class="product-rating">
      <div class="stars"><?= star_rating($product['average_rating']) ?></div>
      <span class="product-rating-count">(<?= $product['review_count'] ?? 0 ?>)</span>
    </div>
    <?php endif; ?>
    <div class="product-price-row">
      <div>
        <span class="product-price"><?= format_price($product['price']) ?></span>
        <?php if ($hasDiscount): ?>
        <span class="product-price-compare"><?= format_price($product['compare_at_price']) ?></span>
        <?php endif; ?>
      </div>
      <?php if ($inStock): ?>
      <button class="product-add-btn" title="Add to Cart"
        onclick="event.preventDefault();event.stopPropagation();openFlavourPopup(<?= $product['id'] ?>, '<?= $productName ?>', this)">
        <i class="fas fa-plus"></i>
      </button>
      <?php else: ?>
      <span class="product-out-of-stock">Out of Stock</span>
      <?php endif; ?>
    </div>
  </div>
</a>
