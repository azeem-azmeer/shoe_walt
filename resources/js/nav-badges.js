// One place to keep the cart + wishlist badges in sync across the whole site.
// Requires: authenticated user (for token mint) and #cart-count / #wishlist-count in the DOM.

(function () {
  // ---------- App + URL helpers ----------
  const getMeta = (n) => document.querySelector(`meta[name="${n}"]`)?.content || '';
  const BASE = (window.__APP?.baseUrl ||
                getMeta('app:base-url') ||
                document.querySelector('base')?.href ||
                window.location.origin).replace(/\/+$/, '');
  const api = (p) => `${BASE}${p.startsWith('/') ? p : '/' + p}`;

  // ---------- CSRF helpers (for Sanctum SPA mint) ----------
  function getCookie(name) {
    const m = document.cookie.split('; ').find(r => r.startsWith(name + '='));
    return m ? decodeURIComponent(m.split('=')[1]) : null;
  }
  async function ensureCsrfCookie() {
    if (!document.cookie.includes('XSRF-TOKEN=')) {
      await fetch(api('/sanctum/csrf-cookie'), {
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      });
    }
  }

  // ---------- Bearer token (re)mint + cache ----------
  const TK = 'customerBearer';
  const TKTS = 'customerBearer:ts';
  const TTL = 30 * 60 * 1000;

  function readCached() {
    try {
      const t = localStorage.getItem(TK);
      const ts = Number(localStorage.getItem(TKTS) || 0);
      if (!t || !ts) return null;
      if (Date.now() - ts > TTL) return null;
      return t;
    } catch { return null; }
  }
  function writeCached(t) { try { localStorage.setItem(TK, t || ''); localStorage.setItem(TKTS, String(Date.now())); } catch {} }
  function clearCached()  { try { localStorage.removeItem(TK); localStorage.removeItem(TKTS); } catch {} window.__customerBearer = null; }

  async function mintLocally() {
    await ensureCsrfCookie();
    const xsrf = getCookie('XSRF-TOKEN') || '';
    const res = await fetch(api('/user/api-token'), {
      method: 'POST',
      credentials: 'include', // use web session to mint
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        // IMPORTANT: Sanctum SPA expects the cookie value here:
        'X-XSRF-TOKEN': xsrf,
      },
    });
    if (!res.ok) throw new Error('token mint failed');
    const data = await res.json().catch(() => ({}));
    if (!data?.token) throw new Error('no token in response');
    window.__customerBearer = data.token;
    writeCached(data.token);
    return data.token;
  }

  async function getBearer() {
    if (typeof window.getCustomerBearer === 'function') {
      try { return await window.getCustomerBearer(); } catch {}
    }
    if (window.__customerBearer) return window.__customerBearer;
    return readCached() || await mintLocally();
  }

  // Gate first calls so we never hit 401 on first render or pass a Promise URL
  let _ready;
  async function tokenReady() {
    if (!_ready) _ready = (async () => { try { await getBearer(); } catch {} })();
    return _ready;
  }

  async function authedFetch(input, init = {}, retry = true) {
    await tokenReady();
    const token = await getBearer();
    const headers = new Headers(init.headers || {});
    headers.set('Authorization', `Bearer ${token}`);
    headers.set('X-Requested-With', 'XMLHttpRequest');
    headers.set('Accept', headers.get('Accept') || 'application/json');

    const res = await fetch(input, { ...init, headers, cache: 'no-store' });
    if ((res.status === 401 || res.status === 403) && retry) {
      clearCached();
      await tokenReady();
      return authedFetch(input, init, false);
    }
    return res;
  }

  // ---------- Badge utilities ----------
  function setBadge(el, n) {
    if (!el) return;
    const v = Number(n) || 0;
    el.textContent = String(v);
    const hide = v <= 0;
    el.classList.toggle('hidden', hide);
    if (hide) el.setAttribute('hidden', 'hidden'); else el.removeAttribute('hidden');
  }

  async function refreshBadges() {
    try {
      await tokenReady();
      const [wishRes, cartRes] = await Promise.allSettled([
        authedFetch(api(`/api/wishlist/count?_=${Date.now()}`)),
        authedFetch(api(`/api/cart/count?_=${Date.now()}`)),
      ]);

      if (wishRes.status === 'fulfilled' && wishRes.value.ok) {
        const { count } = await wishRes.value.json().catch(() => ({ count: 0 }));
        setBadge(document.getElementById('wishlist-count'), count);
      }
      if (cartRes.status === 'fulfilled' && cartRes.value.ok) {
        const { count } = await cartRes.value.json().catch(() => ({ count: 0 }));
        setBadge(document.getElementById('cart-count'), count);
      }
    } catch { /* guest/network: ignore */ }
  }

  // Expose broadcasts (other scripts can call after add/remove)
  window.broadcastCartCount = function (count) {
    setBadge(document.getElementById('cart-count'), count);
    try { localStorage.setItem('cart:changed', JSON.stringify({ ts: Date.now(), count: Number(count) || 0 })); } catch {}
    document.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: Number(count) || 0 } }));
  };

  window.broadcastWishlistCount = function (count) {
    setBadge(document.getElementById('wishlist-count'), count);
    try { localStorage.setItem('wishlist:changed', JSON.stringify({ ts: Date.now(), count: Number(count) || 0 })); } catch {}
    document.dispatchEvent(new CustomEvent('wishlist:updated', { detail: { count: Number(count) || 0 } }));
  };

  // ---------- Wire up ----------
  document.addEventListener('DOMContentLoaded', async () => {
    await tokenReady();
    refreshBadges();
  });
  window.addEventListener('livewire:navigated', refreshBadges);
  document.addEventListener('cart:updated', refreshBadges);
  document.addEventListener('wishlist:updated', refreshBadges);

  // Cross-tab sync
  window.addEventListener('storage', (e) => {
    if (e.key === 'cart:changed' && e.newValue) {
      try { setBadge(document.getElementById('cart-count'), JSON.parse(e.newValue).count); } catch {}
    }
    if (e.key === 'wishlist:changed' && e.newValue) {
      try { setBadge(document.getElementById('wishlist-count'), JSON.parse(e.newValue).count); } catch {}
    }
  });
})();
