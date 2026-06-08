// search.js — Live search autocomplete
let searchTimeout;
const searchInput    = document.getElementById('searchInput');
const searchDropdown = document.getElementById('searchDropdown');

// Move dropdown to body so it's above all stacking contexts (nav, mega menu etc.)
if (searchDropdown) {
    document.body.appendChild(searchDropdown);
}

function positionDropdown() {
    if (!searchInput || !searchDropdown) return;
    const rect = searchInput.getBoundingClientRect();
    searchDropdown.style.position   = 'fixed';
    searchDropdown.style.top        = (rect.bottom + 4) + 'px';
    searchDropdown.style.left       = rect.left + 'px';
    searchDropdown.style.width      = (searchInput.closest('.header-search')?.offsetWidth || rect.width) + 'px';
    searchDropdown.style.zIndex     = '99999';
}

if (searchInput) {
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const q = searchInput.value.trim();
        if (q.length < 2) { searchDropdown?.classList.remove('show'); return; }
        searchTimeout = setTimeout(() => fetchSearchResults(q), 300);
    });

    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim().length >= 2) {
            positionDropdown();
            searchDropdown?.classList.add('show');
        }
    });

    // Reposition on scroll/resize
    window.addEventListener('scroll', () => {
        if (searchDropdown?.classList.contains('show')) positionDropdown();
    }, { passive: true });

    window.addEventListener('resize', () => {
        if (searchDropdown?.classList.contains('show')) positionDropdown();
    }, { passive: true });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.header-search') && !e.target.closest('#searchDropdown')) {
            searchDropdown?.classList.remove('show');
        }
    });
}

async function fetchSearchResults(q) {
    try {
        const res  = await fetch(`/api/products/search?q=${encodeURIComponent(q)}&limit=8`);
        const data = await res.json();
        renderSearchResults(data.results, q);
    } catch (e) {}
}

function renderSearchResults(results, q) {
    if (!searchDropdown) return;
    if (!results.length) { searchDropdown.classList.remove('show'); return; }
    searchDropdown.innerHTML = results.map(p => `
        <a href="/product/${p.slug}" class="search-result-item">
            <img src="${p.primary_image || p.product_image || '/assets/images/placeholder.jpg'}"
                 class="search-result-img" alt="${p.name}" loading="lazy">
            <div>
                <div class="search-result-name">${p.name}</div>
                <div class="search-result-price">AED ${parseFloat(p.price).toFixed(2)}</div>
            </div>
        </a>
    `).join('') + `<a href="/search?q=${encodeURIComponent(q)}" class="search-all-link">See all results for "${q}" →</a>`;
    positionDropdown();
    searchDropdown.classList.add('show');
}
