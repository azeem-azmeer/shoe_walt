// resources/js/user-cart.js
// Confirm modal + toast (no alerts), remove item, and refresh DOM.

document.addEventListener('DOMContentLoaded', () => {
  // nothing needed on load; functions are global
});

// Public API used by the Remove (✕) button in Blade
// Only on the cart page, do a full page refresh after remove
window.removeCartItem = async function (id) {
  const ok = await confirmBox(
    'Remove item?',
    'This will remove the item from your bag.',
    'Remove',
    'Cancel'
  );
  if (!ok) return;

  try {
    await ensureCsrf();
    const res = await fetch(`/api/cart/${id}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      },
      credentials: 'include',
    });

    if (res.status === 401) { toast('Please sign in to manage your bag.', 'error'); return; }
    if (!res.ok) throw new Error('Remove failed');

    const data = await res.json();

    // Update the nav badge immediately in this and other tabs (no page refresh needed for nav)
    if (typeof window.broadcastCartCount === 'function') {
      window.broadcastCartCount(data?.count ?? 0);
    }

    // If we're on the cart page (cart.blade.php has #cartItems), refresh the whole page
    if (document.getElementById('cartItems')) {
      toast('Item removed from your bag.', 'success');
      setTimeout(() => location.reload(), 250);
      return;
    }

    // Elsewhere (if you ever call remove outside the cart page), fall back to DOM refresh
    toast('Item removed from your bag.', 'success');
    await refreshCartDom();
  } catch (e) {
    console.error(e);
    toast('Sorry, something went wrong while removing the item.', 'error');
  }
};


// ---------- Refresh DOM after changes ----------
async function refreshCartDom() {
  const full = await fetchJson('/api/cart/full');

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
            <img src="${escapeHtml(it.img || '')}" alt="" class="max-h-32 object-contain">
          </div>
          <div class="col-span-12 sm:col-span-9 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="font-semibold text-lg leading-6">${escapeHtml(it.name)}</div>
                <div class="text-gray-600 text-sm mt-1">SIZE: ${escapeHtml(it.size)}</div>
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

// ---------- Confirm modal (centered) ----------
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

// ---------- Toast (top-center) ----------
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

// ---------- Fetch helpers ----------
async function fetchJson(url) {
  await ensureCsrf();
  const res = await fetch(url, {
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
    },
    credentials: 'include',
  });
  if (!res.ok) throw new Error('Network error');
  return res.json();
}

async function ensureCsrf() {
  if (!getCookie('XSRF-TOKEN')) {
    await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
  }
}
function getCookie(name) {
  const m = document.cookie.split('; ').find(r => r.startsWith(name + '='));
  return m ? decodeURIComponent(m.split('=')[1]) : null;
}
function escapeHtml(s) {
  return String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
}
