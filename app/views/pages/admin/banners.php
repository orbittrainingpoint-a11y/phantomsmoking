<div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start">
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">Banners</div></div>
    <div class="admin-card-body" style="padding:0">
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Banner</th><th>Position</th><th>Order</th><th>Active</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($banners as $b): ?>
            <tr>
              <td>
                <?php if ($b['image_desktop']): ?><img src="<?= e($b['image_desktop']) ?>" style="width:80px;height:40px;object-fit:cover;border-radius:var(--radius);margin-right:8px;vertical-align:middle"><?php endif; ?>
                <strong><?= e($b['title']) ?></strong>
                <?php if ($b['subtitle']): ?><div style="font-size:0.78rem;color:var(--color-text-muted)"><?= e($b['subtitle']) ?></div><?php endif; ?>
              </td>
              <td><span class="badge badge-primary"><?= ucfirst($b['position']) ?></span></td>
              <td><?= $b['sort_order'] ?></td>
              <td><span class="badge badge-<?= $b['is_active'] ? 'success' : 'secondary' ?>"><?= $b['is_active'] ? 'Active' : 'Inactive' ?></span></td>
              <td style="display:flex;gap:6px">
                <button onclick="editBanner(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></button>
                <form method="POST" action="/admin/banners/<?= $b['id'] ?>/delete" onsubmit="return confirm('Delete banner?')" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($banners)): ?><tr><td colspan="5" style="text-align:center;padding:32px;color:var(--color-text-muted)">No banners yet</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="admin-card" id="bannerFormCard">
    <div class="admin-card-header">
      <div class="admin-card-title" id="bannerFormTitle">Add Banner</div>
    </div>
    <div class="admin-card-body">
      <form method="POST" id="bannerForm" action="/admin/banners" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" id="bannerTitle" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Subtitle</label><input type="text" name="subtitle" id="bannerSubtitle" class="form-control"></div>
        <div class="form-group">
          <label class="form-label">Desktop Image</label>
          <input type="file" name="image_desktop_file" class="form-control" accept="image/*">
          <div class="form-hint">Or enter URL:</div>
          <input type="text" name="image_desktop" id="bannerImgDesktop" class="form-control" style="margin-top:6px" placeholder="/uploads/banners/banner.jpg">
          <div id="bannerImgPreview" style="margin-top:8px;display:none"><img id="bannerPreviewImg" src="" style="width:100%;max-height:100px;object-fit:cover;border-radius:var(--radius)"></div>
        </div>
        <div class="form-group"><label class="form-label">Mobile Image URL</label><input type="text" name="image_mobile" id="bannerImgMobile" class="form-control" placeholder="/uploads/banners/banner-mobile.jpg"></div>
        <div class="form-group"><label class="form-label">Link URL</label><input type="text" name="link_url" id="bannerLinkUrl" class="form-control" placeholder="/shop/cigars"></div>
        <div class="form-group"><label class="form-label">Button Text</label><input type="text" name="link_text" id="bannerLinkText" class="form-control" placeholder="Shop Now"></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Position</label><select name="position" id="bannerPosition" class="form-control"><option value="hero">Hero Slider</option><option value="secondary">Secondary</option><option value="category_top">Category Top</option><option value="popup">Popup</option></select></div>
          <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" id="bannerSortOrder" class="form-control" value="0"></div>
        </div>
        <div class="form-group"><label class="form-check"><input type="checkbox" name="is_active" id="bannerActive" value="1" checked> Active</label></div>
        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary" id="bannerSubmitBtn">Add Banner</button>
          <button type="button" class="btn btn-outline" id="bannerCancelBtn" style="display:none" onclick="resetBannerForm()">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editBanner(b) {
  document.getElementById('bannerFormTitle').textContent = 'Edit Banner';
  document.getElementById('bannerForm').action = '/admin/banners/' + b.id + '/edit';
  document.getElementById('bannerTitle').value     = b.title || '';
  document.getElementById('bannerSubtitle').value  = b.subtitle || '';
  document.getElementById('bannerImgDesktop').value= b.image_desktop || '';
  document.getElementById('bannerImgMobile').value = b.image_mobile || '';
  document.getElementById('bannerLinkUrl').value   = b.link_url || '';
  document.getElementById('bannerLinkText').value  = b.link_text || '';
  document.getElementById('bannerPosition').value  = b.position || 'hero';
  document.getElementById('bannerSortOrder').value = b.sort_order || 0;
  document.getElementById('bannerActive').checked  = b.is_active == 1;
  if (b.image_desktop) {
    document.getElementById('bannerPreviewImg').src = b.image_desktop;
    document.getElementById('bannerImgPreview').style.display = 'block';
  }
  document.getElementById('bannerSubmitBtn').textContent = 'Update Banner';
  document.getElementById('bannerCancelBtn').style.display = 'inline-flex';
  document.getElementById('bannerFormCard').scrollIntoView({behavior:'smooth'});
}
function resetBannerForm() {
  document.getElementById('bannerFormTitle').textContent = 'Add Banner';
  document.getElementById('bannerForm').action = '/admin/banners';
  document.getElementById('bannerForm').reset();
  document.getElementById('bannerImgPreview').style.display = 'none';
  document.getElementById('bannerSubmitBtn').textContent = 'Add Banner';
  document.getElementById('bannerCancelBtn').style.display = 'none';
}
</script>
