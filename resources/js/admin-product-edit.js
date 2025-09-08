// resources/js/admin/product-edit.js

// Bind image preview helpers once the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  bindPreview('mainInput', 'mainPreview', 'mainPlaceholder');
  for (let i = 0; i < 4; i++) {
    bindPreview(`vInput-${i}`, `vPreview-${i}`, `vPlaceholder-${i}`);
  }
});

// Expose submitEdit globally for the Blade onsubmit=""
window.submitEdit = async function submitEdit(ev, id) {
  ev.preventDefault();

  const fd = new FormData(ev.target);
  fd.append('_method', 'PUT'); // Laravel-friendly multipart PUT

  try {
    const res = await api(`/api/admin/products/${id}`, {
      method: 'POST', // keep POST + _method=PUT for file uploads
      body: fd,
    });

    if (res.status === 401) {
      alert('Not authenticated. Please log in again.');
      return false;
    }
    if (res.status === 419) {
      alert('CSRF expired. Refresh and try again.');
      return false;
    }
    if (!res.ok) {
      let msg = 'Update failed';
      try {
        const data = await res.json();
        if (data?.message) msg = data.message;
      } catch {}
      alert(msg);
      return false;
    }

    // Redirect back to products list
    window.location.href = '/admin/products';
    return false;
  } catch (e) {
    console.error(e);
    alert('Unexpected error. Check console.');
    return false;
  }
};

// ---------- Helpers ----------
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
    } else {
      img.src = '';
      img.classList.add('hidden');
      ph.classList.remove('hidden');
    }
  });
}

async function api(url, opts = {}) {
  await ensureCsrf();
  const headers = opts.headers || {};
  // Do NOT set Content-Type for FormData; the browser will add the boundary
  return fetch(url, {
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      ...headers,
    },
    ...opts,
  });
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
