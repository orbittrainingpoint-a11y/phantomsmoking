<div style="display:grid;grid-template-columns:1fr 360px;gap:24px">
  <div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">All Categories</div></div>
    <div class="admin-card-body" style="padding:0">
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Name</th><th>Slug</th><th>Parent</th><th>Position</th><th>Menu</th><th>Active</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
              <td><?php if ($cat['parent_id']): ?><span style="color:var(--color-text-muted);margin-right:8px">↳</span><?php endif; ?><strong><?= e($cat['name']) ?></strong></td>
              <td style="font-family:var(--font-mono);font-size:0.8rem"><?= e($cat['slug']) ?></td>
              <td><?= e($cat['parent_name'] ?? '—') ?></td>
              <td><?= $cat['position'] ?></td>
              <td><span class="badge badge-<?= $cat['show_in_menu'] ? 'success' : 'secondary' ?>"><?= $cat['show_in_menu'] ? 'Yes' : 'No' ?></span></td>
              <td><span class="badge badge-<?= $cat['is_active'] ? 'success' : 'secondary' ?>"><?= $cat['is_active'] ? 'Active' : 'Hidden' ?></span></td>
              <td>
                <button onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="admin-card" id="categoryFormCard">
    <div class="admin-card-header"><div class="admin-card-title" id="categoryFormTitle">Add Category</div></div>
    <div class="admin-card-body">
      <form method="POST" action="/admin/categories" id="categoryForm" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" id="categoryMethod" value="POST">
        <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" id="catName" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Parent Category</label>
          <select name="parent_id" id="catParent" class="form-control">
            <option value="">None (Top Level)</option>
            <?php foreach (array_filter($categories, fn($c) => !$c['parent_id']) as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="catDesc" class="form-control" rows="2"></textarea></div>
        <div class="form-group">
          <label class="form-label">Category Image</label>
          <div id="catImgPreviewWrap" style="display:none;margin-bottom:8px">
            <img id="catImgPreview" src="" alt="" style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;border:1px solid var(--color-border)">
          </div>
          <input type="file" name="image" id="catImage" class="form-control" accept="image/*" onchange="previewCatImage(this)">
          <input type="hidden" name="existing_image" id="catExistingImage" value="">
          <div style="font-size:0.78rem;color:var(--color-text-muted);margin-top:4px">Recommended: 800×600px. JPG, PNG or WebP.</div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Position</label><input type="number" name="position" id="catPos" class="form-control" value="0"></div>
          <div class="form-group" style="display:flex;flex-direction:column;gap:8px;justify-content:flex-end">
            <label class="form-check"><input type="checkbox" name="is_active" id="catActive" value="1" checked> Active</label>
            <label class="form-check"><input type="checkbox" name="show_in_menu" id="catMenu" value="1" checked> Show in Menu</label>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full" id="catSubmitBtn">Add Category</button>
        <button type="button" class="btn btn-outline btn-full" style="margin-top:8px;display:none" id="catResetBtn" onclick="resetCategoryForm()">Cancel Edit</button>
      </form>
    </div>
  </div>
</div>
<script>
function previewCatImage(input) {
  const wrap = document.getElementById('catImgPreviewWrap');
  const img = document.getElementById('catImgPreview');
  if (input.files && input.files[0]) {
    img.src = URL.createObjectURL(input.files[0]);
    wrap.style.display = 'block';
  }
}
function resetCategoryForm() {
  document.getElementById('categoryFormTitle').textContent = 'Add Category';
  document.getElementById('categoryForm').action = '/admin/categories';
  document.getElementById('catName').value = '';
  document.getElementById('catParent').value = '';
  document.getElementById('catDesc').value = '';
  document.getElementById('catPos').value = '0';
  document.getElementById('catActive').checked = true;
  document.getElementById('catMenu').checked = true;
  document.getElementById('catImage').value = '';
  document.getElementById('catExistingImage').value = '';
  document.getElementById('catImgPreviewWrap').style.display = 'none';
  document.getElementById('catSubmitBtn').textContent = 'Add Category';
  document.getElementById('catResetBtn').style.display = 'none';
}
function editCategory(cat) {
  document.getElementById('categoryFormTitle').textContent = 'Edit Category';
  document.getElementById('categoryForm').action = '/admin/categories/' + cat.id;
  document.getElementById('catName').value = cat.name;
  document.getElementById('catParent').value = cat.parent_id || '';
  document.getElementById('catDesc').value = cat.description || '';
  document.getElementById('catPos').value = cat.position;
  document.getElementById('catActive').checked = cat.is_active == 1;
  document.getElementById('catMenu').checked = cat.show_in_menu == 1;
  document.getElementById('catExistingImage').value = cat.image || '';
  const wrap = document.getElementById('catImgPreviewWrap');
  const img = document.getElementById('catImgPreview');
  if (cat.image) { img.src = cat.image; wrap.style.display = 'block'; }
  else { wrap.style.display = 'none'; }
  document.getElementById('catImage').value = '';
  document.getElementById('catSubmitBtn').textContent = 'Update Category';
  document.getElementById('catResetBtn').style.display = 'block';
  document.getElementById('categoryFormCard').scrollIntoView({behavior:'smooth'});
}
</script>
