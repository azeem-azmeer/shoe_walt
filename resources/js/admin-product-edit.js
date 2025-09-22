// resources/js/admin-product-edit.js

// ----------------- Previews -----------------
document.addEventListener('DOMContentLoaded', () => {
  bindPreview('mainInput', 'mainPreview', 'mainPlaceholder');
  for (let i = 0; i < 4; i++) {
    bindPreview(`vInput-${i}`, `vPreview-${i}`, `vPlaceholder-${i}`);
  }
});

function bindPreview(inputId, previewId, placeholderId) {
  const input = document.getElementById(inputId);
  const img = document.getElementById(previewId);
  const ph = document.getElementById(placeholderId);
  if (!input || !img || !ph) return;

  input.addEventListener('change', () => {
    const file = input.files && input.files[0];
    if (file) {
      const url = URL.createObjectURL(file);
      img.src = url;
      img.classList.remove('hidden');
      ph.classList.add('hidden');
      img.onload = () => URL.revokeObjectURL(url);
    } else {
      img.src = '';
      img.classList.add('hidden');
      ph.classList.remove('hidden');
    }
  });
}

// ----------------- Token mint & authed fetch -----------------
const CSRF_TOKEN =
  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

async function mintAdminToken() {
  const res = await fetch('/admin/api-token', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': CSRF_TOKEN,
      Accept: 'application/json',
    },
    credentials: 'include', // uses web session for this web-only route
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
  return mintAdminToken();
}

async function authedFetch(input, init = {}, retry = true) {
  const token = await getAdminBearer();
  const headers = new Headers(init.headers || {});
  headers.set('Authorization', `Bearer ${token}`);
  headers.set('X-Requested-With', 'XMLHttpRequest');
  headers.set('Accept', headers.get('Accept') || 'application/json');

  const res = await fetch(input, { ...init, headers });

  // If the token was revoked/expired, refresh once and retry
  if (res.status === 401 && retry) {
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
  const url =
    (window.__APP && window.__APP.adminProductsUrl) ? window.__APP.adminProductsUrl : fallback;
  window.location.href = url;
}

// ----------------- Submit (PUT via POST + _method) -----------------
window.submitEdit = async function submitEdit(ev, id) {
  ev.preventDefault();

  const fd = new FormData(ev.target);
  fd.set('_method', 'PUT'); // Laravel-friendly multipart "PUT"

  try {
    const res = await authedFetch(`/api/admin/products/${id}`, {
      method: 'POST', // keep POST for FormData with files
      body: fd,
    });

    if (!res.ok) {
      let msg = await readError(res);
      alert(msg);
      return false;
    }

    // success → back to products list
    toast('Product updated successfully.', 'success');
    setTimeout(goToProductsList, 600);
    return false;
  } catch (e) {
    console.error(e);
    alert(e?.message || 'Unexpected error. Check console.');
    return false;
  }
};

// ----------------- Tiny toast helper (optional) -----------------
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
