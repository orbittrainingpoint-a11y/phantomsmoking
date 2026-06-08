<div class="container section">
  <div class="account-layout">
    <?php include dirname(__DIR__) . '/../components/account-sidebar.php'; ?>
    <div class="account-content">
      <div class="account-page-title">Change Password</div>
      <?php if (flash_get('success')): ?><div class="alert alert-success"><?= e(flash_get('success')) ?></div><?php endif; ?>
      <?php if (flash_get('error')): ?><div class="alert alert-error"><?= e(flash_get('error')) ?></div><?php endif; ?>
      <form method="POST" action="/account/change-password" style="max-width:440px">
        <?= csrf_field() ?>
        <div class="form-group"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
        <div class="form-group"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required minlength="8"><div class="form-hint">Min 8 characters, 1 uppercase, 1 number</div></div>
        <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary">Update Password</button>
      </form>
    </div>
  </div>
</div>
