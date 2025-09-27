{{-- resources/views/user/index.blade.php --}}
<x-app-layout>
  {{-- Skip link for keyboard/AT users --}}
  <a href="#main-content"
     class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:text-black focus:px-3 focus:py-2 focus:rounded">
    Skip to content
  </a>

  {{-- Hide Jetstream header bar --}}
  <x-slot name="header"></x-slot>

  @php
    $heroSlides = [
      ['src' => asset('storage/products/bgwallpaer2.webp'), 'alt' => 'Summer collection for men',   'href' => route('user.mens')],
      ['src' => asset('storage/products/bgwallpaer1.webp'), 'alt' => 'Kids sneakers and sandals',   'href' => route('user.kids')],
      ['src' => asset('storage/products/bgwallpaer3.webp'), 'alt' => 'Women’s newest arrivals',     'href' => route('user.womans')],
    ];
  @endphp

  {{-- ===================== HERO SLIDER ===================== --}}
  <section
    x-data="heroSlider({ images: @js($heroSlides), interval: 6000 })"
    x-init="start()"
    @mouseenter="stop()" @mouseleave="start()" @keydown="key($event)"
    tabindex="0" role="region" aria-roledescription="carousel" aria-label="Featured promotions"
    class="relative w-full min-h-[420px] h-[76vh] sm:h-[82vh] md:h-[88vh] overflow-hidden bg-black"
  >
    {{-- soft ambient blobs --}}
    <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-30 animate-[pulse_14s_ease-in-out_infinite] bg-gradient-to-br from-violet-400/40 via-pink-300/30 to-amber-300/30"></div>
    <div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full blur-3xl opacity-30 animate-[pulse_16s_ease-in-out_infinite] bg-gradient-to-tr from-sky-400/40 via-emerald-300/30 to-cyan-300/30"></div>

    <template x-for="(slide, i) in images" :key="i">
      <div class="absolute inset-0" x-show="index === i" x-transition.opacity :aria-hidden="index !== i">
        <img :src="slide.src" :alt="slide.alt" :loading="i === 0 ? 'eager' : 'lazy'" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent"></div>

        {{-- copy card --}}
        <div class="absolute left-4 right-4 sm:left-10 sm:right-auto bottom-24 sm:bottom-28 max-w-xl">
          <div class="backdrop-blur-sm bg-white/10 border border-white/15 text-white rounded-2xl p-4 sm:p-6 shadow-2xl">
            <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold tracking-tight leading-tight">
              <span class="bg-clip-text text-transparent bg-gradient-to-r from-white to-white/70" x-text="slide.alt"></span>
            </h1>
            <p class="mt-2 sm:mt-3 text-sm sm:text-base text-white/90">
              Step into comfort, speed and style — curated drops updated weekly.
            </p>
            <div class="mt-4">
              <a :href="images[index]?.href || '#'"
                 class="inline-flex items-center gap-2 rounded-xl bg-white text-black font-semibold px-5 py-2.5 sm:px-6 sm:py-3 shadow hover:shadow-lg active:scale-[.99] transition
                        focus:outline-none focus:ring-2 focus:ring-white/80"
                 :aria-label="`Shop now: ${images[index]?.alt || ''}`">
                SHOP NOW
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </template>

    {{-- arrows --}}
    <button @click="prev()" class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-white/85 hover:bg-white flex items-center justify-center shadow
                                focus:outline-none focus:ring-2 focus:ring-black" aria-label="Previous slide">‹</button>
    <button @click="next()" class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-white/85 hover:bg-white flex items-center justify-center shadow
                                focus:outline-none focus:ring-2 focus:ring-black" aria-label="Next slide">›</button>

    {{-- dots --}}
    <div class="absolute bottom-5 sm:bottom-7 inset-x-0 flex justify-center gap-2 sm:gap-3">
      <template x-for="(slide, i) in images" :key="i">
        <button class="h-2.5 w-2.5 sm:h-3 sm:w-3 rounded-full border border-white/80"
                :class="index === i ? 'bg-white' : 'bg-white/50 hover:bg-white/70'"
                @click="index = i" :aria-label="`Go to slide ${i+1}`" :aria-current="index === i ? 'true' : 'false'">
        </button>
      </template>
    </div>
  </section>

  {{-- slim headline --}}
  <section class="w-full bg-black text-white py-2 sm:py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-center text-lg sm:text-xl md:text-2xl font-extrabold tracking-wider uppercase">Summer Drops</h2>
    </div>
  </section>

  {{-- trust bar --}}
  <section class="bg-gradient-to-b from-white to-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
      <ul class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <li class="flex items-center gap-3 rounded-2xl border bg-white/80 backdrop-blur-sm p-3 sm:p-4 shadow-sm">
          <svg class="h-6 w-6 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" d="M3 12l2 2 4-4M13 5h6M13 9h6M13 13h6M13 17h6"/></svg>
          <span class="text-sm sm:text-base font-semibold">Fast & Free Shipping*</span>
        </li>
        <li class="flex items-center gap-3 rounded-2xl border bg-white/80 backdrop-blur-sm p-3 sm:p-4 shadow-sm">
          <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" d="M4 4h16v16H4z"/><path stroke-width="2" d="M9 9h6v6H9z"/></svg>
          <span class="text-sm sm:text-base font-semibold">7-Day Easy Returns</span>
        </li>
        <li class="flex items-center gap-3 rounded-2xl border bg-white/80 backdrop-blur-sm p-3 sm:p-4 shadow-sm">
          <svg class="h-6 w-6 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/><path stroke-width="2" d="M19 21a7 7 0 10-14 0"/></svg>
          <span class="text-sm sm:text-base font-semibold">Secure Checkout</span>
        </li>
      </ul>
    </div>
  </section>

  {{-- brands marquee --}}
  <section aria-label="Popular brands" class="bg-white">
    <div class="relative overflow-hidden border-y">
      <div class="marquee flex items-center gap-10 py-4 text-slate-600/80 font-black tracking-widest uppercase">
        <span>NIKE</span><span>ADIDAS</span><span>NEW BALANCE</span><span>PUMA</span><span>ASICS</span><span>VANS</span>
        <span>NIKE</span><span>ADIDAS</span><span>NEW BALANCE</span><span>PUMA</span><span>ASICS</span><span>VANS</span>
      </div>
    </div>
  </section>

  <main id="main-content">
    {{-- ======= Category tiles (mobile scroller / desktop grid) ======= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <div x-data="cardScroller" class="relative" data-step="0.8">
        {{-- Mobile arrows --}}
        <button
          @click="prev()"
          class="md:!hidden absolute left-2 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-black/10 backdrop-blur
                 border border-black/10 text-black flex items-center justify-center active:scale-95"
          aria-label="Scroll left"
        >‹</button>

        <button
          @click="next()"
          class="md:!hidden absolute right-2 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-black/10 backdrop-blur
                 border border-black/10 text-black flex items-center justify-center active:scale-95"
          aria-label="Scroll right"
        >›</button>

        {{-- Scroll list / grid --}}
        <div
          x-ref="wrap"
          class="no-scrollbar flex md:grid gap-4 sm:gap-6 md:gap-8 overflow-x-auto md:overflow-visible scroll-smooth
                 md:grid-cols-2 lg:grid-cols-4 px-1"
          style="scroll-snap-type: x mandatory;"
        >
          @php
            $cards = [
              ['img' => 'shoe1.avif', 'title' => 'SAMBA',               'desc' => 'Always iconic, always in style.',          'href' => route('user.kids')],
              ['img' => 'shoe2.jpg',  'title' => '327 New Balance',     'desc' => 'Feel fast. In all aspects of life.',       'href' => route('user.womans')],
              ['img' => 'shoe3.jpg',  'title' => 'Air Jordan',          'desc' => 'Become Legendary.',                        'href' => route('user.mens')],
              ['img' => 'shoe4.jpg',  'title' => 'CLASS READY: GAZELLE','desc' => 'Amplify your style this school year.',      'href' => route('user.kids')],
            ];
          @endphp

          @foreach($cards as $c)
            <a href="{{ $c['href'] }}"
               class="relative group snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto
                      rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md md:bg-transparent md:shadow-none"
               aria-label="Shop {{ $c['title'] }}">
              <div class="pointer-events-none md:absolute md:-inset-2 md:border-2 md:border-black md:opacity-0 group-hover:opacity-100 md:transition md:duration-300"></div>
              <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
                <img src="{{ asset('storage/products/'.$c['img']) }}" alt="{{ $c['title'] }}"
                     class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.03]" loading="lazy"/>
              </div>
              <div class="p-4 md:p-0 md:pt-4">
                <h3 class="text-base md:text-lg font-extrabold tracking-tight">{{ $c['title'] }}</h3>
                <p class="text-gray-700 text-sm md:text-base mt-1">{{ $c['desc'] }}</p>
                <span class="inline-block mt-3 text-sm md:text-base font-semibold border-b-2 border-black">SHOP NOW</span>
              </div>
            </a>
          @endforeach
        </div>
      </div>
    </section>

    {{-- ======= Shop by Category (mobile scroller / desktop grid) ======= --}}
    <section id="shop-by-category" class="w-full bg-black py-8 sm:py-10">
      <div class="px-4 sm:px-6 lg:px-8">
        <h2 class="text-white text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight mb-6">
          Shop by Category
        </h2>
      </div>

      <div x-data="cardScroller" class="relative" data-step="0.8">
        {{-- Right arrow (mobile only) --}}
        <button
          @click="next()"
          class="md:!hidden absolute right-3 top-1/2 -translate-y-1/2 z-10 h-10 w-10 rounded-full bg-white/15 backdrop-blur
                 border border-white/20 text-white flex items-center justify-center active:scale-95"
          aria-label="Scroll categories">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
               class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <div
          x-ref="wrap"
          class="no-scrollbar flex md:grid gap-4 md:gap-6 overflow-x-auto md:overflow-visible scroll-smooth px-4
                 md:px-8 md:grid-cols-3"
          style="scroll-snap-type: x mandatory;"
        >
          @php
            $cats = [
              ['img' => 'c1men.webp',   'label' => 'MEN',   'href' => route('user.mens'),   'cta' => 'Shop Men →'],
              ['img' => 'c2woman.avif', 'label' => 'WOMEN', 'href' => route('user.womans'), 'cta' => 'Shop Women →'],
              ['img' => 'c3kids.webp',  'label' => 'KIDS',  'href' => route('user.kids'),   'cta' => 'Shop Kids →'],
            ];
          @endphp

          @foreach($cats as $c)
            <a href="{{ $c['href'] }}" class="relative snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto overflow-hidden rounded-2xl group" aria-label="Shop {{ $c['label'] }}">
              <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
                <img src="{{ asset('storage/products/'.$c['img']) }}" alt="{{ $c['label'] }} category"
                     class="w-full h-full object-cover md:transition md:duration-500 md:group-hover:scale-[1.03]" loading="lazy"/>
              </div>
              <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
              <div class="absolute left-4 bottom-4 md:left-6 md:bottom-6 text-white">
                <span class="inline-block text-[10px] md:text-[11px] tracking-[0.2em] font-bold bg-white/15 backdrop-blur px-2.5 md:px-3 py-1 rounded">SHOP BY CATEGORY</span>
                <h3 class="mt-1 md:mt-2 text-2xl md:text-3xl sm:text-4xl font-black leading-none">{{ $c['label'] }}</h3>
                <span class="mt-2 md:mt-3 inline-flex items-center gap-2 text-xs md:text-sm font-semibold bg-white text-black px-3 md:px-4 py-1.5 md:py-2">{{ $c['cta'] }}</span>
              </div>
            </a>
          @endforeach
        </div>
      </div>
    </section>

    {{-- Utilities --}}
    <style>
      @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
      .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      .no-scrollbar::-webkit-scrollbar { display: none; }
      .marquee { width:max-content; animation: marquee 30s linear infinite; }
      @keyframes marquee { 0%{ transform: translateX(0);} 100%{ transform: translateX(-50%);} }
    </style>

    {{-- Still interested --}}
    <livewire:user.still-interested :limit="12" />

    
      {{-- ======= FULL IMAGE HERO (with CSS fallbacks, works without Tailwind build) ======= --}}
      <section class="relative w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] bg-black">
        <div class="relative hero-full">
          <img
            src="{{ asset('storage/products/image.jpg') }}"
            alt="Play like the legend hero graphic"
            class="absolute inset-0 w-full h-full hero-fit"
            loading="lazy"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

          <div class="absolute bottom-6 sm:bottom-8 left-4 sm:left-10 right-4 sm:right-auto text-white">
            <h2 class="text-2xl sm:text-4xl md:text-5xl font-extrabold tracking-tight uppercase">
              Play Like The Legend
            </h2>
            <a href="{{ route('user.mens') }}"
              class="mt-3 sm:mt-4 inline-flex items-center gap-2 bg-white text-black font-semibold px-5 py-3 sm:px-6 sm:py-3 rounded-xl shadow hover:shadow-lg transition
                      focus:outline-none focus:ring-2 focus:ring-white/80"
              aria-label="Shop Men - Play Like The Legend">
              Shop Now
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>
      </section>

      {{-- Fallback CSS so this section works even if Tailwind JIT classes were purged --}}
      <style>
        /* size the wrapper (replaces min-h-[240px] sm:h-[68vh] md:h-[72vh]) */
        .hero-full { min-height: 240px; }
        @media (min-width: 640px) { .hero-full { height: 68vh; } }
        @media (min-width: 768px) { .hero-full { height: 72vh; } }

        /* image fit (replaces object-cover sm:object-contain) */
        .hero-fit { object-fit: cover; }
        @media (min-width: 640px) { .hero-fit { object-fit: contain; } }
      </style>


  </main>

  {{-- Footer --}}
  @include('user.footer')

  {{-- Your bundle (kept) --}}
  @vite('resources/js/user-index.js')

  {{-- Fallback inline Alpine initializers (tiny, safe to keep) --}}
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('heroSlider', ({ images, interval = 6000 }) => ({
        images, index: 0, timer: null,
        start() { this.stop(); this.timer = setInterval(() => this.next(), interval); },
        stop() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
        next() { this.index = (this.index + 1) % this.images.length; },
        prev() { this.index = (this.index - 1 + this.images.length) % this.images.length; },
        key(e) { if (e.key === 'ArrowRight') this.next(); if (e.key === 'ArrowLeft') this.prev(); }
      }));
      Alpine.data('cardScroller', () => ({
        wrap: null,
        init(){ this.wrap = this.$refs.wrap; },
        next(){ if(!this.wrap) return; this.wrap.scrollBy({left: this.wrap.clientWidth * 0.85, behavior: 'smooth'}); },
        prev(){ if(!this.wrap) return; this.wrap.scrollBy({left: -this.wrap.clientWidth * 0.85, behavior: 'smooth'}); }
      }));
    });
  </script>
</x-app-layout>
