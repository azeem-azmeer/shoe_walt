// resources/js/admin-product-create.js

// ===== Image previews =====
(function wirePreviews() {
  const mainInput = document.getElementById('mainInput');
  const mainPreview = document.getElementById('mainPreview');
  const mainPlaceholder = document.getElementById('mainPlaceholder');
  if (mainInput) {
    mainInput.addEventListener('change', () => {
      const f = mainInput.files?.[0];
      if (!f) {
        mainPreview.classList.add('hidden');
        mainPlaceholder.classList.remove('hidden');
        return;
      }
      const url = URL.createObjectURL(f);
      mainPreview.src = url;
      mainPreview.onload = () => URL.revokeObjectURL(url);
      mainPreview.classList.remove('hidden');
      mainPlaceholder.classList.add('hidden');
    });
  }
  for (let i = 0; i < 4; i++) {
    const input = document.getElementById(`vInput-${i}`);
    const preview = document.getElementById(`vPreview-${i}`);
    const placeholder = document.getElementById(`vPlaceholder-${i}`);
    if (!input) continue;
    input.addEventListener('change', () => {
      const f = input.files?.[0];
      if (!f) {
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        return;
      }
      const url = URL.createObjectURL(f);
      preview.src = url;
      preview.onload = () => URL.revokeObjectURL(url);
      preview.classList.remove('hidden');
      placeholder.classList.add('hidden');
    });
  }
})();

// ===== Token mint & authed fetch (Jetstream/Sanctum) =====
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
    credentials: 'include',
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
  if (res.status === 401 && retry) {
    window.__adminBearer = null;
    await mintAdminToken();
    return authedFetch(input, init, false);
  }
  return res;
}
// Return only the first validation message (or a generic one)
async function readError(res) {
  try {
    const data = await res.json();
    if (data?.errors) {
      const [[, msgs]] = Object.entries(data.errors); // first field only
      return Array.isArray(msgs) ? msgs[0] : String(msgs);
    }
    if (data?.message) return data.message;
  } catch {}
  return 'Validation failed';
}


function goToProductsList() {
  const fallback = '/admin/products';
  const url = (window.__APP && window.__APP.adminProductsUrl) ? window.__APP.adminProductsUrl : fallback;
  window.location.href = url;
}


// ===== Submit handler (redirects to products list) =====
window.submitCreate = async function (e) {
  e.preventDefault();
  const form = document.getElementById('createForm');
  if (!form) return false;

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

    const category = fd.get('category');
    const status = fd.get('status');
    if (!['Men','Women','Kids'].includes(category)) throw new Error('Category must be Men, Women, or Kids.');
    if (!['Active','Inactive'].includes(status)) throw new Error('Status must be Active or Inactive.');
    const main = fd.get('main_image');
    if (!main || !main.name) throw new Error('The main image field is required.');

    const res = await authedFetch('/api/admin/products', {
      method: 'POST',
      body: fd,
    });
    if (!res.ok) throw new Error(await readError(res));

    toast('Product created successfully.', 'success');
    setTimeout(goToProductsList, 600);
  } catch (err) {
    console.error('Create failed:', err);         // <— log only
    toast(err?.message || 'Create failed', 'error', 4000); // <— single message (no alert)
  }

  return false;
};

// ===== Simple toast =====
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
