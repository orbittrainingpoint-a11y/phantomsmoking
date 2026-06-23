<?php
$_mainProduct = $product;
$primaryImage = '';
foreach ($product['images'] as $img) {
    if ($img['is_primary']) { $primaryImage = $img['image_path']; break; }
}
if (!$primaryImage && !empty($product['images'])) $primaryImage = $product['images'][0]['image_path'];
$primaryImage = $primaryImage ?: '/assets/images/placeholder.jpg';
?>
<div class="container" style="padding-top:24px;padding-bottom:48px">
  <div class="breadcrumb">
    <a href="/">Home</a><span class="breadcrumb-sep">/</span>
    <a href="/shop/<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
    <span class="breadcrumb-sep">/</span>
    <span><?= e($product['name']) ?></span>
  </div>

  <div class="age-warning-banner"><i class="fas fa-exclamation-triangle"></i> Age-restricted product. For adults 18+ only. Tobacco is harmful to health.</div>

  <div class="product-detail">
    <!-- Gallery -->
    <div class="product-gallery">
      <div class="gallery-main" id="galleryMain">
        <img src="<?= e($primaryImage) ?>" alt="<?= e($product['name']) ?>" id="mainImage">
      </div>
      <?php if (count($product['images']) > 1): ?>
      <div class="gallery-thumbs">
        <?php foreach ($product['images'] as $img): ?>
        <div class="gallery-thumb <?= $img['is_primary'] ? 'active' : '' ?>" onclick="switchImage('<?= e($img['image_path']) ?>', this)">
          <img src="<?= e($img['image_path']) ?>" alt="<?= e($img['alt_text'] ?: $product['name']) ?>" loading="lazy">
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div class="product-info">
      <?php if ($product['brand_name']): ?>
      <div class="product-info-brand"><a href="/brand/<?= e($product['brand_slug']) ?>"><?= e($product['brand_name']) ?></a></div>
      <?php endif; ?>
      <h1 class="product-info-title"><?= e($product['name']) ?></h1>

      <div class="product-info-rating">
        <div class="stars"><?= star_rating($product['average_rating']) ?></div>
        <a href="#reviews" style="font-size:0.85rem;color:var(--color-text-muted)"><?= $product['review_count'] ?> reviews</a>
        <span class="product-info-sku">SKU: <?= e($product['sku']) ?></span>
      </div>

      <!-- price + stock rendered dynamically by pvRender() below -->

      <?php if ($product['short_description']): ?>
      <p style="color:var(--color-text-muted);font-size:0.92rem;margin-bottom:20px"><?= e($product['short_description']) ?></p>
      <?php endif; ?>

      <?php if (!empty($product['shisha_weight'])): ?>
      <div style="background:var(--color-bg-light);border-radius:var(--radius);padding:10px 14px;font-size:0.88rem;margin-bottom:12px;display:flex;align-items:center;gap:8px">
        <i class="fas fa-weight-hanging" style="color:var(--color-secondary)"></i>
        <strong>Weight:</strong> <?= e($product['shisha_weight']) ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($product['hookah_height'])): ?>
      <div style="background:var(--color-bg-light);border-radius:var(--radius);padding:10px 14px;font-size:0.88rem;margin-bottom:12px;display:flex;align-items:center;gap:8px">
        <i class="fas fa-ruler-vertical" style="color:var(--color-secondary)"></i>
        <strong>Height:</strong> <?= e($product['hookah_height']) ?>
      </div>
      <?php endif; ?>

      <!-- Variation Selectors (new system) -->
      <div id="pvSelectors"></div>

      <!-- Price + stock area -->
      <div id="pvPriceArea" style="margin-bottom:16px">
        <div id="pvPriceWrap" style="display:none">
          <div class="product-info-price">
            <span class="price" id="productPrice"></span>
          </div>
          <div id="pvStockStatus" class="stock-status in-stock" style="margin-bottom:12px">
            <i class="fas fa-circle" style="font-size:0.5rem"></i> <span id="pvStockLabel"></span>
          </div>
        </div>
        <div id="pvSelectPrompt" style="font-size:0.9rem;color:var(--color-text-muted);margin-bottom:16px;padding:10px 14px;background:var(--color-bg-light);border-radius:var(--radius);border:1px dashed var(--color-border)">
          <i class="fas fa-hand-pointer" style="color:var(--color-secondary)"></i>
          Select options above to see price.
        </div>
      </div>

      <!-- Qty + Buttons -->
      <div class="add-to-cart-row" style="flex-wrap:wrap;gap:10px">
        <div class="qty-selector">
          <button class="qty-btn" onclick="changeQty(-1)">−</button>
          <input type="number" class="qty-input" id="productQty" value="1" min="1" max="99">
          <button class="qty-btn" onclick="changeQty(1)">+</button>
        </div>
        <button class="btn btn-primary btn-lg" id="addToCartBtn" onclick="pvAddToCart(false)">
          <i class="fas fa-shopping-bag"></i> Add to Cart
        </button>
        <button class="btn btn-lg" id="buyNowBtn" onclick="pvAddToCart(true)"
          style="background:var(--color-primary);color:#fff;border:2px solid var(--color-primary)">
          <i class="fas fa-bolt"></i> Buy Now
        </button>
        <button class="btn btn-outline wishlist-btn <?= $in_wishlist ? 'active' : '' ?>" onclick="toggleWishlist(<?= $_mainProduct['id'] ?>, this)" title="Wishlist">
          <i class="fas fa-heart"></i>
        </button>
      </div>

      <!-- After-add panel -->
      <div id="detailAfterAdd" style="display:none;margin-top:16px;padding:16px;background:rgba(200,150,60,0.06);border:1.5px solid rgba(200,150,60,0.3);border-radius:var(--radius-lg)">
        <p style="font-size:0.85rem;font-weight:600;margin-bottom:10px">✓ Added to cart:</p>
        <div id="detailAddedList" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px"></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <button onclick="document.getElementById('detailAfterAdd').style.display='none';document.getElementById('detailAddedList').innerHTML=''" style="flex:1;min-width:120px;padding:10px;border:2px solid #1A1A2E;border-radius:6px;background:#fff;cursor:pointer;font-weight:600;font-size:0.88rem"><i class="fas fa-plus"></i> Add Another Option</button>
          <button onclick="document.getElementById('cartDrawer').classList.add('open');document.getElementById('cartOverlay').classList.add('show');document.body.style.overflow='hidden'" style="flex:1;min-width:120px;padding:10px;border:none;border-radius:6px;background:#C8963C;color:#fff;cursor:pointer;font-weight:600;font-size:0.88rem"><i class="fas fa-shopping-bag"></i> View Cart</button>
          <a href="/checkout" style="flex:1;min-width:120px;padding:10px;border:2px solid #1A1A2E;border-radius:6px;background:#1A1A2E;color:#fff;cursor:pointer;font-weight:600;font-size:0.88rem;text-align:center;text-decoration:none"><i class="fas fa-bolt"></i> Checkout</a>
        </div>
      </div>

      <?php if ($product['reward_points'] > 0): ?>
      <div style="background:rgba(200,150,60,0.08);border:1px solid rgba(200,150,60,0.2);border-radius:var(--radius);padding:10px 14px;font-size:0.85rem;margin-top:16px">
        <i class="fas fa-star" style="color:var(--color-secondary)"></i> Earn <strong><?= $product['reward_points'] ?> reward points</strong> with this purchase
      </div>
      <?php endif; ?>

      <div style="border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:16px;font-size:0.88rem;margin-top:16px">
        <div style="display:flex;gap:12px;margin-bottom:10px"><i class="fas fa-shipping-fast" style="color:var(--color-secondary);margin-top:2px"></i><div><strong>Free Standard Delivery</strong><br><span style="color:var(--color-text-muted)">On orders over AED 100</span></div></div>
        <div style="display:flex;gap:12px"><i class="fas fa-bolt" style="color:var(--color-secondary);margin-top:2px"></i><div><strong>1-Hour Express Delivery</strong><br><span style="color:var(--color-text-muted)">Available in Dubai — AED 25</span></div></div>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="product-tabs">
    <div class="tabs">
      <button class="tab-btn active" onclick="switchTab('description', this)">Description</button>
      <button class="tab-btn" onclick="switchTab('specifications', this)">Specifications</button>
      <button class="tab-btn" onclick="switchTab('reviews', this)">Reviews (<?= $product['review_count'] ?>)</button>
      <button class="tab-btn" onclick="switchTab('shipping', this)">Shipping Info</button>
    </div>
    <div class="tab-content active" id="tab-description">
      <?php if ($product['description']): ?>
      <div style="line-height:1.8;color:var(--color-text-muted)"><?= $product['description'] ?></div>
      <?php else: ?><p style="color:var(--color-text-muted)">No description available.</p><?php endif; ?>
    </div>
    <div class="tab-content" id="tab-specifications">
      <?php if (!empty($product['attributes']) || $product['nicotine_content_mg'] || $product['puff_count'] || $product['volume_ml']): ?>
      <table class="specs-table">
        <?php foreach ($product['attributes'] as $attr): ?>
        <tr><td><?= e($attr['attribute_name']) ?></td><td><?= e($attr['attribute_value']) ?></td></tr>
        <?php endforeach; ?>
        <?php if ($product['nicotine_content_mg']): ?><tr><td>Nicotine</td><td><?= $product['nicotine_content_mg'] ?>mg</td></tr><?php endif; ?>
        <?php if ($product['puff_count']): ?><tr><td>Puff Count</td><td><?= number_format($product['puff_count']) ?></td></tr><?php endif; ?>
        <?php if ($product['volume_ml']): ?><tr><td>Volume</td><td><?= $product['volume_ml'] ?>ml</td></tr><?php endif; ?>
        <?php if ($product['shisha_weight']): ?><tr><td>Weight</td><td><?= e($product['shisha_weight']) ?></td></tr><?php endif; ?>
        <?php if ($product['hookah_height']): ?><tr><td>Height</td><td><?= e($product['hookah_height']) ?></td></tr><?php endif; ?>
        <?php if ($product['flavor_profile']): ?><tr><td>Flavor</td><td><?= e($product['flavor_profile']) ?></td></tr><?php endif; ?>
      </table>
      <?php else: ?><p style="color:var(--color-text-muted)">No specifications available.</p><?php endif; ?>
    </div>
    <div class="tab-content" id="tab-reviews">
      <div id="reviews">
        <?php if (!empty($reviews['items'])): ?>
        <?php foreach ($reviews['items'] as $review): ?>
        <div class="review-card" style="margin-bottom:16px">
          <div class="review-header">
            <div class="reviewer-avatar"><?= strtoupper(substr($review['first_name'], 0, 1)) ?></div>
            <div><div class="reviewer-name"><?= e($review['first_name'].' '.substr($review['last_name'],0,1)) ?>.</div><div class="reviewer-date"><?= format_date($review['created_at']) ?></div></div>
            <div class="stars" style="margin-left:auto"><?= star_rating($review['rating']) ?></div>
          </div>
          <?php if ($review['title']): ?><strong style="font-size:0.9rem"><?= e($review['title']) ?></strong><?php endif; ?>
          <p class="review-text"><?= e($review['body']) ?></p>
          <?php if ($review['is_verified_purchase']): ?><span class="badge badge-success" style="font-size:0.72rem">✓ Verified Purchase</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php else: ?><p style="color:var(--color-text-muted)">No reviews yet. Be the first!</p><?php endif; ?>
        <?php if (is_logged_in()): ?>
        <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--color-border)">
          <h4 style="margin-bottom:16px">Write a Review</h4>
          <form onsubmit="submitReview(event, <?= $product['id'] ?>)">
            <div class="form-group">
              <label class="form-label">Rating</label>
              <div id="starRating" style="display:flex;gap:8px;font-size:1.5rem;cursor:pointer">
                <?php for ($i=1;$i<=5;$i++): ?><i class="far fa-star" onclick="setRating(<?= $i ?>)" style="color:var(--color-secondary)"></i><?php endfor; ?>
              </div>
              <input type="hidden" id="ratingValue" value="0">
            </div>
            <div class="form-group"><label class="form-label">Title</label><input type="text" id="reviewTitle" class="form-control" placeholder="Summary"></div>
            <div class="form-group"><label class="form-label">Review</label><textarea id="reviewBody" class="form-control" rows="4" placeholder="Share your experience..."></textarea></div>
            <button type="submit" class="btn btn-primary">Submit Review</button>
          </form>
        </div>
        <?php else: ?>
        <p style="margin-top:16px"><a href="/login" style="color:var(--color-secondary)">Login</a> to write a review.</p>
        <?php endif; ?>
      </div>
    </div>
    <div class="tab-content" id="tab-shipping">
      <div style="line-height:1.8">
        <p><strong>Standard Delivery:</strong> 1-2 business days. Free on orders over AED 100.</p>
        <p><strong>Express 1-Hour:</strong> Dubai only. AED 25 flat. Orders before 10PM.</p>
        <p><strong>Next Day:</strong> All UAE. AED 20 flat. Orders before 5PM.</p>
      </div>
    </div>
  </div>

  <?php if (!empty($related)): ?>
  <?php $componentPath = dirname(__DIR__, 2) . '/components'; ?>
  <div style="margin-top:48px">
    <h2 class="section-title" style="margin-bottom:24px">You May Also Like</h2>
    <div class="grid grid-4">
      <?php foreach ($related as $_relProduct): ?>
      <?php $product = $_relProduct; include $componentPath . '/product-card.php'; ?>
      <?php endforeach; ?>
      <?php unset($product, $_relProduct); ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
.pv-group{margin-bottom:18px}
.pv-group-label{font-weight:700;font-size:0.88rem;margin-bottom:8px;display:flex;align-items:center;gap:8px}
.pv-group-label span.pv-selected-val{font-weight:400;color:var(--color-secondary);font-size:0.82rem}
.pv-pills{display:flex;flex-wrap:wrap;gap:8px}
.pv-pill{padding:7px 16px;border:2px solid var(--color-border);border-radius:20px;font-size:0.83rem;font-weight:600;cursor:pointer;background:#fff;transition:all .15s;position:relative;user-select:none}
.pv-pill:hover:not(.pv-pill-oos):not(.pv-pill-active){border-color:var(--color-secondary);color:var(--color-secondary)}
.pv-pill-active{border-color:var(--color-secondary);background:var(--color-secondary);color:#fff}
.pv-pill-oos{border-color:#e5e7eb;color:#9ca3af;cursor:default;background:#f9fafb}
.pv-pill-oos .pv-oos-tag{font-size:0.68rem;font-weight:400;display:block;margin-top:1px}
.pv-group-error .pv-group-label{color:#dc2626}
.pv-group-error .pv-pills{border:2px dashed #dc2626;border-radius:10px;padding:8px}
.pv-error-msg{font-size:0.8rem;color:#dc2626;margin-top:6px;display:flex;align-items:center;gap:4px}
</style>

<script>
const _detailProductId = <?= (int)$_mainProduct['id'] ?>;
const _detailBasePrice = <?= (float)$_mainProduct['price'] ?>;

// ══ Variation state ══
let _pvTypes    = [];   // [{id, type_name, display_order, options:[{id,option_value}]}]
let _pvCombos   = [];   // [{id, sku, price, stock, option_id_level1..5, val_level1..5}]
let _pvSelected = [];   // selected option_value per level, e.g. ['6mg', '5000 puff', null]
let _pvCombo    = null; // currently matched combination

// ══ Boot: fetch variations on page load ══
document.addEventListener('DOMContentLoaded', async () => {
    if (!_detailProductId) return;
    try {
        const res  = await fetch(`/api/products/${_detailProductId}/variations`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.success && data.variation_types?.length) {
            _pvTypes  = data.variation_types;
            _pvCombos = data.combinations;
            _pvSelected = _pvTypes.map(() => null);
            pvRender();
        } else {
            // No new-system variations — show base price
            pvShowBasePrice();
        }
    } catch(e) {
        pvShowBasePrice();
    }
});

// Show base product price when no variation system
function pvShowBasePrice() {
    const el = document.getElementById('productPrice');
    if (el) el.textContent = 'AED ' + _detailBasePrice.toFixed(2);
    document.getElementById('pvPriceWrap')?.style && (document.getElementById('pvPriceWrap').style.display = '');
    document.getElementById('pvSelectPrompt')?.style && (document.getElementById('pvSelectPrompt').style.display = 'none');
    document.getElementById('pvStockLabel') && (document.getElementById('pvStockLabel').textContent = 'In Stock');
    document.getElementById('addToCartBtn') && (document.getElementById('addToCartBtn').disabled = false);
    document.getElementById('buyNowBtn')    && (document.getElementById('buyNowBtn').disabled    = false);
}

// ══ Render all selector groups ══
function pvRender(markErrors = false) {
    const wrap = document.getElementById('pvSelectors');
    if (!wrap) return;
    wrap.innerHTML = '';
    let firstError = null;

    _pvTypes.forEach((type, level) => {
        if (level > 0 && _pvSelected[level - 1] === null) return;

        const available = pvAvailableOptions(level);
        if (!available.length) return;

        const group = document.createElement('div');
        group.className = 'pv-group';
        group.id = 'pvGroup_' + level;

        const selVal = _pvSelected[level];
        const isError = markErrors && selVal === null;
        if (isError) group.classList.add('pv-group-error');

        group.innerHTML = `<div class="pv-group-label">
            <i class="fas fa-${isError ? 'exclamation-circle' : 'check-circle'}" style="color:${isError ? '#dc2626' : (selVal ? 'var(--color-secondary)' : 'var(--color-border)')};font-size:0.85rem"></i>
            ${escPv(type.type_name)}
            ${selVal ? `<span class="pv-selected-val">— ${escPv(selVal)}</span>` : ''}
        </div><div class="pv-pills" id="pvPills_${level}"></div>
        ${isError ? `<div class="pv-error-msg"><i class="fas fa-exclamation-triangle"></i> Please select a ${escPv(type.type_name)}</div>` : ''}`;

        const pillsWrap = group.querySelector(`#pvPills_${level}`);
        available.forEach(opt => {
            const isActive = selVal === opt.value;
            const isOos    = opt.totalStock === 0;
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.className = 'pv-pill' + (isActive ? ' pv-pill-active' : '') + (isOos ? ' pv-pill-oos' : '');
            pill.innerHTML = escPv(opt.value) + (isOos ? `<span class="pv-oos-tag">Out of stock</span>` : '');
            if (!isOos) {
                pill.onclick = () => pvSelect(level, opt.value);
            }
            pillsWrap.appendChild(pill);
        });

        wrap.appendChild(group);
        if (isError && !firstError) firstError = group;
    });

    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    pvUpdatePriceArea();
}

// ══ Get available options for a level given current selections ══
function pvAvailableOptions(level) {
    // Filter combos that match all selections above this level
    const filtered = _pvCombos.filter(c => {
        for (let l = 0; l < level; l++) {
            if (_pvSelected[l] !== null && c['val_level' + (l+1)] !== _pvSelected[l]) return false;
        }
        return true;
    });

    // Extract unique option values at this level
    const seen = {};
    filtered.forEach(c => {
        const val = c['val_level' + (level + 1)];
        if (!val) return;
        if (!seen[val]) seen[val] = 0;
        seen[val] += (c.stock || 0);
    });

    return Object.entries(seen).map(([value, totalStock]) => ({ value, totalStock }));
}

// ══ Handle pill selection ══
function pvSelect(level, value) {
    _pvSelected[level] = value;
    for (let l = level + 1; l < _pvSelected.length; l++) _pvSelected[l] = null;
    _pvCombo = null;
    const lastLevel = _pvTypes.length - 1;
    if (level === lastLevel || pvIsFullySelected()) _pvCombo = pvFindCombo();
    pvRender(false); // clear errors on selection
}

// Check if all levels are selected
function pvIsFullySelected() {
    return _pvSelected.every(v => v !== null);
}

// Find the exact combination matching current selections
function pvFindCombo() {
    return _pvCombos.find(c => {
        return _pvSelected.every((val, i) => {
            if (val === null) return true;
            return c['val_level' + (i+1)] === val;
        });
    }) || null;
}

// ══ Update price + stock area ══
function pvUpdatePriceArea() {
    const priceWrap  = document.getElementById('pvPriceWrap');
    const prompt     = document.getElementById('pvSelectPrompt');
    const priceEl    = document.getElementById('productPrice');
    const stockEl    = document.getElementById('pvStockLabel');
    const stockWrap  = document.getElementById('pvStockStatus');
    const addBtn     = document.getElementById('addToCartBtn');
    const buyBtn     = document.getElementById('buyNowBtn');
    const qtyInput   = document.getElementById('productQty');

    const fullySelected = pvIsFullySelected();
    const combo = fullySelected ? pvFindCombo() : null;
    _pvCombo = combo;

    if (!fullySelected || !combo) {
        priceWrap.style.display  = 'none';
        prompt.style.display     = '';
        return;
    }

    // Show price
    priceWrap.style.display = '';
    prompt.style.display    = 'none';
    priceEl.textContent     = 'AED ' + parseFloat(combo.price).toFixed(2);

    // Show stock
    const stock = parseInt(combo.stock) || 0;
    if (stock > 0) {
        stockWrap.className     = 'stock-status in-stock';
        stockEl.textContent     = stock <= 5 ? `Only ${stock} left!` : `In Stock (${stock} available)`;
        if (qtyInput) qtyInput.max = stock;
    } else {
        stockWrap.className     = 'stock-status out-of-stock';
        stockEl.textContent     = 'Out of Stock';
        if (addBtn) addBtn.disabled = true;
        if (buyBtn) buyBtn.disabled = true;
    }
}

// ══ Add to cart using selected combination ══
async function pvAddToCart(buyNow) {
    const hasVariants = _pvTypes.length > 0;
    if (hasVariants && !_pvCombo) {
        pvRender(true); // show red errors + scroll
        return;
    }
    if (hasVariants && parseInt(_pvCombo.stock) <= 0) { showToast('This combination is out of stock', 'error'); return; }

    const qty   = parseInt(document.getElementById('productQty')?.value) || 1;
    const btnId = buyNow ? 'buyNowBtn' : 'addToCartBtn';
    const btn   = document.getElementById(btnId);
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }

    try {
        const res = await fetch('/api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-Token':     document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                product_id:     _detailProductId,
                combination_id: _pvCombo ? _pvCombo.id : null,
                variant_id:     null,
                qty,
                flavour_names:  _pvSelected.filter(Boolean).join(' / ')
            })
        });
        const data = await res.json();
        if (data.success) {
            if (buyNow) { window.location.href = '/checkout'; return; }
            showToast('Added to cart!', 'success');
            if (typeof updateCartBadge   === 'function') updateCartBadge(data.cart_count);
            if (typeof refreshCartDrawer === 'function') await refreshCartDrawer();
            // Show after-add panel
            const label = _pvSelected.filter(Boolean).join(' / ') || 'Item';
            const panel = document.getElementById('detailAfterAdd');
            const list  = document.getElementById('detailAddedList');
            if (panel && list) {
                list.innerHTML += `<span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:rgba(200,150,60,0.12);border:1px solid #C8963C;border-radius:20px;font-size:0.8rem;font-weight:600"><i class="fas fa-check" style="color:#C8963C;font-size:0.7rem"></i>${label}</span>`;
                panel.style.display = 'block';
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        } else {
            showToast(data.error || 'Could not add to cart', 'error');
        }
    } catch(e) { showToast('Network error', 'error'); }
    finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = buyNow ? '<i class="fas fa-bolt"></i> Buy Now' : '<i class="fas fa-shopping-bag"></i> Add to Cart';
        }
    }
}

// ══ Utility ══
function escPv(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function changeQty(delta) {
    const input = document.getElementById('productQty');
    const max   = parseInt(input.max) || 99;
    input.value = Math.max(1, Math.min(max, parseInt(input.value) + delta));
}
function switchImage(src, thumb) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}
function setRating(val) {
    document.getElementById('ratingValue').value = val;
    document.querySelectorAll('#starRating i').forEach((s, i) => {
        s.className = i < val ? 'fas fa-star' : 'far fa-star';
    });
}
async function submitReview(e, productId) {
    e.preventDefault();
    const rating = parseInt(document.getElementById('ratingValue').value);
    if (!rating) { showToast('Please select a rating', 'error'); return; }
    const res = await fetch('/api/reviews', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: JSON.stringify({ product_id: productId, rating, title: document.getElementById('reviewTitle').value, body: document.getElementById('reviewBody').value })
    });
    const data = await res.json();
    showToast(data.success ? data.message : (data.error || 'Error'), data.success ? 'success' : 'error');
}
</script>
