// resources/js/pdp.js

// Fallback meta getter (if window.__APP is missing)
const getMeta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || '';

// Build the runtime config from window.__APP with safe fallbacks
const APP = {
  isAuth: !!(window.__APP?.isAuth ?? (getMeta('app:is-auth') === '1')),
  registerUrl: window.__APP?.registerUrl || getMeta('app:register-url') || '/register',
  baseUrl: (window.__APP?.baseUrl ||
            getMeta('app:base-url') ||
            document.querySelector('base')?.href ||
            window.location.origin).replace(/\/+$/, ''),
  csrf: window.__APP?.csrf || getMeta('csrf-token'),
};

const api = (p) => `${APP.baseUrl}${p.startsWith('/') ? p : '/' + p}`;
const csrfToken = () => APP.csrf;

// Common headers
const JSON_HEADERS = () => ({
  'Accept': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'Content-Type': 'application/json',
  'X-CSRF-TOKEN': csrfToken(),
});
const GET_HEADERS = () => ({
  'Accept': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'X-CSRF-TOKEN': csrfToken(),
});

document.addEventListener('alpine:init', () => {
  const IS_AUTH = APP.isAuth;
  const REG_URL = APP.registerUrl;

  // Flash/toast
  Alpine.store('flash', {
    visible:false, text:'', type:'info', _t:null,
    show(msg, type='info', ms=4000){ this.text=msg; this.type=type; this.visible=true; clearTimeout(this._t); this._t=setTimeout(()=>this.visible=false, ms); },
    close(){ this.visible=false; clearTimeout(this._t); }
  });

  // Image zoom
  Alpine.data('imageZoom', () => ({
    zoomOpen:false, zoomSrc:'', zoom:1,
    open(src){ this.zoomSrc=src; this.zoom=1; this.zoomOpen=true; },
    close(){ this.zoomOpen=false; },
    zoomIn(){ this.zoom=Math.max(1, Math.min(3, this.zoom+0.25)); },
    zoomOut(){ this.zoom=Math.max(1, Math.min(3, this.zoom-0.25)); },
    onWheel(e){ if(e.deltaY<0) this.zoomIn(); else this.zoomOut(); }
  }));

  // Size guide
  Alpine.data('sizeGuide', () => ({
    open:false, tab:'babies', unit:'in',
    groups:{
      babies:{ cols:[3.2,3.5,3.9,4.2,4.5,4.8,5.0], uk:['0k','1k','2k','3k','4k','5k','5.5k'], us:['1k','2k','3k','4k','5k','5.5k','6k'], eu:['16','17','18','19','20','21','22'] },
      children:{ cols:[6.5,6.7,6.9,7.0,7.2,7.4,7.5], uk:['10k','10.5k','11k','11.5k','12k','12.5k','13k'], us:['10.5k','11k','11.5k','12k','12.5k','13k','13.5k'], eu:['28','28.5','29','30','30.5','31','31.5'] },
      youth:{ cols:[8.5,8.7,8.9,9.0,9.2,9.4,9.5], uk:['3','3.5','4','4.5','5','5.5','6'], us:['3.5','4','4.5','5','5.5','6','6.5'], eu:['35.5','36','36 2/3','37 1/3','38','38 2/3','39 1/3'] },
      adults:{ cols:[9.6,9.8,10.0,10.2,10.4,10.6,10.8,11.0,11.2,11.4,11.6,11.8,12.0], uk:['5','5.5','6','6.5','7','7.5','8','8.5','9','9.5','10','10.5','11'], us:['5.5','6','6.5','7','7.5','8','8.5','9','9.5','10','10.5','11','11.5'], eu:['38','39','39.5','40','40.5','41','42','42.5','43','44','44.5','45','46'] }
    },
    get current(){ return this.groups[this.tab]; },
    get columns(){ return this.current.cols; },
    get currentRows(){ return { uk:this.current.uk, us:this.current.us, eu:this.current.eu }; },
    formatCol(v){ return this.unit==='cm' ? `${(v*2.54).toFixed(1)} cm` : `${v.toFixed(1)}"`; }
  }));

  Alpine.store('pdp', { selectedSize:null });

  const ensureCsrf = async () => {
    if (!document.cookie.includes('XSRF-TOKEN=')) {
      await fetch(api('sanctum/csrf-cookie'), {
        credentials:'same-origin',
        headers: GET_HEADERS(),
      });
    }
  };

  const currency = (n) => `$${Number(n ?? 0).toFixed(2)}`;
  window.currency = currency;

  // Mini cart store
  Alpine.store('miniCart', {
    open:false, items:[], count:0, currency,
    openWith(items, count){
      this.items = items || [];
      this.count = count || 0;
      this.open  = true;
      const badge = document.getElementById('cart-count');
      if (badge) badge.textContent = String(this.count);
    },
    async remove(id){
      await ensureCsrf();
      const res = await fetch(api(`api/cart/${id}`), {
        method:'DELETE',
        credentials:'same-origin',
        headers: GET_HEADERS(),
      });
      if (res.status===401){ window.location.assign(REG_URL); return; }
      if (!res.ok){ Alpine.store('flash').show('Could not remove item.','error'); return; }
      const data = await res.json();
      this.openWith(data.items, data.count);
      Alpine.store('flash').show('Item removed from your bag.','success',2500);
    }
  });

  // Actions
  Alpine.store('actions', {
    ensureAuth(){
      if (IS_AUTH) return true;
      window.location.assign(REG_URL);
      return false;
    },

    async addToCart(productId){
      if (!this.ensureAuth()) return false;
      const size = Alpine.store('pdp').selectedSize;
      if (!size){ Alpine.store('flash').show('Please select a size.','warning'); return false; }

      await ensureCsrf();
      const res = await fetch(api('api/cart'), {
        method:'POST',
        credentials:'same-origin',
        headers: JSON_HEADERS(),
        body: JSON.stringify({ product_id: productId, size, quantity: 1 })
      });

      if (res.status===401){ window.location.assign(REG_URL); return false; }
      if (res.status===422){
        let msg='Not enough stock for that size.'; 
        try{ const d=await res.json(); if (d?.message) msg=d.message; }catch{}
        Alpine.store('flash').show(msg,'error',6000);
        return false;
      }
      if (!res.ok){
        Alpine.store('flash').show('Sorry, something went wrong adding to your bag.','error');
        return false;
      }

      const data = await res.json();
      Alpine.store('miniCart').openWith(data.items, data.count);
      Alpine.store('flash').show('Added to your bag.','success',2000);
      return true;
    },

    async addToWishlist(productId){
      if (!this.ensureAuth()) return false;

      await ensureCsrf();
      const res = await fetch(api('api/wishlist'), {
        method:'POST',
        credentials:'same-origin',
        headers: JSON_HEADERS(),
        body: JSON.stringify({ product_id: productId })
      });

      if (res.status===401){ window.location.assign(REG_URL); return false; }
      if (!res.ok){
        let msg='Could not add to wishlist.'; 
        try{ const d=await res.json(); if (d?.message) msg=d.message; }catch{}
        Alpine.store('flash').show(msg,'error',5000);
        return false;
      }

      const data = await res.json();
      const badge = document.getElementById('wishlist-count');
      if (badge){
        const n = Number(data?.count ?? 0);
        badge.textContent = String(n);
        if ('hidden' in badge) badge.hidden = n <= 0;
      }
      Alpine.store('flash').show(data?.message || 'Product has been added to wishlist.','success',2200);
      return true;
    }
  });
});
