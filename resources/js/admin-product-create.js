// resources/js/admin-product-create.js

// ---- App config helpers (works in sub-folders) ----
const getMeta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || '';
const APP = {
  baseUrl: (window.__APP?.baseUrl ||
            getMeta('app:base-url') ||
            document.querySelector('base')?.href ||
            window.location.origin).replace(/\/+$/, ''),
  csrf: window.__APP?.csrf || getMeta('csrf-token'),
  adminProductsUrl: window.__APP?.adminProductsUrl || '#',
};
const api = (p) => `${APP.baseUrl}${p.startsWith('/') ? p : '/' + p}`;
const csrfToken = () => APP.csrf;

const GET_HEADERS = () => ({
  'Accept': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'X-CSRF-TOKEN': csrfToken(),
});

const ensureCsrf = async () => {
  if (!document.cookie.includes('XSRF-TOKEN=')) {
    await fetch(api('sanctum/csrf-cookie'), {
      credentials: 'same-origin',
      headers: GET_HEADERS(),
    });
  }
};

// ---- Image previews ----
function bindPreview(fileInputId, imgId, placeholderId) {
  const input = document.getElementById(fileInputId);
  const img   = document.getElementById(imgId);
  const ph    = document.getElementById(placeholderId);
  if (!input || !img || !ph) return;

  input.addEventListener('change', () => {
    const f = input.files?.[0];
    if (f) {
      img.src = URL.createObjectURL(f);
      img.classList.remove('hidden');
      ph.classList.add('hidden');
    } else {
      img.src = '';
      img.classList.add('hidden');
      ph.classList.remove('hidden');
    }
  });
}

// ---- Form submit ----
async function submitCreate(ev) {
  ev.preventDefault();
  const form = ev.target;
  const fd = new FormData(form);

  try {
    await ensureCsrf();

    const res = await fetch(api('api/admin/products'), {
      method: 'POST',
      credentials: 'same-origin',
      headers: GET_HEADERS(), // don’t set Content-Type for FormData
      body: fd,
    });

    if (res.status === 401) { alert('Not authenticated. Please log in again.'); return false; }
    if (res.status === 419) { alert('CSRF expired. Refresh and try again.');   return false; }
    if (!res.ok) {
      let msg = 'Create failed';
      try { const j = await res.json(); if (j?.message) msg = j.message; } catch {}
      alert(msg);
      return false;
    }

    // Success → go back to product list
    window.location.href = APP.adminProductsUrl;
    return false;
  } catch (e) {
    console.error(e);
    alert('Something went wrong. Please try again.');
    return false;
  }
}

// ---- Init on DOM ready ----
document.addEventListener('DOMContentLoaded', () => {
  // previews
  bindPreview('mainInput', 'mainPreview', 'mainPlaceholder');
  for (let i = 0; i < 4; i++) {
    bindPreview(`vInput-${i}`, `vPreview-${i}`, `vPlaceholder-${i}`);
  }

  // submit
  const form = document.getElementById('createForm');
  if (form) {
    form.addEventListener('submit', submitCreate);
  }
});
