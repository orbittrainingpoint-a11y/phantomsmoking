<?php $errors = flash_get('errors') ?? []; $old = flash_get('old') ?? []; ?>
<section class="section">
  <div class="container" style="max-width:520px">
    <div class="card">
      <div class="card-body" style="padding:40px">
        <h1 style="font-family:var(--font-heading);font-size:1.8rem;margin-bottom:8px">Create Account</h1>
        <p style="color:var(--color-text-muted);margin-bottom:28px">Join Phantom Smoking and earn reward points</p>
        <?php if (!empty($errors)): ?><div class="alert alert-error"><?= e(array_values($errors)[0]) ?></div><?php endif; ?>
        <form method="POST" action="/register">
          <?= csrf_field() ?>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'error' : '' ?>" value="<?= e($old['first_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'error' : '' ?>" value="<?= e($old['last_name'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>" value="<?= e($old['email'] ?? '') ?>" required>
            <?php if (isset($errors['email'])): ?><div class="form-error"><?= e($errors['email']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label">Phone (UAE)</label>
            <input type="tel" name="phone" class="form-control" value="<?= e($old['phone'] ?? '') ?>" placeholder="+971 50 000 0000">
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'error' : '' ?>" required minlength="8">
            <?php if (isset($errors['password'])): ?><div class="form-error"><?= e($errors['password']) ?></div><?php endif; ?>
            <div class="form-hint">Min 8 characters, 1 uppercase, 1 number</div>
          </div>
          <div class="form-group">
            <label class="form-check">
              <input type="checkbox" name="newsletter" value="1"> Subscribe to newsletter for exclusive deals
            </label>
          </div>
          <div class="form-group" style="font-size:0.82rem;color:var(--color-text-muted)">
            By creating an account, you confirm you are 18+ years old and agree to our <a href="/terms" style="color:var(--color-secondary)">Terms & Conditions</a>.
          </div>
          <button type="submit" class="btn btn-primary btn-full btn-lg">Create Account</button>
        </form>
        <p style="text-align:center;margin-top:20px;font-size:0.9rem;color:var(--color-text-muted)">
          Already have an account? <a href="/login" style="color:var(--color-secondary);font-weight:600">Sign in</a>
        </p>
      </div>
    </div>
  </div>
</section>
