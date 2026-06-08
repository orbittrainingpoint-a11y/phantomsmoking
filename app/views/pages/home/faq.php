<?php $faqs = [
  ['Do you deliver across all UAE?', 'Yes! We deliver to Dubai, Abu Dhabi, Sharjah, Ajman, Ras Al Khaimah, Fujairah and Umm Al Quwain. Express 1-hour delivery is available in Dubai only.'],
  ['What is the minimum age to order?', 'You must be 18 years or older to purchase from Phantom Smoking. We comply with UAE Federal Law No. 15 of 2009 on Tobacco Control.'],
  ['How do reward points work?', 'You earn 1 point for every AED 1 spent. 100 points = AED 10 discount. Minimum 500 points to redeem. Points expire after 12 months of inactivity.'],
  ['What payment methods do you accept?', 'We accept Cash on Delivery (COD), Credit/Debit Cards (Visa, Mastercard, Amex) via Stripe, and regional payment options including Tabby and Tamara (Buy Now Pay Later).'],
  ['Can I return a product?', 'We accept returns within 7 days for unopened, sealed products in original condition. Contact us at info@phantomsmoking.com or call +971 56 217 7081.'],
  ['How do I track my order?', 'You will receive an SMS/email with your order number. Use the Track Order page or your account dashboard to check your order status in real time.'],
  ['Are your products authentic?', '100% yes. All Phantom Smoking products are sourced directly from authorized distributors. We never sell counterfeit or imitation goods.'],
  ['Is there a free delivery threshold?', 'Standard delivery is free on orders over AED 100. Express 1-hour delivery in Dubai is AED 25 flat.'],
  ['How do I contact customer support?', 'You can reach us by phone at +971 55 542 6436, +971 50 333 9627, or +971 56 217 7081. You can also email us at info@phantomsmoking.com or WhatsApp us at +971 56 217 7081.'],
  ['Where are you located?', 'We are based in Dubai Marina - Marsa Dubai, Dubai Marina, Dubai, United Arab Emirates.'],
]; ?>

<div class="page-hero">
  <div class="container">
    <h1 style="font-family:var(--font-heading);font-size:clamp(1.8rem,4vw,2.4rem);color:#fff;margin-bottom:10px">Frequently Asked <span style="color:var(--color-secondary)">Questions</span></h1>
    <p style="color:rgba(255,255,255,0.65)">Everything you need to know about Phantom Smoking</p>
  </div>
</div>

<div class="container section" style="max-width:760px">
  <?php foreach ($faqs as $i => [$q, $a]): ?>
  <div class="faq-item" id="faq-<?= $i ?>">
    <div class="faq-question" onclick="toggleFaq(<?= $i ?>)">
      <span><?= e($q) ?></span>
      <i class="fas fa-chevron-down faq-icon" id="faq-icon-<?= $i ?>"></i>
    </div>
    <div class="faq-answer" id="faq-answer-<?= $i ?>"><?= e($a) ?></div>
  </div>
  <?php endforeach; ?>

  <div style="margin-top:40px;background:var(--color-bg-light);border-radius:var(--radius-lg);padding:28px;text-align:center;border:1px solid var(--color-border)">
    <div style="font-family:var(--font-heading);font-size:1.1rem;margin-bottom:8px">Still have questions?</div>
    <p style="color:var(--color-text-muted);font-size:0.9rem;margin-bottom:16px">Our team is available 7 days a week to help you.</p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
      <a href="tel:+971562177081" class="btn btn-primary"><i class="fas fa-phone"></i> Call Us</a>
      <a href="https://wa.me/971562177081" class="btn" style="background:#25d366;color:#fff;border:none" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
      <a href="/contact" class="btn btn-outline"><i class="fas fa-envelope"></i> Contact Form</a>
    </div>
  </div>
</div>

<style>
.faq-item { border:1px solid var(--color-border); border-radius:var(--radius-lg); margin-bottom:10px; overflow:hidden; transition:border-color .2s; }
.faq-item:hover { border-color:var(--color-secondary); }
.faq-question { padding:18px 20px; font-weight:700; cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:12px; font-size:0.95rem; }
.faq-icon { color:var(--color-secondary); flex-shrink:0; transition:transform .25s; }
.faq-icon.open { transform:rotate(180deg); }
.faq-answer { padding:0 20px; max-height:0; overflow:hidden; transition:max-height .3s ease, padding .3s ease; color:var(--color-text-muted); line-height:1.8; font-size:0.9rem; }
.faq-answer.open { max-height:200px; padding:0 20px 18px; }
</style>
<script>
function toggleFaq(i) {
  const answer = document.getElementById('faq-answer-' + i);
  const icon   = document.getElementById('faq-icon-' + i);
  answer.classList.toggle('open');
  icon.classList.toggle('open');
}
</script>
