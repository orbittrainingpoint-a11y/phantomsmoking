<?php $redirect = $_GET['redirect'] ?? '/'; ?>
<div class="age-gate">
  <div class="age-gate-bg"></div>
  <div class="age-gate-card">
    <div class="age-gate-logo">
      <img src="/assets/images/logo.webp" alt="Phantom Smoking" style="height:70px;width:auto;object-fit:contain;filter:brightness(0) invert(1)">
    </div>
    <div class="age-gate-badge"><i class="fas fa-shield-alt"></i> Age Restricted — 18+ Only</div>
    <h1 class="age-gate-title">Are You 18 or Older?</h1>
    <p class="age-gate-text">This website sells tobacco, nicotine and related products. You must be 18 years or older to enter. By entering, you confirm you are of legal age.</p>

    <?php if (flash_get('error')): ?>
    <div class="alert alert-error" style="margin-bottom:16px"><?= e(flash_get('error')) ?></div>
    <?php endif; ?>

    <form method="POST" action="/age-verify">
      <?= csrf_field() ?>
      <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

      <div class="age-gate-dob" id="dobField" style="display:none">
        <label>Enter your date of birth</label>
        <input type="date" name="dob" class="form-control" max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
      </div>

      <div class="age-gate-actions">
        <button type="submit" name="confirm" value="yes" class="age-gate-enter">
          <i class="fas fa-check-circle"></i> I Am 18 or Older — Enter Site
        </button>
        <button type="submit" name="confirm" value="no" class="age-gate-exit">
          I Am Under 18 — Exit Site
        </button>
      </div>
    </form>

    <p class="age-gate-warning">⚠️ Tobacco products are harmful to health. Smoking causes cancer, heart disease and other serious illnesses. This site complies with UAE Federal Law No. 15 of 2009 on Tobacco Control.</p>
  </div>
</div>
