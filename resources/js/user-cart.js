// Confirm modal + toast (no alerts), remove item, and refresh DOM.
// Uses Sanctum personal access token (Bearer) like product preview page.

// ----------------------- App config helpers -----------------------
const getMeta_uc = (name) => document.querySelector(`meta[name="${name}"]`)?.content || '';
const APP_UC = {
  baseUrl: (window.__APP?.baseUrl ||
            getMeta_uc('app:base-url') ||
            document.querySelector('base')?.href ||
            window.location.origin).replace(/\/+$/, ''),
  csrf: window.__APP?.csrf || getMeta_uc('csrf-token'),
  isAuth: !!(window.__APP?.isAuth ?? (getMeta_uc('app:is-auth') === '1')),
  registerUrl: window.__APP?.registerUrl || getMeta_uc('app:register-url') || '/register',
};
const api_uc = (p) => `${APP_UC.baseUrl}${p.startsWith('/') ? p : '/' + p}`;

// ----------------------- Bearer token helpers -----------------------
async function ensureCsrf_uc() {
  if (!document.cookie.includes('XSRF-TOKEN=')) {
    await fetch(api_uc('sanctum/csrf-cookie'), { credentials: 'include' });
  }
}
async function mintCustomerToken_uc() {
  await ensureCsrf_uc();
  const res = await fetch(api_uc('/user/api-token'), {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': APP_UC.csrf,
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
async function getCustomerBearer_uc() {
  if (window.__customerBearer) return window.__customerBearer;
  return await mintCustomerToken_uc();
}
/** authedFetch — attaches Authorization: Bearer <token>, retries once on 401 */
async function authedFetch_uc(input, init = {}, retry = true) {
  const token = await getCustomerBearer_uc();
  const headers = new Headers(init.headers || {});
  headers.set('Authorization', `Bearer ${token}`);
  headers.set('X-Requested-With', 'XMLHttpRequest');
  headers.set('Accept', headers.get('Accept') || 'application/json');

  const res = await fetch(input, { ...init, headers, cache: 'no-store' });
  if (res.status === 401 && retry) {
    window.__customerBearer = null; // rotate
    await mintCustomerToken_uc();
    return authedFetch_uc(input, init, /* retry */ false);
  }
  return res;
}

// expose for console if not defined elsewhere
window.getCustomerBearer = window.getCustomerBearer || getCustomerBearer_uc;

// ----------------------- Page init -----------------------
document.addEventListener('DOMContentLoaded', () => {
  // nothing needed on load; functions are global
});

// ----------------------- Remove item (uses Bearer) -----------------------
window.removeCartItem = async function (id) {
  const ok = await confirmBox(
    'Remove item?',
    'This will remove the item from your bag.',
    'Remove',
    'Cancel'
  );
  if (!ok) return;

  try {
    const res = await authedFetch_uc(api_uc(`/api/cart/${id}`), { method: 'DELETE' });

    if (res.status === 401) { toast('Please sign in to manage your bag.', 'error'); return; }
    if (!res.ok) throw new Error('Remove failed');

    const data = await res.json();

    // Update nav badge + cross-tab
    const newCount = Number(data?.count ?? 0);
    if (typeof window.broadcastCartCount === 'function') {
      window.broadcastCartCount(newCount);
    }
    document.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: newCount } }));

    // If we're on the cart page, refresh the whole page (keeps totals/taxes honest)
    if (document.getElementById('cartItems')) {
      toast('Item removed from your bag.', 'success');
      setTimeout(() => location.reload(), 250);
      return;
    }

    // Elsewhere, refresh the DOM block
    toast('Item removed from your bag.', 'success');
    await refreshCartDom();
  } catch (e) {
    console.error(e);
    toast('Sorry, something went wrong while removing the item.', 'error');
  }
};

// ----------------------- Refresh DOM after changes -----------------------
async function refreshCartDom() {
  const full = await fetchJsonAuthed_uc('/api/cart/full');

  // Items
  const wrap = document.getElementById('cartItems');
  if (wrap) {
    if (!full.items.length) {
      wrap.innerHTML =
        `<div class="p-6 text-gray-600 text-sm border rounded-lg">Your bag is empty.</div>`;
    } else {
      wrap.innerHTML = full.items.map(it => `
        <div class="border rounded-lg grid grid-cols-12">
          <div class="col-span-12 sm:col-span-3 bg-gray-100 p-4 flex items-center justify-center">
            <img src="${escapeHtml_uc(it.img || '')}" alt="" class="max-h-32 object-contain">
          </div>
          <div class="col-span-12 sm:col-span-9 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="font-semibold text-lg leading-6">${escapeHtml_uc(it.name)}</div>
                <div class="text-gray-600 text-sm mt-1">SIZE: ${escapeHtml_uc(it.size)}</div>
                <div class="text-gray-600 text-sm">QTY: ${Number(it.quantity)}</div>
              </div>
              <div class="text-right">
                <div class="font-semibold">$${Number(it.price).toFixed(2)}</div>
                <button onclick="removeCartItem(${it.id})"
                        class="mt-2 text-gray-600 hover:text-black"
                        title="Remove" aria-label="Remove">✕</button>
              </div>
            </div>
          </div>
        </div>
      `).join('');
    }
  }

  // Summary
  const box = document.getElementById('orderSummary');
  if (box) {
    const dl = box.querySelector('dl');
    if (dl) {
      dl.innerHTML = `
        <div class="flex items-center justify-between">
          <dt>${full.count} ${full.count === 1 ? 'item' : 'items'}</dt>
          <dd>$${Number(full.subtotal).toFixed(2)}</dd>
        </div>
        <div class="flex items-center justify-between">
          <dt>Sales Tax*</dt>
          <dd>$${Number(full.tax).toFixed(2)}</dd>
        </div>
        <div class="flex items-center justify-between">
          <dt>Delivery</dt>
          <dd>Free</dd>
        </div>
        <div class="pt-2 border-t flex items-center justify-between font-semibold">
          <dt>Total</dt>
          <dd>$${Number(full.total).toFixed(2)}</dd>
        </div>`;
    }
  }
}

// ----------------------- Small helpers -----------------------
async function fetchJsonAuthed_uc(path) {
  const res = await authedFetch_uc(api_uc(path));
  if (!res.ok) throw new Error('Network error');
  return res.json();
}
function confirmBox(title, message, okText = 'OK', cancelText = 'Cancel') {
  return new Promise(resolve => {
    let overlay = document.getElementById('x-confirm');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'x-confirm';
      overlay.className = 'fixed inset-0 z-50 hidden';
      overlay.innerHTML = `
        <div id="x-bg" class="absolute inset-0 bg-black/40"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
          <div class="w-full max-w-md bg-white rounded-lg shadow-xl p-6 text-center">
            <h3 id="x-title" class="text-lg font-semibold mb-2"></h3>
            <p id="x-msg" class="text-sm text-gray-600 mb-6"></p>
            <div class="flex justify-center gap-2">
              <button id="x-cancel" class="px-3 py-2 rounded-md border border-gray-300 text-sm hover:bg-gray-50">${cancelText}</button>
              <button id="x-ok" class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:bg-red-700">${okText}</button>
            </div>
          </div>
        </div>`;
      document.body.appendChild(overlay);
    }

    overlay.querySelector('#x-title').textContent = title;
    overlay.querySelector('#x-msg').textContent = message;
    overlay.classList.remove('hidden');

    const okBtn = overlay.querySelector('#x-ok');
    const cancelBtn = overlay.querySelector('#x-cancel');
    const bg = overlay.querySelector('#x-bg');

    const close = (val) => {
      overlay.classList.add('hidden');
      okBtn.removeEventListener('click', onOk);
      cancelBtn.removeEventListener('click', onCancel);
      bg.removeEventListener('click', onCancel);
      document.removeEventListener('keydown', onKey);
      resolve(val);
    };
    const onOk = () => close(true);
    const onCancel = () => close(false);
    const onKey = (e) => {
      if (e.key === 'Escape') close(false);
      if (e.key === 'Enter') close(true);
    };

    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
    bg.addEventListener('click', onCancel);
    document.addEventListener('keydown', onKey);
    okBtn.focus();
  });
}
function toast(msg, variant = 'info', ms = 2200) {
  let c = document.getElementById('x-toast');
  if (!c) {
    c = document.createElement('div');
    c.id = 'x-toast';
    c.className =
      'fixed top-4 left-1/2 -translate-x-1/2 z-[60] flex flex-col items-center gap-2 w-full max-w-md pointer-events-none';
    document.body.appendChild(c);
  }
  const tone =
    variant === 'success'
      ? 'bg-green-50 text-green-800 border-green-200'
      : variant === 'error'
      ? 'bg-red-50 text-red-800 border-red-200'
      : 'bg-blue-50 text-blue-800 border-blue-200';

  const t = document.createElement('div');
  t.className = `pointer-events-auto rounded-md shadow px-4 py-3 text-sm border ${tone} text-center`;
  t.textContent = msg;
  t.style.transition = 'opacity .2s, transform .2s';
  t.style.opacity = '0';
  t.style.transform = 'translateY(4px)';

  c.appendChild(t);
  requestAnimationFrame(() => {
    t.style.opacity = '1';
    t.style.transform = 'translateY(0)';
  });
  setTimeout(() => {
    t.style.opacity = '0';
    t.style.transform = 'translateY(4px)';
    setTimeout(() => t.remove(), 200);
  }, ms);
}
function escapeHtml_uc(s) {
  return String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
}
