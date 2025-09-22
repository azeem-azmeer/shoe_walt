// -------- App config --------
const getMeta_w = (name) => document.querySelector(`meta[name="${name}"]`)?.content || '';
const APP_W = {
  baseUrl: (window.__APP?.baseUrl ||
            getMeta_w('app:base-url') ||
            document.querySelector('base')?.href ||
            window.location.origin).replace(/\/+$/, ''),
  csrf: window.__APP?.csrf || getMeta_w('csrf-token'),
  isAuth: !!(window.__APP?.isAuth ?? (getMeta_w('app:is-auth') === '1')),
  registerUrl: window.__APP?.registerUrl || getMeta_w('app:register-url') || '/register',
};
const api_w = (p) => `${APP_W.baseUrl}${p.startsWith('/') ? p : '/' + p}`;

// -------- CSRF (only once to mint) --------
async function ensureCsrf_w() {
  if (!document.cookie.includes('XSRF-TOKEN=')) {
    await fetch(api_w('sanctum/csrf-cookie'), { credentials: 'include' });
  }
}

// -------- Bearer helpers --------
async function mintCustomerToken_w() {
  await ensureCsrf_w();
  const res = await fetch(api_w('/user/api-token'), {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': APP_W.csrf,
      'Accept': 'application/json',
    },
    credentials: 'include',
  });
  if (!res.ok) {
    let msg = 'Failed to mint customer token';
    try { msg = (await res.json())?.message || msg; } catch {}
    throw new Error(msg);
  }
  const data = await res.json();
  window.__customerBearer = data?.token;
  return window.__customerBearer;
}
async function getCustomerBearer_w() {
  if (window.__customerBearer) return window.__customerBearer;
  return await mintCustomerToken_w();
}
async function authedFetch_w(input, init = {}, retry = true) {
  const token = await getCustomerBearer_w();
  const headers = new Headers(init.headers || {});
  headers.set('Authorization', `Bearer ${token}`);
  headers.set('X-Requested-With', 'XMLHttpRequest');
  headers.set('Accept', headers.get('Accept') || 'application/json');

  const res = await fetch(input, { ...init, headers, cache: 'no-store' });

  if ((res.status === 401 || res.status === 403) && retry) {
    // rotate and retry once
    window.__customerBearer = null;
    try { localStorage.removeItem('customerBearer'); localStorage.removeItem('customerBearer:ts'); } catch {}
    await mintCustomerToken_w();
    return authedFetch_w(input, init, false);
  }
  return res;
}


// Expose for debugging if desired
window.getCustomerBearer = window.getCustomerBearer || getCustomerBearer_w;

// -------- Public handler used by Blade: onclick="removeFromWishlist(id)" --------
window.removeFromWishlist = async function removeFromWishlist(id) {
  try {
    const res = await authedFetch_w(api_w(`api/wishlist/${id}`), { method: 'DELETE' });

    if (res.status === 401) {
      window.location.assign(APP_W.registerUrl);
      return;
    }
    if (!res.ok) {
      alert('Could not remove.');
      return;
    }

    const data = await res.json();

    // Remove the card
    const card = document.querySelector(`[data-wish-id="${id}"]`);
    if (card) card.remove();

    // Update badge + broadcast
    const n = Number(data?.count ?? 0);
    if (typeof window.broadcastWishlistCount === 'function') {
      window.broadcastWishlistCount(n);
    }
    const badge = document.getElementById('wishlist-count');
    if (badge) {
      badge.textContent = String(n);
      if ('hidden' in badge) badge.hidden = n <= 0;
      else badge.classList.toggle('hidden', n <= 0);
    }
    try {
      localStorage.setItem('wishlist:changed', JSON.stringify({ ts: Date.now(), count: n }));
    } catch {}
    document.dispatchEvent(new CustomEvent('wishlist:updated', { detail: { count: n } }));

    // If empty, refresh to show the "empty" message
    if (document.querySelectorAll('[data-wish-id]').length === 0) {
      location.reload();
    }
  } catch (e) {
    console.error(e);
    alert('Something went wrong.');
  }
};
