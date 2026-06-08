<?php
$flashErrors = flash_get('errors') ?? [];
$flashError  = flash_get('error');
$user        = is_logged_in() ? current_user() : null;
?>
<div class="container section">
  <h1 style="font-family:var(--font-heading);font-size:1.8rem;margin-bottom:8px">Checkout</h1>

  <!-- Progress -->
  <div class="checkout-progress" style="margin-bottom:32px">
    <div class="checkout-step"><div class="step-circle active">1</div><div class="step-label active">Details</div></div>
    <div class="step-connector done"></div>
    <div class="checkout-step"><div class="step-circle active">2</div><div class="step-label active">Delivery</div></div>
    <div class="step-connector done"></div>
    <div class="checkout-step"><div class="step-circle active">3</div><div class="step-label active">Payment</div></div>
  </div>

  <?php if ($flashError): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($flashError) ?></div><?php endif; ?>
  <?php if (!empty($flashErrors)): ?><div class="alert alert-error"><?= e(implode(', ', $flashErrors)) ?></div><?php endif; ?>
  <?php if (!empty($_GET['cancelled'])): ?><div class="alert alert-error"><i class="fas fa-times-circle"></i> Payment was cancelled. Please try again.</div><?php endif; ?>

  <form method="POST" action="/checkout/place-order" id="checkoutForm">
    <?= csrf_field() ?>
    <div class="checkout-layout">
      <div>

        <!-- Contact -->
        <div class="checkout-form-section">
          <div class="checkout-section-title">1. Contact Information</div>
          <?php if (!$user): ?>
          <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" required placeholder="your@email.com"></div>
          <?php else: ?>
          <div style="background:var(--color-bg-light);padding:12px;border-radius:var(--radius);font-size:0.9rem;margin-bottom:16px">
            Logged in as <strong><?= e($user['email']) ?></strong>
          </div>
          <?php endif; ?>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input type="text" name="shipping_name" class="form-control" required
                value="<?= $user ? e($user['first_name'] . ' ' . $user['last_name']) : '' ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Phone *</label>
              <input type="tel" name="shipping_phone" class="form-control" required
                placeholder="+971 56 217 7081"
                value="<?= $user ? e($user['phone'] ?? '') : '' ?>">
            </div>
          </div>
        </div>

        <!-- Delivery Address -->
        <div class="checkout-form-section">
          <div class="checkout-section-title">2. Delivery Address</div>

          <?php if (!empty($addresses)): ?>
          <div style="margin-bottom:16px">
            <?php foreach ($addresses as $addr): ?>
            <label class="address-card <?= $addr['is_default'] ? 'selected' : '' ?>" style="cursor:pointer;display:block;padding:12px;border:2px solid <?= $addr['is_default'] ? 'var(--color-secondary)' : 'var(--color-border)' ?>;border-radius:var(--radius);margin-bottom:8px">
              <input type="radio" name="address_select" value="<?= $addr['id'] ?>" style="display:none"
                <?= $addr['is_default'] ? 'checked' : '' ?>
                onchange="fillAddress(<?= htmlspecialchars(json_encode($addr)) ?>)">
              <div style="display:flex;align-items:center;gap:8px">
                <i class="fas fa-map-marker-alt" style="color:var(--color-secondary)"></i>
                <strong><?= e($addr['label']) ?></strong>
                <?= $addr['is_default'] ? '<span class="badge badge-gold" style="font-size:0.7rem">Default</span>' : '' ?>
              </div>
              <div style="font-size:0.85rem;color:var(--color-text-muted);margin-top:4px;padding-left:20px">
                <?= e($addr['full_name']) ?> · <?= e($addr['phone']) ?><br>
                <?= e($addr['address_line1']) ?>, <?= e($addr['area']) ?>, <?= e($addr['emirate']) ?>
              </div>
            </label>
            <?php endforeach; ?>
            <button type="button" onclick="toggleNewAddress()" class="btn btn-outline btn-sm" style="margin-top:4px">
              <i class="fas fa-plus"></i> Add New Address
            </button>
          </div>
          <div id="newAddressFields" style="display:<?= empty($addresses) ? 'block' : 'none' ?>">
          <?php endif; ?>

          <div class="form-group">
            <label class="form-label">Address Line 1 *</label>
            <input type="text" name="shipping_address_line1" id="addr1" class="form-control" required placeholder="Building, Street, Area">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Area</label>
              <input type="text" name="shipping_area" id="addrArea" class="form-control" placeholder="Dubai Marina, JBR...">
            </div>
            <div class="form-group">
              <label class="form-label">City</label>
              <input type="text" name="shipping_city" id="addrCity" class="form-control" value="Dubai">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Emirate *</label>
            <select name="shipping_emirate" id="addrEmirate" class="form-control" required onchange="updateDeliveryFee()">
              <?php foreach (['Dubai','Abu Dhabi','Sharjah','Ajman','Ras Al Khaimah','Fujairah','Umm Al Quwain'] as $em): ?>
              <option value="<?= $em ?>"><?= $em ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if (!empty($addresses)): ?></div><?php endif; ?>

          <!-- Delivery Method -->
          <div style="margin-top:20px">
            <div style="font-weight:700;font-size:0.88rem;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px">Delivery Method</div>
            <label class="delivery-option selected" onclick="selectDelivery(this,'standard')">
              <input type="radio" name="delivery_type" value="standard" checked style="display:none">
              <div class="delivery-option-icon"><i class="fas fa-truck"></i></div>
              <div><strong>Standard Delivery</strong><div style="font-size:0.82rem;color:var(--color-text-muted)" id="standardEta">1-2 business days</div></div>
              <div style="margin-left:auto;font-family:var(--font-mono);font-weight:700" id="standardFeeLabel">Calculating...</div>
            </label>
            <label class="delivery-option" onclick="selectDelivery(this,'express_1hr')">
              <input type="radio" name="delivery_type" value="express_1hr" style="display:none">
              <div class="delivery-option-icon"><i class="fas fa-bolt"></i></div>
              <div><strong>Express 1-Hour</strong><div style="font-size:0.82rem;color:var(--color-text-muted)">Dubai only · Orders before 10PM</div></div>
              <div style="margin-left:auto;font-family:var(--font-mono);font-weight:700" id="expressFeeLabel">Calculating...</div>
            </label>
          </div>

          <div class="form-group" style="margin-top:16px">
            <label class="form-label">Order Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Special instructions..."></textarea>
          </div>
        </div>

        <!-- Payment -->
        <div class="checkout-form-section">
          <div class="checkout-section-title">3. Payment Method</div>

          <?php if (empty($payments)): ?>
          <div class="alert alert-error">No payment methods are currently active. Please contact the store.</div>
          <?php else: ?>
          <?php foreach ($payments as $i => $pm): ?>
          <label class="payment-option <?= $i === 0 ? 'selected' : '' ?>" onclick="selectPayment(this,'<?= $pm['id'] ?>')">
            <input type="radio" name="payment_method" value="<?= $pm['id'] ?>" <?= $i === 0 ? 'checked' : '' ?> style="display:none">
            <i class="fas <?= $pm['icon'] ?>" style="font-size:1.3rem;color:var(--color-secondary);flex-shrink:0"></i>
            <div>
              <strong><?= e($pm['label']) ?></strong>
              <div style="font-size:0.82rem;color:var(--color-text-muted)"><?= e($pm['desc']) ?></div>
            </div>
            <?php if (in_array($pm['id'], ['tabby','tamara'])): ?>
            <span style="margin-left:auto;background:var(--color-secondary);color:#000;font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:20px">BNPL</span>
            <?php endif; ?>
          </label>
          <?php endforeach; ?>
          <?php endif; ?>

          <!-- Reward Points -->
          <?php if ($user && ($user['reward_points'] ?? 0) >= 500): ?>
          <div style="background:rgba(200,150,60,0.08);border:1px solid rgba(200,150,60,0.2);border-radius:var(--radius);padding:14px;margin-top:16px">
            <label class="form-check">
              <input type="checkbox" name="use_reward_points" value="1">
              <span>Use my <strong><?= number_format($user['reward_points']) ?> reward points</strong> (up to 30% discount)</span>
            </label>
          </div>
          <?php endif; ?>

          <div class="form-group" style="margin-top:16px">
            <label class="form-check">
              <input type="checkbox" id="termsCheck" required>
              <span style="font-size:0.85rem">I agree to the <a href="/terms" target="_blank" style="color:var(--color-secondary)">Terms & Conditions</a> and confirm I am 18+ years old</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="order-summary-card">
        <div class="order-summary-title">Order Summary</div>
        <?php foreach ($cart['items'] as $item): ?>
        <div style="display:flex;gap:10px;margin-bottom:12px;font-size:0.85rem;align-items:center">
          <img src="<?= e($item['product_image'] ?: '/assets/images/placeholder.jpg') ?>"
            style="width:48px;height:48px;object-fit:cover;border-radius:var(--radius);flex-shrink:0">
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($item['name']) ?></div>
            <?php $varLabel = $item['variant_name'] ?: ($item['selected_flavours'] ?? ''); ?>
            <?php if ($varLabel): ?><div style="color:var(--color-text-muted);font-size:0.78rem"><?= e($varLabel) ?></div><?php endif; ?>
            <div style="color:var(--color-text-muted)">x<?= $item['quantity'] ?></div>
          </div>
          <div style="font-family:var(--font-mono);font-weight:600;flex-shrink:0"><?= format_price($item['unit_price'] * $item['quantity']) ?></div>
        </div>
        <?php endforeach; ?>
        <hr class="divider">
        <div class="summary-row"><span>Subtotal</span><span><?= format_price($cart['subtotal']) ?></span></div>
        <?php if ($cart['discount'] > 0): ?>
        <div class="summary-row discount"><span>Discount</span><span>-<?= format_price($cart['discount']) ?></span></div>
        <?php endif; ?>
        <div class="summary-row"><span>Shipping</span><span id="checkoutShipping">Select emirate above</span></div>
        <div class="summary-row"><span>VAT (5%)</span><span><?= format_price($cart['tax']) ?></span></div>
        <div class="summary-row total"><span>Total</span><span id="checkoutTotal"><?= format_price($cart['total']) ?></span></div>
        <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:16px" id="placeOrderBtn">
          <i class="fas fa-lock"></i> Place Order
        </button>
        <p style="text-align:center;font-size:0.75rem;color:var(--color-text-muted);margin-top:10px">
          <i class="fas fa-shield-alt"></i> Secure checkout — SSL encrypted
        </p>
      </div>
    </div>
  </form>
</div>

<script>
const cartSubtotal = <?= $cart['subtotal'] ?>;

async function updateShipping() {
  const emirate = document.getElementById('addrEmirate')?.value || 'Dubai';
  const type    = document.querySelector('[name=delivery_type]:checked')?.value || 'standard';
  try {
    const res  = await fetch(`/api/delivery/estimate?emirate=${encodeURIComponent(emirate)}&type=${type}&subtotal=${cartSubtotal}`);
    const data = await res.json();

    // Update delivery option labels
    if (type === 'standard') {
      document.getElementById('standardFeeLabel').innerHTML = data.formatted;
      if (data.eta) document.getElementById('standardEta').textContent = data.eta;
    }

    // Always fetch both fees for display
    fetchBothFees(emirate);

    // Update order summary
    const shippingEl = document.getElementById('checkoutShipping');
    if (shippingEl) shippingEl.innerHTML = data.formatted;

    // Update total
    updateTotal(data.fee);
  } catch(e) {}
}

async function fetchBothFees(emirate) {
  try {
    const [std, exp] = await Promise.all([
      fetch(`/api/delivery/estimate?emirate=${encodeURIComponent(emirate)}&type=standard&subtotal=${cartSubtotal}`).then(r=>r.json()),
      fetch(`/api/delivery/estimate?emirate=${encodeURIComponent(emirate)}&type=express_1hr&subtotal=${cartSubtotal}`).then(r=>r.json()),
    ]);
    const stdEl = document.getElementById('standardFeeLabel');
    const expEl = document.getElementById('expressFeeLabel');
    const stdEta = document.getElementById('standardEta');
    if (stdEl) stdEl.innerHTML = std.formatted;
    if (expEl) expEl.innerHTML = exp.formatted;
    if (stdEta && std.eta) stdEta.textContent = std.eta;

    // Update summary based on selected type
    const selectedType = document.querySelector('[name=delivery_type]:checked')?.value || 'standard';
    const selected = selectedType === 'express_1hr' ? exp : std;
    const shippingEl = document.getElementById('checkoutShipping');
    if (shippingEl) shippingEl.innerHTML = selected.formatted;
    updateTotal(selected.fee);
  } catch(e) {}
}

function updateTotal(shippingFee) {
  const tax      = <?= $cart['tax'] ?>;
  const discount = <?= $cart['discount'] ?>;
  const total    = cartSubtotal - discount + shippingFee + tax;
  const el = document.getElementById('checkoutTotal');
  if (el) el.textContent = 'AED ' + Math.max(0, total).toFixed(2);
}

function selectDelivery(el, type) {
  document.querySelectorAll('.delivery-option').forEach(o => {
    o.classList.remove('selected');
    o.style.borderColor = 'var(--color-border)';
  });
  el.classList.add('selected');
  el.style.borderColor = 'var(--color-secondary)';
  el.querySelector('input').checked = true;
  updateShipping();
}

function selectPayment(el, type) {
  document.querySelectorAll('.payment-option').forEach(o => {
    o.classList.remove('selected');
    o.style.borderColor = 'var(--color-border)';
  });
  el.classList.add('selected');
  el.style.borderColor = 'var(--color-secondary)';
  el.querySelector('input').checked = true;
}

function fillAddress(addr) {
  document.getElementById('addr1').value       = addr.address_line1 || '';
  document.getElementById('addrArea').value    = addr.area || '';
  document.getElementById('addrCity').value    = addr.city || 'Dubai';
  document.getElementById('addrEmirate').value = addr.emirate || 'Dubai';
  document.querySelector('[name=shipping_phone]').value = addr.phone || '';
  document.querySelectorAll('.address-card').forEach(c => c.style.borderColor = 'var(--color-border)');
  event.currentTarget.closest('.address-card').style.borderColor = 'var(--color-secondary)';
  updateShipping();
}

function toggleNewAddress() {
  const f = document.getElementById('newAddressFields');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

// Prevent double submit
document.getElementById('checkoutForm').addEventListener('submit', function() {
  const btn = document.getElementById('placeOrderBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
});

// Init on load
document.addEventListener('DOMContentLoaded', () => {
  const emirateEl = document.getElementById('addrEmirate');
  if (emirateEl) emirateEl.addEventListener('change', updateShipping);
  updateShipping();
});
</script>
