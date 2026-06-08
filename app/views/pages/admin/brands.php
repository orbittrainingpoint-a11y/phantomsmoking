<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">All Brands</div></div>
    <div class="admin-card-body" style="padding:0">
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Brand</th><th>Country</th><th>Featured</th><th>Active</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($brands as $b): ?>
            <tr>
              <td style="display:flex;align-items:center;gap:10px">
                <?php if ($b['logo']): ?><img src="<?= e($b['logo']) ?>" style="height:32px;max-width:80px;object-fit:contain"><?php endif; ?>
                <div><strong><?= e($b['name']) ?></strong><div style="font-family:var(--font-mono);font-size:0.75rem;color:var(--color-text-muted)"><?= e($b['slug']) ?></div></div>
              </td>
              <td><?= e($b['country_of_origin'] ?? '—') ?></td>
              <td><span class="badge badge-<?= $b['is_featured'] ? 'gold' : 'secondary' ?>"><?= $b['is_featured'] ? 'Featured' : 'No' ?></span></td>
              <td><span class="badge badge-<?= $b['is_active'] ? 'success' : 'secondary' ?>"><?= $b['is_active'] ? 'Active' : 'Hidden' ?></span></td>
              <td style="display:flex;gap:6px">
                <button onclick="editBrand(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></button>
                <form method="POST" action="/admin/brands/<?= $b['id'] ?>/delete" onsubmit="return confirm('Delete brand?')" style="display:inline">
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

  <div class="admin-card" id="brandFormCard">
    <div class="admin-card-header"><div class="admin-card-title" id="brandFormTitle">Add Brand</div></div>
    <div class="admin-card-body">
      <form method="POST" id="brandForm" action="/admin/brands" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group"><label class="form-label">Brand Name *</label><input type="text" name="name" id="brandName" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Country of Origin</label><input type="text" name="country" id="brandCountry" class="form-control" placeholder="UAE, UK, USA..."></div>
        <div class="form-group">
          <label class="form-label">Logo</label>
          <input type="file" name="logo_file" class="form-control" accept="image/*">
          <div class="form-hint">Or enter URL:</div>
          <input type="text" name="logo" id="brandLogo" class="form-control" style="margin-top:6px" placeholder="/uploads/brands/logo.png">
          <div id="brandLogoPreview" style="margin-top:8px;display:none"><img id="brandPreviewImg" src="" style="height:40px;object-fit:contain"></div>
        </div>
        <div class="form-group"><label class="form-label">Website URL</label><input type="text" name="website_url" id="brandWebsite" class="form-control" placeholder="https://brand.com"></div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="brandDesc" class="form-control" rows="2"></textarea></div>
        <div class="form-group" style="display:flex;gap:16px">
          <label class="form-check"><input type="checkbox" name="is_featured" id="brandFeatured" value="1"> Featured in Carousel</label>
          <label class="form-check"><input type="checkbox" name="is_active" id="brandActive" value="1" checked> Active</label>
        </div>
        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary" id="brandSubmitBtn">Add Brand</button>
          <button type="button" class="btn btn-outline" id="brandCancelBtn" style="display:none" onclick="resetBrandForm()">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editBrand(b) {
  document.getElementById('brandFormTitle').textContent = 'Edit Brand';
  document.getElementById('brandForm').action = '/admin/brands/' + b.id + '/edit';
  document.getElementById('brandName').value    = b.name || '';
  document.getElementById('brandCountry').value = b.country_of_origin || '';
  document.getElementById('brandLogo').value    = b.logo || '';
  document.getElementById('brandWebsite').value = b.website_url || '';
  document.getElementById('brandDesc').value    = b.description || '';
  document.getElementById('brandFeatured').checked = b.is_featured == 1;
  document.getElementById('brandActive').checked   = b.is_active == 1;
  if (b.logo) {
    document.getElementById('brandPreviewImg').src = b.logo;
    document.getElementById('brandLogoPreview').style.display = 'block';
  }
  document.getElementById('brandSubmitBtn').textContent = 'Update Brand';
  document.getElementById('brandCancelBtn').style.display = 'inline-flex';
  document.getElementById('brandFormCard').scrollIntoView({behavior:'smooth'});
}
function resetBrandForm() {
  document.getElementById('brandFormTitle').textContent = 'Add Brand';
  document.getElementById('brandForm').action = '/admin/brands';
  document.getElementById('brandForm').reset();
  document.getElementById('brandLogoPreview').style.display = 'none';
  document.getElementById('brandSubmitBtn').textContent = 'Add Brand';
  document.getElementById('brandCancelBtn').style.display = 'none';
}
</script>
