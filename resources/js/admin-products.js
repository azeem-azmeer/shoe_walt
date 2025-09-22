// resources/js/admin-products.js

// ----------------- Token Minting & Auth Helpers -----------------
const CSRF_TOKEN =
  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

/** Mint a short-lived admin token via your web-only route. */
async function mintAdminToken() {
  const res = await fetch('/admin/api-token', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': CSRF_TOKEN,
      Accept: 'application/json',
    },
    credentials: 'include', // use session cookie
  });

  if (!res.ok) {
    let msg = 'Failed to mint API token';
    try { msg = (await res.json())?.message || msg; } catch {}
    throw new Error(msg);
  }

  const data = await res.json();
  window.__adminBearer = data?.token;
  return window.__adminBearer;
}

async function getAdminBearer() {
  if (window.__adminBearer) return window.__adminBearer;
  return await mintAdminToken();
}

/** Fetch wrapper that injects Bearer and retries once on 401. */
async function authedFetch(input, init = {}, retry = true) {
  const token = await getAdminBearer();
  const headers = new Headers(init.headers || {});
  headers.set('Authorization', `Bearer ${token}`);
  headers.set('X-Requested-With', 'XMLHttpRequest');
  headers.set('Accept', headers.get('Accept') || 'application/json');

  const res = await fetch(input, { ...init, headers });

  if (res.status === 401 && retry) {
    // Token possibly rotated/expired → re-mint then retry once.
    window.__adminBearer = null;
    await mintAdminToken();
    return authedFetch(input, init, /* retry */ false);
  }
  return res;
}

async function readError(res) {
  try {
    const data = await res.json();
    if (data?.message) return data.message;
    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0];
      const v = data.errors[firstKey];
      return Array.isArray(v) ? v[0] : String(v);
    }
  } catch {}
  return `${res.status} ${res.statusText || 'Error'}`;
}

function goToProductsList() {
  const fallback = '/admin/products';
  const url = (window.__APP && window.__APP.adminProductsUrl) ? window.__APP.adminProductsUrl : fallback;
  window.location.href = url;
}

// Pre-mint once for a smoother first API call.
document.addEventListener('DOMContentLoaded', () => {
  // fire-and-forget; errors will be handled later if any call fails
  mintAdminToken().catch(() => {});
});

// ----------------- CRUD: Create / Update / Delete -----------------

/**
 * Create product with multipart/form-data.
 * Required: product_name, price, category (Men|Women|Kids), status (Active|Inactive), main_image(file), sizes(JSON)
 * Optional: description, view_images[] (file[])
 */
window.createProduct = async function (form) {
  try {
    const fd = new FormData(form);

    // Build sizes JSON if not already present (supports .size-row fallback UIs)
    if (!fd.get('sizes')) {
      const rows = Array.from(form.querySelectorAll('.size-row'));
      const sizes = rows.map(r => ({
        size: r.querySelector('input[name="size[]"]')?.value || '',
        qty: Number(r.querySelector('input[name="qty[]"]')?.value || 0),
      })).filter(s => s.size !== '');
      fd.set('sizes', JSON.stringify(sizes));
    }

    const res = await authedFetch('/api/admin/products', {
      method: 'POST',
      body: fd,
    });

    if (!res.ok) throw new Error(await readError(res));

    toast('Product created successfully.', 'success');
    setTimeout(goToProductsList, 600);
  } catch (e) {
    alert(e?.message || 'Create failed'); // keep alert for quick debug
    toast(e?.message || 'Failed to create product.', 'error');
  }
};

/**
 * Update product (multipart/form-data with _method=PUT)
 */
window.updateProduct = async function (id, form) {
  try {
    const fd = new FormData(form);

    if (!fd.get('sizes')) {
      const rows = Array.from(form.querySelectorAll('.size-row'));
      const sizes = rows.map(r => ({
        size: r.querySelector('input[name="size[]"]')?.value || '',
        qty: Number(r.querySelector('input[name="qty[]"]')?.value || 0),
      })).filter(s => s.size !== '');
      fd.set('sizes', JSON.stringify(sizes));
    }

    // Laravel method spoofing(update)
    fd.set('_method', 'PUT');

    const res = await authedFetch(`/api/admin/products/${id}`, {
      method: 'POST',
      body: fd,
    });

    if (!res.ok) throw new Error(await readError(res));

    toast('Product updated successfully.', 'success');
    setTimeout(goToProductsList, 600);
  } catch (e) {
    alert(e?.message || 'Update failed');
    toast(e?.message || 'Failed to update product.', 'error');
  }
};

/**
 * Delete product (confirm + toast)
 */
window.deleteProduct = async function (id) {
  const ok = await confirmBox(
    'Delete product?',
    'This will permanently remove the product and its sizes. You can’t undo this action.',
    'Delete',
    'Cancel'
  );
  if (!ok) return;

  try {
    const res = await authedFetch(`/api/admin/products/${id}`, { method: 'DELETE' });
    if (!res.ok) throw new Error(await readError(res));

    toast('Product deleted successfully.', 'success');
    setTimeout(() => location.reload(), 800);
  } catch (e) {
    alert(e?.message || 'Delete failed');
    toast(e?.message || 'Failed to delete product.', 'error');
  }
};

// ----------------- UI Helpers (unchanged) -----------------
function confirmBox(title, message, okText = 'OK', cancelText = 'Cancel') {
  return new Promise((resolve) => {
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
// ---- DEBUG EXPORTS (put near the end of the file) ----
if (typeof window !== 'undefined') {
  window.getAdminBearer  = getAdminBearer;
  window.mintAdminToken  = mintAdminToken;
  window.authedFetch     = authedFetch;
}
