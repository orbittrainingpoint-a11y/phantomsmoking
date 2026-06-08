<div class="container section">
  <div class="account-layout">
    <?php include dirname(__DIR__) . '/../components/account-sidebar.php'; ?>
    <div class="account-content">
      <div class="account-page-title">My Addresses</div>
      <?php if (flash_get('success')): ?><div class="alert alert-success"><?= e(flash_get('success')) ?></div><?php endif; ?>
      <div class="grid grid-2" style="margin-bottom:28px">
        <?php foreach ($addresses as $addr): ?>
        <div class="address-card-item <?= $addr['is_default'] ? 'default' : '' ?>">
          <div class="address-label"><?= e($addr['label']) ?> <?= $addr['is_default'] ? '<span class="badge badge-gold" style="font-size:0.7rem">Default</span>' : '' ?></div>
          <strong><?= e($addr['full_name']) ?></strong><br>
          <span style="font-size:0.88rem;color:var(--color-text-muted)"><?= e($addr['phone']) ?></span><br>
          <span style="font-size:0.88rem"><?= e($addr['address_line1']) ?><?= $addr['address_line2'] ? ', ' . e($addr['address_line2']) : '' ?></span><br>
          <span style="font-size:0.88rem"><?= e($addr['area']) ?>, <?= e($addr['emirate']) ?>, <?= e($addr['country']) ?></span>
          <div style="display:flex;gap:8px;margin-top:12px">
            <form method="POST" action="/account/addresses/<?= $addr['id'] ?>/delete" onsubmit="return confirm('Delete this address?')">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <h3 style="font-family:var(--font-heading);margin-bottom:16px">Add New Address</h3>
      <form method="POST" action="/account/addresses" style="max-width:560px">
        <?= csrf_field() ?>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Label</label><select name="label" class="form-control"><option>Home</option><option>Work</option><option>Other</option></select></div>
          <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
        </div>
        <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Address Line 1</label><input type="text" name="address_line1" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Address Line 2</label><input type="text" name="address_line2" class="form-control"></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Area</label><input type="text" name="area" class="form-control"></div>
          <div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="Dubai"></div>
        </div>
        <div class="form-group"><label class="form-label">Emirate</label><select name="emirate" class="form-control"><?php foreach (['Dubai','Abu Dhabi','Sharjah','Ajman','Ras Al Khaimah','Fujairah','Umm Al Quwain'] as $em): ?><option><?= $em ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-check"><input type="checkbox" name="is_default" value="1"> Set as default address</label></div>
        <button type="submit" class="btn btn-primary">Save Address</button>
      </form>
    </div>
  </div>
</div>
