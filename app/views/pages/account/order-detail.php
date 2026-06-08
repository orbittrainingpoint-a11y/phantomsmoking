<div class="container section">
  <div class="account-layout">
    <?php include dirname(__DIR__) . '/../components/account-sidebar.php'; ?>
    <div class="account-content">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div class="account-page-title" style="margin-bottom:0">Order <?= e($order['order_number']) ?></div>
        <?= order_status_badge($order['order_status']) ?>
      </div>

      <!-- Items -->
      <div class="table-wrap" style="margin-bottom:24px">
        <table>
          <thead><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
          <tbody>
            <?php foreach ($order['items'] as $item): ?>
            <tr>
              <td style="display:flex;align-items:center;gap:10px">
                <?php if ($item['product_image']): ?><img src="<?= e($item['product_image']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius)"><?php endif; ?>
                <?= e($item['product_name']) ?>
              </td>
              <td><?= e($item['variant_name'] ?? '—') ?></td>
              <td><?= $item['quantity'] ?></td>
              <td><?= format_price($item['unit_price']) ?></td>
              <td><strong><?= format_price($item['total_price']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <!-- Order Summary -->
        <div style="background:var(--color-bg-light);border-radius:var(--radius-lg);padding:20px">
          <strong style="display:block;margin-bottom:12px">Order Summary</strong>
          <div class="summary-row"><span>Subtotal</span><span><?= format_price($order['subtotal']) ?></span></div>
          <?php if ($order['discount_amount'] > 0): ?><div class="summary-row" style="color:var(--color-success)"><span>Discount</span><span>-<?= format_price($order['discount_amount']) ?></span></div><?php endif; ?>
          <div class="summary-row"><span>Shipping</span><span><?= format_price($order['shipping_cost']) ?></span></div>
          <div class="summary-row"><span>VAT (5%)</span><span><?= format_price($order['tax_amount']) ?></span></div>
          <div class="summary-row" style="border-top:2px solid var(--color-border);margin-top:8px;padding-top:12px;font-weight:700"><span>Total</span><span><?= format_price($order['total_amount']) ?></span></div>
        </div>
        <!-- Shipping & Payment -->
        <div style="background:var(--color-bg-light);border-radius:var(--radius-lg);padding:20px;font-size:0.88rem">
          <strong style="display:block;margin-bottom:12px">Delivery Details</strong>
          <div><?= e($order['shipping_name']) ?></div>
          <div><?= e($order['shipping_phone']) ?></div>
          <div><?= e($order['shipping_address_line1']) ?></div>
          <div><?= e($order['shipping_area']) ?>, <?= e($order['shipping_emirate']) ?></div>
          <hr class="divider">
          <div><strong>Payment:</strong> <?= ucwords(str_replace('_',' ',$order['payment_method'])) ?></div>
          <div><strong>Status:</strong> <span class="badge badge-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($order['payment_status']) ?></span></div>
          <div><strong>Delivery:</strong> <?= ucwords(str_replace('_',' ',$order['delivery_type'])) ?></div>
          <div><strong>Ordered:</strong> <?= format_datetime($order['created_at']) ?></div>
        </div>
      </div>

      <!-- Status History -->
      <div style="margin-top:24px">
        <strong style="display:block;margin-bottom:12px">Order Timeline</strong>
        <?php foreach ($order['status_history'] as $h): ?>
        <div style="display:flex;gap:12px;margin-bottom:10px;font-size:0.88rem">
          <div style="width:8px;height:8px;border-radius:50%;background:var(--color-secondary);margin-top:5px;flex-shrink:0"></div>
          <div><strong><?= ucwords(str_replace('_',' ',$h['status'])) ?></strong> — <?= format_datetime($h['created_at']) ?><?php if ($h['note']): ?><br><span style="color:var(--color-text-muted)"><?= e($h['note']) ?></span><?php endif; ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="margin-top:20px;display:flex;gap:12px">
        <a href="/account/orders" class="btn btn-outline btn-sm">← Back to Orders</a>
        <a href="/track/<?= e($order['order_number']) ?>" class="btn btn-outline btn-sm">Track Order</a>
      </div>
    </div>
  </div>
</div>
