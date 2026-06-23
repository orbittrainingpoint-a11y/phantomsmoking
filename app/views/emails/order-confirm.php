<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{margin:0;padding:0;background:#f0ede8;font-family:Arial,sans-serif}
.wrap{max-width:600px;margin:0 auto;background:#fff}
.header{background:#1A1A2E;padding:28px 32px;text-align:center}
.logo{font-size:1.5rem;font-weight:900;color:#fff;letter-spacing:1px}
.logo span{color:#C8963C}
.tagline{color:rgba(255,255,255,0.6);font-size:0.8rem;margin-top:4px}
.body{padding:32px}
.order-box{background:#f9f6f1;border:1px solid #e5ddd0;border-radius:8px;padding:16px 20px;margin:20px 0;text-align:center}
.order-num{font-size:1.4rem;font-weight:700;color:#C8963C;font-family:monospace;letter-spacing:2px}
table{width:100%;border-collapse:collapse;margin:16px 0;font-size:0.88rem}
th{background:#1A1A2E;color:#fff;padding:9px 12px;text-align:left}
td{padding:9px 12px;border-bottom:1px solid #eee}
.total-row td{font-weight:700;font-size:1rem;background:#f9f6f1}
.info-grid{display:table;width:100%;margin:16px 0}
.info-col{display:table-cell;width:50%;vertical-align:top;padding:12px;background:#f9f6f1;border-radius:6px;font-size:0.85rem}
.info-col:first-child{margin-right:8px}
.info-label{font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#C8963C;margin-bottom:4px}
.btn{display:inline-block;background:#C8963C;color:#fff;padding:12px 28px;border-radius:4px;text-decoration:none;font-weight:700;margin:16px 0;font-size:0.9rem}
.footer{background:#1A1A2E;padding:20px 32px;text-align:center;color:rgba(255,255,255,0.45);font-size:0.75rem}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:700}
.badge-paid{background:#d4edda;color:#155724}
.badge-pending{background:#fff3cd;color:#856404}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">Phantom <span>Smoking</span></div>
    <div class="tagline">Premium Tobacco · Vape · Shisha — Dubai, UAE</div>
  </div>
  <div class="body">
    <h2 style="color:#1A1A2E;margin:0 0 6px">Order Confirmed! 🎉</h2>
    <p style="color:#6B7280;margin:0 0 16px">Hi <?= e($order['shipping_name']) ?>, thank you for your order. We're preparing it right now.</p>

    <div class="order-box">
      <div style="font-size:0.78rem;color:#6B7280;margin-bottom:4px">Your Order Number</div>
      <div class="order-num"><?= e($order['order_number']) ?></div>
      <div style="margin-top:8px">
        <span class="badge badge-<?= $order['payment_status'] === 'paid' ? 'paid' : 'pending' ?>">
          <?= ucfirst($order['payment_status']) ?>
        </span>
      </div>
    </div>

    <!-- Items Table -->
    <table>
      <thead><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td><?= e($item['product_name']) ?></td>
          <td><?= e($item['variant_name'] ?? '—') ?></td>
          <td><?= $item['quantity'] ?></td>
          <td>AED <?= number_format($item['unit_price'], 2) ?></td>
          <td>AED <?= number_format($item['total_price'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="4" style="text-align:right;color:#6B7280">Subtotal</td><td>AED <?= number_format($order['subtotal'], 2) ?></td></tr>
        <?php if ($order['discount_amount'] > 0): ?>
        <tr><td colspan="4" style="text-align:right;color:#10b981">Discount</td><td style="color:#10b981">- AED <?= number_format($order['discount_amount'], 2) ?></td></tr>
        <?php endif; ?>
        <tr><td colspan="4" style="text-align:right;color:#6B7280">Shipping</td><td>AED <?= number_format($order['shipping_cost'], 2) ?></td></tr>
        <tr><td colspan="4" style="text-align:right;color:#6B7280">VAT (5%)</td><td>AED <?= number_format($order['tax_amount'], 2) ?></td></tr>
        <tr class="total-row"><td colspan="4" style="text-align:right">TOTAL</td><td style="color:#C8963C;font-size:1.1rem">AED <?= number_format($order['total_amount'], 2) ?></td></tr>
      </tbody>
    </table>

    <!-- Delivery & Payment Info -->
    <table style="margin:0">
      <tr>
        <td style="width:50%;vertical-align:top;padding:12px;background:#f9f6f1;border-radius:6px 0 0 6px;border-bottom:none">
          <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#C8963C;margin-bottom:6px">Delivery Address</div>
          <div style="font-size:0.85rem;line-height:1.6">
            <?= e($order['shipping_name']) ?><br>
            <?= e($order['shipping_phone']) ?><br>
            <?= e($order['shipping_address_line1']) ?><?= !empty($order['shipping_area']) ? ', '.e($order['shipping_area']) : '' ?><br>
            <?= e($order['shipping_emirate']) ?>, UAE
          </div>
        </td>
        <td style="width:8px"></td>
        <td style="width:50%;vertical-align:top;padding:12px;background:#f9f6f1;border-radius:0 6px 6px 0;border-bottom:none">
          <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#C8963C;margin-bottom:6px">Order Info</div>
          <div style="font-size:0.85rem;line-height:1.6">
            <strong>Payment:</strong> <?= payment_method_label($order['payment_method']) ?><br>
            <strong>Delivery:</strong> <?= ucwords(str_replace('_',' ',$order['delivery_type'])) ?><br>
            <strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
            <?php if (!empty($order['customer_notes'])): ?>
            <br><strong>Notes:</strong> <?= e($order['customer_notes']) ?>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    </table>

    <div style="text-align:center;margin-top:24px">
      <a href="<?= url('track/' . $order['order_number']) ?>" class="btn">Track Your Order →</a>
    </div>

    <p style="color:#6B7280;font-size:0.83rem;margin-top:20px;text-align:center">
      Questions? WhatsApp us at <a href="https://wa.me/971568335210" style="color:#C8963C">+971 56 833 5210</a>
      or email <a href="mailto:phantomsmokingonline@gmail.com" style="color:#C8963C">phantomsmokingonline@gmail.com</a>
    </p>
  </div>
  <div class="footer">
    © <?= date('Y') ?> Phantom Smoking · Dubai, UAE<br>
    ⚠️ Tobacco products are harmful to health. For adults 18+ only.
  </div>
</div>
</body></html>
