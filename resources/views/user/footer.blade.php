<footer class="bg-black text-white -mt-px">  {{-- removed mt-16; -mt-px kills the seam --}}

  {{-- Top --}}
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
      {{-- Brand / blurb --}}
      <div>
        <a href="{{ route('user.index') }}" class="inline-block">
          <span class="text-2xl font-extrabold tracking-tight">
            {{ config('app.name', 'Shoe Walt') }}
          </span>
        </a>
        <p class="mt-3 text-sm text-white/70">
          Fresh drops, iconic classics, and performance picks for Men, Women, and Kids.
        </p>
      </div>

      {{-- Shop --}}
      <div>
        <h3 class="text-sm font-semibold tracking-wider text-white/80 uppercase">Shop</h3>
        <ul class="mt-4 space-y-2 text-sm">
          <li><a href="{{ route('user.mens') }}"   class="hover:underline">Men</a></li>
          <li><a href="{{ route('user.womans') }}" class="hover:underline">Women</a></li>
          <li><a href="{{ route('user.kids') }}"   class="hover:underline">Kids</a></li>
        </ul>
      </div>

      {{-- Help --}}
      <div>
        <h3 class="text-sm font-semibold tracking-wider text-white/80 uppercase">Help</h3>
        <ul class="mt-4 space-y-2 text-sm">
          <li><a href="#" class="hover:underline">Order Status</a></li>
          <li><a href="#" class="hover:underline">Returns &amp; Exchanges</a></li>
          <li><a href="#" class="hover:underline">Shipping</a></li>
          <li><a href="#" class="hover:underline">Contact</a></li>
        </ul>
      </div>

      {{-- Account / actions --}}
      <div>
        <h3 class="text-sm font-semibold tracking-wider text-white/80 uppercase">Account</h3>
        <ul class="mt-4 space-y-2 text-sm">
          <li><a href="{{ route('user.wishlist') }}" class="hover:underline">Wishlist</a></li>
          <li><a href="{{ route('user.cart') }}"     class="hover:underline">Cart</a></li>
          <li><a href="{{ route('login') }}"         class="hover:underline">Sign in</a></li>
        </ul>

        {{-- Newsletter (optional) --}}
        <form class="mt-6 flex gap-2" onsubmit="event.preventDefault()">
          <input type="email" placeholder="Email for updates"
                 class="w-full px-3 py-2 rounded-md bg-white/5 border border-white/15 text-white placeholder-white/40
                        focus:outline-none focus:ring-2 focus:ring-white/30" />
          <button class="px-4 py-2 rounded-md bg-white text-black font-semibold hover:bg-white/90 transition">
            Join
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Bottom bar --}}
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
      <p class="text-xs text-white/60">© {{ now()->year }} {{ config('app.name', 'Shoe Walt') }}. All rights reserved.</p>
      <nav class="text-xs text-white/60">
        <a href="#" class="hover:text-white">Privacy</a>
        <span class="mx-3">•</span>
        <a href="#" class="hover:text-white">Terms</a>
        <span class="mx-3">•</span>
        <a href="#" class="hover:text-white">Cookies</a>
      </nav>
    </div>
  </div>
</footer>
