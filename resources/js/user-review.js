// resources/js/user-review.js

// ---------- App + CSRF helpers ----------
const getMeta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || '';

const APP = {
  baseUrl: (window.__APP?.baseUrl ||
            getMeta('app:base-url') ||
            document.querySelector('base')?.href ||
            window.location.origin).replace(/\/+$/, ''),
  csrf: window.__APP?.csrf || getMeta('csrf-token'),
};
const api = (p) => `${APP.baseUrl}${p.startsWith('/') ? p : '/' + p}`;
const CSRF_TOKEN = APP.csrf;

// We only need this once to mint the token (Sanctum requires the cookie)
async function ensureCsrfCookie() {
  if (!document.cookie.includes('XSRF-TOKEN=')) {
    await fetch(api('/sanctum/csrf-cookie'), {
      credentials: 'include',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    });
  }
}

// ---------- Bearer token helpers (same pattern as cart) ----------
async function mintCustomerToken() {
  const res = await fetch(api('/user/api-token'), {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': CSRF_TOKEN,
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

async function getCustomerBearer() {
  if (window.__customerBearer) return window.__customerBearer;
  await ensureCsrfCookie();          // make sure Sanctum cookie exists before mint
  return mintCustomerToken();
}

async function authedFetch(input, init = {}, retry = true) {
  const token = await getCustomerBearer();
  const headers = new Headers(init.headers || {});
  headers.set('Authorization', `Bearer ${token}`);
  headers.set('X-Requested-With', 'XMLHttpRequest');
  headers.set('Accept', headers.get('Accept') || 'application/json');

  const res = await fetch(input, { ...init, headers });

  if (res.status === 401 && retry) {
    window.__customerBearer = null;     // rotate once
    await mintCustomerToken();
    return authedFetch(input, init, false);
  }
  return res;
}

// ---------- small utils ----------
function oid(doc){
  if (!doc) return null;
  if (typeof doc === 'string') return doc;
  if (doc._id){
    if (typeof doc._id === 'string') return doc._id;
    if (doc._id.$oid) return doc._id.$oid;
    if (doc._id.oid)  return doc._id.oid;
  }
  if (doc.id){
    if (typeof doc.id === 'string') return doc.id;
    if (doc.id.$oid) return doc.id.$oid;
  }
  return null;
}
function fmtDate(v){
  try{
    if (!v) return '';
    const raw = (v?.$date ? v.$date : v);
    const d = new Date(raw);
    if (!isNaN(d)) return d.toLocaleString();
  }catch(e){}
  return '';
}
function esc(s){ return (s||'').replace(/[&<]/g, ch => ch === '&' ? '&amp;' : '&lt;'); }

// ---------- renderers ----------
function renderForm(container, orderId, existing){
  container.innerHTML = `
    <form id="reviewForm" class="space-y-3">
      <input type="hidden" name="order_id" value="${orderId}">
      <div>
        <label class="block text-sm font-medium mb-1">Rating</label>
        <div class="flex items-center gap-1" role="radiogroup" aria-label="Rating 1 to 5">
          ${[1,2,3,4,5].map(n => `
            <button type="button" data-rate="${n}"
              class="px-2 py-1 rounded border text-xs ${existing && Number(existing.rating) >= n ? 'bg-yellow-200' : 'bg-white'}">
              ${n}★
            </button>
          `).join('')}
          <input type="hidden" name="rating" value="${existing ? Number(existing.rating) : 5}">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Your Feedback</label>
        <textarea name="feedback" rows="3" required
                  class="w-full border rounded p-2"
                  placeholder="Tell us about your shopping experience...">${existing ? esc(existing.feedback || '') : ''}</textarea>
      </div>

      <div class="flex items-center gap-2">
        <button class="px-4 py-2 rounded-xl font-bold text-white bg-gray-900 hover:bg-black">
          ${existing ? 'Update Review' : 'Submit Review'}
        </button>
        ${existing ? `<button type="button" id="cancelEdit" class="px-3 py-2 rounded border">Cancel</button>` : ''}
      </div>
    </form>
  `;

  container.querySelectorAll('[data-rate]').forEach(btn=>{
    btn.addEventListener('click', () => {
      const rate = btn.getAttribute('data-rate');
      container.querySelector('input[name="rating"]').value = rate;
      container.querySelectorAll('[data-rate]').forEach(b => b.classList.remove('bg-yellow-200'));
      container.querySelectorAll('[data-rate]').forEach(b=>{
        if (Number(b.getAttribute('data-rate')) <= Number(rate)) b.classList.add('bg-yellow-200');
      });
    });
  });
}

function renderExisting(container, review){
  const r = Number(review.rating) || 0;
  const stars = '★'.repeat(r) + '☆'.repeat(5 - r);
  container.innerHTML = `
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <div class="font-semibold">Rating: ${stars}</div>
        <div class="text-xs text-gray-500">${fmtDate(review.updated_at || review.created_at)}</div>
      </div>
      <p class="text-gray-800 whitespace-pre-line">${esc(review.feedback || '')}</p>
      <div class="pt-2 flex items-center gap-2">
        <button id="editReview"  class="px-3 py-2 rounded border">Edit</button>
        <button id="deleteReview" class="px-3 py-2 rounded border text-red-600 border-red-300 hover:bg-red-50">Delete</button>
      </div>
    </div>
  `;
}

// ---------- main ----------
document.addEventListener('DOMContentLoaded', () => {
  const box = document.getElementById('reviewContent');
  if (!box) return;
  const msg = document.getElementById('reviewMsg');
  const orderId = Number(box.dataset.orderId);

  // show create form first
  renderForm(box, orderId, null);
  bindCreate();
  loadReview();

  async function loadReview(){
    try{
      const res  = await authedFetch(api(`/api/reviews?order_id=${orderId}&_=${Date.now()}`));
      if (res.status === 401){
        msg.className = 'mt-3 text-sm text-blue-700';
        msg.textContent = 'Please log in to leave a review.';
        return;
      }
      const json = await res.json();
      const review = (json?.data && json.data.length) ? json.data[0] : null;

      if (review){
        renderExisting(box, review);
        msg.textContent = '';
        bindExisting(review);
      }else{
        renderForm(box, orderId, null);
        bindCreate();
        msg.textContent = '';
      }
    }catch(e){
      msg.className = 'mt-3 text-sm text-red-600';
      msg.textContent = 'Could not load review, but you can still submit one.';
    }
  }

  function bindCreate(){
    const form = document.getElementById('reviewForm');
    form?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      msg.className = 'mt-3 text-sm';
      msg.textContent = 'Saving...';

      const payload = {
        order_id: Number(form.order_id.value),
        rating:   Number(form.rating.value),
        feedback: form.feedback.value.trim()
      };

      const res = await authedFetch(api('/api/reviews'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const ct = res.headers.get('content-type') || '';
      if (ct.includes('application/json')) {
        const json = await res.json();
        if (!res.ok) {
          msg.className = 'mt-3 text-sm text-red-600';
          msg.textContent = (json.message || JSON.stringify(json)).slice(0, 300);
          return;
        }
      } else if (!res.ok) {
        const text = await res.text();
        msg.className = 'mt-3 text-sm text-red-600';
        msg.textContent = (`${res.status} ${res.statusText}: ` + text).slice(0, 300);
        return;
      }

      msg.className = 'mt-3 text-sm text-emerald-700';
      msg.textContent = 'Thank you for your review!';
      await loadReview();
      setTimeout(()=>{ msg.textContent=''; }, 2500);
    });
  }

  function bindExisting(review){
    document.getElementById('editReview')?.addEventListener('click', ()=>{
      renderForm(box, orderId, review);
      msg.textContent = '';
      document.getElementById('cancelEdit')?.addEventListener('click', loadReview);

      const form = document.getElementById('reviewForm');
      form?.addEventListener('submit', async (e)=>{
        e.preventDefault();
        msg.className = 'mt-3 text-sm';
        msg.textContent = 'Updating...';

        const payload = { rating: Number(form.rating.value), feedback: form.feedback.value.trim() };
        const id = oid(review);

        const res = await authedFetch(api(`/api/reviews/${id}`), {
          method:'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
          const json = await res.json();
          if (!res.ok){
            msg.className = 'mt-3 text-sm text-red-600';
            msg.textContent = json.message || 'Update failed.';
            return;
          }
        } else if (!res.ok){
          const text = await res.text();
          msg.className = 'mt-3 text-sm text-red-600';
          msg.textContent = (text || 'Update failed.').slice(0,300);
          return;
        }

        msg.className = 'mt-3 text-sm text-emerald-700';
        msg.textContent = 'Review updated.';
        await loadReview();
        setTimeout(()=>{ msg.textContent=''; }, 2500);
      });
    });

    document.getElementById('deleteReview')?.addEventListener('click', async ()=>{
      msg.className = 'mt-3 text-sm';
      msg.textContent = 'Deleting your review...';

      const id = oid(review);
      const res = await authedFetch(api(`/api/reviews/${id}`), { method:'DELETE' });

      if (!res.ok){
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
          const json = await res.json().catch(()=>({}));
          msg.className = 'mt-3 text-sm text-red-600';
          msg.textContent = json.message || 'Delete failed.';
        } else {
          const text = await res.text();
          msg.className = 'mt-3 text-sm text-red-600';
          msg.textContent = (text || 'Delete failed.').slice(0,300);
        }
        return;
      }

      msg.className = 'mt-3 text-sm text-emerald-700';
      msg.textContent = 'Your review was deleted. You can add a new one below.';
      await loadReview();
      setTimeout(()=>{ msg.textContent=''; }, 2500);
    });
  }
});
