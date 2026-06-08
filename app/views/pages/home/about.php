<!-- Hero -->
<div style="background:linear-gradient(135deg,var(--color-primary) 0%,#2a2a4e 100%);padding:72px 0 56px;text-align:center;position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:url('/assets/images/placeholder.jpg') center/cover no-repeat;opacity:0.06"></div>
  <div class="container" style="position:relative;z-index:1">
    <div style="display:inline-block;background:rgba(200,150,60,0.15);border:1px solid rgba(200,150,60,0.3);border-radius:20px;padding:6px 18px;font-size:0.78rem;font-weight:700;letter-spacing:2px;color:var(--color-secondary);text-transform:uppercase;margin-bottom:16px">Premium Lifestyle Brand</div>
    <h1 style="font-family:var(--font-heading);font-size:clamp(2rem,5vw,3rem);color:#fff;margin-bottom:16px">About <span style="color:var(--color-secondary)">Phantom Smoking</span></h1>
    <p style="color:rgba(255,255,255,0.7);font-size:1.05rem;max-width:600px;margin:0 auto">Luxury Flavor — Crafted for those who value sophistication, rich taste, and a refined experience.</p>
  </div>
</div>

<!-- Introduction -->
<section class="section">
  <div class="container" style="max-width:900px">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center">
      <div>
        <div style="color:var(--color-secondary);font-weight:700;font-size:0.82rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px">Who We Are</div>
        <h2 style="font-family:var(--font-heading);font-size:clamp(1.5rem,3vw,2rem);margin-bottom:20px">Phantom Smoking Luxury Flavor</h2>
        <p style="color:var(--color-text-muted);line-height:1.9;margin-bottom:16px">Phantom Smoking Luxury Flavor is a premium lifestyle brand designed for individuals who value sophistication, rich taste, and a refined smoking flavor experience. Our brand represents luxury, elegance, and innovation in every detail.</p>
        <p style="color:var(--color-text-muted);line-height:1.9">At Phantom, we focus on creating exceptional flavors that deliver a smooth, satisfying, and unforgettable experience for our customers. We believe that flavor is more than just taste — it is an expression of lifestyle, personality, and quality.</p>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <?php foreach ([
          ['fas fa-gem',        'Luxury',      'Premium ingredients and refined design in every product'],
          ['fas fa-flask',      'Innovation',  'Advanced flavor development and modern techniques'],
          ['fas fa-shield-alt', 'Quality',     'Strict quality standards and premium ingredients only'],
          ['fas fa-star',       'Excellence',  'Every product reflects our commitment to perfection'],
        ] as [$icon, $title, $desc]): ?>
        <div style="background:var(--color-bg-light);border-radius:var(--radius-lg);padding:20px;text-align:center">
          <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--color-secondary),#e8a84c);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.1rem;color:#fff">
            <i class="<?= $icon ?>"></i>
          </div>
          <div style="font-weight:700;font-size:0.9rem;margin-bottom:6px"><?= $title ?></div>
          <div style="font-size:0.78rem;color:var(--color-text-muted);line-height:1.5"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Vision & Mission -->
<section class="section" style="background:var(--color-bg-light)">
  <div class="container" style="max-width:900px">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px">

      <!-- Vision -->
      <div style="background:var(--color-white);border-radius:var(--radius-lg);padding:36px;border-top:4px solid var(--color-secondary)">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
          <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--color-primary),#2a2a4e);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-eye" style="color:var(--color-secondary);font-size:1.1rem"></i>
          </div>
          <h3 style="font-family:var(--font-heading);font-size:1.3rem;margin:0">Our Vision</h3>
        </div>
        <p style="color:var(--color-text-muted);line-height:1.9;margin-bottom:12px">To become a globally respected luxury flavor brand that sets new standards in quality, creativity, and customer experience. Phantom aims to lead the industry with innovative flavor concepts and premium product design.</p>
        <p style="color:var(--color-text-muted);line-height:1.9">We envision a future where Phantom is recognized as a symbol of luxury, trust, and modern lifestyle across international markets.</p>
      </div>

      <!-- Mission -->
      <div style="background:var(--color-white);border-radius:var(--radius-lg);padding:36px;border-top:4px solid var(--color-primary)">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
          <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--color-secondary),#e8a84c);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-bullseye" style="color:#fff;font-size:1.1rem"></i>
          </div>
          <h3 style="font-family:var(--font-heading);font-size:1.3rem;margin:0">Our Mission</h3>
        </div>
        <p style="color:var(--color-text-muted);line-height:1.9;margin-bottom:16px">To deliver high-quality luxury flavors that provide a smooth, rich, and enjoyable experience while maintaining the highest standards of quality and innovation.</p>
        <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:10px">
          <?php foreach ([
            'Developing unique and premium flavor profiles that stand out in the market',
            'Maintaining strict quality control and using high-standard ingredients',
            'Building a brand that symbolizes prestige, style, and reliability',
            'Creating a strong connection with customers through excellence',
          ] as $item): ?>
          <li style="display:flex;gap:10px;align-items:flex-start;font-size:0.88rem;color:var(--color-text-muted)">
            <i class="fas fa-check-circle" style="color:var(--color-secondary);margin-top:2px;flex-shrink:0"></i>
            <?= $item ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Why Choose Us -->
<section class="section">
  <div class="container" style="max-width:900px;text-align:center">
    <div style="color:var(--color-secondary);font-weight:700;font-size:0.82rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px">Why Phantom</div>
    <h2 style="font-family:var(--font-heading);font-size:clamp(1.4rem,3vw,1.9rem);margin-bottom:40px">The Phantom Difference</h2>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px">
      <?php foreach ([
        ['fas fa-certificate',  'Authentic Products',    '100% genuine products sourced from authorized distributors. No counterfeits, ever.'],
        ['fas fa-bolt',         '1-Hour Delivery',       'Express delivery within 1 hour in Dubai. Next-day delivery across the UAE.'],
        ['fas fa-star',         'Reward Points',         'Earn points on every purchase and redeem them for exclusive discounts.'],
        ['fas fa-user-shield',  '18+ Verified',          'Fully age-verified store. We are committed to responsible retail.'],
        ['fas fa-headset',      'Expert Support',        'Our knowledgeable team is available 7 days a week to assist you.'],
        ['fas fa-truck',        'Free Delivery',         'Free standard delivery on all orders over AED 100 across the UAE.'],
      ] as [$icon, $title, $desc]): ?>
      <div style="padding:28px 20px;border:1px solid var(--color-border);border-radius:var(--radius-lg);transition:all .25s ease" onmouseover="this.style.borderColor='var(--color-secondary)';this.style.transform='translateY(-4px)'" onmouseout="this.style.borderColor='var(--color-border)';this.style.transform='none'">
        <i class="<?= $icon ?>" style="font-size:1.8rem;color:var(--color-secondary);margin-bottom:14px;display:block"></i>
        <div style="font-weight:700;margin-bottom:8px"><?= $title ?></div>
        <div style="font-size:0.85rem;color:var(--color-text-muted);line-height:1.6"><?= $desc ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Health Warning -->
<div style="background:rgba(192,57,43,0.06);border-top:1px solid rgba(192,57,43,0.15);padding:20px 0">
  <div class="container" style="max-width:900px;text-align:center;font-size:0.85rem;color:var(--color-error)">
    ⚠️ <strong>Health Warning:</strong> Tobacco and nicotine products are harmful to health. Smoking causes cancer, heart disease, stroke, and lung diseases. For adults 18+ only. We are fully compliant with UAE Federal Law No. 15 of 2009 on Tobacco Control.
  </div>
</div>

<style>
@media (max-width:768px) {
  .about-grid-2 { grid-template-columns:1fr !important; }
}
</style>
