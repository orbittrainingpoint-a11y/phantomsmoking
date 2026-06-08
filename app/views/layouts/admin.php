<?php
use App\Core\Auth;
use App\Core\Session;
$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
$flashSuccess = Session::flash('success');
$flashError   = Session::flash('error');
$currentUri   = $_SERVER['REQUEST_URI'] ?? '/';
function adminNavActive(string $path): string {
    return str_starts_with($_SERVER['REQUEST_URI'] ?? '/', $path) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Admin — Phantom Smoking') ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/root.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-logo">
      <img src="<?= $baseUrl ?>/assets/images/logo.webp" alt="Phantom Smoking" style="height:44px;width:auto;object-fit:contain;display:block;margin-bottom:6px;filter:brightness(0) invert(1)">
      <div class="admin-logo-sub">Admin Panel</div>
    </div>
    <div class="admin-nav-section">
      <div class="admin-nav-label">Main</div>
      <a href="/admin" class="admin-nav-link <?= adminNavActive('/admin') && $_SERVER['REQUEST_URI'] === '/admin' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    </div>
    <div class="admin-nav-section">
      <div class="admin-nav-label">Catalog</div>
      <a href="/admin/products" class="admin-nav-link <?= adminNavActive('/admin/products') ?>"><i class="fas fa-box"></i> Products</a>
      <a href="/admin/categories" class="admin-nav-link <?= adminNavActive('/admin/categories') ?>"><i class="fas fa-tags"></i> Categories</a>
      <a href="/admin/brands" class="admin-nav-link <?= adminNavActive('/admin/brands') ?>"><i class="fas fa-trademark"></i> Brands</a>
      <a href="/admin/flavours" class="admin-nav-link <?= adminNavActive('/admin/flavours') ?>"><i class="fas fa-candy-cane"></i> Flavours</a>
    </div>
    <div class="admin-nav-section">
      <div class="admin-nav-label">Sales</div>
      <a href="/admin/orders" class="admin-nav-link <?= adminNavActive('/admin/orders') ?>"><i class="fas fa-shopping-cart"></i> Orders</a>
      <a href="/admin/customers" class="admin-nav-link <?= adminNavActive('/admin/customers') ?>"><i class="fas fa-users"></i> Customers</a>
      <a href="/admin/coupons" class="admin-nav-link <?= adminNavActive('/admin/coupons') ?>"><i class="fas fa-ticket-alt"></i> Coupons</a>
    </div>
    <div class="admin-nav-section">
      <div class="admin-nav-label">Content</div>
      <a href="/admin/banners" class="admin-nav-link <?= adminNavActive('/admin/banners') ?>"><i class="fas fa-image"></i> Banners</a>
      <a href="/admin/reviews" class="admin-nav-link <?= adminNavActive('/admin/reviews') ?>"><i class="fas fa-star"></i> Reviews</a>
      <a href="/admin/delivery-zones" class="admin-nav-link <?= adminNavActive('/admin/delivery-zones') ?>"><i class="fas fa-truck"></i> Delivery Zones</a>
    </div>
    <div class="admin-nav-section">
      <div class="admin-nav-label">Analytics</div>
      <a href="/admin/reports" class="admin-nav-link <?= adminNavActive('/admin/reports') ?>"><i class="fas fa-chart-bar"></i> Reports</a>
    </div>
    <div class="admin-nav-section">
      <a href="/admin/settings" class="admin-nav-link <?= adminNavActive('/admin/settings') ?>"><i class="fas fa-cog"></i> Settings</a>
      <a href="/" class="admin-nav-link"><i class="fas fa-external-link-alt"></i> View Store</a>
      <a href="/logout" class="admin-nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <!-- Main -->
  <div class="admin-main">
    <div class="admin-topbar">
      <div style="display:flex;align-items:center;gap:16px">
        <button onclick="document.getElementById('adminSidebar').classList.toggle('open')" style="display:none" id="adminMenuBtn"><i class="fas fa-bars"></i></button>
        <div class="admin-page-title"><?= e($title ?? 'Dashboard') ?></div>
      </div>
      <div style="display:flex;align-items:center;gap:16px">
        <span style="font-size:0.88rem;color:var(--color-text-muted)">Welcome, <?= e(Auth::user()['first_name'] ?? 'Admin') ?></span>
        <a href="/logout" class="btn btn-sm btn-outline">Logout</a>
      </div>
    </div>

    <?php if ($flashSuccess): ?>
    <div style="padding:0 28px;margin-top:16px"><div class="alert alert-success"><i class="fas fa-check-circle"></i><?= e($flashSuccess) ?></div></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
    <div style="padding:0 28px;margin-top:16px"><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?= e($flashError) ?></div></div>
    <?php endif; ?>

    <div class="admin-content">
      <?= $content ?>
    </div>
  </div>
</div>
<div id="toastContainer" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px"></div>
<script src="<?= $baseUrl ?>/assets/js/admin.js?v=<?= @filemtime(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/assets/js/admin.js') ?: time() ?>"></script>
</body>
</html>
