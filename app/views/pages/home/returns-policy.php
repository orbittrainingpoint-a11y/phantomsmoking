<?php $_email = setting('contact_email', 'phantomsmokingonline@gmail.com'); ?>
<div class="page-hero">
  <div class="container">
    <h1 style="font-family:var(--font-heading);font-size:clamp(1.8rem,4vw,2.4rem);color:#fff;margin-bottom:10px">Returns <span style="color:var(--color-secondary)">Policy</span></h1>
    <p style="color:rgba(255,255,255,0.65)">Your satisfaction is our priority</p>
  </div>
</div>

<div class="container section" style="max-width:800px">
  <div style="line-height:1.9;color:var(--color-text-muted)">
    <div style="background:rgba(45,122,79,0.08);border:1px solid rgba(45,122,79,0.2);border-radius:var(--radius-lg);padding:20px;margin-bottom:32px;display:flex;gap:14px;align-items:flex-start">
      <i class="fas fa-check-circle" style="color:var(--color-success);font-size:1.4rem;flex-shrink:0;margin-top:2px"></i>
      <div><strong style="color:var(--color-success)">7-Day Returns Accepted</strong><br>We accept returns within 7 days of delivery for unopened, sealed products in their original condition and packaging.</div>
    </div>

    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:0 0 12px">Eligible for Return</h2>
    <ul style="padding-left:20px;line-height:2.2">
      <li>Unopened, sealed products in original packaging</li>
      <li>Items received damaged or defective</li>
      <li>Wrong item delivered</li>
    </ul>

    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:24px 0 12px">Non-Returnable Items</h2>
    <div style="background:rgba(192,57,43,0.06);border:1px solid rgba(192,57,43,0.15);border-radius:var(--radius-lg);padding:16px">
      <ul style="padding-left:20px;line-height:2.2;margin:0">
        <li>Opened or used e-liquids and vape devices</li>
        <li>Opened tobacco or nicotine products</li>
        <li>Items without original packaging</li>
        <li>Products purchased on clearance or final sale</li>
      </ul>
    </div>

    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:24px 0 12px">How to Initiate a Return</h2>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ([
        ['1', 'Contact us within 7 days of delivery at <a href="mailto:' . e($_email) . '" style="color:var(--color-secondary)">' . e($_email) . '</a> or call <a href="tel:+971568335210" style="color:var(--color-secondary)">+971 56 833 5210</a>'],
        ['2', 'Provide your order number, the item(s) you wish to return, and the reason for return'],
        ['3', 'Our team will arrange a collection from your address at no cost for defective or wrong items'],
        ['4', 'Once received and inspected, your refund will be processed within 5–7 business days'],
      ] as [$step, $text]): ?>
      <div style="display:flex;gap:14px;align-items:flex-start">
        <div style="width:32px;height:32px;background:var(--color-secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;color:#000;flex-shrink:0"><?= $step ?></div>
        <div style="padding-top:4px;font-size:0.9rem"><?= $text ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <h2 style="font-family:var(--font-heading);color:var(--color-primary);font-size:1.2rem;margin:24px 0 12px">Refunds</h2>
    <p>Refunds are processed within 5–7 business days to the original payment method. Cash on Delivery refunds will be issued via bank transfer or store credit.</p>
  </div>
</div>
