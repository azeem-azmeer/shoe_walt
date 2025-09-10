{{-- resources/views/user/wishlist.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Wishlist</h2>
    <meta name="csrf-token" content="{{ csrf_token() }}">
  </x-slot>

  @php
    $frameWidthDesktop = 'calc(33cm + 24px * 5)'; /* 5.5 tiles @ 6cm + 5 gaps */
  @endphp

  <style>
    [x-cloak]{display:none}
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
  </style>

  <div class="bg-white min-h-screen">
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if($items->isEmpty())
          <div class="bg-white p-6 rounded shadow text-gray-600">Your wishlist is empty.</div>
        @else
          <section class="mt-2"
            x-data="{
              step:0, atStart:true, atEnd:false,
              init(){ this.$nextTick(()=>{ const first=this.$refs.scroller?.querySelector('[data-card]'); const gap=24; this.step=(first?first.offsetWidth:227)+gap; this.onScroll(); }); },
              onScroll(){ const el=this.$refs.scroller; if(!el) return; this.atStart=el.scrollLeft<=1; this.atEnd=Math.ceil(el.scrollLeft+el.clientWidth)>=el.scrollWidth-1; },
              prev(){ this.$refs.scroller.scrollBy({left:-this.step,behavior:'smooth'}); },
              next(){ this.$refs.scroller.scrollBy({left: this.step,behavior:'smooth'}); }
            }" x-init="init()">
            <div class="relative">
              <div class="mx-auto overflow-hidden" style="max-width: {{ $frameWidthDesktop }};">
                <div x-ref="scroller"
                     class="no-scrollbar flex gap-6 overflow-x-auto scroll-smooth py-1"
                     style="scroll-snap-type:x mandatory;"
                     @scroll="onScroll">
                  @foreach($items as $it)
                    @php $p = $it->product; @endphp
                    <div data-card data-wish-id="{{ $it->id }}" class="snap-start shrink-0 block group" style="width:6cm">
                      <a href="{{ route('user.product.preview', $p) }}" class="block">
                        {{-- ✅ Image fills the box, no frame --}}
                        <div class="w-[6cm] h-[7cm] overflow-hidden">
                          <img
                            src="{{ $p->main_image_url ?? asset('storage/products/placeholder.png') }}"
                            alt="{{ $p->product_name }}"
                            class="w-full h-full object-cover block"
                            loading="lazy">
                        </div>

                        <div class="pt-3">
                          <div class="font-extrabold text-[18px]">${{ number_format((float) $p->price, 0) }}</div>
                          <div class="mt-1 font-semibold text-[16px] leading-snug line-clamp-1">{{ $p->product_name }}</div>
                          <div class="mt-1 text-gray-600 text-[15px]">{{ $p->category }}</div>
                        </div>
                      </a>

                      <div class="mt-2">
                        <button type="button"
                                class="text-sm text-red-600 hover:underline"
                                onclick="removeFromWishlist({{ $it->id }})">
                          Remove
                        </button>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

              <button @click="prev" x-show="!atStart" x-cloak
                class="flex items-center justify-center absolute left-0 top-1/2 -translate-y-1/2
                       h-10 w-10 md:h-11 md:w-11 rounded-full bg-white shadow-md ring-1 ring-black/10 z-10
                       hover:bg-gray-50 active:scale-95"
                aria-label="Scroll left">‹</button>

              <button @click="next" x-show="!atEnd" x-cloak
                class="flex items-center justify-center absolute right-0 top-1/2 -translate-y-1/2
                       h-10 w-10 md:h-11 md:w-11 rounded-full bg-white shadow-md ring-1 ring-black/10 z-10
                       hover:bg-gray-50 active:scale-95"
                aria-label="Scroll right">›</button>
            </div>
          </section>
        @endif
      </div>
    </div>

    <div class="h-6 md:h-8 bg-white"></div>

    <script>
      window.__APP = Object.assign({}, window.__APP || {}, {
        baseUrl: @js(url('/')),
        csrf: @js(csrf_token()),
      });
    </script>

    @vite('resources/js/user-wishlist.js')
    @include('user.footer')
  </div>
</x-app-layout>
