<?php
$baseExport = '/admin/reports/export?tab='.$tab.'&from='.$from.'&to='.$to;
$baseUrl    = '/admin/reports?tab='.$tab;
?>
<!-- Toolbar -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px">
  <div style="font-size:0.82rem;color:var(--color-text-muted)">
    <i class="fas fa-clock"></i> Last updated: <strong><?= e($last_updated) ?></strong>
    <a href="<?= $baseUrl ?>&from=<?= $from ?>&to=<?= $to ?>" class="btn btn-sm btn-outline" style="margin-left:10px"><i class="fas fa-sync-alt"></i> Refresh</a>
  </div>
  <div style="display:flex;gap:8px">
    <a href="<?= $baseExport ?>&format=csv" class="btn btn-sm btn-outline"><i class="fas fa-file-csv"></i> Export CSV</a>
    <a href="<?= $baseExport ?>&format=print" target="_blank" class="btn btn-sm btn-outline"><i class="fas fa-print"></i> Print / PDF</a>
  </div>
</div>

<!-- Tabs -->
<div style="display:flex;gap:4px;flex-wrap:wrap;border-bottom:2px solid var(--color-border);margin-bottom:20px">
  <?php foreach(['sales'=>'📊 Sales','inventory'=>'📦 Inventory','customers'=>'👥 Customers','invoices'=>'🧾 Invoices','delivery'=>'🚚 Delivery','reviews'=>'⭐ Reviews'] as $t=>$label): ?>
  <a href="/admin/reports?tab=<?= $t ?>&range=<?= $range ?>&from=<?= $from ?>&to=<?= $to ?>"
    style="padding:10px 16px;font-weight:600;font-size:0.83rem;text-decoration:none;border-bottom:3px solid <?= $tab===$t ? 'var(--color-secondary)' : 'transparent' ?>;color:<?= $tab===$t ? 'var(--color-primary)' : 'var(--color-text-muted)' ?>;white-space:nowrap">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Date Filter -->
<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:24px;background:var(--color-bg-light);padding:14px;border-radius:var(--radius)">
  <input type="hidden" name="tab" value="<?= e($tab) ?>">
  <div>
    <label class="form-label" style="font-size:0.78rem">Quick Range</label>
    <select name="range" class="form-control" style="min-width:130px" onchange="this.form.submit()">
      <?php foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $v=>$l): ?>
      <option value="<?= $v ?>" <?= $range===$v?'selected':'' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div><label class="form-label" style="font-size:0.78rem">From</label><input type="date" name="from" class="form-control" value="<?= e($from) ?>"></div>
  <div><label class="form-label" style="font-size:0.78rem">To</label><input type="date" name="to" class="form-control" value="<?= e($to) ?>"></div>
  <?php if($tab==='invoices'): ?>
  <div><label class="form-label" style="font-size:0.78rem">Customer / Order #</label><input type="text" name="search" class="form-control" value="<?= e($search??'') ?>" placeholder="Search..."></div>
  <div><label class="form-label" style="font-size:0.78rem">Payment Status</label>
    <select name="payment_status" class="form-control">
      <option value="">All</option>
      <?php foreach(['pending','paid','failed','refunded'] as $s): ?>
      <option value="<?= $s ?>" <?= ($status??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?php if($tab==='reviews'): ?>
  <div><label class="form-label" style="font-size:0.78rem">Status</label>
    <select name="review_status" class="form-control">
      <option value="">All</option>
      <?php foreach(['pending','approved','rejected'] as $s): ?>
      <option value="<?= $s ?>" <?= ($status??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <button type="submit" class="btn btn-primary btn-sm">Apply</button>
</form>

<?php if($tab==='sales'): ?>
<!-- ── SALES ── -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
  <?php
  $cards = [
    ['Total Orders', number_format($summary['total_orders']), 'fa-shopping-cart','#3b82f6'],
    ['Revenue', 'AED '.number_format($summary['revenue'],2), 'fa-coins','#C8963C'],
    ['Avg Order Value', 'AED '.number_format($summary['avg_order'],2), 'fa-chart-line','#10b981'],
    ['Paid Revenue', 'AED '.number_format($summary['paid_revenue'],2), 'fa-check-circle','#8b5cf6'],
  ];
  foreach($cards as [$label,$val,$icon,$color]):
  ?>
  <div class="admin-card" style="margin:0">
    <div class="admin-card-body" style="display:flex;align-items:center;gap:14px;padding:16px">
      <div style="width:44px;height:44px;border-radius:50%;background:<?= $color ?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas <?= $icon ?>" style="color:<?= $color ?>;font-size:1.1rem"></i>
      </div>
      <div><div style="font-size:0.78rem;color:var(--color-text-muted)"><?= $label ?></div><div style="font-weight:700;font-size:1rem"><?= $val ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">Top Products</div></div>
    <div class="admin-card-body" style="padding:0">
      <table class="admin-table">
        <thead><tr><th>#</th><th>Product</th><th>SKU</th><th>Units</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php foreach($top_products as $i=>$p): ?>
          <tr><td><?= $i+1 ?></td><td><?= e($p['name']) ?></td><td style="font-family:monospace"><?= e($p['sku']) ?></td><td><?= number_format($p['units']) ?></td><td><strong>AED <?= number_format($p['revenue'],2) ?></strong></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">Payment Methods</div></div>
    <div class="admin-card-body" style="padding:0">
      <table class="admin-table">
        <thead><tr><th>Method</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php foreach($payment_breakdown as $p): ?>
          <tr><td><?= payment_method_label($p['payment_method']) ?></td><td><?= $p['orders'] ?></td><td>AED <?= number_format($p['revenue'],2) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php elseif($tab==='inventory'): ?>
<!-- ── INVENTORY ── -->
<div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap">
  <div class="admin-card" style="margin:0;flex:1;min-width:160px"><div class="admin-card-body" style="padding:14px;text-align:center"><div style="font-size:1.6rem;font-weight:700"><?= count($products) ?></div><div style="font-size:0.8rem;color:var(--color-text-muted)">Total Products</div></div></div>
  <div class="admin-card" style="margin:0;flex:1;min-width:160px;border-color:var(--color-error)"><div class="admin-card-body" style="padding:14px;text-align:center"><div style="font-size:1.6rem;font-weight:700;color:var(--color-error)"><?= count($low_stock) ?></div><div style="font-size:0.8rem;color:var(--color-text-muted)">Low Stock Alerts</div></div></div>
  <div class="admin-card" style="margin:0;flex:1;min-width:160px"><div class="admin-card-body" style="padding:14px;text-align:center"><div style="font-size:1.6rem;font-weight:700"><?= count($variants) ?></div><div style="font-size:0.8rem;color:var(--color-text-muted)">Variant Options</div></div></div>
</div>

<?php if(!empty($low_stock)): ?>
<div class="admin-card" style="border-color:var(--color-error);margin-bottom:20px">
  <div class="admin-card-header" style="background:#fff5f5"><div class="admin-card-title" style="color:var(--color-error)">⚠️ Low Stock Alerts (threshold: <?= $threshold ?>)</div></div>
  <div class="admin-card-body" style="padding:0">
    <table class="admin-table">
      <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Stock</th><th>Threshold</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach($low_stock as $p): ?>
        <tr><td><?= e($p['name']) ?></td><td><?= e($p['sku']) ?></td><td><?= e($p['category']) ?></td>
          <td><strong style="color:var(--color-error)"><?= $p['stock_quantity'] ?></strong></td>
          <td><?= $p['low_stock_threshold'] ?></td>
          <td><a href="/admin/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header"><div class="admin-card-title">All Products Stock</div></div>
  <div class="admin-card-body" style="padding:0">
    <table class="admin-table">
      <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Brand</th><th>Stock</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($products as $p): $low = $p['stock_quantity'] <= $p['low_stock_threshold']; ?>
        <tr>
          <td><?= e($p['name']) ?></td><td style="font-family:monospace"><?= e($p['sku']) ?></td>
          <td><?= e($p['category']) ?></td><td><?= e($p['brand']) ?></td>
          <td><strong><?= $p['stock_quantity'] ?></strong></td>
          <td><?= $low ? '<span class="badge badge-danger">Low Stock</span>' : '<span class="badge badge-success">OK</span>' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if(!empty($variants)): ?>
<div class="admin-card" style="margin-top:20px">
  <div class="admin-card-header"><div class="admin-card-title">Variant Options Stock</div></div>
  <div class="admin-card-body" style="padding:0">
    <table class="admin-table">
      <thead><tr><th>Product</th><th>Variant Type</th><th>Option</th><th>SKU</th><th>Stock</th></tr></thead>
      <tbody>
        <?php foreach($variants as $v): ?>
        <tr><td><?= e($v['product_name']) ?></td><td><?= e($v['variant_type']) ?></td><td><?= e($v['option_label']) ?></td>
          <td style="font-family:monospace"><?= e($v['variant_sku']) ?></td>
          <td><strong <?= $v['stock_qty']<=0 ? 'style="color:var(--color-error)"' : '' ?>><?= $v['stock_qty'] ?></strong></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php elseif($tab==='customers'): ?>
<!-- ── CUSTOMERS ── -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
  <div class="admin-card" style="margin:0"><div class="admin-card-body" style="padding:16px;text-align:center"><div style="font-size:1.8rem;font-weight:700"><?= number_format($summary['total']) ?></div><div style="font-size:0.8rem;color:var(--color-text-muted)">Total Customers</div></div></div>
  <div class="admin-card" style="margin:0"><div class="admin-card-body" style="padding:16px;text-align:center"><div style="font-size:1.8rem;font-weight:700;color:#10b981"><?= number_format($summary['new_customers']) ?></div><div style="font-size:0.8rem;color:var(--color-text-muted)">New in Period</div></div></div>
  <div class="admin-card" style="margin:0"><div class="admin-card-body" style="padding:16px;text-align:center"><div style="font-size:1.8rem;font-weight:700;color:#C8963C"><?= number_format($summary['returning_customers']) ?></div><div style="font-size:0.8rem;color:var(--color-text-muted)">Returning Customers</div></div></div>
</div>
<div class="admin-card">
  <div class="admin-card-header"><div class="admin-card-title">Top Customers by Lifetime Value</div></div>
  <div class="admin-card-body" style="padding:0">
    <table class="admin-table">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Member Since</th><th>Last Order</th></tr></thead>
      <tbody>
        <?php foreach($top_customers as $i=>$c): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><strong><?= e($c['first_name'].' '.$c['last_name']) ?></strong></td>
          <td><?= e($c['email']) ?></td><td><?= e($c['phone']) ?></td>
          <td><?= $c['total_orders'] ?></td>
          <td><strong>AED <?= number_format($c['total_spent'],2) ?></strong></td>
          <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
          <td><?= $c['last_order'] ? date('d M Y', strtotime($c['last_order'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif($tab==='invoices'): ?>
<!-- ── INVOICES ── -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Invoices (<?= number_format($pagination['total']) ?> total)</div>
  </div>
  <div class="admin-card-body" style="padding:0">
    <table class="admin-table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Emirate</th><th>Total</th><th>Payment</th><th>Status</th><th>Order Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($invoices as $inv): ?>
        <tr>
          <td><strong><?= e($inv['order_number']) ?></strong></td>
          <td><?= e($inv['shipping_name']) ?></td>
          <td><?= e($inv['shipping_phone']) ?></td>
          <td><?= e($inv['shipping_emirate']) ?></td>
          <td><strong>AED <?= number_format($inv['total_amount'],2) ?></strong></td>
          <td><?= payment_method_label($inv['payment_method']) ?></td>
          <td><span class="badge badge-<?= $inv['payment_status']==='paid'?'success':($inv['payment_status']==='refunded'?'info':'warning') ?>"><?= ucfirst($inv['payment_status']) ?></span></td>
          <td><?= order_status_badge($inv['order_status']) ?></td>
          <td><?= date('d M Y', strtotime($inv['created_at'])) ?></td>
          <td style="white-space:nowrap">
            <a href="/admin/orders/<?= $inv['id'] ?>" class="btn btn-sm btn-outline">View</a>
            <a href="/admin/reports/invoice/<?= $inv['id'] ?>" target="_blank" class="btn btn-sm btn-outline" style="margin-left:4px"><i class="fas fa-file-pdf"></i> PDF</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<!-- Pagination -->
<?php if($pagination['total_pages'] > 1): ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px">
  <span style="font-size:0.85rem;color:var(--color-text-muted)">Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?></span>
  <div style="display:flex;gap:6px">
    <?php for($p=max(1,$pagination['current_page']-2);$p<=min($pagination['total_pages'],$pagination['current_page']+2);$p++): ?>
    <a href="?tab=invoices&from=<?= $from ?>&to=<?= $to ?>&page=<?= $p ?>&search=<?= urlencode($search??'') ?>&payment_status=<?= urlencode($status??'') ?>"
      class="btn btn-sm <?= $p==$pagination['current_page']?'btn-primary':'btn-outline' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
</div>
<?php endif; ?>

<?php elseif($tab==='delivery'): ?>
<!-- ── DELIVERY ── -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
  <div class="admin-card" style="margin:0">
    <div class="admin-card-header"><div class="admin-card-title">Orders by Status</div></div>
    <div class="admin-card-body" style="padding:0">
      <table class="admin-table">
        <thead><tr><th>Status</th><th>Count</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php foreach($summary as $s): ?>
          <tr><td><?= order_status_badge($s['order_status']) ?></td><td><strong><?= $s['count'] ?></strong></td><td>AED <?= number_format($s['revenue'],2) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="admin-card" style="margin:0">
    <div class="admin-card-header"><div class="admin-card-title">Delivery Types</div></div>
    <div class="admin-card-body" style="padding:0">
      <table class="admin-table">
        <thead><tr><th>Type</th><th>Count</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php foreach($delivery_types as $d): ?>
          <tr><td><?= ucwords(str_replace('_',' ',$d['delivery_type'])) ?></td><td><strong><?= $d['count'] ?></strong></td><td>AED <?= number_format($d['revenue'],2) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="admin-card">
  <div class="admin-card-header"><div class="admin-card-title">Recent Orders</div></div>
  <div class="admin-card-body" style="padding:0">
    <table class="admin-table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Emirate</th><th>Type</th><th>Status</th><th>Total</th><th>Date</th><th>Delivered</th></tr></thead>
      <tbody>
        <?php foreach($orders as $o): ?>
        <tr>
          <td><a href="/admin/orders/<?= $o['id'] ?? '' ?>" style="color:var(--color-secondary)"><?= e($o['order_number']) ?></a></td>
          <td><?= e($o['shipping_name']) ?></td><td><?= e($o['shipping_emirate']) ?></td>
          <td><?= ucwords(str_replace('_',' ',$o['delivery_type'])) ?></td>
          <td><?= order_status_badge($o['order_status']) ?></td>
          <td>AED <?= number_format($o['total_amount'],2) ?></td>
          <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          <td><?= $o['delivered_at'] ? date('d M Y', strtotime($o['delivered_at'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif($tab==='reviews'): ?>
<!-- ── REVIEWS ── -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px">
  <div class="admin-card" style="margin:0"><div class="admin-card-body" style="padding:14px;text-align:center"><div style="font-size:1.6rem;font-weight:700"><?= number_format($summary['total']) ?></div><div style="font-size:0.78rem;color:var(--color-text-muted)">Total Reviews</div></div></div>
  <div class="admin-card" style="margin:0"><div class="admin-card-body" style="padding:14px;text-align:center"><div style="font-size:1.6rem;font-weight:700;color:#C8963C"><?= number_format($summary['avg_rating'],1) ?>★</div><div style="font-size:0.78rem;color:var(--color-text-muted)">Avg Rating</div></div></div>
  <div class="admin-card" style="margin:0"><div class="admin-card-body" style="padding:14px;text-align:center"><div style="font-size:1.6rem;font-weight:700;color:#10b981"><?= $summary['approved'] ?></div><div style="font-size:0.78rem;color:var(--color-text-muted)">Approved</div></div></div>
  <div class="admin-card" style="margin:0"><div class="admin-card-body" style="padding:14px;text-align:center"><div style="font-size:1.6rem;font-weight:700;color:#f59e0b"><?= $summary['pending'] ?></div><div style="font-size:0.78rem;color:var(--color-text-muted)">Pending</div></div></div>
</div>
<div class="admin-card">
  <div class="admin-card-header"><div class="admin-card-title">All Reviews</div></div>
  <div class="admin-card-body" style="padding:0">
    <table class="admin-table">
      <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Title</th><th>Review</th><th>Status</th><th>Verified</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach($reviews as $r): ?>
        <tr>
          <td><?= e($r['product_name']) ?></td>
          <td><?= e($r['first_name'].' '.$r['last_name']) ?><br><small style="color:var(--color-text-muted)"><?= e($r['email']) ?></small></td>
          <td><span style="color:#C8963C"><?= str_repeat('★',$r['rating']) ?><?= str_repeat('☆',5-$r['rating']) ?></span></td>
          <td><?= e($r['title']) ?></td>
          <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($r['body']) ?></td>
          <td><span class="badge badge-<?= $r['status']==='approved'?'success':($r['status']==='rejected'?'danger':'warning') ?>"><?= ucfirst($r['status']) ?></span></td>
          <td><?= $r['is_verified_purchase'] ? '<span class="badge badge-success">✓</span>' : '—' ?></td>
          <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          <td style="white-space:nowrap">
            <?php if($r['status']==='pending'): ?>
            <form method="POST" action="/admin/reviews/<?= $r['id'] ?>/approve" style="display:inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline" style="color:green">✓</button></form>
            <form method="POST" action="/admin/reviews/<?= $r['id'] ?>/reject" style="display:inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline" style="color:red">✗</button></form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
