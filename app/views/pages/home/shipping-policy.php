<div class="page-hero">
  <div class="container">
    <h1 style="font-family:var(--font-heading);font-size:clamp(1.8rem,4vw,2.4rem);color:#fff;margin-bottom:10px">Shipping <span style="color:var(--color-secondary)">Policy</span></h1>
    <p style="color:rgba(255,255,255,0.65)">Fast, reliable delivery across the UAE</p>
  </div>
</div>

<div class="container section" style="max-width:800px">

  <!-- Delivery Options -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:40px">
    <?php foreach ([
      ['fas fa-bolt',         'var(--color-secondary)', 'Express 1-Hour',    'AED 25 flat',          'Dubai only · Orders before 10PM'],
      ['fas fa-truck',        '#3B82F6',                'Standard Delivery', 'Free over AED 100',    'All UAE · 1-2 business days'],
      ['fas fa-calendar-day', 'var(--color-success)',   'Next Day Delivery', 'AED 20 flat',          'All UAE · Order before 5PM'],
    ] as [$icon, $color, $title, $price, $note]): ?>
    <div style="border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:24px;text-align:center">
      <i class="<?= $icon ?>" style="font-size:2rem;color:<?= $color ?>;margin-bottom:12px;display:block"></i>
      <div style="font-weight:700;font-size:1rem;margin-bottom:6px"><?= $title ?></div>
      <div style="color:var(--color-secondary);font-weight:700;font-family:var(--font-mono);margin-bottom:6px"><?= $price ?></div>
      <div style="font-size:0.82rem;color:var(--color-text-muted)"><?= $note ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="line-height:1.9;color:var(--color-text-muted)">
    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:0 0 12px">Order Processing</h2>
    <p>Orders placed on Phantom Smoking are processed within 1–2 hours during business hours (10AM–11PM, Saturday to Thursday; 2PM–11PM on Fridays). You will receive an SMS and email confirmation with your order details once your order is confirmed.</p>

    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:24px 0 12px">Delivery Areas</h2>
    <p>We deliver to all seven emirates of the UAE: Dubai, Abu Dhabi, Sharjah, Ajman, Ras Al Khaimah, Fujairah, and Umm Al Quwain. Express 1-hour delivery is currently available in selected areas of Dubai only.</p>

    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:24px 0 12px">Free Shipping</h2>
    <p>Standard delivery is completely free on all orders over AED 100. Orders below AED 100 are subject to a standard delivery fee based on your emirate, as shown at checkout.</p>

    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:24px 0 12px">Age Verification on Delivery</h2>
    <p>In compliance with UAE Federal Law No. 15 of 2009 on Tobacco Control, our delivery team may request proof of age (Emirates ID or passport) upon delivery. Orders will not be handed over to individuals who appear to be under 18 years of age.</p>

    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:24px 0 12px">Contact Us</h2>
    <p>For any shipping queries, contact us at <a href="mailto:info@phantomsmoking.com" style="color:var(--color-secondary)">info@phantomsmoking.com</a> or call <a href="tel:+971562177081" style="color:var(--color-secondary)">+971 56 217 7081</a>.</p>
  </div>
</div>
