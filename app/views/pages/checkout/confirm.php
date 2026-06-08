<div class="container section" style="max-width:680px">
  <div class="card">
    <div class="card-body order-confirm-card">
      <div class="order-confirm-icon"><i class="fas fa-check"></i></div>
      <h1 style="font-family:var(--font-heading);font-size:1.8rem;margin-bottom:8px">Order Confirmed!</h1>
      <p style="color:var(--color-text-muted);margin-bottom:16px">Thank you for your order. We've sent a confirmation to your email.</p>
      <div class="order-number"><?= e($order['order_number']) ?></div>
      <p style="color:var(--color-text-muted);font-size:0.88rem;margin-bottom:28px">
        Estimated delivery: <strong><?= $order['delivery_type'] === 'express_1hr' ? 'Within 1 Hour' : '1-2 Business Days' ?></strong>
      </p>

      <!-- Order Items -->
      <div style="text-align:left;border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:20px;margin-bottom:24px">
        <strong style="display:block;margin-bottom:12px">Order Items</strong>
        <?php foreach ($order['items'] as $item): ?>
        <div style="display:flex;gap:12px;margin-bottom:10px;font-size:0.88rem">
          <?php if ($item['product_image']): ?><img src="<?= e($item['product_image']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius)"><?php endif; ?>
          <div style="flex:1"><div style="font-weight:600"><?= e($item['product_name']) ?></div><?php if ($item['variant_name']): ?><div style="color:var(--color-text-muted)"><?= e($item['variant_name']) ?></div><?php endif; ?><div>Qty: <?= $item['quantity'] ?></div></div>
          <div style="font-family:var(--font-mono);font-weight:600"><?= format_price($item['total_price']) ?></div>
        </div>
        <?php endforeach; ?>
        <hr class="divider">
        <div style="display:flex;justify-content:space-between;font-weight:700"><?= format_price($order['total_amount']) ?></div>
      </div>

      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="/track/<?= e($order['order_number']) ?>" class="btn btn-primary">Track Order</a>
        <a href="/" class="btn btn-outline">Continue Shopping</a>
        <?php if (is_logged_in()): ?><a href="/account/orders" class="btn btn-outline">My Orders</a><?php endif; ?>
      </div>
    </div>
  </div>
</div>
