// resources/js/admin/products.js

// -------- Public API (called from Blade) --------
window.deleteProduct = async function (id) {
  const ok = await confirmBox(
    'Delete product?',
    'This will permanently remove the product and its sizes. You can’t undo this action.',
    'Delete',
    'Cancel'
  );
  if (!ok) return;

  try {
    await csrf();
    const res = await fetch(`/api/admin/products/${id}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      },
      credentials: 'include',
    });

    if (!res.ok) {
      let msg = 'Delete failed';
      try { msg = (await res.json())?.message || msg; } catch {}
      throw new Error(msg);
    }

    toast('Product deleted successfully.', 'success');
    setTimeout(() => location.reload(), 800);
  } catch (e) {
    toast(e?.message || 'Failed to delete product.', 'error');
  }
};

// -------- Helpers --------
const csrf = async () =>
  document.cookie.includes('XSRF-TOKEN=') ||
  (await fetch('/sanctum/csrf-cookie', { credentials: 'include' }));

const getCookie = (name) => {
  const m = document.cookie.split('; ').find((r) => r.startsWith(name + '='));
  return m ? decodeURIComponent(m.split('=')[1]) : null;
};

// -------- Centered Confirm Modal --------
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

// -------- Top-center Toast --------
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
