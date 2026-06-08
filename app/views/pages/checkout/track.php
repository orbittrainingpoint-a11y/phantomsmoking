<div class="container section" style="max-width:680px">
  <h1 style="font-family:var(--font-heading);font-size:1.8rem;margin-bottom:24px">Track Order</h1>
  <div class="card">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div>
          <div style="font-size:0.82rem;color:var(--color-text-muted)">Order Number</div>
          <div style="font-family:var(--font-mono);font-size:1.2rem;font-weight:700;color:var(--color-secondary)"><?= e($order['order_number']) ?></div>
        </div>
        <div><?= order_status_badge($order['order_status']) ?></div>
      </div>

      <!-- Status Timeline -->
      <div style="margin-bottom:24px">
        <?php
        $statuses = ['pending','confirmed','processing','packed','out_for_delivery','delivered'];
        $currentIdx = array_search($order['order_status'], $statuses);
        if ($currentIdx === false) $currentIdx = -1;
        ?>
        <div style="display:flex;align-items:center;justify-content:space-between;position:relative;padding:0 20px">
          <div style="position:absolute;top:20px;left:20px;right:20px;height:2px;background:var(--color-border);z-index:0"></div>
          <?php foreach ($statuses as $i => $s): ?>
          <?php $done = $i <= $currentIdx; $active = $i === $currentIdx; ?>
          <div style="display:flex;flex-direction:column;align-items:center;gap:8px;position:relative;z-index:1">
            <div style="width:40px;height:40px;border-radius:50%;background:<?= $done ? 'var(--color-secondary)' : 'var(--color-border)' ?>;display:flex;align-items:center;justify-content:center;color:<?= $done ? '#fff' : 'var(--color-text-muted)' ?>;font-size:0.9rem;border:3px solid <?= $active ? 'var(--color-primary)' : 'transparent' ?>">
              <i class="fas <?= $done ? 'fa-check' : 'fa-circle' ?>" style="font-size:0.7rem"></i>
            </div>
            <div style="font-size:0.7rem;font-weight:600;text-align:center;color:<?= $done ? 'var(--color-primary)' : 'var(--color-text-muted)' ?>"><?= ucwords(str_replace('_',' ',$s)) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Order Items -->
      <div style="border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:16px;margin-bottom:16px">
        <strong style="display:block;margin-bottom:12px">Items Ordered</strong>
        <?php foreach ($order['items'] as $item): ?>
        <div style="display:flex;gap:12px;margin-bottom:10px;font-size:0.88rem">
          <?php if ($item['product_image']): ?><img src="<?= e($item['product_image']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius)"><?php endif; ?>
          <div style="flex:1"><div style="font-weight:600"><?= e($item['product_name']) ?></div><?php if ($item['variant_name']): ?><div style="color:var(--color-text-muted)"><?= e($item['variant_name']) ?></div><?php endif; ?></div>
          <div>x<?= $item['quantity'] ?> — <strong><?= format_price($item['total_price']) ?></strong></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Delivery Info -->
      <div style="font-size:0.88rem;color:var(--color-text-muted)">
        <div><strong>Delivering to:</strong> <?= e($order['shipping_name']) ?>, <?= e($order['shipping_address_line1']) ?>, <?= e($order['shipping_emirate']) ?></div>
        <?php if ($order['estimated_delivery_at']): ?><div><strong>Estimated Delivery:</strong> <?= format_datetime($order['estimated_delivery_at']) ?></div><?php endif; ?>
        <?php if ($order['delivered_at']): ?><div style="color:var(--color-success)"><strong>Delivered:</strong> <?= format_datetime($order['delivered_at']) ?></div><?php endif; ?>
      </div>

      <div style="display:flex;gap:12px;margin-top:20px;flex-wrap:wrap">
        <a href="/" class="btn btn-outline btn-sm">Continue Shopping</a>
        <?php if (is_logged_in()): ?><a href="/account/orders" class="btn btn-outline btn-sm">My Orders</a><?php endif; ?>
      </div>
    </div>
  </div>
</div>
