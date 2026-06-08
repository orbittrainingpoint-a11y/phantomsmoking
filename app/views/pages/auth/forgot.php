<section class="section">
  <div class="container" style="max-width:440px">
    <div class="card"><div class="card-body" style="padding:40px">
      <h1 style="font-family:var(--font-heading);font-size:1.6rem;margin-bottom:8px">Forgot Password?</h1>
      <p style="color:var(--color-text-muted);margin-bottom:24px">Enter your email and we'll send a reset link.</p>
      <?php if (flash_get('success')): ?><div class="alert alert-success"><?= e(flash_get('success')) ?></div><?php endif; ?>
      <form method="POST" action="/forgot-password">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Send Reset Link</button>
      </form>
      <p style="text-align:center;margin-top:16px;font-size:0.9rem"><a href="/login" style="color:var(--color-secondary)">← Back to Login</a></p>
    </div></div>
  </div>
</section>
