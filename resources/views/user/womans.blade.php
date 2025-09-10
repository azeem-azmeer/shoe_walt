{{-- resources/views/user/womans.blade.php --}}
<x-app-layout>
  <div class="bg-white min-h-screen">
    <x-slot name="header"></x-slot>

    {{-- HERO --}}
    <section class="relative">
      <div class="h-[40vh] md:h-[52vh] bg-cover bg-center"
           style="background-image:url('{{ \Storage::url('products/womensbg.jpg') }}')">
        <div class="absolute inset-0 bg-black/35"></div>
        <div class="relative h-full flex items-end">
          <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 pb-8 sm:pb-12">
            <h1 class="text-white text-3xl sm:text-4xl font-extrabold drop-shadow-lg">
              Women’s Sneakers
            </h1>
          </div>
        </div>
      </div>
    </section>

    {{-- Filters --}}
    <livewire:user.filters
      category="Women"
      size="{{ request('size', '') }}"
      sort="{{ request('sort', '') }}" />

    {{-- GRID + PAGINATION --}}
    <section class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse(($products ?? collect()) as $p)
          @php
            $raw = array_values(array_filter([
              $p->main_image, $p->view_image2, $p->view_image3, $p->view_image4,
            ]));

            $toUrl = function ($path) {
              if (!$path) return null;
              $path = str_replace('\\', '/', ltrim((string) $path, '/'));
              if (preg_match('~^https?://~i', $path)) return $path;
              if (str_starts_with($path, '/storage/')) return url(ltrim($path,'/'));
              if (str_starts_with($path, 'storage/'))  return url($path);
              return \Storage::url($path);
            };

            $imgs = array_values(array_filter(array_map($toUrl, $raw)));
            if (empty($imgs)) $imgs = [asset('storage/products/placeholder.webp')];
          @endphp

          {{-- Product Card with Alpine hover slideshow --}}
          <a href="{{ route('user.product.preview', $p->product_id) }}"
             class="group block border border-gray-300 rounded-md p-3 transition-colors hover:border-black"
             x-data="slideCard"
             data-imgs='@json($imgs, JSON_UNESCAPED_SLASHES)'
             @mouseenter="begin()"
             @mouseleave="end()">

            <div class="bg-white border border-gray-300 transition-colors group-hover:border-black overflow-hidden prod-img-box">
              <img class="w-full h-full object-cover object-center block"
                   :src="current"
                   alt="{{ $p->product_name }}"
                   loading="lazy"
                   onerror="this.onerror=null;this.src='{{ asset('storage/products/placeholder.webp') }}'">
            </div>

            <div class="pt-3">
              <div class="text-sm font-semibold">${{ number_format((float) $p->price, 2) }}</div>
              <div class="text-sm text-gray-800 line-clamp-2">{{ $p->product_name }}</div>
              <div class="text-xs text-gray-500 mt-1">Women’s Sneakers</div>
            </div>
          </a>
        @empty
          <p class="text-sm text-gray-500 col-span-full">No products found.</p>
        @endforelse
      </div>

      <div class="mt-6">
        {{ $products->links() }}
      </div>
    </section>

    {{-- Responsive image box --}}
    <style>
      .prod-img-box { width: 7cm; height: 7cm; }
      @media (max-width: 767.98px) {
        .prod-img-box { width: 100%; height: auto; aspect-ratio: 1 / 1; }
      }
    </style>

    <div class="h-6 md:h-8 bg-white"></div>
    @include('user.footer')
  </div>

  {{-- Tiny Alpine component (same as men’s) --}}
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('slideCard', () => ({
        imgs: [],
        i: 0,
        t: null,
        init() {
          try { this.imgs = JSON.parse(this.$el.dataset.imgs || '[]'); }
          catch { this.imgs = []; }
        },
        get current() { return this.imgs[this.i] || ''; },
        begin() {
          if (!Array.isArray(this.imgs) || this.imgs.length < 2) return;
          if (this.t) clearInterval(this.t);
          this.t = setInterval(() => { this.i = (this.i + 1) % this.imgs.length; }, 1000);
        },
        end() {
          if (this.t) clearInterval(this.t);
          this.t = null; this.i = 0;
        },
      }));
    });
  </script>
</x-app-layout>
