{{-- resources/views/user/index.blade.php --}}
<x-app-layout>
  {{-- Skip link for keyboard/AT users --}}
  <a href="#main-content"
     class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:text-black focus:px-3 focus:py-2 focus:rounded">
    Skip to content
  </a>

  {{-- No header slot so Jetstream doesn’t render the extra white bar --}}
  <x-slot name="header"></x-slot>

  @php
    $heroSlides = [
      ['src' => asset('storage/products/bgwallpaer2.webp'), 'alt' => 'Summer collection for men',   'href' => route('user.mens')],
      ['src' => asset('storage/products/bgwallpaer1.webp'), 'alt' => 'Kids sneakers and sandals',  'href' => route('user.kids')],
      ['src' => asset('storage/products/bgwallpaer3.webp'), 'alt' => 'Women’s newest arrivals',    'href' => route('user.womans')],
    ];
  @endphp

  {{-- ======= HERO SLIDER (Accessible, single block) ======= --}}
  <section
    x-data="heroSlider({ images: @js($heroSlides), interval: 5000 })"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
    @keydown="key($event)"
    tabindex="0"
    role="region"
    aria-roledescription="carousel"
    aria-label="Featured promotions"
    class="relative w-full h-[90vh] sm:h-[92vh] md:h-[95vh] min-h-[460px] overflow-hidden outline-none"
  >
    <template x-for="(slide, i) in images" :key="i">
      <div
        class="absolute inset-0"
        x-show="index === i"
        x-transition.opacity
        :aria-hidden="index !== i"
      >
        <img :src="slide.src" :alt="slide.alt"
             class="w-full h-full object-cover"
             :loading="i === 0 ? 'eager' : 'lazy'"/>
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-transparent"></div>
      </div>
    </template>

    {{-- Slider arrows --}}
    <button @click="prev()"
      class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full h-12 w-12 sm:h-10 sm:w-10 flex items-center justify-center shadow
             focus:outline-none focus:ring-2 focus:ring-black"
      aria-label="Previous slide">‹</button>

    <button @click="next()"
      class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full h-12 w-12 sm:h-10 sm:w-10 flex items-center justify-center shadow
             focus:outline-none focus:ring-2 focus:ring-black"
      aria-label="Next slide">›</button>

    {{-- Dots --}}
    <div class="absolute bottom-24 sm:bottom-20 inset-x-0 flex justify-center gap-3">
      <template x-for="(slide, i) in images" :key="i">
        <button
          class="h-3 w-3 rounded-full border border-white/80"
          :class="index === i ? 'bg-white' : 'bg-white/50 hover:bg-white/70'"
          @click="index = i"
          :aria-label="`Go to slide ${i+1}`"
          :aria-current="index === i ? 'true' : 'false'">
        </button>
      </template>
    </div>

    {{-- SHOP NOW --}}
    <div class="absolute bottom-8 inset-x-0 flex justify-center">
      <a
        :href="images[index]?.href || '#'"
        class="px-6 py-3 bg-white text-black font-semibold tracking-wide inline-flex items-center gap-2 shadow hover:shadow-md transition
              focus:outline-none focus:ring-2 focus:ring-white/80"
        :aria-label="`Shop now: ${images[index]?.alt || ''}`"
      >
        SHOP NOW
      </a>
    </div>
  </section>

  <main id="main-content">
    {{-- ======= Category tiles (ONE block: scroller on mobile, grid on md+) ======= --}}
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

        {{-- One list that scrolls on mobile, grids on md+ --}}
        <div
          x-ref="wrap"
          class="no-scrollbar flex md:grid gap-4 sm:gap-6 md:gap-8 overflow-x-auto md:overflow-visible scroll-smooth
                 md:grid-cols-2 lg:grid-cols-4 px-1"
          style="scroll-snap-type: x mandatory;"
        >
          {{-- 1) SAMBA -> kids --}}
          <a href="{{ route('user.kids') }}"
             class="relative group snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto
                    rounded-lg md:rounded-none overflow-hidden bg-white/90 md:bg-transparent"
             aria-label="Shop SAMBA for Kids">
            <div class="pointer-events-none md:absolute md:-inset-2 md:border-2 md:border-black md:opacity-0 group-hover:opacity-100 md:transition md:duration-300"></div>
            <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
              <img src="{{ asset('storage/products/shoe1.avif') }}" alt="Samba shoe"
                   class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.02]" loading="lazy"/>
            </div>
            <div class="p-4 md:p-0 md:pt-4">
              <h3 class="text-base md:text-lg font-extrabold tracking-tight">SAMBA</h3>
              <p class="text-gray-700 text-sm md:text-base mt-1">Always iconic, always in style.</p>
              <span class="inline-block mt-3 text-sm md:text-base font-semibold border-b-2 border-black">SHOP NOW</span>
            </div>
          </a>

          {{-- 2) 327 New Balance -> women --}}
          <a href="{{ route('user.womans') }}"
             class="relative group snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto
                    rounded-lg md:rounded-none overflow-hidden bg-white/90 md:bg-transparent"
             aria-label="Shop 327 New Balance for Women">
            <div class="pointer-events-none md:absolute md:-inset-2 md:border-2 md:border-black md:opacity-0 group-hover:opacity-100 md:transition md:duration-300"></div>
            <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
              <img src="{{ asset('storage/products/shoe2.jpg') }}" alt="327 New Balance shoe"
                   class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.02]" loading="lazy"/>
            </div>
            <div class="p-4 md:p-0 md:pt-4">
              <h3 class="text-base md:text-lg font-extrabold tracking-tight">327 New Balance</h3>
              <p class="text-gray-700 text-sm md:text-base mt-1">Feel fast. In all aspects of life.</p>
              <span class="inline-block mt-3 text-sm md:text-base font-semibold border-b-2 border-black">SHOP NOW</span>
            </div>
          </a>

          {{-- 3) Air Jordan -> men --}}
          <a href="{{ route('user.mens') }}"
             class="relative group snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto
                    rounded-lg md:rounded-none overflow-hidden bg-white/90 md:bg-transparent"
             aria-label="Shop Air Jordan for Men">
            <div class="pointer-events-none md:absolute md:-inset-2 md:border-2 md:border-black md:opacity-0 group-hover:opacity-100 md:transition md:duration-300"></div>
            <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
              <img src="{{ asset('storage/products/shoe3.jpg') }}" alt="Air Jordan shoe"
                   class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.02]" loading="lazy"/>
            </div>
            <div class="p-4 md:p-0 md:pt-4">
              <h3 class="text-base md:text-lg font-extrabold tracking-tight">Air Jordan</h3>
              <p class="text-gray-700 text-sm md:text-base mt-1">Become Legendary.</p>
              <span class="inline-block mt-3 text-sm md:text-base font-semibold border-b-2 border-black">SHOP NOW</span>
            </div>
          </a>

          {{-- 4) GAZELLE -> kids --}}
          <a href="{{ route('user.kids') }}"
             class="relative group snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto
                    rounded-lg md:rounded-none overflow-hidden bg-white/90 md:bg-transparent"
             aria-label="Shop Gazelle for Kids">
            <div class="pointer-events-none md:absolute md:-inset-2 md:border-2 md:border-black md:opacity-0 group-hover:opacity-100 md:transition md:duration-300"></div>
            <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
              <img src="{{ asset('storage/products/shoe4.jpg') }}" alt="Gazelle shoe"
                   class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.02]" loading="lazy"/>
            </div>
            <div class="p-4 md:p-0 md:pt-4">
              <h3 class="text-base md:text-lg font-extrabold tracking-tight">CLASS READY: GAZELLE</h3>
              <p class="text-gray-700 text-sm md:text-base mt-1">Amplify your style this school year.</p>
              <span class="inline-block mt-3 text-sm md:text-base font-semibold border-b-2 border-black">SHOP NOW</span>
            </div>
          </a>
        </div>
      </div>
    </section>

    {{-- ======= Shop by Category (ONE block: scroller on mobile, grid on md+) ======= --}}
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
          aria-label="Scroll categories"
        >
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
          {{-- MEN --}}
          <a href="{{ route('user.mens') }}"
             class="relative snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto overflow-hidden rounded-lg group"
             aria-label="Shop Men">
            <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
              <img src="{{ asset('storage/products/c1men.webp') }}" alt="Men category"
                   class="w-full h-full object-cover md:transition md:duration-500 md:group-hover:scale-[1.03]" loading="lazy" />
            </div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
            <div class="absolute left-4 bottom-4 md:left-6 md:bottom-6 text-white">
              <span class="inline-block text-[10px] md:text-[11px] tracking-[0.2em] font-bold bg-white/15 backdrop-blur px-2.5 md:px-3 py-1 rounded">
                SHOP BY CATEGORY
              </span>
              <h3 class="mt-1 md:mt-2 text-2xl md:text-3xl sm:text-4xl font-black leading-none">MEN</h3>
              <span class="mt-2 md:mt-3 inline-flex items-center gap-2 text-xs md:text-sm font-semibold bg-white text-black px-3 md:px-4 py-1.5 md:py-2">
                Shop Men →
              </span>
            </div>
          </a>

          {{-- WOMEN --}}
          <a href="{{ route('user.womans') }}"
             class="relative snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto overflow-hidden rounded-lg group"
             aria-label="Shop Women">
            <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
              <img src="{{ asset('storage/products/c2woman.avif') }}" alt="Women category"
                   class="w-full h-full object-cover md:transition md:duration-500 md:group-hover:scale-[1.03]" loading="lazy" />
            </div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
            <div class="absolute left-4 bottom-4 md:left-6 md:bottom-6 text-white">
              <span class="inline-block text-[10px] md:text-[11px] tracking-[0.2em] font-bold bg-white/15 backdrop-blur px-2.5 md:px-3 py-1 rounded">
                SHOP BY CATEGORY
              </span>
              <h3 class="mt-1 md:mt-2 text-2xl md:text-3xl sm:text-4xl font-black leading-none">WOMEN</h3>
              <span class="mt-2 md:mt-3 inline-flex items-center gap-2 text-xs md:text-sm font-semibold bg-white text-black px-3 md:px-4 py-1.5 md:py-2">
                Shop Women →
              </span>
            </div>
          </a>

          {{-- KIDS --}}
          <a href="{{ route('user.kids') }}"
             class="relative snap-start md:snap-none shrink-0 md:shrink w-[78vw] max-w-[360px] md:w-auto overflow-hidden rounded-lg group"
             aria-label="Shop Kids">
            <div class="w-full overflow-hidden aspect-[4/3] md:aspect-[3/4] max-h-[220px] md:max-h-none">
              <img src="{{ asset('storage/products/c3kids.webp') }}" alt="Kids category"
                   class="w-full h-full object-cover md:transition md:duration-500 md:group-hover:scale-[1.03]" loading="lazy" />
            </div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
            <div class="absolute left-4 bottom-4 md:left-6 md:bottom-6 text-white">
              <span class="inline-block text-[10px] md:text-[11px] tracking-[0.2em] font-bold bg-white/15 backdrop-blur px-2.5 md:px-3 py-1 rounded">
                SHOP BY CATEGORY
              </span>
              <h3 class="mt-1 md:mt-2 text-2xl md:text-3xl sm:text-4xl font-black leading-none">KIDS</h3>
              <span class="mt-2 md:mt-3 inline-flex items-center gap-2 text-xs md:text-sm font-semibold bg-white text-black px-3 md:px-4 py-1.5 md:py-2">
                Shop Kids →
              </span>
            </div>
          </a>
        </div>
      </div>
    </section>

    {{-- ======= Utility CSS (no scrollbar + reduced motion) ======= --}}
    <style>
      @media (prefers-reduced-motion: reduce) {
        * { transition: none !important; animation: none !important; }
      }
      .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>

    {{-- Still interested --}}
    <livewire:user.still-interested :limit="12" />
  </main>

  {{-- ======= FULL IMAGE HERO (shorter, with CTA) ======= --}}
  <section class="relative w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] bg-black">
    <div class="relative h-[60vh] sm:h-[68vh] md:h-[72vh] min-h-[340px]">
      <img
        src="{{ asset('storage/products/image.jpg') }}"
        alt="Play like the legend hero graphic"
        class="absolute inset-0 w-full h-full object-contain"
        loading="lazy"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
      <div class="absolute bottom-6 sm:bottom-8 left-4 sm:left-10 right-4 sm:right-auto text-white">
        <h2 class="text-2xl sm:text-4xl md:text-5xl font-extrabold tracking-tight uppercase">Play Like The Legend</h2>
        <a href="{{ route('user.mens') }}"
           class="mt-3 sm:mt-4 inline-flex items-center gap-2 bg-white text-black font-semibold px-5 py-3 sm:px-6 sm:py-3 shadow hover:shadow-md transition
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

  {{-- Footer inside the layout for consistent spacing --}}
  @include('user.footer')

  {{-- ======= Alpine helpers (loaded once) ======= --}}
  <script>
    document.addEventListener('alpine:init', () => {
      // Carousel / hero slider
      Alpine.data('heroSlider', (opts = {}) => ({
        index: 0,
        images: Array.isArray(opts.images) ? opts.images : [],
        timer: null,
        interval: Number(opts.interval) || 5000,
        start() { this.stop(); if (this.images.length > 1) this.timer = setInterval(() => this.next(), this.interval); },
        stop()  { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
        next()  { if (!this.images.length) return; this.index = (this.index + 1) % this.images.length; },
        prev()  { if (!this.images.length) return; this.index = (this.index - 1 + this.images.length) % this.images.length; },
        key(e)  { if (e.key === 'ArrowRight') this.next(); if (e.key === 'ArrowLeft') this.prev(); },
      }));

      // Horizontal scroller used by both sections
      Alpine.data('cardScroller', () => ({
        get stepPx() {
          const pct = parseFloat(this.$root.dataset.step || '0.8');
          const safe = isNaN(pct) ? 0.8 : Math.max(0.2, Math.min(1, pct));
          return Math.round(window.innerWidth * safe);
        },
        next(){ this.$refs.wrap?.scrollBy({ left:  this.stepPx, behavior: 'smooth' }); },
        prev(){ this.$refs.wrap?.scrollBy({ left: -this.stepPx, behavior: 'smooth' }); },
      }));
    });
  </script>
</x-app-layout>
