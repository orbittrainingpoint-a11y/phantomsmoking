<?php $flashError = flash_get('error'); $flashSuccess = flash_get('success'); ?>
<section class="section">
  <div class="container" style="max-width:440px">
    <div class="card">
      <div class="card-body" style="padding:40px">

        <div style="text-align:center;margin-bottom:24px">
          <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--color-primary),#2a2a4e);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;border:2px solid var(--color-secondary)">
            <i class="fas fa-shield-alt" style="font-size:1.6rem;color:var(--color-secondary)"></i>
          </div>
          <h1 style="font-family:var(--font-heading);font-size:1.6rem;margin-bottom:8px">Verify Your Identity</h1>
          <p style="color:var(--color-text-muted);font-size:0.9rem">
            We sent a 6-digit code to<br>
            <strong style="color:var(--color-text)"><?= e($masked_email ?? '') ?></strong>
          </p>
        </div>

        <?php if ($flashError): ?>
          <div class="alert alert-error"><?= e($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
          <div class="alert alert-success"><?= e($flashSuccess) ?></div>
        <?php endif; ?>

        <form method="POST" action="/otp/verify" id="otpForm">
          <?= csrf_field() ?>
          <input type="hidden" name="purpose" value="<?= e($purpose ?? 'login') ?>">

          <div class="form-group">
            <label class="form-label" style="text-align:center;display:block;margin-bottom:16px">Enter 6-digit OTP</label>
            <div id="otpInputs" style="display:flex;gap:10px;justify-content:center;margin-bottom:8px">
              <?php for ($i = 0; $i < 6; $i++): ?>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                class="otp-digit"
                style="width:48px;height:56px;text-align:center;font-size:1.5rem;font-weight:700;border:2px solid var(--color-border);border-radius:8px;background:var(--color-bg-light);color:var(--color-text);outline:none;transition:border-color .2s"
                onfocus="this.style.borderColor='var(--color-secondary)'"
                onblur="this.style.borderColor='var(--color-border)'"
                oninput="otpNext(this,<?= $i ?>)"
                onkeydown="otpBack(event,this,<?= $i ?>)">
              <?php endfor; ?>
            </div>
            <input type="hidden" name="otp_code" id="otpHidden">
          </div>

          <button type="submit" class="btn btn-primary btn-full btn-lg" id="otpSubmitBtn" disabled>
            <i class="fas fa-check-circle"></i> Verify & Continue
          </button>
        </form>

        <div style="text-align:center;margin-top:20px">
          <p style="color:var(--color-text-muted);font-size:0.85rem;margin-bottom:8px">
            Didn't receive the code?
          </p>
          <form method="POST" action="/otp/resend" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="purpose" value="<?= e($purpose ?? 'login') ?>">
            <button type="submit" class="btn btn-outline btn-sm" id="resendBtn">
              <i class="fas fa-redo"></i> Resend Code
            </button>
          </form>
          <div id="resendTimer" style="color:var(--color-text-muted);font-size:0.82rem;margin-top:8px;display:none">
            Resend available in <span id="timerCount">60</span>s
          </div>
        </div>

        <p style="text-align:center;margin-top:16px;font-size:0.82rem;color:var(--color-text-muted)">
          <a href="/login" style="color:var(--color-secondary)"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </p>

      </div>
    </div>
  </div>
</section>

<script>
const digits = document.querySelectorAll('.otp-digit');
const hidden  = document.getElementById('otpHidden');
const submitBtn = document.getElementById('otpSubmitBtn');

function otpNext(el, idx) {
  el.value = el.value.replace(/[^0-9]/g, '');
  updateHidden();
  if (el.value && idx < 5) digits[idx + 1].focus();
}
function otpBack(e, el, idx) {
  if (e.key === 'Backspace' && !el.value && idx > 0) digits[idx - 1].focus();
}
function updateHidden() {
  let val = '';
  digits.forEach(d => val += d.value);
  hidden.value = val;
  submitBtn.disabled = val.length < 6;
  if (val.length === 6) submitBtn.style.opacity = '1';
}

// Paste support
digits[0].addEventListener('paste', function(e) {
  e.preventDefault();
  const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
  pasted.split('').forEach((ch, i) => { if (digits[i]) digits[i].value = ch; });
  updateHidden();
  const next = Math.min(pasted.length, 5);
  digits[next].focus();
});

// Resend timer
(function() {
  const btn   = document.getElementById('resendBtn');
  const timer = document.getElementById('resendTimer');
  const count = document.getElementById('timerCount');
  let secs = 60;
  btn.style.display = 'none';
  timer.style.display = 'block';
  const iv = setInterval(() => {
    secs--;
    count.textContent = secs;
    if (secs <= 0) {
      clearInterval(iv);
      timer.style.display = 'none';
      btn.style.display = 'inline-flex';
    }
  }, 1000);
})();
</script>
