<?php
use App\Core\Auth;
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
function accountActive(string $path): string {
    return str_starts_with($_SERVER['REQUEST_URI'] ?? '/', $path) ? 'active' : '';
}
$user = Auth::user();
?>
<aside class="account-sidebar">
  <div class="account-user-info">
    <div class="account-avatar"><?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?></div>
    <div class="account-user-name"><?= e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></div>
    <div class="account-user-email"><?= e($user['email'] ?? '') ?></div>
  </div>
  <a href="/account" class="account-nav-link <?= accountActive('/account') && $_SERVER['REQUEST_URI'] === '/account' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="/account/orders" class="account-nav-link <?= accountActive('/account/orders') ?>"><i class="fas fa-shopping-bag"></i> My Orders</a>
  <a href="/account/wishlist" class="account-nav-link <?= accountActive('/account/wishlist') ?>"><i class="fas fa-heart"></i> Wishlist</a>
  <a href="/account/addresses" class="account-nav-link <?= accountActive('/account/addresses') ?>"><i class="fas fa-map-marker-alt"></i> Addresses</a>
  <a href="/account/rewards" class="account-nav-link <?= accountActive('/account/rewards') ?>"><i class="fas fa-star"></i> Reward Points</a>
  <a href="/account/profile" class="account-nav-link <?= accountActive('/account/profile') ?>"><i class="fas fa-user-edit"></i> My Profile</a>
  <a href="/account/change-password" class="account-nav-link <?= accountActive('/account/change-password') ?>"><i class="fas fa-lock"></i> Change Password</a>
  <a href="/logout" class="account-nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
