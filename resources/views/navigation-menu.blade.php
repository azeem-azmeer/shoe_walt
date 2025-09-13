{{-- resources/views/components/nav.blade.php --}}
@php
  $isAdmin       = auth()->check() && (auth()->user()->role ?? null) === 'admin';
  $wishlistCount = $wishlistCount ?? 0;   // provide from server if you can; else 0
  $cartCount     = $cartCount ?? 0;       // provide from server if you can; else 0
@endphp

<nav x-data="{ open: false, acctOpen: false }" class="bg-white">

  {{-- Top promo strip (hidden for admin) --}}
  @unless($isAdmin)
    <div class="w-full bg-black text-white text-center text-sm py-2">
      Free and Easy Return — <a href="#" class="underline underline-offset-2">Learn More</a>
    </div>
  @endunless

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-20">
      {{-- Left: Logo --}}
      <div class="flex items-center">
        <a href="{{ route('user.index') }}" class="select-none flex items-center">
          <img src="{{ asset('storage/products/logo.png') }}" alt="Shoe Walt" class="h-8 w-auto" />
        </a>
      </div>

      {{-- Center: Links --}}
      <ul class="hidden md:flex items-center gap-10 text-[17px]">
        @if($isAdmin)
          {{-- ADMIN LINKS --}}
          <li><a href="{{ route('admin.dashboard') }}" class="hover:opacity-80">Dashboard</a></li>
          <li><a href="{{ route('admin.products') }}" class="hover:opacity-80">Products</a></li>
          <li><a href="{{ route('admin.reorders') }}" class="hover:opacity-80">Stock Reorders</a></li>
          <li><a href="{{ route('admin.customers') }}" class="hover:opacity-80">Customers</a></li>
          <li><a href="{{ route('admin.orders') }}" class="hover:opacity-80">Customer Orders</a></li>
        @else
          {{-- PUBLIC LINKS --}}
          <li><a href="{{ route('user.index') }}" class="hover:opacity-80">Home</a></li>
          <li><a href="{{ route('user.mens') }}" class="hover:opacity-80">Men</a></li>
          <li><a href="{{ route('user.womans') }}" class="hover:opacity-80">Women</a></li>
          <li><a href="{{ route('user.kids') }}" class="hover:opacity-80">Kids</a></li>
        @endif
      </ul>

      {{-- Right side --}}
      <div class="flex items-center gap-4">
        @if(!$isAdmin)
          {{-- Search pill (hidden for admin) --}}
                <livewire:user.search />
          {{-- Cart --}}
          @auth
            <a href="{{ route('user.cart') }}"
               class="relative inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100"
               aria-label="Cart">
              <img src="{{ asset('storage/products/cart.png') }}" alt="Cart" class="h-5 w-5" />
              <span id="cart-count"
                    @class([
                      'absolute -top-1 -right-1 h-5 min-w-[20px] px-1 rounded-full bg-black text-white text-[11px] font-bold flex items-center justify-center',
                      ($cartCount <= 0) ? 'hidden' : '',
                    ])>
                {{ $cartCount }}
              </span>
            </a>
          @else
            <a href="{{ route('register') }}"
               class="relative inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100"
               aria-label="Cart (register)">
              <img src="{{ asset('storage/products/cart.png') }}" alt="Cart" class="h-5 w-5" />
            </a>
          @endauth

          {{-- Wishlist --}}
          @auth
            <a href="{{ route('user.wishlist') }}"
               class="relative inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100"
               aria-label="Wishlist">
              <img src="{{ asset('storage/products/wishlist.png') }}" alt="Wishlist" class="h-5 w-5" />
              <span id="wishlist-count"
                    @class([
                      'absolute -top-1 -right-1 h-5 min-w-[20px] px-1 rounded-full bg-red-600 text-white text-[11px] font-bold flex items-center justify-center',
                      ($wishlistCount <= 0) ? 'hidden' : '',
                    ])>
                {{ $wishlistCount }}
              </span>
            </a>
          @else
            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100"
               aria-label="Wishlist (register)">
              <img src="{{ asset('storage/products/wishlist.png') }}" alt="Wishlist" class="h-5 w-5" />
            </a>
          @endauth
        @endif

        {{-- Profile --}}
        @auth
          <div class="relative" @keydown.escape.window="acctOpen=false">
            <button @click="acctOpen = !acctOpen"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100 focus:outline-none"
                    aria-label="Account">
              @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <img class="h-9 w-9 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
              @else
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 2.5-9 5.5A1.5 1.5 0 0 0 4.5 21h15A1.5 1.5 0 0 0 21 19.5C21 16.5 17 14 12 14Z"/>
                </svg>
              @endif
            </button>

            {{-- Manage Account dropdown --}}
            <div x-show="acctOpen" x-cloak @click.outside="acctOpen=false"
                 class="absolute right-0 mt-2 w-56 rounded-xl border bg-white shadow-lg p-2 z-50">
              <div class="px-3 py-2 text-xs text-gray-400">Manage Account</div>

              <a href="{{ route('profile.show') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Profile</a>
               @unless($isAdmin)
                <a href="{{ route('user.orders') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-50">My Orders</a>
              @endunless
              @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                <a href="{{ route('api-tokens.index') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-50">API Tokens</a>
              @endif

              @if ($isAdmin)
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Admin Dashboard</a>
              @endif

              <div class="my-2 border-t"></div>

              <form method="POST" action="{{ route('logout') }}" id="logout-form">@csrf
                <button
                  class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-50"
                  onclick="event.preventDefault();
                    if (typeof firebaseLogout === 'function') {
                      firebaseLogout().then(() => document.getElementById('logout-form').submit());
                    } else {
                      document.getElementById('logout-form').submit();
                    }"
                >
                  Log Out
                </button>
              </form>
            </div>
          </div>
        @endauth

        @guest
          <a href="{{ route('register') }}"
             class="inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100"
             aria-label="Register">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 2.5-9 5.5A1.5 1.5 0 0 0 4.5 21h15A1.5 1.5 0 0 0 21 19.5C21 16.5 17 14 12 14Z"/>
            </svg>
          </a>
        @endguest

        {{-- Mobile hamburger --}}
        <button @click="open = !open"
                class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100"
                aria-label="Menu">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" />
            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak class="md:hidden pb-4">
      <ul class="flex flex-col gap-2 text-base pt-2">
        @if($isAdmin)
          {{-- Admin mobile links --}}
          <li><a href="{{ route('admin.dashboard') }}" class="px-2 py-1 rounded hover:bg-gray-50">Dashboard</a></li>
          <li><a href="{{ route('admin.products') }}" class="px-2 py-1 rounded hover:bg-gray-50">Products</a></li>
          <li><a href="{{ route('admin.reorders') }}" class="px-2 py-1 rounded hover:bg-gray-50">Stock Reorders</a></li>
          <li><a href="{{ route('admin.customers') }}" class="px-2 py-1 rounded hover:bg-gray-50">Customers</a></li>
          <li><a href="{{ route('admin.orders') }}" class="px-2 py-1 rounded hover:bg-gray-50">Customer Orders</a></li>
        @else
          {{-- Public mobile links --}}
          <li><a href="{{ route('user.index') }}" class="px-2 py-1 rounded hover:bg-gray-50">Home</a></li>
          <li><a href="{{ route('user.mens') }}" class="px-2 py-1 rounded hover:bg-gray-50">Men</a></li>
          <li><a href="{{ route('user.womans') }}" class="px-2 py-1 rounded hover:bg-gray-50">Women</a></li>
          <li><a href="{{ route('user.kids') }}" class="px-2 py-1 rounded hover:bg-gray-50">Kids</a></li>

          @auth
            @if ((Auth::user()->role ?? null) === 'admin')
              <li><a href="{{ route('admin.dashboard') }}" class="px-2 py-1 rounded hover:bg-gray-50">Admin Dashboard</a></li>
            @endif
            <li><a href="{{ route('dashboard') }}" class="px-2 py-1 rounded hover:bg-gray-50">Dashboard</a></li>
            <li><a href="{{ route('profile.show') }}" class="px-2 py-1 rounded hover:bg-gray-50">Profile</a></li>
            <li>
              <form method="POST" action="{{ route('logout') }}" class="px-2 py-1">@csrf
                <button class="w-full text-left rounded hover:bg-gray-50"
                        onclick="event.preventDefault();
                          if (typeof firebaseLogout === 'function') {
                            firebaseLogout().then(() => this.closest('form').submit());
                          } else {
                            this.closest('form').submit();
                          }">
                  Log Out
                </button>
              </form>
            </li>
          @else
            <li><a href="{{ route('login') }}" class="px-2 py-1 rounded hover:bg-gray-50">Log In</a></li>
            <li><a href="{{ route('register') }}" class="px-2 py-1 rounded hover:bg-gray-50">Register</a></li>
          @endauth
        @endif
      </ul>
    </div>
  </div>
</nav>

{{-- Live badge refresh for authenticated users --}}
@auth
<script>
  (function(){
    async function refreshBadges(){
      try{
        const wc = document.getElementById('wishlist-count');
        const cc = document.getElementById('cart-count');

        // fetch wishlist count
        const wRes = await fetch('/api/wishlist/count', {credentials:'same-origin'});
        if (wRes.ok && wc){
          const w = await wRes.json();
          const wn = Number(w?.count ?? 0);
          wc.textContent = String(wn);
          wc.classList.toggle('hidden', wn <= 0);
        }

        // fetch mini cart (expects {count, items:[]})
        const cRes = await fetch('/api/cart/mini', {credentials:'same-origin'});
        if (cRes.ok && cc){
          const c = await cRes.json();
          const cn = Number(c?.count ?? 0);
          cc.textContent = String(cn);
          cc.classList.toggle('hidden', cn <= 0);
        }
      } catch(e){ /* ignore */ }
    }

    // initial load
    document.addEventListener('DOMContentLoaded', refreshBadges);

    // Optional: listen for custom events if you dispatch them after add/remove
    document.addEventListener('cart:updated',   refreshBadges);
    document.addEventListener('wishlist:updated', refreshBadges);
  })();
</script>
@endauth
