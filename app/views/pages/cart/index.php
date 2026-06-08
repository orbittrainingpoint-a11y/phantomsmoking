<div class="container section">
  <h1 style="font-family:var(--font-heading);font-size:1.8rem;margin-bottom:32px">Your Shopping Cart</h1>
  <?php if (empty($cart['items'])): ?>
  <div class="empty-state"><i class="fas fa-shopping-bag"></i><h3>Your cart is empty</h3><p>Browse our products and add items to your cart</p><a href="/" class="btn btn-primary" style="margin-top:16px">Continue Shopping</a></div>
  <?php else: ?>
  <div class="cart-layout">
    <div>
      <!-- Free shipping bar -->
      <?php $threshold = 100; $remaining = max(0, $threshold - $cart['subtotal']); ?>
      <div class="free-shipping-bar">
        <?php if ($remaining > 0): ?>
        <div style="font-size:0.85rem">Add <strong><?= format_price($remaining) ?></strong> more for <strong style="color:var(--color-success)">FREE delivery</strong></div>
        <?php else: ?><div style="font-size:0.85rem;color:var(--color-success)"><i class="fas fa-check-circle"></i> You qualify for <strong>FREE delivery!</strong></div><?php endif; ?>
        <div class="free-shipping-progress"><div class="free-shipping-fill" style="width:<?= min(100, ($cart['subtotal'] / $threshold) * 100) ?>%"></div></div>
      </div>

      <?php foreach ($cart['items'] as $item): ?>
      <div class="cart-item" id="cart-item-<?= $item['id'] ?>">
        <div class="cart-item-img"><a href="/product/<?= e($item['slug']) ?>"><img src="<?= e($item['product_image'] ?: '/assets/images/placeholder.jpg') ?>" alt="<?= e($item['name']) ?>"></a></div>
        <div style="flex:1;min-width:0">
          <div class="cart-item-name"><a href="/product/<?= e($item['slug']) ?>"><?= e($item['name']) ?></a></div>
          <?php $varLabel = $item['variant_name'] ?: ($item['selected_flavours'] ?? ''); ?>
          <?php if ($varLabel): ?><div class="cart-item-variant"><?= e($varLabel) ?></div><?php endif; ?>
          <div class="cart-item-price"><?= format_price($item['unit_price']) ?> each</div>
          <div class="qty-selector" style="margin-top:10px">
            <button class="qty-btn" onclick="updateCartItem(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">−</button>
            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" onchange="updateCartItem(<?= $item['id'] ?>, this.value)">
            <button class="qty-btn" onclick="updateCartItem(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0">
          <div class="cart-item-total"><?= format_price($item['unit_price'] * $item['quantity']) ?></div>
          <a href="/product/<?= e($item['slug']) ?>?cart_item_id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" style="font-size:0.78rem;padding:4px 10px"><i class="fas fa-edit"></i> Edit</a>
          <a class="cart-item-remove" onclick="removeCartItem(<?= $item['id'] ?>)" style="cursor:pointer;font-size:0.82rem"><i class="fas fa-trash"></i> Remove</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Order Summary -->
    <div class="order-summary-card">
      <div class="order-summary-title">Order Summary</div>
      <div class="summary-row"><span>Subtotal</span><span id="summarySubtotal"><?= format_price($cart['subtotal']) ?></span></div>
      <?php if ($cart['discount'] > 0): ?>
      <div class="summary-row discount"><span>Discount</span><span>-<?= format_price($cart['discount']) ?></span></div>
      <?php endif; ?>
      <div class="summary-row"><span>Shipping</span><span id="summaryShipping"><?= $cart['shipping'] > 0 ? format_price($cart['shipping']) : 'Calculated at checkout' ?></span></div>
      <div class="summary-row"><span>VAT (5%)</span><span><?= format_price($cart['tax']) ?></span></div>
      <div class="summary-row total"><span>Total</span><span id="summaryTotal"><?= format_price($cart['total']) ?></span></div>

      <!-- Coupon -->
      <div class="coupon-form">
        <input type="text" class="coupon-input" id="couponCode" placeholder="COUPON CODE">
        <button class="btn btn-outline btn-sm" onclick="applyCoupon()">Apply</button>
      </div>
      <div id="couponMsg" style="font-size:0.82rem;margin-bottom:12px"></div>

      <a href="/checkout" class="btn btn-primary btn-full btn-lg">Proceed to Checkout <i class="fas fa-arrow-right"></i></a>
      <a href="/" class="btn btn-outline btn-full" style="margin-top:8px">Continue Shopping</a>
    </div>
  </div>

  <!-- Upsell -->
  <?php if (!empty($upsell)): ?>
  <div style="margin-top:48px">
    <h3 style="font-family:var(--font-heading);margin-bottom:20px">Customers Also Bought</h3>
    <div class="grid grid-4">
      <?php foreach ($upsell as $product): ?>
      <?php include dirname(__DIR__) . '/../components/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<script>
async function applyCoupon() {
  const code = document.getElementById('couponCode').value.trim();
  if (!code) return;
  const res = await fetch('/api/cart/coupon', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({code})});
  const data = await res.json();
  const msg = document.getElementById('couponMsg');
  if (data.success) { msg.style.color = 'var(--color-success)'; msg.textContent = data.message; location.reload(); }
  else { msg.style.color = 'var(--color-error)'; msg.textContent = data.error; }
}
</script>
