// admin.js — Admin panel functionality

// Prevent unhandled promise rejections from causing frame navigation errors
window.addEventListener('unhandledrejection', e => {
    console.warn('Admin unhandled rejection:', e.reason);
    e.preventDefault();
});

// Centralised admin fetch — always uses absolute origin URL
async function adminFetch(path, options = {}) {
    const url = window.location.origin + path;
    const res = await fetch(url, {
        ...options,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {})
        }
    });
    if (!res.ok && res.status === 401) {
        window.location.href = window.location.origin + '/login';
        return null;
    }
    return res;
}
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    toast.innerHTML = `<i class="fas ${icon}"></i>${message}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
document.addEventListener('DOMContentLoaded', () => {
    // Mobile sidebar toggle
    const menuBtn = document.getElementById('adminMenuBtn');
    if (menuBtn) {
        menuBtn.style.display = 'flex';
        menuBtn.addEventListener('click', () => {
            document.getElementById('adminSidebar')?.classList.toggle('open');
        });
    }

    // Bulk select
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = selectAll.checked);
            updateBulkActions();
        });
        document.querySelectorAll('.row-check').forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });
    }

    // Image preview
    document.querySelectorAll('input[type=file]').forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.getElementById(this.dataset.preview);
            if (!preview || !this.files[0]) return;
            const reader = new FileReader();
            reader.onload = e => preview.src = e.target.result;
            reader.readAsDataURL(this.files[0]);
        });
    });
});

function updateBulkActions() {
    const checked = document.querySelectorAll('.row-check:checked').length;
    const bar = document.querySelector('.bulk-actions');
    if (bar) bar.classList.toggle('show', checked > 0);
    const countEl = document.getElementById('bulkCount');
    if (countEl) countEl.textContent = checked;
}

async function archiveProduct(id) {
    if (!confirm('Archive this product? It will be hidden from customers.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/products/${id}/delete`;
    document.body.appendChild(form);
    form.submit();
}

async function restoreProduct(id) {
    if (!confirm('Restore this product to Active?')) return;
    const form = document.createElement('form');
    form.method = 'POST'; form.action = `/admin/products/${id}/restore`;
    document.body.appendChild(form); form.submit();
}

async function destroyProduct(id, name) {
    if (!confirm(`PERMANENTLY DELETE "${name || 'this product'}"? This cannot be undone.`)) return;
    const form = document.createElement('form');
    form.method = 'POST'; form.action = `/admin/products/${id}/destroy`;
    document.body.appendChild(form); form.submit();
}

async function updateOrderStatus(orderId, status) {
    const note = prompt('Add a note (optional):') || '';
    try {
        const res  = await adminFetch(`/api/admin/orders/${orderId}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status, note })
        });
        if (!res) return;
        const data = await res.json();
        if (data.success) { showToast('Order status updated', 'success'); location.reload(); }
    } catch(e) { showToast('Could not update order status', 'error'); }
}
