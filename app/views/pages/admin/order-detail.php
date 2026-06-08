<div style="display:grid;grid-template-columns:1fr 360px;gap:24px">
  <div>
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">Order <?= e($order['order_number']) ?></div>
        <?= order_status_badge($order['order_status']) ?>
      </div>
      <div class="admin-card-body">
        <div class="table-wrap">
          <table>
            <thead><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
            <tbody>
              <?php foreach ($order['items'] as $item): ?>
              <tr>
                <td><?php if ($item['product_image']): ?><img src="<?= e($item['product_image']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:var(--radius);margin-right:8px"><?php endif; ?><?= e($item['product_name']) ?></td>
                <td><?= e($item['variant_name'] ?? '—') ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= format_price($item['unit_price']) ?></td>
                <td><strong><?= format_price($item['total_price']) ?></strong></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="text-align:right;margin-top:16px">
          <div>Subtotal: <?= format_price($order['subtotal']) ?></div>
          <?php if ($order['discount_amount'] > 0): ?><div style="color:var(--color-success)">Discount: -<?= format_price($order['discount_amount']) ?></div><?php endif; ?>
          <div>Shipping: <?= format_price($order['shipping_cost']) ?></div>
          <div>VAT (5%): <?= format_price($order['tax_amount']) ?></div>
          <div style="font-size:1.1rem;font-weight:700;margin-top:8px">Total: <?= format_price($order['total_amount']) ?></div>
        </div>
      </div>
    </div>
    <!-- Status History -->
    <div class="admin-card">
      <div class="admin-card-header"><div class="admin-card-title">Status History</div></div>
      <div class="admin-card-body">
        <?php foreach ($order['status_history'] as $h): ?>
        <div style="display:flex;gap:12px;margin-bottom:12px;font-size:0.88rem">
          <div style="width:10px;height:10px;border-radius:50%;background:var(--color-secondary);margin-top:4px;flex-shrink:0"></div>
          <div><strong><?= ucwords(str_replace('_',' ',$h['status'])) ?></strong> — <?= format_datetime($h['created_at']) ?><?php if ($h['note']): ?><br><span style="color:var(--color-text-muted)"><?= e($h['note']) ?></span><?php endif; ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div>
    <!-- Update Status -->
    <div class="admin-card">
      <div class="admin-card-header"><div class="admin-card-title">Update Status</div></div>
      <div class="admin-card-body">
        <form method="POST" action="/admin/orders/<?= $order['id'] ?>/status">
          <?= csrf_field() ?>
          <div class="form-group"><label class="form-label">New Status</label>
            <select name="status" class="form-control">
              <?php foreach (['pending','confirmed','processing','packed','out_for_delivery','delivered','cancelled','returned'] as $s): ?>
              <option value="<?= $s ?>" <?= $order['order_status'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Note</label><textarea name="note" class="form-control" rows="2"></textarea></div>
          <button type="submit" class="btn btn-primary btn-full">Update Status</button>
        </form>
      </div>
    </div>
    <!-- Customer & Shipping -->
    <div class="admin-card">
      <div class="admin-card-header"><div class="admin-card-title">Shipping Details</div></div>
      <div class="admin-card-body" style="font-size:0.88rem">
        <strong><?= e($order['shipping_name']) ?></strong><br>
        <?= e($order['shipping_phone']) ?><br>
        <?= e($order['shipping_address_line1']) ?><?= $order['shipping_address_line2'] ? ', ' . e($order['shipping_address_line2']) : '' ?><br>
        <?= e($order['shipping_area']) ?>, <?= e($order['shipping_emirate']) ?>, <?= e($order['shipping_country']) ?>
        <hr class="divider">
        <div><strong>Payment:</strong> <?= payment_method_label($order['payment_method']) ?></div>
        <div><strong>Payment Status:</strong> <span class="badge badge-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($order['payment_status']) ?></span></div>
        <div><strong>Delivery:</strong> <?= ucwords(str_replace('_',' ',$order['delivery_type'])) ?></div>
        <?php if ($order['customer_notes']): ?><div style="margin-top:8px"><strong>Notes:</strong> <?= e($order['customer_notes']) ?></div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
