<?php
$flashError   = flash_get('error');
$flashSuccess = flash_get('success');
?>
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title"><?= $product ? 'Edit Product' : 'Add New Product' ?></div>
    <a href="/admin/products" class="btn btn-sm btn-outline">← Back</a>
  </div>
  <div class="admin-card-body">
    <?php if ($flashError): ?>
    <div class="alert alert-error" style="margin-bottom:20px;padding:14px 18px;background:#fee2e2;border:1px solid #fca5a5;border-radius:var(--radius);color:#991b1b;font-size:0.9rem">
      <strong>❌ Error:</strong> <?= e($flashError) ?>
    </div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
    <div class="alert alert-success" style="margin-bottom:20px;padding:14px 18px;background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);color:#166534;font-size:0.9rem">
      <strong>✅</strong> <?= e($flashSuccess) ?>
    </div>
    <?php endif; ?>
    <form method="POST" action="<?= $product ? '/admin/products/'.$product['id'].'/edit' : '/admin/products/create' ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="variations_json" id="variationsJson" value="">
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:28px">
        <div>

          <!-- Basic -->
          <div class="form-group"><label class="form-label">Product Name *</label><input type="text" name="name" class="form-control" value="<?= e($product['name'] ?? '') ?>" required></div>
          <div class="form-group"><label class="form-label">Short Description</label><textarea name="short_description" class="form-control" rows="2"><?= e($product['short_description'] ?? '') ?></textarea></div>
          <div class="form-group"><label class="form-label">Full Description</label><textarea name="description" class="form-control" rows="6"><?= e($product['description'] ?? '') ?></textarea></div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">SKU *</label><input type="text" name="sku" class="form-control" value="<?= e($product['sku'] ?? '') ?>" required></div>
            <div class="form-group"><label class="form-label">Barcode</label><input type="text" name="barcode" class="form-control" value="<?= e($product['barcode'] ?? '') ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Price (AED) *</label><input type="number" name="price" class="form-control" step="0.01" value="<?= $product['price'] ?? '' ?>" required></div>
            <div class="form-group"><label class="form-label">Compare At Price</label><input type="number" name="compare_at_price" class="form-control" step="0.01" value="<?= $product['compare_at_price'] ?? '' ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Stock Quantity</label><input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity'] ?? 0 ?>"></div>
            <div class="form-group"><label class="form-label">Low Stock Alert</label><input type="number" name="low_stock_threshold" class="form-control" value="<?= $product['low_stock_threshold'] ?? 5 ?>"></div>
          </div>

          <!-- ── VARIATION TYPES ── -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none;margin-bottom:20px">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div>
                <div class="admin-card-title" style="font-size:0.95rem">🎛️ Variation Types</div>
                <div style="font-size:0.8rem;color:var(--color-text-muted);margin-top:3px">Define the types of choices a customer must make before buying. Example: Nicotine Strength, Puff Count, Flavour.</div>
              </div>
              <button type="button" id="addVtBtn" onclick="vtAdd()" class="btn btn-sm btn-outline" style="flex-shrink:0">+ Add Variation Type</button>
            </div>
            <div class="admin-card-body" style="padding:16px">
              <div id="vtList">
                <div id="vtEmpty" style="font-size:0.83rem;color:var(--color-text-muted);padding:8px 0">No variation types yet. Click <strong>+ Add Variation Type</strong> to start.</div>
              </div>
            </div>
          </div>

          <!-- ── COMBINATION BUILDER placeholder — filled in next step ── -->
          <div id="vcSection" style="display:none" class="admin-card" style="border:1px solid var(--color-border);box-shadow:none;margin-bottom:20px">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div>
                <div class="admin-card-title" style="font-size:0.95rem">🔗 Variation Combinations</div>
                <div style="font-size:0.8rem;color:var(--color-text-muted);margin-top:3px">Set which options are available together. Not all combinations may exist — only add real ones.</div>
              </div>
              <button type="button" onclick="vcAddRow()" class="btn btn-sm btn-outline" style="flex-shrink:0">+ Add Combination</button>
            </div>
            <div class="admin-card-body" style="padding:0">
              <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:0.83rem" id="vcTable">
                  <thead id="vcHead" style="background:var(--color-bg-light)"></thead>
                  <tbody id="vcBody"></tbody>
                </table>
              </div>
              <div style="padding:12px 16px;border-top:1px solid var(--color-border);font-size:0.82rem;color:var(--color-text-muted)" id="vcSummary">0 combinations added.</div>
            </div>
          </div>

          <!-- ── PRODUCT-SPECIFIC ── -->
          <div class="admin-card" style="border:1px solid var(--color-border);box-shadow:none;margin-bottom:20px">
            <div class="admin-card-header" style="background:var(--color-bg-light)">
              <div class="admin-card-title" style="font-size:0.9rem">📦 Product-Specific Details</div>
            </div>
            <div class="admin-card-body">
              <div class="form-row">
                <div class="form-group"><label class="form-label">Nicotine (mg)</label><input type="number" name="nicotine_content_mg" class="form-control" step="0.01" value="<?= $product['nicotine_content_mg'] ?? '' ?>"></div>
                <div class="form-group"><label class="form-label">Puff Count</label><input type="number" name="puff_count" class="form-control" value="<?= $product['puff_count'] ?? '' ?>"></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Volume (ml)</label><input type="number" name="volume_ml" class="form-control" step="0.01" value="<?= $product['volume_ml'] ?? '' ?>"></div>
                <div class="form-group"><label class="form-label">Flavor Profile (text)</label><input type="text" name="flavor_profile" class="form-control" value="<?= e($product['flavor_profile'] ?? '') ?>" placeholder="Mango, Ice, Mint..."></div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Shisha Weight <span style="color:var(--color-text-muted);font-weight:400">— shisha products</span></label>
                  <select name="shisha_weight" class="form-control">
                    <option value="">N/A</option>
                    <?php foreach (['50g','100g','250g','500g','1kg','2kg'] as $w): ?>
                    <option value="<?= $w ?>" <?= ($product['shisha_weight'] ?? '') === $w ? 'selected' : '' ?>><?= $w ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Hookah Height <span style="color:var(--color-text-muted);font-weight:400">— hookah products</span></label>
                  <input type="text" name="hookah_height" class="form-control" value="<?= e($product['hookah_height'] ?? '') ?>" placeholder="e.g. 70cm, 90cm, 120cm">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Cigar Size</label><input type="text" name="cigar_size" class="form-control" value="<?= e($product['cigar_size'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Cigar Strength</label>
                  <select name="cigar_strength" class="form-control">
                    <option value="">N/A</option>
                    <?php foreach (['mild','medium','medium-full','full'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($product['cigar_strength'] ?? '') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('-',' ',$s)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Cigar Wrapper</label><input type="text" name="cigar_wrapper" class="form-control" value="<?= e($product['cigar_wrapper'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Cigar Origin</label><input type="text" name="cigar_origin" class="form-control" value="<?= e($product['cigar_origin'] ?? '') ?>"></div>
              </div>
            </div>
          </div>

          <!-- Images -->
          <div class="form-group"><label class="form-label">Product Images</label><input type="file" name="images[]" multiple accept="image/*" class="form-control"></div>
          <?php if (!empty($product['images'])): ?>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
            <?php foreach ($product['images'] as $img): ?>
            <div style="position:relative">
              <img src="<?= e($img['image_path']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius)">
              <button type="button" onclick="deleteProductImage(<?= $img['id'] ?>, this)" style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;background:var(--color-error);color:#fff;border-radius:50%;font-size:0.7rem;display:flex;align-items:center;justify-content:center">×</button>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="form-group" style="margin-top:16px"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="<?= e($product['meta_title'] ?? '') ?>"></div>
          <div class="form-group"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2"><?= e($product['meta_description'] ?? '') ?></textarea></div>
        </div>

        <!-- Sidebar -->
        <div>
          <div class="form-group"><label class="form-label">Category *</label><select name="category_id" class="form-control" required><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Brand</label><select name="brand_id" class="form-control"><option value="">No Brand</option><?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? 0) == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-control"><option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="draft" <?= ($product['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option><option value="archived" <?= ($product['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option></select></div>
          <div class="form-group"><label class="form-label">Product Type</label><select name="product_type" class="form-control"><option value="simple">Simple</option><option value="variable">Variable</option><option value="bundle">Bundle</option></select></div>
          <div class="form-group"><label class="form-label">Reward Points</label><input type="number" name="reward_points" class="form-control" value="<?= $product['reward_points'] ?? 0 ?>"></div>
          <div style="display:flex;flex-direction:column;gap:10px;margin-top:16px">
            <label class="form-check"><input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?>> Featured Product</label>
            <label class="form-check"><input type="checkbox" name="is_new_arrival" value="1" <?= !empty($product['is_new_arrival']) ? 'checked' : '' ?>> New Arrival</label>
            <label class="form-check"><input type="checkbox" name="is_best_seller" value="1" <?= !empty($product['is_best_seller']) ? 'checked' : '' ?>> Best Seller</label>
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:24px" id="productSaveBtn"><?= $product ? 'Update Product' : 'Create Product' ?></button>
          <?php if ($product): ?>
          <button type="button" onclick="if(confirm('Archive this product?')){var f=document.createElement('form');f.method='POST';f.action='/admin/products/<?= $product['id'] ?>/delete';document.body.appendChild(f);f.submit();}" class="btn btn-danger btn-full" style="margin-top:8px">Archive Product</button>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
/* ── Variation Types ── */
.vt-row{border:1px solid var(--color-border);border-radius:var(--radius);margin-bottom:12px;background:#fff}
.vt-row-header{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--color-bg-light);border-radius:var(--radius) var(--radius) 0 0;border-bottom:1px solid var(--color-border)}
.vt-drag{cursor:grab;color:var(--color-text-muted);font-size:1.1rem;padding:0 4px;user-select:none}
.vt-drag:active{cursor:grabbing}
.vt-label-input{flex:1;padding:7px 10px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:0.88rem;font-weight:600;background:#fff}
.vt-label-input:focus{outline:none;border-color:var(--color-secondary)}
.vt-del-btn{background:none;border:1px solid #fca5a5;color:#dc2626;border-radius:var(--radius);padding:5px 10px;cursor:pointer;font-size:0.8rem;display:flex;align-items:center;gap:4px}
.vt-del-btn:hover{background:#fee2e2}
.vt-chips-area{padding:10px 12px}
.vt-chips-wrap{display:flex;flex-wrap:wrap;gap:6px;align-items:center;min-height:36px;padding:6px 8px;border:1px solid var(--color-border);border-radius:var(--radius);background:#fff;cursor:text}
.vt-chips-wrap:focus-within{border-color:var(--color-secondary)}
.vt-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 8px 3px 10px;background:rgba(200,150,60,0.12);border:1px solid rgba(200,150,60,0.4);border-radius:20px;font-size:0.78rem;font-weight:600;color:var(--color-primary)}
.vt-chip-del{background:none;border:none;cursor:pointer;color:var(--color-text-muted);font-size:0.85rem;padding:0;line-height:1;display:flex;align-items:center}
.vt-chip-del:hover{color:#dc2626}
.vt-chip-input{border:none;outline:none;font-size:0.83rem;min-width:80px;flex:1;padding:2px 4px;background:transparent}
.vt-hint{font-size:0.75rem;color:var(--color-text-muted);margin-top:5px}
.vt-row.dragging{opacity:0.4}
.vt-row.drag-over{border-color:var(--color-secondary);box-shadow:0 0 0 2px rgba(200,150,60,0.2)}
</style>

<script>
// ═══════════════════════════════════════════════════════
// VARIATION TYPES STATE
// vtState = array of { id, label, options:[] }
// This is the single source of truth read by the
// Combination Builder in the next step.
// ═══════════════════════════════════════════════════════
const _productId = <?= (int)($product['id'] ?? 0) ?>;
let vtState = [];   // variation types
let vcState = [];   // combinations (built in next step)
let _vtDragging = null;

// ── Render the full vtList from vtState ──────────────────
function vtRender() {
    const list  = document.getElementById('vtList');
    const empty = document.getElementById('vtEmpty');
    const addBtn = document.getElementById('addVtBtn');

    // Remove all existing rows (keep #vtEmpty)
    list.querySelectorAll('.vt-row').forEach(r => r.remove());

    if (vtState.length === 0) {
        empty.style.display = '';
        addBtn.disabled = false;
        return;
    }
    empty.style.display = 'none';
    addBtn.disabled = vtState.length >= 5;
    addBtn.title    = vtState.length >= 5 ? 'Maximum 5 variation types allowed' : '';

    vtState.forEach((vt, idx) => {
        const row = document.createElement('div');
        row.className   = 'vt-row';
        row.draggable   = true;
        row.dataset.idx = idx;

        // ── chips HTML ──
        const chipsHtml = vt.options.map((opt, oi) =>
            `<span class="vt-chip" data-oi="${oi}">
                ${escHtml(opt)}
                <button type="button" class="vt-chip-del" onclick="vtRemoveOption(${idx},${oi})" title="Remove">×</button>
             </span>`
        ).join('');

        row.innerHTML = `
          <div class="vt-row-header">
            <span class="vt-drag" title="Drag to reorder (top = Level 1 parent)">⠿</span>
            <input  class="vt-label-input"
                    type="text"
                    value="${escHtml(vt.label)}"
                    placeholder="e.g. Nicotine Strength"
                    title="This is the category name customers will see, like Nicotine Strength or Puff Count."
                    oninput="vtState[${idx}].label = this.value"
                    required>
            <span style="font-size:0.75rem;color:var(--color-text-muted);white-space:nowrap">Level ${idx + 1}</span>
            <button type="button" class="vt-del-btn" onclick="vtRemove(${idx})">
                <i class="fas fa-trash"></i> Remove
            </button>
          </div>
          <div class="vt-chips-area">
            <div class="vt-chips-wrap" onclick="this.querySelector('.vt-chip-input').focus()">
                ${chipsHtml}
                <input  class="vt-chip-input"
                        type="text"
                        placeholder="Type option then press Enter or comma…"
                        data-idx="${idx}"
                        onkeydown="vtChipKey(event,${idx})"
                        onblur="vtChipBlur(event,${idx})">
            </div>
            <div class="vt-hint">Press <strong>Enter</strong> or <strong>,</strong> to add. Examples: 0mg, 3mg, 6mg</div>
          </div>`;

        // ── drag events ──
        row.addEventListener('dragstart', e => {
            _vtDragging = idx;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        row.addEventListener('dragend', () => {
            row.classList.remove('dragging');
            document.querySelectorAll('.vt-row').forEach(r => r.classList.remove('drag-over'));
            _vtDragging = null;
        });
        row.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            document.querySelectorAll('.vt-row').forEach(r => r.classList.remove('drag-over'));
            row.classList.add('drag-over');
        });
        row.addEventListener('drop', e => {
            e.preventDefault();
            row.classList.remove('drag-over');
            if (_vtDragging === null || _vtDragging === idx) return;
            // Reorder vtState
            const moved = vtState.splice(_vtDragging, 1)[0];
            vtState.splice(idx, 0, moved);
            vtRender();
            vcRebuildHeaders(); // update combination table headers
        });

        list.appendChild(row);
    });
}

// ── Add a new empty variation type ───────────────────────
function vtAdd() {
    if (vtState.length >= 5) { showToast('Maximum 5 variation types allowed', 'error'); return; }
    vtState.push({ id: null, label: '', options: [] });
    vtRender();
    // Focus the new label input
    const rows = document.querySelectorAll('.vt-row');
    if (rows.length) rows[rows.length - 1].querySelector('.vt-label-input').focus();
}

// ── Remove a variation type ───────────────────────────────
function vtRemove(idx) {
    if (!confirm('Remove this variation type? Any combinations using it will also be cleared.')) return;
    vtState.splice(idx, 1);
    vcState = []; // clear combinations — they reference old indices
    vtRender();
    vcRebuildHeaders();
    vcRender();
}

// ── Chip keyboard handler ─────────────────────────────────
function vtChipKey(e, idx) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        vtCommitChip(e.target, idx);
    } else if (e.key === 'Backspace' && e.target.value === '' && vtState[idx].options.length) {
        vtState[idx].options.pop();
        vtRender();
        vcRebuildHeaders();
        vcRender();
        // Re-focus the input in the re-rendered row
        const inputs = document.querySelectorAll('.vt-chip-input');
        if (inputs[idx]) inputs[idx].focus();
    }
}

// ── Chip blur handler ─────────────────────────────────────
function vtChipBlur(e, idx) {
    if (e.target.value.trim()) vtCommitChip(e.target, idx);
}

// ── Commit a chip value ───────────────────────────────────
function vtCommitChip(input, idx) {
    const val = input.value.replace(/,/g, '').trim();
    if (!val) { input.value = ''; return; }
    if (vtState[idx].options.includes(val)) {
        showToast(`"${val}" already exists in this type`, 'error');
        input.value = '';
        return;
    }
    vtState[idx].options.push(val);
    input.value = '';
    vtRender();
    vcRebuildHeaders();
    // Re-focus the chip input for this row
    const inputs = document.querySelectorAll('.vt-chip-input');
    if (inputs[idx]) inputs[idx].focus();
}

// ── Remove a single chip option ───────────────────────────
function vtRemoveOption(typeIdx, optIdx) {
    vtState[typeIdx].options.splice(optIdx, 1);
    // Remove any combinations that used this option value
    const removedVal = vtState[typeIdx]?.options[optIdx]; // already spliced, just clean combos
    vcState = vcState.filter(c => c.options[typeIdx] !== undefined);
    vtRender();
    vcRebuildHeaders();
    vcRender();
}

// ══ COMBINATION BUILDER ══

function vcRebuildHeaders() {
    const sec = document.getElementById('vcSection');
    if (!sec) return;
    const hasOptions = vtState.length > 0 && vtState.some(t => t.options.length > 0);
    sec.style.display = hasOptions ? '' : 'none';
    if (!hasOptions) return;
    const head = document.getElementById('vcHead');
    if (!head) return;
    let th = '<tr style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--color-text-muted);background:var(--color-bg-light)">';
    vtState.forEach((vt, i) => {
        th += `<th style="padding:10px 12px;border-bottom:1px solid var(--color-border);white-space:nowrap">${escHtml(vt.label || ('Type '+(i+1)))}</th>`;
    });
    th += `<th style="padding:10px 12px;border-bottom:1px solid var(--color-border)">Price (AED)</th>`
        + `<th style="padding:10px 12px;border-bottom:1px solid var(--color-border)">Stock</th>`
        + `<th style="padding:10px 12px;border-bottom:1px solid var(--color-border)">SKU</th>`
        + `<th style="padding:10px 12px;border-bottom:1px solid var(--color-border);width:40px"></th></tr>`;
    head.innerHTML = th;
    vcRender();
}

function vcAddRow() {
    vcState.push({ id: null, options: vtState.map(() => ''), price: '', stock: '', sku: '' });
    vcRender();
}

function vcDeleteRow(idx) {
    vcState.splice(idx, 1);
    vcRender();
}

function vcFindDuplicates() {
    const seen = {};
    const dups = new Set();
    vcState.forEach((c, i) => {
        const key = c.options.join('|');
        if (c.options.some(o => o === '')) return; // skip incomplete rows
        if (seen[key] !== undefined) { dups.add(seen[key]); dups.add(i); }
        else seen[key] = i;
    });
    return dups;
}

function vcRender() {
    const tbody = document.getElementById('vcBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const dups = vcFindDuplicates();
    vcState.forEach((combo, idx) => {
        const isDup = dups.has(idx);
        const tr = document.createElement('tr');
        tr.style.cssText = isDup ? 'background:#fff5f5;border-left:3px solid #dc2626' : 'border-left:3px solid transparent';
        let cells = '';
        vtState.forEach((vt, ti) => {
            const hasOpts = vt.options.length > 0;
            const opts = hasOpts
                ? `<option value="">-- Select --</option>` + vt.options.map(o =>
                    `<option value="${escHtml(o)}" ${combo.options[ti]===o?'selected':''}>${escHtml(o)}</option>`).join('')
                : `<option value="" disabled>Add options above first</option>`;
            cells += `<td style="padding:6px 8px;border-bottom:1px solid var(--color-border)">`
                   + `<select style="width:100%;padding:6px 8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:0.83rem;background:#fff" ${!hasOpts?'disabled':''} onchange="vcState[${idx}].options[${ti}]=this.value;vcRender()">${opts}</select></td>`;
        });
        cells += `<td style="padding:6px 8px;border-bottom:1px solid var(--color-border)"><input type="number" step="0.01" min="0" placeholder="0.00" value="${escHtml(String(combo.price))}" style="width:90px;padding:6px 8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:0.83rem" oninput="vcState[${idx}].price=this.value"></td>`;
        cells += `<td style="padding:6px 8px;border-bottom:1px solid var(--color-border)"><input type="number" min="0" placeholder="0" value="${escHtml(String(combo.stock))}" style="width:70px;padding:6px 8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:0.83rem" oninput="vcState[${idx}].stock=this.value"></td>`;
        cells += `<td style="padding:6px 8px;border-bottom:1px solid var(--color-border)"><input type="text" placeholder="e.g. CX-6MG-5K" value="${escHtml(combo.sku)}" style="width:130px;padding:6px 8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:0.83rem" oninput="vcState[${idx}].sku=this.value"></td>`;
        cells += `<td style="padding:6px 8px;border-bottom:1px solid var(--color-border);text-align:center">`
               + `<button type="button" onclick="vcDeleteRow(${idx})" style="background:none;border:1px solid #fca5a5;color:#dc2626;border-radius:var(--radius);padding:5px 8px;cursor:pointer" title="Remove"><i class="fas fa-trash"></i></button>`
               + (isDup ? `<div style="font-size:0.7rem;color:#dc2626;margin-top:3px;white-space:nowrap">Duplicate!</div>` : '')
               + `</td>`;
        tr.innerHTML = cells;
        tbody.appendChild(tr);
    });
    vcUpdateSummary();
    // Disable save button if duplicates
    const saveBtn = document.querySelector('button[type="submit"]');
    if (saveBtn) { saveBtn.disabled = dups.size > 0; saveBtn.title = dups.size > 0 ? 'Fix duplicate combinations before saving' : ''; }
}

function vcUpdateSummary() {
    const el = document.getElementById('vcSummary');
    if (!el) return;
    const n = vcState.length;
    el.textContent = n + ' combination' + (n!==1?'s':'') + ' added. Make sure all real product options are covered.';
}

// ══ SAVE VARIATIONS ══

async function vcSaveVariations(productId) {
    if (vtState.length === 0) return true;
    if (vcFindDuplicates().size > 0) { showToast('Fix duplicate combinations before saving', 'error'); return false; }
    const payload = {
        variation_types: vtState.map((vt, i) => ({
            type_name: vt.label, display_order: i,
            options: vt.options.map(o => ({ option_value: o }))
        })),
        combinations: vcState
            .filter(c => c.options[0] !== '')
            .map(c => ({ options: c.options, price: parseFloat(c.price)||0, stock: parseInt(c.stock)||0, sku: c.sku.trim() }))
    };
    try {
        const res  = await fetch(`/api/products/${productId}/variations`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content||'', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) { showToast(data.error || 'Failed to save variations', 'error'); return false; }
        return true;
    } catch(e) { showToast('Network error saving variations', 'error'); return false; }
}

// Hook form submit to also save variations
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action*="/admin/products"]');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        // Duplicate check
        if (vcFindDuplicates().size > 0) {
            e.preventDefault();
            showToast('Fix duplicate combinations before saving', 'error');
            return;
        }

        // Serialize variations into hidden field (works for both create and edit)
        if (vtState.length > 0) {
            const payload = {
                variation_types: vtState.map((vt, i) => ({
                    type_name: vt.label, display_order: i,
                    options: vt.options.map(o => ({ option_value: o }))
                })),
                combinations: vcState
                    .filter(c => c.options[0] !== '')
                    .map(c => ({ options: c.options, price: parseFloat(c.price)||0, stock: parseInt(c.stock)||0, sku: c.sku.trim() }))
            };
            document.getElementById('variationsJson').value = JSON.stringify(payload);
        }

        // Edit page with existing product — save variations via API first, then submit
        if (_productId && vtState.length > 0) {
            e.preventDefault();
            const saveBtn = document.getElementById('productSaveBtn');
            const orig = saveBtn?.innerHTML;
            if (saveBtn) { saveBtn.disabled = true; saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…'; }
            try {
                const ok = await vcSaveVariations(_productId);
                if (!ok) {
                    if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = orig; }
                    return;
                }
                form.removeEventListener('submit', arguments.callee);
                form.submit();
            } catch(err) {
                showToast('Error saving variations', 'error');
                if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = orig; }
            }
        }
        // New product (_productId === 0): form submits normally with variations_json hidden field
        // Server reads it and saves variations after creating the product
    });
});

// ── HTML escape helper ────────────────────────────────────
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Load existing variation data on edit ─────────────────
document.addEventListener('DOMContentLoaded', async () => {
    if (_productId) {
        try {
            const res  = await fetch(`/api/products/${_productId}/variations`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success && data.variation_types?.length) {
                vtState = data.variation_types.map(t => ({
                    id:      t.id,
                    label:   t.type_name,
                    options: t.options.map(o => o.option_value)
                }));
                vcState = (data.combinations || []).map(c => ({
                    id:      c.id,
                    options: [
                        c.val_level1 ?? null,
                        c.val_level2 ?? null,
                        c.val_level3 ?? null,
                        c.val_level4 ?? null,
                        c.val_level5 ?? null,
                    ].filter((v, i) => i < vtState.length),
                    price: c.price,
                    stock: c.stock,
                    sku:   c.sku ?? ''
                }));
                vtRender();
                vcRebuildHeaders();
                vcRender();
            }
        } catch(e) { /* new product — no variations yet */ }
    }
});

// ── Image delete ──────────────────────────────────────────
async function deleteProductImage(id, btn) {
    if (!confirm('Delete this image?')) return;
    try {
        const res  = await fetch('/api/admin/products/images/' + id + '/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        if (data.success) btn.closest('div[style]').remove();
        else showToast('Could not delete image', 'error');
    } catch(e) { showToast('Network error', 'error'); }
}
</script>
