<div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start">

  <!-- Zones Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Delivery Zones</div>
      <span style="font-size:0.8rem;color:var(--color-text-muted)">Click a row to edit</span>
    </div>
    <div class="admin-card-body" style="padding:0">
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>Zone / Emirate</th><th>Standard</th><th>Express</th><th>Free From</th><th>ETA</th><th>Express?</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($zones as $z): ?>
            <tr style="cursor:pointer" onclick="editZone(<?= htmlspecialchars(json_encode($z)) ?>)">
              <td>
                <strong><?= e($z['zone_name']) ?></strong>
                <div style="font-size:0.78rem;color:var(--color-text-muted)"><?= e($z['emirate']) ?></div>
              </td>
              <td style="font-family:var(--font-mono)"><?= format_price($z['standard_delivery_fee']) ?></td>
              <td style="font-family:var(--font-mono)"><?= $z['is_express_available'] ? format_price($z['express_delivery_fee']) : '—' ?></td>
              <td style="font-family:var(--font-mono)"><?= format_price($z['free_shipping_threshold']) ?></td>
              <td style="font-size:0.82rem"><?= e($z['standard_days']) ?></td>
              <td><span class="badge badge-<?= $z['is_express_available'] ? 'success' : 'secondary' ?>"><?= $z['is_express_available'] ? 'Yes' : 'No' ?></span></td>
              <td>
                <form method="POST" action="/admin/delivery-zones/<?= $z['id'] ?>/delete" onsubmit="return confirm('Delete this zone?')" onclick="event.stopPropagation()">
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

  <!-- Add / Edit Form -->
  <div>
    <div class="admin-card" id="zoneFormCard">
      <div class="admin-card-header">
        <div class="admin-card-title" id="zoneFormTitle">Add Delivery Zone</div>
      </div>
      <div class="admin-card-body">
        <form method="POST" id="zoneForm" action="/admin/delivery-zones">
          <?= csrf_field() ?>
          <input type="hidden" name="_zone_id" id="zoneId" value="">
          <div class="form-group">
            <label class="form-label">Zone Name *</label>
            <input type="text" name="zone_name" id="zoneName" class="form-control" required placeholder="e.g. Dubai Marina">
          </div>
          <div class="form-group">
            <label class="form-label">Emirate *</label>
            <select name="emirate" id="zoneEmirate" class="form-control" required>
              <?php foreach (['Dubai','Abu Dhabi','Sharjah','Ajman','Ras Al Khaimah','Fujairah','Umm Al Quwain'] as $em): ?>
              <option><?= $em ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Standard Fee (AED)</label>
              <input type="number" name="standard_fee" id="zoneStdFee" class="form-control" step="0.01" value="10" min="0">
            </div>
            <div class="form-group">
              <label class="form-label">Express Fee (AED)</label>
              <input type="number" name="express_fee" id="zoneExpFee" class="form-control" step="0.01" value="25" min="0">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Free Shipping From (AED) <span style="color:var(--color-text-muted);font-weight:400">— 0 = never free</span></label>
            <input type="number" name="free_threshold" id="zoneFreeThreshold" class="form-control" step="0.01" value="100" min="0">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Standard ETA</label>
              <input type="text" name="standard_days" id="zoneStdDays" class="form-control" value="1-2 Days">
            </div>
            <div class="form-group">
              <label class="form-label">Express ETA</label>
              <input type="text" name="express_hours" id="zoneExpHours" class="form-control" value="1 Hour">
            </div>
          </div>
          <div class="form-group">
            <label class="form-check">
              <input type="checkbox" name="is_express" id="zoneIsExpress" value="1" checked> Express Delivery Available
            </label>
          </div>
          <div style="display:flex;gap:8px">
            <button type="submit" class="btn btn-primary" id="zoneSubmitBtn">Add Zone</button>
            <button type="button" class="btn btn-outline" id="zoneCancelBtn" style="display:none" onclick="resetZoneForm()">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Global Shipping Rules -->
    <div class="admin-card" style="margin-top:20px">
      <div class="admin-card-header"><div class="admin-card-title">Global Shipping Rules</div></div>
      <div class="admin-card-body">
        <form method="POST" action="/admin/settings">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Free Shipping Threshold (AED)</label>
            <input type="number" name="free_shipping_threshold" class="form-control" step="0.01"
              value="<?= e($settings['free_shipping_threshold'] ?? '100') ?>" min="0">
            <div style="font-size:0.78rem;color:var(--color-text-muted);margin-top:4px">Orders above this amount get free standard shipping. Set 0 to disable.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Default Standard Fee (AED) <span style="color:var(--color-text-muted);font-weight:400">— used if no zone matches</span></label>
            <input type="number" name="default_shipping_fee" class="form-control" step="0.01"
              value="<?= e($settings['default_shipping_fee'] ?? '15') ?>" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Default Express Fee (AED)</label>
            <input type="number" name="default_express_fee" class="form-control" step="0.01"
              value="<?= e($settings['default_express_fee'] ?? '25') ?>" min="0">
          </div>
          <button type="submit" class="btn btn-primary">Save Rules</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function editZone(z) {
  document.getElementById('zoneFormTitle').textContent = 'Edit Zone';
  document.getElementById('zoneForm').action = '/admin/delivery-zones/' + z.id;
  document.getElementById('zoneId').value        = z.id;
  document.getElementById('zoneName').value      = z.zone_name;
  document.getElementById('zoneEmirate').value   = z.emirate;
  document.getElementById('zoneStdFee').value    = z.standard_delivery_fee;
  document.getElementById('zoneExpFee').value    = z.express_delivery_fee;
  document.getElementById('zoneFreeThreshold').value = z.free_shipping_threshold;
  document.getElementById('zoneStdDays').value   = z.standard_days;
  document.getElementById('zoneExpHours').value  = z.express_hours;
  document.getElementById('zoneIsExpress').checked = z.is_express_available == 1;
  document.getElementById('zoneSubmitBtn').textContent = 'Update Zone';
  document.getElementById('zoneCancelBtn').style.display = 'inline-flex';
  document.getElementById('zoneFormCard').scrollIntoView({behavior:'smooth'});
}
function resetZoneForm() {
  document.getElementById('zoneFormTitle').textContent = 'Add Delivery Zone';
  document.getElementById('zoneForm').action = '/admin/delivery-zones';
  document.getElementById('zoneForm').reset();
  document.getElementById('zoneId').value = '';
  document.getElementById('zoneSubmitBtn').textContent = 'Add Zone';
  document.getElementById('zoneCancelBtn').style.display = 'none';
}
</script>
