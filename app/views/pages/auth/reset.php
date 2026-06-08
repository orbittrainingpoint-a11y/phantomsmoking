<section class="section">
  <div class="container" style="max-width:440px">
    <div class="card"><div class="card-body" style="padding:40px">
      <h1 style="font-family:var(--font-heading);font-size:1.6rem;margin-bottom:8px">Reset Password</h1>
      <p style="color:var(--color-text-muted);margin-bottom:24px">Enter your new password below.</p>
      <?php if (flash_get('error')): ?><div class="alert alert-error"><?= e(flash_get('error')) ?></div><?php endif; ?>
      <form method="POST" action="/reset-password/<?= e($token) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" name="password" class="form-control" required minlength="8">
          <div class="form-hint">Min 8 characters, 1 uppercase, 1 number</div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="password_confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Reset Password</button>
      </form>
    </div></div>
  </div>
</section>
