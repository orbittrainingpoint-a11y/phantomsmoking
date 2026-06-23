<?php
$flashSuccess = flash_get('success');
$flashError   = flash_get('error');
$_db = \App\Core\Database::getInstance();
$_mapUrl = $_db->fetch("SELECT setting_value FROM settings WHERE setting_key='google_maps_embed_url'")['setting_value'] ?? '';
$_mapAddr = $_db->fetch("SELECT setting_value FROM settings WHERE setting_key='store_map_address'")['setting_value'] ?? 'Dubai Marina, Dubai, UAE';
$_contactEmail = setting('contact_email', 'phantomsmokingonline@gmail.com');
?>

<!-- Hero -->
<div style="background:linear-gradient(135deg,var(--color-primary),#2a2a4e);padding:56px 0 40px;text-align:center">
  <div class="container">
    <h1 style="font-family:var(--font-heading);font-size:clamp(1.8rem,4vw,2.6rem);color:#fff;margin-bottom:10px">Get In <span style="color:var(--color-secondary)">Touch</span></h1>
    <p style="color:rgba(255,255,255,0.65);font-size:0.95rem">We're here to help — reach out via any channel below</p>
  </div>
</div>

<div class="container section">

  <!-- Google Map -->
  <?php if (!empty($_mapUrl)): ?>
  <div style="border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--color-border);margin-bottom:40px;box-shadow:var(--shadow-card)">
    <iframe src="<?= e($_mapUrl) ?>" width="100%" height="360" style="border:0;display:block" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    <div style="padding:14px 20px;background:var(--color-bg-light);display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <i class="fas fa-map-marker-alt" style="color:var(--color-secondary);font-size:1rem"></i>
      <span style="font-size:0.88rem;flex:1"><?= e($_mapAddr) ?></span>
      <a href="https://maps.google.com/?q=<?= urlencode($_mapAddr) ?>" target="_blank" class="btn btn-sm btn-primary">
        <i class="fas fa-directions"></i> Get Directions
      </a>
      <a href="/contact" style="font-size:0.82rem;color:var(--color-secondary)">View full contact info →</a>
    </div>
  </div>
  <?php endif; ?>

  <!-- Desktop: side by side | Mobile: stacked -->
  <div class="contact-layout">

    <!-- Left: Info -->
    <div class="contact-info-col">

      <!-- Contact Cards -->
      <div class="contact-cards">

        <div class="contact-card">
          <div class="contact-card-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div>
            <div class="contact-card-label">Address</div>
            <div class="contact-card-value">Dubai Marina - Marsa Dubai<br>Dubai Marina - Dubai<br>United Arab Emirates</div>
          </div>
        </div>

        <div class="contact-card">
          <div class="contact-card-icon"><i class="fas fa-phone"></i></div>
          <div>
            <div class="contact-card-label">Telephone</div>
            <div class="contact-card-value">
              <a href="tel:+971555426436">+971 55 542 6436</a><br>
              <a href="tel:+971503339627">+971 50 333 9627</a><br>
              <a href="tel:+971568335210">+971 56 833 5210</a>
            </div>
          </div>
        </div>

        <div class="contact-card">
          <div class="contact-card-icon" style="background:rgba(37,211,102,0.12)"><i class="fab fa-whatsapp" style="color:#25d366"></i></div>
          <div>
            <div class="contact-card-label">WhatsApp</div>
            <div class="contact-card-value">
              <a href="https://wa.me/971568335210" target="_blank">+971 56 833 5210</a><br>
              <span style="font-size:0.78rem;color:var(--color-text-muted)">Tap to chat instantly</span>
            </div>
          </div>
        </div>

        <div class="contact-card">
          <div class="contact-card-icon"><i class="fas fa-envelope"></i></div>
          <div>
            <div class="contact-card-label">Email</div>
            <div class="contact-card-value"><a href="mailto:<?= e($_contactEmail) ?>"><?= e($_contactEmail) ?></a></div>
          </div>
        </div>

        <div class="contact-card">
          <div class="contact-card-icon"><i class="fas fa-clock"></i></div>
          <div>
            <div class="contact-card-label">Working Hours</div>
            <div class="contact-card-value">Sat – Thu: 10AM – 11PM<br>Friday: 2PM – 11PM</div>
          </div>
        </div>

      </div>

      <!-- Quick Action Buttons -->
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:24px">
        <a href="tel:+971568335210" class="btn btn-primary" style="flex:1;min-width:140px;justify-content:center">
          <i class="fas fa-phone"></i> Call Now
        </a>
        <a href="https://wa.me/971568335210" target="_blank" class="btn" style="flex:1;min-width:140px;justify-content:center;background:#25d366;color:#fff;border:none">
          <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
      </div>

      <!-- Mobile: Open Form Button -->
      <button class="contact-form-trigger" onclick="openContactForm()">
        <i class="fas fa-paper-plane"></i> Send Us a Message
      </button>

    </div>

    <!-- Right: Form (desktop always visible, mobile = slide-up) -->
    <div class="contact-form-col" id="contactFormCol">
      <div class="contact-form-card">
        <div class="contact-form-card-header">
          <h2 style="font-family:var(--font-heading);font-size:1.3rem;margin:0">Send a Message</h2>
          <button class="contact-form-close" onclick="closeContactForm()"><i class="fas fa-times"></i></button>
        </div>

        <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($flashError) ?></div>
        <?php endif; ?>

        <form method="POST" action="/contact">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Your Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="John Doe">
          </div>
          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required placeholder="your@email.com">
          </div>
          <div class="form-group">
            <label class="form-label">Phone (optional)</label>
            <input type="tel" name="phone" class="form-control" placeholder="+971 50 000 0000">
          </div>
          <div class="form-group">
            <label class="form-label">Subject</label>
            <select name="subject" class="form-control">
              <option value="">Select a subject</option>
              <option>Product Inquiry</option>
              <option>Order Support</option>
              <option>Delivery Issue</option>
              <option>Returns & Refunds</option>
              <option>Wholesale / B2B</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Message *</label>
            <textarea name="message" class="form-control" rows="5" required placeholder="How can we help you?"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-full btn-lg">
            <i class="fas fa-paper-plane"></i> Send Message
          </button>
        </form>
      </div>
    </div>

  </div>
</div>

<!-- Mobile overlay -->
<div class="contact-overlay" id="contactOverlay" onclick="closeContactForm()"></div>

<style>
.contact-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: start;
  max-width: 960px;
  margin: 0 auto;
}
.contact-cards { display: flex; flex-direction: column; gap: 16px; }
.contact-card {
  display: flex;
  gap: 16px;
  align-items: flex-start;
  padding: 18px;
  background: var(--color-bg-light);
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  transition: border-color .2s;
}
.contact-card:hover { border-color: var(--color-secondary); }
.contact-card-icon {
  width: 44px; height: 44px;
  background: rgba(200,150,60,0.12);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  font-size: 1rem;
  color: var(--color-secondary);
}
.contact-card-label { font-weight: 700; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px; }
.contact-card-value { font-size: 0.9rem; line-height: 1.7; color: var(--color-text); }
.contact-card-value a { color: var(--color-text); transition: color .2s; }
.contact-card-value a:hover { color: var(--color-secondary); }

.contact-form-card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  padding: 32px;
  box-shadow: var(--shadow-card);
}
.contact-form-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}
.contact-form-close {
  display: none;
  width: 32px; height: 32px;
  border-radius: 50%;
  background: var(--color-bg-light);
  border: 1px solid var(--color-border);
  align-items: center; justify-content: center;
  cursor: pointer; font-size: 0.9rem;
  color: var(--color-text);
}
.contact-form-trigger { display: none; }
.contact-overlay { display: none; }

/* ── Mobile ── */
@media (max-width: 768px) {
  .contact-layout { grid-template-columns: 1fr; gap: 24px; }

  /* Hide form col by default on mobile */
  .contact-form-col {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 901;
    transform: translateY(100%);
    transition: transform .35s cubic-bezier(.32,.72,0,1);
    max-height: 90vh;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }
  .contact-form-col.open { transform: translateY(0); }

  .contact-form-card {
    border-radius: 20px 20px 0 0;
    border-bottom: none;
    padding: 24px 20px;
    padding-bottom: max(24px, env(safe-area-inset-bottom));
  }
  .contact-form-close { display: flex; }

  /* Show trigger button on mobile */
  .contact-form-trigger {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    margin-top: 20px;
    padding: 14px;
    background: var(--color-secondary);
    color: #000;
    font-weight: 700;
    font-size: 1rem;
    border: none;
    border-radius: var(--radius-lg);
    cursor: pointer;
  }

  /* Overlay */
  .contact-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 900;
    backdrop-filter: blur(2px);
  }
  .contact-overlay.open { display: block; }
}
</style>

<script>
function openContactForm() {
  document.getElementById('contactFormCol').classList.add('open');
  document.getElementById('contactOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeContactForm() {
  document.getElementById('contactFormCol').classList.remove('open');
  document.getElementById('contactOverlay').classList.remove('open');
  document.body.style.overflow = '';
}
// Auto-open if there's a flash message (form was submitted)
<?php if ($flashSuccess || $flashError): ?>
if (window.innerWidth <= 768) openContactForm();
<?php endif; ?>
</script>
