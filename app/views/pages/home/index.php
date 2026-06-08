<?php
// __DIR__ = app/views/pages/home
$componentPath = dirname(__DIR__, 2) . '/components';
$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
$_db = \App\Core\Database::getInstance();
$_mapUrl  = $_db->fetch("SELECT setting_value FROM settings WHERE setting_key='google_maps_embed_url'")['setting_value'] ?? '';
$_mapAddr = $_db->fetch("SELECT setting_value FROM settings WHERE setting_key='store_map_address'")['setting_value'] ?? 'Dubai Marina, Dubai, UAE';
?>

<!-- Age Warning -->
<div style="background:var(--color-error);color:#fff;text-align:center;padding:8px;font-size:0.82rem;font-weight:600">
  ⚠️ This website sells tobacco and nicotine products. For adults 18+ only. Tobacco is harmful to health.
</div>

<!-- Hero Slider -->
<section class="hero-slider">
  <div class="swiper heroSwiper">
    <div class="swiper-wrapper">
      <?php if (!empty($banners)): ?>
      <?php foreach ($banners as $banner): ?>
      <div class="swiper-slide">
        <div class="hero-slide" style="background-image:url('<?= e($banner['image_desktop']) ?>')">
          <div class="container">
            <div class="hero-content" data-aos="fade-right">
              <div class="hero-eyebrow">Phantom Smoking — Dubai</div>
              <h1 class="hero-title"><?= e($banner['title']) ?></h1>
              <?php if ($banner['subtitle']): ?><p class="hero-subtitle"><?= e($banner['subtitle']) ?></p><?php endif; ?>
              <div class="hero-actions">
                <?php if ($banner['link_url']): ?>
                <a href="<?= e($banner['link_url']) ?>" class="btn btn-primary btn-lg"><?= e($banner['link_text'] ?: 'Shop Now') ?></a>
                <?php endif; ?>
                <a href="/deals" class="btn btn-outline-gold btn-lg">View Deals</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="swiper-slide">
        <div class="hero-slide" style="background:linear-gradient(135deg,#1A1A2E,#2a2a4e)">
          <div class="container">
            <div class="hero-content">
              <div class="hero-eyebrow">Phantom Smoking — Dubai</div>
              <h1 class="hero-title">Premium <span>Tobacco</span> & Vape Store</h1>
              <p class="hero-subtitle">Cigars, Vapes, Shisha & More — Free Delivery Over AED 100</p>
              <div class="hero-actions">
                <a href="/shop/cigars" class="btn btn-primary btn-lg">Shop Cigars</a>
                <a href="/shop/vapes" class="btn btn-outline-gold btn-lg">Shop Vapes</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
  </div>
</section>

<!-- Trust Badges -->
<div class="trust-badges">
  <div class="container">
    <div class="trust-badge"><i class="fas fa-shipping-fast"></i><div class="trust-badge-text"><strong>Free Delivery</strong><span>On orders over AED 100</span></div></div>
    <div class="trust-badge"><i class="fas fa-bolt"></i><div class="trust-badge-text"><strong>1-Hour Delivery</strong><span>Express in Dubai</span></div></div>
    <div class="trust-badge"><i class="fas fa-star"></i><div class="trust-badge-text"><strong>Reward Points</strong><span>Earn on every order</span></div></div>
    <div class="trust-badge"><i class="fas fa-shield-alt"></i><div class="trust-badge-text"><strong>18+ Verified</strong><span>Age-restricted store</span></div></div>
  </div>
</div>

<!-- Brand Carousel -->
<?php if (!empty($brands)): ?>
<section class="section-sm" style="background:var(--color-bg-light)">
  <div class="container">
    <div class="section-header">
      <div><h2 class="section-title">Our Brands</h2><p class="section-subtitle">Premium brands from around the world</p></div>
      <a href="/brands" class="btn btn-outline btn-sm">View All Brands</a>
    </div>
  </div>
  <!-- Full-width marquee track (outside container so it bleeds edge to edge) -->
  <div class="brand-marquee-wrap">
    <div class="brand-marquee-track">
      <?php
      // Repeat brands enough times to always fill the screen seamlessly
      $repeatCount = max(3, (int)ceil(12 / count($brands)) + 2);
      for ($r = 0; $r < $repeatCount; $r++):
        foreach ($brands as $brand): ?>
      <a href="/brand/<?= e($brand['slug']) ?>" class="brand-marquee-item">
        <?php if ($brand['logo']): ?>
        <img src="<?= e($brand['logo']) ?>" alt="<?= e($brand['name']) ?>" loading="lazy">
        <?php else: ?>
        <span><?= e($brand['name']) ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; endfor; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Category Showcase -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div><h2 class="section-title">Shop by Category</h2><p class="section-subtitle">Explore our premium product range</p></div>
    </div>
    <div class="category-grid">
      <?php foreach (array_slice($categories, 0, 8) as $cat): ?>
      <a href="/shop/<?= e($cat['slug']) ?>" class="category-card" data-aos="fade-up">
        <?php if ($cat['image']): ?>
        <img src="<?= e($cat['image']) ?>" alt="<?= e($cat['name']) ?>" loading="lazy">
        <?php else: ?>
        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--color-primary),#2a2a4e);display:flex;align-items:center;justify-content:center"><i class="fas <?= e($cat['icon'] ?: 'fa-box') ?>" style="font-size:3rem;color:var(--color-secondary)"></i></div>
        <?php endif; ?>
        <div class="category-card-overlay">
          <div class="category-card-name"><?= e($cat['name']) ?></div>
          <div class="category-card-count">Shop Now →</div>
        </div>
        <div class="category-card-arrow"><i class="fas fa-arrow-right"></i></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- New Arrivals -->
<?php if (!empty($new_arrivals)): ?>
<section class="section" style="background:var(--color-bg-light)">
  <div class="container">
    <div class="section-header">
      <div><h2 class="section-title">✨ Just Arrived</h2><p class="section-subtitle">Fresh products added to our collection</p></div>
      <a href="/search?new_arrival=1" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="swiper productSwiper">
      <div class="swiper-wrapper">
        <?php foreach ($new_arrivals as $product): ?>
        <div class="swiper-slide"><?php include $componentPath . '/product-card.php'; ?></div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Featured / Best Sellers Tabs -->
<section class="section">
  <div class="container">
    <div class="tabs">
      <button class="tab-btn active" onclick="switchTab('best-sellers', this)">🏆 Best Sellers</button>
      <button class="tab-btn" onclick="switchTab('featured', this)">⭐ Featured</button>
      <button class="tab-btn" onclick="switchTab('on-sale', this)">💰 On Sale</button>
    </div>
    <div class="tab-content active" id="tab-best-sellers">
      <div class="grid grid-4">
        <?php foreach ($best_sellers as $product): ?>
        <?php include $componentPath . '/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="tab-content" id="tab-featured">
      <div class="grid grid-4">
        <?php foreach ($featured as $product): ?>
        <?php include $componentPath . '/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="tab-content" id="tab-on-sale">
      <div class="grid grid-4">
        <?php foreach ($on_sale as $product): ?>
        <?php include $componentPath . '/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Promo Banner -->
<section class="section-sm">
  <div class="container">
    <div class="promo-banner">
      <div class="promo-banner-content">
        <div class="hero-eyebrow">Limited Time Offer</div>
        <h2 class="promo-banner-title">Get <span>AED 20 Off</span><br>Your First Order</h2>
        <p style="opacity:0.8;margin-bottom:20px">Use code <strong style="color:var(--color-secondary)">WELCOME20</strong> at checkout</p>
        <a href="/register" class="btn btn-primary btn-lg">Create Account</a>
      </div>
    </div>
  </div>
</section>

<!-- Why Choose Us -->
<section class="section" style="background:var(--color-bg-light)">
  <div class="container">
    <div class="section-header" style="justify-content:center;text-align:center">
      <div><h2 class="section-title">Why Choose Phantom Smoking?</h2></div>
    </div>
    <div class="why-grid">
      <div class="why-card" data-aos="fade-up" data-aos-delay="0"><div class="why-icon"><i class="fas fa-certificate"></i></div><div class="why-title">100% Authentic</div><div class="why-text">All products are genuine and sourced directly from authorized distributors.</div></div>
      <div class="why-card" data-aos="fade-up" data-aos-delay="100"><div class="why-icon"><i class="fas fa-bolt"></i></div><div class="why-title">1-Hour Delivery</div><div class="why-text">Express delivery within 1 hour in Dubai. Next-day delivery across UAE.</div></div>
      <div class="why-card" data-aos="fade-up" data-aos-delay="200"><div class="why-icon"><i class="fas fa-star"></i></div><div class="why-title">Reward Points</div><div class="why-text">Earn 1 point per AED spent. Redeem for discounts on future orders.</div></div>
      <div class="why-card" data-aos="fade-up" data-aos-delay="300"><div class="why-icon"><i class="fas fa-headset"></i></div><div class="why-title">Expert Support</div><div class="why-text">Our knowledgeable team is available 7 days a week to help you.</div></div>
    </div>
  </div>
</section>

<!-- Newsletter -->
<section class="section" style="background:var(--color-primary)">
  <div class="container" style="text-align:center;max-width:560px">
    <h2 style="color:#fff;font-family:var(--font-heading);margin-bottom:8px">Stay in the Loop</h2>
    <p style="color:rgba(255,255,255,0.7);margin-bottom:24px">Subscribe for exclusive deals, new arrivals and special offers</p>
    <form onsubmit="subscribeNewsletter(event)" style="display:flex;gap:0;max-width:400px;margin:0 auto">
      <input type="email" class="newsletter-input" placeholder="Enter your email" required style="flex:1;border-radius:var(--radius) 0 0 var(--radius)">
      <button type="submit" class="newsletter-btn" style="border-radius:0 var(--radius) var(--radius) 0;padding:12px 20px">Get AED 20 Off</button>
    </form>
  </div>
</section>

<!-- Store Location Map -->
<?php if (!empty($_mapUrl)): ?>
<section style="background:var(--color-bg-light);padding:0">
  <div style="position:relative">
    <iframe
      src="<?= e($_mapUrl) ?>"
      width="100%"
      height="380"
      style="border:0;display:block"
      allowfullscreen
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade">
    </iframe>
    <!-- Overlay info card -->
    <div style="position:absolute;bottom:20px;left:50%;transform:translateX(-50%);background:var(--color-white);border-radius:var(--radius-lg);padding:16px 24px;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;align-items:center;gap:16px;min-width:280px;max-width:90vw;z-index:10">
      <div style="width:44px;height:44px;background:rgba(200,150,60,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-map-marker-alt" style="color:var(--color-secondary);font-size:1.1rem"></i>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-weight:700;font-size:0.9rem;margin-bottom:2px">Phantom Smoking</div>
        <div style="font-size:0.8rem;color:var(--color-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($_mapAddr) ?></div>
      </div>
      <a href="https://maps.google.com/?q=<?= urlencode($_mapAddr) ?>" target="_blank"
        class="btn btn-sm btn-primary" style="flex-shrink:0;white-space:nowrap">
        <i class="fas fa-directions"></i> Directions
      </a>
    </div>
  </div>
</section>
<?php endif; ?>
