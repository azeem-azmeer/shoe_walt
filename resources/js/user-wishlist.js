// resources/js/wishlist.js

// Fallback meta getter
const getMeta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || '';

// Global app config (pull from window.__APP if your layout sets it)
const APP = {
  baseUrl: (window.__APP?.baseUrl ||
            getMeta('app:base-url') ||
            document.querySelector('base')?.href ||
            window.location.origin).replace(/\/+$/, ''),
  csrf: window.__APP?.csrf || getMeta('csrf-token'),
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

// Expose the handler globally because the Blade uses onclick="removeFromWishlist(id)"
window.removeFromWishlist = async function removeFromWishlist(id) {
  try {
    await ensureCsrf();

    const res = await fetch(api(`api/wishlist/${id}`), {
      method: 'DELETE',
      credentials: 'same-origin',
      headers: GET_HEADERS(),
    });

    if (!res.ok) {
      alert('Could not remove.');
      return;
    }

    const data = await res.json();

    // Remove the card
    const card = document.querySelector(`[data-wish-id="${id}"]`);
    if (card) card.remove();

    // Update the wishlist badge if present
    const badge = document.getElementById('wishlist-count');
    if (badge) {
      const n = Number(data?.count ?? 0);
      badge.textContent = String(n);
      if ('hidden' in badge) badge.hidden = n <= 0;
      else badge.classList.toggle('hidden', n <= 0);
    }

    // If empty, refresh to show the "empty" message
    if (document.querySelectorAll('[data-wish-id]').length === 0) {
      location.reload();
    }
  } catch (e) {
    console.error(e);
    alert('Something went wrong.');
  }
};
