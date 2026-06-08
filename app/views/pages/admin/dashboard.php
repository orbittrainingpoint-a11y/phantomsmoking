<!-- KPI Cards -->
<div class="kpi-grid">
  <div class="kpi-card"><div class="kpi-icon gold"><i class="fas fa-dollar-sign"></i></div><div><div class="kpi-value"><?= format_price($stats['today_revenue']) ?></div><div class="kpi-label">Today's Revenue</div></div></div>
  <div class="kpi-card"><div class="kpi-icon blue"><i class="fas fa-shopping-cart"></i></div><div><div class="kpi-value"><?= $stats['today_orders'] ?></div><div class="kpi-label">Today's Orders</div></div></div>
  <div class="kpi-card"><div class="kpi-icon green"><i class="fas fa-users"></i></div><div><div class="kpi-value"><?= $stats['new_customers'] ?></div><div class="kpi-label">New Customers Today</div></div></div>
  <div class="kpi-card"><div class="kpi-icon red"><i class="fas fa-clock"></i></div><div><div class="kpi-value"><?= $stats['pending_orders'] ?></div><div class="kpi-label">Pending Orders</div></div></div>
</div>

<!-- Revenue Chart -->
<div class="admin-card">
  <div class="admin-card-header"><div class="admin-card-title">Revenue — Last 30 Days</div></div>
  <div class="admin-card-body"><canvas id="revenueChart" height="80"></canvas></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
  <!-- Recent Orders -->
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">Recent Orders</div><a href="/admin/orders" class="btn btn-sm btn-outline">View All</a></div>
    <div class="admin-card-body" style="padding:0">
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($recent_orders as $order): ?>
            <tr>
              <td><a href="/admin/orders/<?= $order['id'] ?>" style="color:var(--color-secondary)"><?= e($order['order_number']) ?></a></td>
              <td><?= e($order['shipping_name']) ?></td>
              <td><strong><?= format_price($order['total_amount']) ?></strong></td>
              <td><?= order_status_badge($order['order_status']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Low Stock -->
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">⚠️ Low Stock Alert</div><a href="/admin/products?status=active" class="btn btn-sm btn-outline">View All</a></div>
    <div class="admin-card-body" style="padding:0">
      <?php if (empty($low_stock)): ?>
      <div style="padding:20px;text-align:center;color:var(--color-text-muted)"><i class="fas fa-check-circle" style="color:var(--color-success)"></i> All products well stocked</div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Product</th><th>Stock</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($low_stock as $p): ?>
            <tr>
              <td><?php if ($p['primary_image']): ?><img src="<?= e($p['primary_image']) ?>" class="product-thumb" style="margin-right:8px"><?php endif; ?><?= e(truncate($p['name'], 30)) ?></td>
              <td><span style="color:<?= $p['stock_quantity'] == 0 ? 'var(--color-error)' : 'var(--color-warning)' ?>;font-weight:700"><?= $p['stock_quantity'] ?></span></td>
              <td><a href="/admin/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="admin-card" style="margin-top:0">
  <div class="admin-card-header"><div class="admin-card-title">Quick Actions</div></div>
  <div class="admin-card-body" style="display:flex;gap:12px;flex-wrap:wrap">
    <a href="/admin/products/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Product</a>
    <a href="/admin/coupons" class="btn btn-outline btn-sm"><i class="fas fa-ticket-alt"></i> Add Coupon</a>
    <a href="/admin/orders?status=pending" class="btn btn-outline btn-sm"><i class="fas fa-clock"></i> Pending Orders</a>
    <a href="/admin/reports" class="btn btn-outline btn-sm"><i class="fas fa-chart-bar"></i> View Reports</a>
  </div>
</div>

<script>
const salesData = <?= json_encode($sales_data) ?>;
function initRevenueChart() {
  const canvas = document.getElementById('revenueChart');
  if (!canvas || typeof Chart === 'undefined') return;
  new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: salesData.map(d => d.period),
      datasets: [{
        label: 'Revenue (AED)',
        data: salesData.map(d => parseFloat(d.revenue || 0)),
        borderColor: '#C8963C',
        backgroundColor: 'rgba(200,150,60,0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#C8963C',
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
}
// Load chart.js async — only on dashboard, won't block page render
const _chartScript = document.createElement('script');
_chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js';
_chartScript.onload = initRevenueChart;
_chartScript.onerror = () => console.warn('Chart.js failed to load');
document.body.appendChild(_chartScript);
</script>
