// cart.js — AJAX cart operations

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_csrf_token"]')?.value
        || '';
}

async function addToCart(productId, variantId, qty = 1, btn = null, flavourName = null) {
    if (btn) {
        btn.disabled = true;
        btn._orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    try {
        const res = await fetch('/api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                product_id:    parseInt(productId),
                variant_id:    variantId ? parseInt(variantId) : null,
                qty:           parseInt(qty) || 1,
                flavour_names: flavourName || ''
            })
        });
        const data = await res.json();
        if (data.success) {
            updateCartBadge(data.cart_count);
            await refreshCartDrawer();
            showToast('Added to cart!', 'success');
            const drawer  = document.getElementById('cartDrawer');
            const overlay = document.getElementById('cartOverlay');
            if (drawer && overlay) {
                drawer.classList.add('open');
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        } else {
            showToast(data.error || 'Could not add to cart', 'error');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = btn._orig || '<i class="fas fa-plus"></i>';
        }
    }
}

async function updateCartItem(itemId, qty) {
    try {
        const res = await fetch('/api/cart/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ cart_item_id: parseInt(itemId), qty: parseInt(qty) })
        });
        const data = await res.json();
        if (data.success) {
            updateCartBadge(data.cart.count);
            if (window.location.pathname === '/cart') location.reload();
            else await refreshCartDrawer();
        }
    } catch (e) { showToast('Could not update cart', 'error'); }
}

async function removeCartItem(itemId) {
    try {
        const res = await fetch('/api/cart/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ cart_item_id: parseInt(itemId) })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('cart-item-' + itemId)?.remove();
            updateCartBadge(data.cart.count);
            if (window.location.pathname === '/cart') location.reload();
            else await refreshCartDrawer();
        }
    } catch (e) { showToast('Could not remove item', 'error'); }
}

async function refreshCartDrawer() {
    try {
        const res  = await fetch('/api/cart', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (!data.success) return;
        const cart = data.cart;
        updateCartBadge(cart.count);
        const total = document.getElementById('drawerCartTotal');
        if (total) total.textContent = 'AED ' + parseFloat(cart.total).toFixed(2);

        const body = document.getElementById('cartDrawerBody');
        if (!body) return;
        if (!cart.items || cart.items.length === 0) {
            body.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-bag"></i><h3>Your cart is empty</h3><p>Add some products to get started</p></div>';
            return;
        }
        body.innerHTML = cart.items.map(item => `
            <div class="cart-item" id="cart-item-${item.id}">
                <div class="cart-item-img">
                    <img src="${item.product_image || '/assets/images/placeholder.jpg'}" alt="${item.name}" loading="lazy">
                </div>
                <div style="flex:1;min-width:0">
                    <div class="cart-item-name">${item.name}</div>
                    ${(item.variant_name || item.selected_flavours)
                        ? `<div class="cart-item-variant">${item.variant_name || item.selected_flavours}</div>`
                        : ''
                    }
                    <div class="cart-item-price">AED ${parseFloat(item.unit_price).toFixed(2)}</div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:8px">
                        <div class="qty-selector">
                            <button class="qty-btn" onclick="updateCartItem(${item.id}, ${item.quantity - 1})">−</button>
                            <input type="number" class="qty-input" value="${item.quantity}" min="1" onchange="updateCartItem(${item.id}, this.value)">
                            <button class="qty-btn" onclick="updateCartItem(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                        <a class="cart-item-remove" onclick="removeCartItem(${item.id})" style="cursor:pointer;flex-shrink:0">
                            <i class="fas fa-trash"></i> Remove
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (e) {}
}

function updateCartBadge(count) {
    document.querySelectorAll('#cartBadge,#drawerCartCount').forEach(el => el && (el.textContent = count));
}

// ── Product Card Variant Popup (new variation system, same logic as detail page) ──
let _pcp = {
    productId: null, productName: null, btn: null,
    types: [], combos: [], selected: [], combo: null, addedLabels: []
};

function _pcpEsc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function _pcpStep(step) {
    ['loading','select','after'].forEach(s => {
        const el = document.getElementById('addModal_' + s);
        if (el) el.style.display = (s === step) ? '' : 'none';
    });
}

async function openFlavourPopup(productId, productName, btn) {
    _pcp = { productId: parseInt(productId), productName, btn, types: [], combos: [], selected: [], combo: null, addedLabels: [] };

    const modal = document.getElementById('addModal');
    if (!modal) { addToCart(productId, null, 1, btn); return; }

    document.getElementById('addModalTitle').textContent = productName;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    _pcpStep('loading');

    try {
        const res  = await fetch(`/api/products/${productId}/variations`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.success && data.variation_types?.length) {
            _pcp.types  = data.variation_types;
            _pcp.combos = data.combinations;
            _pcp.selected = _pcp.types.map(() => null);
            _pcpRender();
            _pcpStep('select');
            return;
        }
    } catch(e) {}

    // No new-system variations — fall back to direct add
    closeAddModal();
    addToCart(productId, null, 1, btn);
}

function _pcpAvailableOptions(level) {
    const filtered = _pcp.combos.filter(c => {
        for (let l = 0; l < level; l++) {
            if (_pcp.selected[l] !== null && c['val_level' + (l+1)] !== _pcp.selected[l]) return false;
        }
        return true;
    });
    const seen = {};
    filtered.forEach(c => {
        const val = c['val_level' + (level + 1)];
        if (!val) return;
        if (!seen[val]) seen[val] = 0;
        seen[val] += (c.stock || 0);
    });
    return Object.entries(seen).map(([value, totalStock]) => ({ value, totalStock }));
}

function _pcpFindCombo() {
    return _pcp.combos.find(c =>
        _pcp.selected.every((val, i) => val === null || c['val_level' + (i+1)] === val)
    ) || null;
}

function _pcpSelect(level, value) {
    _pcp.selected[level] = value;
    for (let l = level + 1; l < _pcp.selected.length; l++) _pcp.selected[l] = null;
    _pcp.combo = _pcp.selected.every(v => v !== null) ? _pcpFindCombo() : null;
    _pcpRender(false); // clear errors on selection
}

function _pcpRender(markErrors = false) {
    const wrap = document.getElementById('addModal_selects');
    if (!wrap) return;
    wrap.innerHTML = '';
    let firstError = null;

    _pcp.types.forEach((type, level) => {
        if (level > 0 && _pcp.selected[level - 1] === null) return;
        const available = _pcpAvailableOptions(level);
        if (!available.length) return;

        const selVal = _pcp.selected[level];
        const isError = markErrors && selVal === null;

        const group = document.createElement('div');
        group.style.marginBottom = '14px';
        group.innerHTML = `<div style="font-weight:700;font-size:0.88rem;margin-bottom:8px;color:${isError ? '#dc2626' : 'inherit'}">
            ${isError ? '<i class="fas fa-exclamation-circle" style="margin-right:4px"></i>' : ''}
            ${_pcpEsc(type.type_name)}
            ${selVal ? `<span style="font-weight:400;color:#C8963C;font-size:0.82rem"> — ${_pcpEsc(selVal)}</span>` : ''}
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;${isError ? 'border:2px dashed #dc2626;border-radius:10px;padding:8px;' : ''}" id="pcpPills_${level}"></div>
        ${isError ? `<div style="font-size:0.8rem;color:#dc2626;margin-top:6px"><i class="fas fa-exclamation-triangle"></i> Please select a ${_pcpEsc(type.type_name)}</div>` : ''}`;

        const pillsWrap = group.querySelector(`#pcpPills_${level}`);
        available.forEach(opt => {
            const isActive = selVal === opt.value;
            const isOos    = opt.totalStock === 0;
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.style.cssText = `padding:7px 16px;border:2px solid ${isActive ? '#C8963C' : (isOos ? '#e5e7eb' : 'var(--color-border)')};border-radius:20px;font-size:0.83rem;font-weight:600;cursor:${isOos ? 'default' : 'pointer'};background:${isActive ? '#C8963C' : (isOos ? '#f9fafb' : '#fff')};color:${isActive ? '#fff' : (isOos ? '#9ca3af' : 'inherit')};transition:all .15s`;
            pill.innerHTML = _pcpEsc(opt.value) + (isOos ? '<span style="font-size:0.68rem;font-weight:400;display:block">Out of stock</span>' : '');
            if (!isOos) pill.onclick = () => { _pcpSelect(level, opt.value); };
            pillsWrap.appendChild(pill);
        });
        wrap.appendChild(group);
        if (isError && !firstError) firstError = group;
    });

    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    // Price preview
    const priceEl = document.getElementById('addModal_price');
    const confirmBtn = document.getElementById('addModal_confirmBtn');
    const fullySelected = _pcp.selected.every(v => v !== null);
    _pcp.combo = fullySelected ? _pcpFindCombo() : null;

    if (priceEl) {
        if (_pcp.combo) {
            priceEl.textContent = 'AED ' + parseFloat(_pcp.combo.price).toFixed(2);
            priceEl.style.display = '';
        } else {
            priceEl.style.display = 'none';
        }
    }
    if (confirmBtn) confirmBtn.disabled = _pcp.combo ? (parseInt(_pcp.combo.stock) <= 0) : false;
}

async function addModalConfirm() {
    if (!_pcp.combo) { _pcpRender(true); return; } // show red errors
    if (parseInt(_pcp.combo.stock) <= 0) { showToast('Out of stock', 'error'); return; }

    const confirmBtn = document.getElementById('addModal_confirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Adding...';

    const label = _pcp.selected.filter(Boolean).join(' / ');

    try {
        const res = await fetch('/api/cart/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({
                product_id:     _pcp.productId,
                combination_id: _pcp.combo.id,
                variant_id:     null,
                qty:            1,
                flavour_names:  label
            })
        });
        const data = await res.json();
        if (data.success) {
            updateCartBadge(data.cart_count);
            await refreshCartDrawer();
            _pcp.addedLabels.push(label || 'Item');
            const list = document.getElementById('addModal_addedList');
            if (list) {
                list.innerHTML = _pcp.addedLabels.map(l =>
                    `<span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:rgba(200,150,60,0.12);border:1px solid #C8963C;border-radius:20px;font-size:0.8rem;font-weight:600"><i class="fas fa-check" style="color:#C8963C;font-size:0.7rem"></i>${l}</span>`
                ).join('');
            }
            _pcpStep('after');
        } else {
            showToast(data.error || 'Could not add to cart', 'error');
        }
    } catch(e) { showToast('Network error', 'error'); }

    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Add to Cart';
}

function addModalAddMore() {
    _pcp.selected = _pcp.types.map(() => null);
    _pcp.combo = null;
    _pcpRender();
    _pcpStep('select');
}

function closeAddModal() {
    document.getElementById('addModal')?.classList.remove('open');
    document.body.style.overflow = '';
}

// Legacy alias
function closeFlavourModal() { closeAddModal(); }

async function toggleWishlist(productId, btn) {
    try {
        const res = await fetch('/api/wishlist/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ product_id: productId })
        });
        const data = await res.json();
        if (data.success) {
            btn?.classList.toggle('active', data.added);
            showToast(data.added ? 'Added to wishlist!' : 'Removed from wishlist', 'success');
        } else if (res.status === 401) {
            window.location.href = '/login';
        }
    } catch (e) { showToast('Could not update wishlist', 'error'); }
}
