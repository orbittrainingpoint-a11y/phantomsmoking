<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">All Coupons</div></div>
    <div class="admin-card-body" style="padding:0">
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used</th><th>Expires</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($coupons as $c): ?>
            <tr>
              <td><strong style="font-family:var(--font-mono)"><?= e($c['code']) ?></strong><?php if ($c['description']): ?><div style="font-size:0.75rem;color:var(--color-text-muted)"><?= e($c['description']) ?></div><?php endif; ?></td>
              <td><?= ucwords(str_replace('_',' ',$c['type'])) ?></td>
              <td><?= $c['type'] === 'percentage' ? $c['value'].'%' : format_price($c['value']) ?></td>
              <td><?= format_price($c['min_order_amount']) ?></td>
              <td><?= $c['used_count'] ?><?= $c['usage_limit'] ? '/'.$c['usage_limit'] : '' ?></td>
              <td style="font-size:0.82rem"><?= $c['end_date'] ? date('d M Y', strtotime($c['end_date'])) : 'Never' ?></td>
              <td><span class="badge badge-<?= $c['is_active'] ? 'success' : 'secondary' ?>"><?= $c['is_active'] ? 'Active' : 'Off' ?></span></td>
              <td style="display:flex;gap:6px">
                <button onclick="editCoupon(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></button>
                <form method="POST" action="/admin/coupons/<?= $c['id'] ?>/delete" onsubmit="return confirm('Delete?')" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="admin-card" id="couponFormCard">
    <div class="admin-card-header"><div class="admin-card-title" id="couponFormTitle">Create Coupon</div></div>
    <div class="admin-card-body">
      <form method="POST" id="couponForm" action="/admin/coupons">
        <?= csrf_field() ?>
        <div class="form-group"><label class="form-label">Code *</label><input type="text" name="code" id="couponCode" class="form-control" required placeholder="SAVE20" style="text-transform:uppercase"></div>
        <div class="form-group"><label class="form-label">Description</label><input type="text" name="description" id="couponDesc" class="form-control"></div>
        <div class="form-group"><label class="form-label">Type</label>
          <select name="type" id="couponType" class="form-control">
            <option value="percentage">Percentage (%)</option>
            <option value="fixed_amount">Fixed Amount (AED)</option>
            <option value="free_shipping">Free Shipping</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Value</label><input type="number" name="value" id="couponValue" class="form-control" step="0.01" required></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Min Order (AED)</label><input type="number" name="min_order_amount" id="couponMinOrder" class="form-control" step="0.01" value="0"></div>
          <div class="form-group"><label class="form-label">Max Discount (AED)</label><input type="number" name="max_discount_amount" id="couponMaxDiscount" class="form-control" step="0.01"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Usage Limit</label><input type="number" name="usage_limit" id="couponUsageLimit" class="form-control" placeholder="Unlimited"></div>
          <div class="form-group"><label class="form-label">Per User</label><input type="number" name="usage_per_user" id="couponPerUser" class="form-control" value="1"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Start Date</label><input type="datetime-local" name="start_date" id="couponStartDate" class="form-control"></div>
          <div class="form-group"><label class="form-label">End Date</label><input type="datetime-local" name="end_date" id="couponEndDate" class="form-control"></div>
        </div>
        <div class="form-group"><label class="form-check"><input type="checkbox" name="is_active" id="couponActive" value="1" checked> Active</label></div>
        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary" id="couponSubmitBtn">Create Coupon</button>
          <button type="button" class="btn btn-outline" id="couponCancelBtn" style="display:none" onclick="resetCouponForm()">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editCoupon(c) {
  document.getElementById('couponFormTitle').textContent = 'Edit Coupon';
  document.getElementById('couponForm').action = '/admin/coupons/' + c.id + '/edit';
  document.getElementById('couponCode').value        = c.code || '';
  document.getElementById('couponDesc').value        = c.description || '';
  document.getElementById('couponType').value        = c.type || 'percentage';
  document.getElementById('couponValue').value       = c.value || '';
  document.getElementById('couponMinOrder').value    = c.min_order_amount || 0;
  document.getElementById('couponMaxDiscount').value = c.max_discount_amount || '';
  document.getElementById('couponUsageLimit').value  = c.usage_limit || '';
  document.getElementById('couponPerUser').value     = c.usage_per_user || 1;
  document.getElementById('couponStartDate').value   = c.start_date ? c.start_date.replace(' ','T').slice(0,16) : '';
  document.getElementById('couponEndDate').value     = c.end_date ? c.end_date.replace(' ','T').slice(0,16) : '';
  document.getElementById('couponActive').checked    = c.is_active == 1;
  document.getElementById('couponSubmitBtn').textContent = 'Update Coupon';
  document.getElementById('couponCancelBtn').style.display = 'inline-flex';
  document.getElementById('couponFormCard').scrollIntoView({behavior:'smooth'});
}
function resetCouponForm() {
  document.getElementById('couponFormTitle').textContent = 'Create Coupon';
  document.getElementById('couponForm').action = '/admin/coupons';
  document.getElementById('couponForm').reset();
  document.getElementById('couponSubmitBtn').textContent = 'Create Coupon';
  document.getElementById('couponCancelBtn').style.display = 'none';
}
</script>
