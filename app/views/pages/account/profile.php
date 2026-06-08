<div class="container section">
  <div class="account-layout">
    <?php include dirname(__DIR__) . '/../components/account-sidebar.php'; ?>
    <div class="account-content">
      <div class="account-page-title">My Profile</div>
      <?php if (flash_get('success')): ?><div class="alert alert-success"><?= e(flash_get('success')) ?></div><?php endif; ?>
      <form method="POST" action="/account/profile">
        <?= csrf_field() ?>
        <div class="form-row">
          <div class="form-group"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" value="<?= e($user['first_name']) ?>" required></div>
          <div class="form-group"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" value="<?= e($user['last_name']) ?>" required></div>
        </div>
        <div class="form-group"><label class="form-label">Email Address</label><input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled><div class="form-hint">Email cannot be changed</div></div>
        <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label">Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?= e($user['date_of_birth'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-check"><input type="checkbox" name="newsletter" value="1" <?= $user['newsletter_subscribed'] ? 'checked' : '' ?>> Subscribe to newsletter</label></div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>
  </div>
</div>
