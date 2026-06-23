<?php
use App\Core\Auth;
use App\Core\Session;
use App\Models\Category;
use App\Models\Cart;

$categories = (new Category())->getMenuCategories();
$cartData   = (new Cart())->getCartWithItems();
$cartCount  = $cartData['count'] ?? 0;
$flashSuccess = Session::flash('success');
$flashError   = Session::flash('error');
// Use already-loaded APP_URL env var directly
$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? "Phantom Smoking") ?></title>
<meta name="description" content="<?= e($meta_description ?? "Premium tobacco, cigars, vape & shisha store in Dubai UAE. Free delivery over AED 100.") ?>">
<link rel="canonical" href="<?= $baseUrl . e($_SERVER['REQUEST_URI'] ?? '/') ?>">
<!-- Open Graph -->
<meta property="og:title" content="<?= e($title ?? "Phantom Smoking") ?>">
<meta property="og:description" content="<?= e($meta_description ?? '') ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= $baseUrl . e($_SERVER['REQUEST_URI'] ?? '/') ?>">
<meta name="csrf-token" content="<?php if (!\App\Core\Session::has('csrf_token')) \App\Core\Session::set('csrf_token', bin2hex(random_bytes(32))); echo e(\App\Core\Session::get('csrf_token')); ?>">
<!-- Favicons -->
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon-180x180.png">
<!-- PWA -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#C8963C">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Phantom">
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Swiper -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<!-- AOS -->
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<!-- App CSS -->
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/root.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/layout.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/header.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/footer.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/home.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/product.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/shop.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/cart.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/checkout.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/account.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/responsive.css">
</head>
<body>

<!-- Announcement Bar -->
<div class="announcement-bar">
  <div class="container">
    <div class="announcement-marquee">
      <span>🚀 FREE NEXT DAY DELIVERY ON ORDERS OVER AED 100 &nbsp;|&nbsp; ⚡ 1 HOUR EXPRESS DELIVERY AVAILABLE IN DUBAI &nbsp;|&nbsp; 🎁 EARN REWARD POINTS ON EVERY PURCHASE &nbsp;|&nbsp; 🔞 AGE RESTRICTED — 18+ ONLY</span>
    </div>
    <div class="announcement-phone"><a href="tel:+971568335210"><i class="fas fa-phone"></i> +971 56 833 5210</a></div>
  </div>
</div>

<!-- Main Header -->
<header class="site-header" id="siteHeader">
  <div class="container">
    <div class="header-main">
      <a href="/" class="site-logo">
        <img src="<?= $baseUrl ?>/assets/images/logo.webp" alt="Phantom Smoking" class="site-logo-img">
      </a>
      <div class="header-search">
        <form class="search-form" action="/search" method="GET" autocomplete="off">
          <select class="search-category" name="cat">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="q" class="search-input" placeholder="Search products, brands..." id="searchInput" value="<?= e($_GET['q'] ?? '') ?>">
          <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
        </form>
        <div class="search-dropdown" id="searchDropdown"></div>
      </div>
      <div class="header-icons">
        <?php if (Auth::check()): ?>
        <a href="/account" class="header-icon-btn"><i class="fas fa-user"></i><span>Account</span></a>
        <?php else: ?>
        <a href="/login" class="header-icon-btn"><i class="fas fa-user"></i><span>Login</span></a>
        <?php endif; ?>
        <?php if (Auth::check()): ?>
        <a href="/account/wishlist" class="header-icon-btn"><i class="fas fa-heart"></i><span>Wishlist</span></a>
        <?php endif; ?>
        <button class="header-icon-btn" id="cartToggle" onclick="toggleCartDrawer()">
          <i class="fas fa-shopping-bag"></i>
          <span>Cart</span>
          <span class="icon-badge" id="cartBadge"><?= $cartCount ?></span>
        </button>
        <button class="nav-toggle" id="navToggle" onclick="toggleMobileNav()">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Primary Navigation -->
  <nav class="main-nav">
    <div class="container">
      <ul class="nav-list">
        <li class="nav-item"><a href="/" class="nav-link">Home</a></li>
        <?php foreach ($categories as $cat): ?>
        <li class="nav-item">
          <a href="/shop/<?= e($cat['slug']) ?>" class="nav-link">
            <?= e($cat['name']) ?>
            <?php if (!empty($cat['children'])): ?><i class="fas fa-chevron-down"></i><?php endif; ?>
          </a>
          <?php if (!empty($cat['children'])): ?>
          <div class="mega-dropdown">
            <div>
              <div class="mega-col-title"><?= e($cat['name']) ?></div>
              <?php foreach ($cat['children'] as $child): ?>
              <a href="/shop/<?= e($child['slug']) ?>" class="mega-link"><?= e($child['name']) ?></a>
              <?php endforeach; ?>
            </div>
            <div>
              <div class="mega-col-title">Popular Brands</div>
              <a href="/brands" class="mega-link">View All Brands</a>
            </div>
            <div>
              <div class="mega-col-title">Quick Links</div>
              <a href="/deals" class="mega-link">🔥 Today's Deals</a>
              <a href="/shop/<?= e($cat['slug']) ?>?new_arrival=1" class="mega-link">✨ New Arrivals</a>
              <a href="/shop/<?= e($cat['slug']) ?>?on_sale=1" class="mega-link">💰 On Sale</a>
            </div>
          </div>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <li class="nav-item"><a href="/brands" class="nav-link">Brands</a></li>
        <li class="nav-item"><a href="/deals" class="nav-link" style="color:var(--color-secondary)">🔥 Deals</a></li>
      </ul>
    </div>
  </nav>
</header>

<!-- Mobile Nav -->
<div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMobileNav()"></div>
<nav class="mobile-nav" id="mobileNav">
  <div class="mobile-nav-header">
    <img src="<?= $baseUrl ?>/assets/images/logo.webp" alt="Phantom Smoking" style="height:40px;width:auto;object-fit:contain;filter:brightness(0) invert(1)">
    <span class="mobile-nav-close" onclick="toggleMobileNav()"><i class="fas fa-times"></i></span>
  </div>
  <a href="/" class="mobile-nav-link">Home</a>
  <?php foreach ($categories as $cat): ?>
  <div class="mobile-nav-link" onclick="toggleMobileSub('sub-<?= $cat['id'] ?>')">
    <?= e($cat['name']) ?>
    <?php if (!empty($cat['children'])): ?><i class="fas fa-chevron-right"></i><?php endif; ?>
  </div>
  <?php if (!empty($cat['children'])): ?>
  <div class="mobile-sub-links" id="sub-<?= $cat['id'] ?>">
    <a href="/shop/<?= e($cat['slug']) ?>" class="mobile-sub-link">All <?= e($cat['name']) ?></a>
    <?php foreach ($cat['children'] as $child): ?>
    <a href="/shop/<?= e($child['slug']) ?>" class="mobile-sub-link"><?= e($child['name']) ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php endforeach; ?>
  <a href="/brands" class="mobile-nav-link">All Brands</a>
  <a href="/deals" class="mobile-nav-link">🔥 Deals</a>
  <hr style="border-color:rgba(255,255,255,0.1);margin:8px 0">
  <?php if (Auth::check()): ?>
  <a href="/account" class="mobile-nav-link">My Account</a>
  <a href="/logout" class="mobile-nav-link">Logout</a>
  <?php else: ?>
  <a href="/login" class="mobile-nav-link">Login</a>
  <a href="/register" class="mobile-nav-link">Register</a>
  <?php endif; ?>
</nav>

<!-- Cart Drawer -->
<div class="cart-drawer-overlay" id="cartOverlay" onclick="toggleCartDrawer()"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-drawer-header">
    <div class="cart-drawer-title">Your Cart (<span id="drawerCartCount"><?= $cartCount ?></span>)</div>
    <span class="cart-drawer-close" onclick="toggleCartDrawer()"><i class="fas fa-times"></i></span>
  </div>
  <div class="cart-drawer-body" id="cartDrawerBody">
    <?php if (empty($cartData['items'])): ?>
    <div class="empty-state"><i class="fas fa-shopping-bag"></i><h3>Your cart is empty</h3><p>Add some products to get started</p></div>
    <?php else: ?>
    <?php foreach ($cartData['items'] as $item): ?>
    <div class="cart-item" id="cart-item-<?= $item['id'] ?>">
      <div class="cart-item-img"><img src="<?= e($item['product_image'] ?: '/assets/images/placeholder.jpg') ?>" alt="<?= e($item['name']) ?>" loading="lazy"></div>
      <div>
        <div class="cart-item-name"><?= e($item['name']) ?></div>
        <?php
          $varLabel = $item['variant_name'] ?: ($item['selected_flavours'] ?? '');
        ?>
        <?php if ($varLabel): ?><div class="cart-item-variant"><?= e($varLabel) ?></div><?php endif; ?>
        <div class="cart-item-price"><?= format_price($item['unit_price']) ?></div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px">
          <div class="qty-selector">
            <button class="qty-btn" onclick="updateCartItem(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">−</button>
            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" onchange="updateCartItem(<?= $item['id'] ?>, this.value)">
            <button class="qty-btn" onclick="updateCartItem(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
          </div>
          <a class="cart-item-remove" onclick="removeCartItem(<?= $item['id'] ?>)"><i class="fas fa-trash"></i> Remove</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="cart-drawer-footer">
    <div class="cart-drawer-total"><span>Total</span><span id="drawerCartTotal"><?= format_price($cartData['total']) ?></span></div>
    <a href="/cart" class="btn btn-outline btn-full" style="margin-bottom:8px">View Cart</a>
    <a href="/checkout" class="btn btn-primary btn-full">Checkout</a>
  </div>
</div>

<!-- Flash Messages -->
<?php if ($flashSuccess): ?>
<div class="toast-container"><div class="toast success"><i class="fas fa-check-circle"></i><?= e($flashSuccess) ?></div></div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="toast-container"><div class="toast error"><i class="fas fa-exclamation-circle"></i><?= e($flashError) ?></div></div>
<?php endif; ?>

<!-- Main Content -->
<main id="main">
<?= $content ?>
</main>

<!-- Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <img src="<?= $baseUrl ?>/assets/images/logo.webp" alt="Phantom Smoking" style="height:56px;width:auto;object-fit:contain;margin-bottom:12px;filter:brightness(0) invert(1)">
        <p class="footer-tagline">Premium Tobacco, Cigars, Vape & Shisha — Dubai, UAE</p>
        <div class="footer-social">
          <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
          <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
          <a href="https://wa.me/971568335210" class="social-link"><i class="fab fa-whatsapp"></i></a>
        </div>
        <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
          <input type="email" class="newsletter-input" placeholder="Your email address" required>
          <button type="submit" class="newsletter-btn">Subscribe</button>
        </form>
      </div>
      <div>
        <div class="footer-heading">Quick Links</div>
        <div class="footer-links">
          <a href="/">Home</a><a href="/about">About Us</a><a href="/contact">Contact</a>
          <a href="/brands">All Brands</a><a href="/deals">Deals & Offers</a>
        </div>
      </div>
      <div>
        <div class="footer-heading">Customer Service</div>
        <div class="footer-links">
          <a href="/faq">FAQ</a><a href="/shipping-policy">Shipping Policy</a>
          <a href="/returns-policy">Returns Policy</a><a href="/privacy-policy">Privacy Policy</a>
          <a href="/terms">Terms & Conditions</a>
        </div>
      </div>
      <div>
        <div class="footer-heading">Contact Us</div>
        <div class="footer-contact-item"><i class="fas fa-map-marker-alt"></i><span>Dubai, United Arab Emirates</span></div>
        <div class="footer-contact-item"><i class="fas fa-phone"></i><a href="tel:+971568335210">+971 56 833 5210</a></div>
        <div class="footer-contact-item"><i class="fas fa-envelope"></i><a href="mailto:<?= e(setting('contact_email','phantomsmokingonline@gmail.com')) ?>"><?= e(setting('contact_email','phantomsmokingonline@gmail.com')) ?></a></div>
        <div class="footer-contact-item"><i class="fab fa-whatsapp"></i><a href="https://wa.me/971568335210">WhatsApp Us</a></div>
        <div class="footer-contact-item"><i class="fas fa-clock"></i><span>Sat–Thu: 10AM–11PM | Fri: 2PM–11PM</span></div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> Phantom Smoking. All rights reserved.</span>
      <div class="footer-age-badge"><i class="fas fa-shield-alt"></i> 18+ Only</div>
      <span style="font-size:0.75rem;opacity:0.5">⚠️ Tobacco products are harmful to health. For adults only.</span>
    </div>
  </div>
</footer>

<!-- Scroll to top -->
<button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-arrow-up"></i></button>

<!-- Floating Contact Buttons -->
<?php
$_db = \App\Core\Database::getInstance();
$_wa  = setting('whatsapp_number', '971568335210');
$_em  = setting('contact_email', 'phantomsmokingonline@gmail.com');
?>
<div style="position:fixed;bottom:24px;right:18px;display:flex;flex-direction:column;gap:12px;z-index:99999">
  <a href="https://wa.me/<?= e($_wa) ?>" target="_blank" rel="noopener"
    style="width:54px;height:54px;border-radius:50%;background:#25d366;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(0,0,0,0.35);text-decoration:none"
    title="WhatsApp Us">
    <i class="fab fa-whatsapp" style="color:#ffffff;font-size:1.6rem;line-height:1"></i>
  </a>
  <a href="mailto:<?= e($_em) ?>"
    style="width:54px;height:54px;border-radius:50%;background:#C8963C;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(0,0,0,0.35);text-decoration:none"
    title="Email Us">
    <i class="fas fa-envelope" style="color:#ffffff;font-size:1.3rem;line-height:1"></i>
  </a>
</div>

<!-- Add Modal: Variants + Flavours -->
<div id="addModal" style="display:none;position:fixed;inset:0;z-index:99990;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,0.6)" onclick="if(event.target===this)closeAddModal()">
  <div style="background:var(--color-white);border-radius:16px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden">
    <div style="background:linear-gradient(135deg,#1A1A2E,#2a2a4e);padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
      <div id="addModalTitle" style="color:#fff;font-size:1rem;font-weight:700"></div>
      <button onclick="closeAddModal()" style="width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.15);border:none;color:#fff;cursor:pointer;font-size:1rem">×</button>
    </div>
    <div id="addModal_loading" style="padding:40px;text-align:center">
      <i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#C8963C"></i>
    </div>
    <div id="addModal_select" style="display:none;padding:20px">
      <div id="addModal_selects"></div>
      <div id="addModal_price" style="display:none;font-family:var(--font-mono);font-size:1.1rem;font-weight:700;color:#C8963C;margin-bottom:12px"></div>
      <div style="display:flex;gap:10px;margin-top:4px">
        <button onclick="closeAddModal()" style="flex:1;padding:11px;border:2px solid #E5E0D8;border-radius:6px;background:#fff;cursor:pointer;font-weight:600">Cancel</button>
        <button id="addModal_confirmBtn" onclick="addModalConfirm()" disabled style="flex:1;padding:11px;border:none;border-radius:6px;background:#C8963C;color:#fff;cursor:pointer;font-weight:600">Add to Cart</button>
      </div>
    </div>
    <div id="addModal_after" style="display:none;padding:20px">
      <p style="font-size:0.85rem;color:#6B7280;margin-bottom:10px">Added to cart!</p>
      <div id="addModal_addedList" style="margin-bottom:14px;display:flex;flex-wrap:wrap;gap:6px"></div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <button onclick="addModalAddMore()" style="padding:11px;border:2px solid #1A1A2E;border-radius:6px;background:#fff;cursor:pointer;font-weight:600"><i class="fas fa-plus"></i> Add Different Option</button>
        <button onclick="closeAddModal();document.getElementById('cartDrawer').classList.add('open');document.getElementById('cartOverlay').classList.add('show');document.body.style.overflow='hidden'" style="padding:11px;border:none;border-radius:6px;background:#C8963C;color:#fff;cursor:pointer;font-weight:600"><i class="fas fa-shopping-bag"></i> View Cart</button>
        <a href="/checkout" onclick="closeAddModal()" style="display:block;padding:11px;border:2px solid #1A1A2E;border-radius:6px;background:#1A1A2E;color:#fff;cursor:pointer;font-weight:600;text-align:center;text-decoration:none"><i class="fas fa-bolt"></i> Checkout Now</a>
      </div>
    </div>
    <div id="addModal_none" style="display:none;padding:32px;text-align:center;color:#6B7280">No options available.</div>
  </div>
</div>
<style>#addModal.open{display:flex!important}</style>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" onerror="console.warn('Swiper CDN failed')"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js" onerror="console.warn('AOS CDN failed')"></script>
<?php
$_jsBase = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/assets/js';
$_v = function(string $file) use ($_jsBase): string {
    $path = $_jsBase . '/' . $file;
    return (string)(@filemtime($path) ?: '1');
};
?>
<script src="<?= $baseUrl ?>/assets/js/main.js?v=<?= $_v('main.js') ?>"></script>
<script src="<?= $baseUrl ?>/assets/js/cart.js?v=<?= $_v('cart.js') ?>"></script>
<script src="<?= $baseUrl ?>/assets/js/search.js?v=<?= $_v('search.js') ?>"></script>
<script src="<?= $baseUrl ?>/assets/js/slider.js?v=<?= $_v('slider.js') ?>"></script>
<script src="<?= $baseUrl ?>/assets/js/animations.js?v=<?= $_v('animations.js') ?>"></script>
<script src="<?= $baseUrl ?>/assets/js/notifications.js?v=<?= $_v('notifications.js') ?>"></script>
<script src="<?= $baseUrl ?>/assets/js/pwa.js?v=<?= $_v('pwa.js') ?>"></script>

<!-- PWA Install Banner -->
<div id="pwaBanner" style="
  position:fixed;bottom:0;left:0;right:0;z-index:999999;
  background:linear-gradient(135deg,#1A1A2E,#16213E);
  border-top:2px solid #C8963C;
  padding:16px 20px 20px;
  transform:translateY(100%);
  transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
  box-shadow:0 -8px 40px rgba(0,0,0,0.5);
  display:none;
">
  <style>
    #pwaBanner.show{display:block!important;transform:translateY(0)!important}
    #pwaIosHint{display:none;margin-top:10px;padding:10px 12px;background:rgba(200,150,60,0.12);border:1px solid rgba(200,150,60,0.3);border-radius:8px;font-size:0.8rem;color:#C8963C;line-height:1.5}
    #pwaIosHint.show{display:block}
  </style>
  <button onclick="_pwaDismiss()" style="position:absolute;top:10px;right:14px;background:none;border:none;color:rgba(255,255,255,0.4);font-size:1.2rem;cursor:pointer;line-height:1">×</button>
  <div style="display:flex;align-items:center;gap:14px">
    <img src="/assets/images/icon-192.png" alt="" style="width:52px;height:52px;border-radius:12px;flex-shrink:0" onerror="this.style.display='none'">
    <div style="flex:1;min-width:0">
      <div style="color:#fff;font-weight:700;font-size:0.95rem;margin-bottom:2px">Install Phantom Smoking</div>
      <div style="color:rgba(255,255,255,0.55);font-size:0.78rem">Shop faster — works like a native app</div>
    </div>
  </div>
  <div style="display:flex;gap:10px;margin-top:14px">
    <button onclick="_pwaDismiss()" style="flex:1;padding:10px;border:1px solid rgba(255,255,255,0.15);border-radius:8px;background:transparent;color:rgba(255,255,255,0.6);font-size:0.85rem;cursor:pointer">Not now</button>
    <button onclick="_pwaInstall()" style="flex:2;padding:10px;border:none;border-radius:8px;background:#C8963C;color:#fff;font-weight:700;font-size:0.9rem;cursor:pointer"><i class="fas fa-download"></i> Install App</button>
  </div>
  <div id="pwaIosHint"><i class="fas fa-share-square"></i> Tap <strong>Share</strong> then <strong>"Add to Home Screen"</strong> to install.</div>
</div>
</body>
</html>
