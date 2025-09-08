// Live cart-count updater for the nav badge (no refresh needed)

document.addEventListener('DOMContentLoaded', () => {
  // Initial render from API (if count wasn't injected or changed)
  updateCartCountFromApi();
});

// Public helpers you can call from anywhere
window.updateCartCount = async function () {
  await updateCartCountFromApi();
};

// Broadcast a known count (after add/remove); also sync to other tabs
window.broadcastCartCount = function (count) {
  setCartCount(count);
  try {
    localStorage.setItem(
      'cart:changed',
      JSON.stringify({ ts: Date.now(), count: Number(count) || 0 })
    );
  } catch {}
};

// Listen for cross-tab updates
window.addEventListener('storage', (e) => {
  if (e.key === 'cart:changed' && e.newValue) {
    try {
      const { count } = JSON.parse(e.newValue);
      setCartCount(count);
    } catch {}
  }
});

// Listen for in-page custom events
window.addEventListener('cart:changed', (e) => {
  const count = Number(e?.detail?.count);
  if (!Number.isNaN(count)) setCartCount(count);
});

// ---- internals ----
async function updateCartCountFromApi() {
  try {
    await ensureCsrf();
    const res = await fetch('/api/cart/count', {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      },
    });
    if (!res.ok) return;
    const { count } = await res.json();
    setCartCount(count);
  } catch {}
}

function setCartCount(count) {
  const el = document.getElementById('cart-count');
  if (!el) return;
  const n = Number(count) || 0;
  el.textContent = n;
  el.classList.toggle('hidden', n <= 0);
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
