<?php $errors = flash_get('errors') ?? []; $old = flash_get('old') ?? []; ?>
<section class="section">
  <div class="container" style="max-width:480px">
    <div class="card">
      <div class="card-body" style="padding:40px">
        <h1 style="font-family:var(--font-heading);font-size:1.8rem;margin-bottom:8px">Welcome Back</h1>
        <p style="color:var(--color-text-muted);margin-bottom:28px">Sign in to your Phantom Smoking account</p>

        <?php $flashError = flash_get('error'); $flashSuccess = flash_get('success'); ?>
        <?php if ($flashError): ?><div class="alert alert-error"><?= e($flashError) ?></div><?php endif; ?>
        <?php if ($flashSuccess): ?><div class="alert alert-success"><?= e($flashSuccess) ?></div><?php endif; ?>

        <form method="POST" action="/login">
          <?= csrf_field() ?>
          <input type="hidden" name="redirect" value="<?= e($redirect ?? '/account') ?>">
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="your@email.com" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label" style="display:flex;justify-content:space-between">
              Password <a href="/forgot-password" style="font-weight:400;color:var(--color-secondary);font-size:0.85rem">Forgot password?</a>
            </label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
          <button type="submit" class="btn btn-primary btn-full btn-lg">Sign In</button>
        </form>
        <p style="text-align:center;margin-top:20px;font-size:0.9rem;color:var(--color-text-muted)">
          Don't have an account? <a href="/register" style="color:var(--color-secondary);font-weight:600">Create one</a>
        </p>
      </div>
    </div>
  </div>
</section>
