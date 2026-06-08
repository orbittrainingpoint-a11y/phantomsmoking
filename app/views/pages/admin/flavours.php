<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">All Flavours</div>
      <span style="font-size:0.8rem;color:var(--color-text-muted)">Click row to edit</span>
    </div>
    <div class="admin-card-body" style="padding:0">
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Name</th><th>Category</th><th>Active</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($flavours as $f): ?>
            <tr style="cursor:pointer" onclick="editFlavour(<?= htmlspecialchars(json_encode($f)) ?>)">
              <td><strong><?= e($f['name']) ?></strong><div style="font-size:0.75rem;color:var(--color-text-muted)"><?= e($f['slug']) ?></div></td>
              <td><span class="badge badge-primary"><?= ucfirst($f['category'] ?? 'general') ?></span></td>
              <td><span class="badge badge-<?= $f['is_active'] ? 'success' : 'secondary' ?>"><?= $f['is_active'] ? 'Active' : 'Hidden' ?></span></td>
              <td onclick="event.stopPropagation()">
                <form method="POST" action="/admin/flavours/<?= $f['id'] ?>/delete" onsubmit="return confirm('Delete flavour?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($flavours)): ?>
            <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--color-text-muted)">No flavours yet. Add one →</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="admin-card" id="flavourFormCard">
    <div class="admin-card-header">
      <div class="admin-card-title" id="flavourFormTitle">Add Flavour</div>
    </div>
    <div class="admin-card-body">
      <form method="POST" id="flavourForm" action="/admin/flavours">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">Flavour Name *</label>
          <input type="text" name="name" id="flavourName" class="form-control" required placeholder="e.g. Mango Ice">
        </div>
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category" id="flavourCategory" class="form-control">
            <option value="general">General</option>
            <option value="vape">Vape / E-Liquid</option>
            <option value="shisha">Shisha / Hookah</option>
            <option value="cigar">Cigar</option>
            <option value="nic-pouch">Nic Pouch</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="is_active" id="flavourActive" value="1" checked> Active
          </label>
        </div>
        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary" id="flavourSubmitBtn">Add Flavour</button>
          <button type="button" class="btn btn-outline" id="flavourCancelBtn" style="display:none" onclick="resetFlavourForm()">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editFlavour(f) {
  document.getElementById('flavourFormTitle').textContent = 'Edit Flavour';
  document.getElementById('flavourForm').action = '/admin/flavours/' + f.id;
  document.getElementById('flavourName').value = f.name;
  document.getElementById('flavourCategory').value = f.category || 'general';
  document.getElementById('flavourActive').checked = f.is_active == 1;
  document.getElementById('flavourSubmitBtn').textContent = 'Update Flavour';
  document.getElementById('flavourCancelBtn').style.display = 'inline-flex';
  document.getElementById('flavourFormCard').scrollIntoView({behavior:'smooth'});
}
function resetFlavourForm() {
  document.getElementById('flavourFormTitle').textContent = 'Add Flavour';
  document.getElementById('flavourForm').action = '/admin/flavours';
  document.getElementById('flavourForm').reset();
  document.getElementById('flavourSubmitBtn').textContent = 'Add Flavour';
  document.getElementById('flavourCancelBtn').style.display = 'none';
}
</script>
