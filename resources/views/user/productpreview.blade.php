{{-- resources/views/user/productpreview.blade.php --}}
<x-app-layout>
  <style>
    [x-cloak]{display:none}
    .no-scrollbar { -ms-overflow-style:none; scrollbar-width:none; }
    .no-scrollbar::-webkit-scrollbar { display:none; }
  </style>

  <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:text-black focus:px-3 focus:py-2 focus:rounded">
    Skip to content
  </a>

  <x-slot name="header">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app:is-auth" content="{{ auth()->check() ? '1' : '0' }}">
    <meta name="app:register-url" content="{{ route('register') }}">
    <meta name="app:base-url" content="{{ url('') }}">
  </x-slot>

  {{-- Toast --}}
  <div x-data x-cloak x-show="$store.flash?.visible" x-transition.opacity class="fixed top-4 right-4 z-[60] max-w-sm w-[92vw] sm:w-[420px]">
    <div class="rounded-lg shadow-lg text-white px-4 py-3 pr-9 relative"
         :class="{
           'bg-green-600': $store.flash?.type==='success',
           'bg-red-600':   $store.flash?.type==='error',
           'bg-amber-600': $store.flash?.type==='warning',
           'bg-gray-900':  $store.flash?.type==='info'
         }"
         role="status" aria-live="polite">
      <div class="text-sm leading-5" x-text="$store.flash?.text"></div>
      <button type="button" class="absolute top-2.5 right-2.5 h-7 w-7 rounded-full/50 hover:bg-white/15 focus:outline-none" @click="$store.flash?.close()" aria-label="Dismiss">✕</button>
    </div>
  </div>

  <main id="main-content" class="bg-white">
    <div class="px-0">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-[2px] bg-transparent">

        {{-- LEFT: Gallery --}}
        <section x-data="imageZoom()" class="lg:col-span-7 xl:col-span-6 bg-white pl-4 sm:pl-6 lg:pl-8">
          @php $imgs = $images ?? []; @endphp
          <div class="max-w-[980px] mx-auto w-full">
            {{-- MOBILE --}}
            <div class="md:hidden relative"
                 x-data="{
                   step:0, atStart:true, atEnd:false,
                   init(){ this.$nextTick(()=>{ const first=this.$refs.mobScroller?.querySelector('figure > div'); const gap=12; this.step=(first?first.offsetWidth:320)+gap; this.onScroll(); }); },
                   onScroll(){ const el=this.$refs.mobScroller; if(!el) return; this.atStart=el.scrollLeft<=1; this.atEnd=Math.ceil(el.scrollLeft+el.clientWidth)>=el.scrollWidth-1; },
                   prev(){ this.$refs.mobScroller.scrollBy({left:-this.step,behavior:'smooth'}); },
                   next(){ this.$refs.mobScroller.scrollBy({left: this.step,behavior:'smooth'}); }
                 }"
                 x-init="init()">
              <div x-ref="mobScroller" class="no-scrollbar flex flex-nowrap gap-3 overflow-x-auto snap-x snap-mandatory px-1 py-1 bg-gray-200 rounded" aria-label="Product images" @scroll="onScroll">
                @forelse($imgs as $im)
                  <figure class="snap-start shrink-0 bg-white overflow-hidden flex items-center justify-center rounded">
                    <div class="overflow-hidden" style="width:9cm;height:8cm">
                      <img src="{{ $im }}" alt="{{ $product->product_name }} image" class="block w-full h-full object-cover cursor-zoom-in" @click="open(@js($im))" loading="lazy" />
                    </div>
                  </figure>
                @empty
                  <figure class="snap-start shrink-0 bg-white overflow-hidden flex items-center justify-center rounded">
                    <div class="overflow-hidden" style="width:9cm;height:8cm">
                      <img src="{{ asset('storage/products/placeholder.png') }}" alt="No image" class="block w-full h-full object-cover" loading="lazy" />
                    </div>
                  </figure>
                @endforelse
              </div>

              <button @click="prev" x-show="!atStart" x-cloak class="absolute left-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white shadow ring-1 ring-black/10 flex items-center justify-center active:scale-95" aria-label="Previous image">‹</button>
              <button @click="next" x-show="!atEnd" x-cloak class="absolute right-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white shadow ring-1 ring-black/10 flex items-center justify-center active:scale-95" aria-label="Next image">›</button>
            </div>

            {{-- DESKTOP/TABLET --}}
            <div class="hidden md:block">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200">
                <figure class="bg-white overflow-hidden flex items-center justify-center group">
                  <div class="overflow-hidden" style="width:11cm;height:10cm">
                    <img src="{{ $imgs[0] ?? asset('storage/products/placeholder.png') }}" alt="{{ $product->product_name }} - view 1" class="block w-full h-full object-cover cursor-zoom-in transition-transform duration-300 group-hover:scale-[1.03]" @click="open(@js($imgs[0] ?? asset('storage/products/placeholder.png')))" />
                  </div>
                </figure>

                @if(!empty($imgs[1]))
                  <figure class="bg-white overflow-hidden flex items-center justify-center group">
                    <div class="overflow-hidden" style="width:11cm;height:10cm">
                      <img src="{{ $imgs[1] }}" alt="{{ $product->product_name }} - view 2" class="block w-full h-full object-cover cursor-zoom-in transition-transform duration-300 group-hover:scale-[1.03]" @click="open(@js($imgs[1]))" />
                    </div>
                  </figure>
                @endif
              </div>

              @if(count($imgs) > 2)
                <div class="mt-[2px] grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200">
                  @foreach(array_slice($imgs, 2) as $idx => $im)
                    <figure class="bg-white overflow-hidden flex items-center justify-center group">
                      <div class="overflow-hidden" style="width:11cm;height:10cm">
                        <img src="{{ $im }}" alt="{{ $product->product_name }} - view {{ $idx + 3 }}" class="block w-full h-full object-cover cursor-zoom-in transition-transform duration-300 group-hover:scale-[1.03]" @click="open(@js($im))" />
                      </div>
                    </figure>
                  @endforeach
                </div>
              @endif
            </div>
          </div>

          {{-- Zoom Modal --}}
          <div x-show="zoomOpen" x-cloak class="fixed inset-0 z-50" @keydown.window.escape="close()">
            <div class="absolute inset-0 bg-black/70" @click="close()" aria-hidden="true"></div>
            <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
              <div class="relative w-full max-w-6xl bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b">
                  <div class="font-semibold">Zoom</div>
                  <div class="flex items-center gap-2">
                    <button type="button" @click="zoomOut()" class="h-9 px-3 rounded border hover:bg-gray-50">−</button>
                    <span class="text-sm w-12 text-center" x-text="`${Math.round(zoom*100)}%`"></span>
                    <button type="button" @click="zoomIn()" class="h-9 px-3 rounded border hover:bg-gray-50">+</button>
                    <button type="button" @click="close()" class="h-9 w-9 ml-2 rounded-full hover:bg-gray-100 flex items-center justify-center" aria-label="Close">✕</button>
                  </div>
                </div>
                <div class="max-h-[85vh] overflow-auto p-3 bg-neutral-50">
                  <img :src="zoomSrc" alt="Zoomed image" class="block mx-auto object-contain" :style="`transform: scale(${zoom}); transform-origin:center;`" @wheel.prevent="onWheel($event)">
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- RIGHT: Details --}}
        <aside class="lg:col-span-5 xl:col-span-6 bg-white px-4 sm:px-6 lg:px-8 py-6 lg:sticky lg:top-8 self-start">
          <div class="text-gray-600 text-sm">{{ $product->category }}</div>
          <h1 class="mt-1 text-3xl sm:text-4xl font-black leading-tight">{{ $product->product_name }}</h1>

          <div class="mt-3 text-2xl font-semibold">
            ${{ number_format((float) $product->price, 2) }}
          </div>

          <div class="mt-6">
            <h2 class="font-bold text-lg">Description</h2>
            <p class="mt-2 text-gray-700 leading-relaxed">
              {{ $product->description ?: 'No description provided.' }}
            </p>
          </div>

          {{-- Size selector + Size Guide --}}
          <div x-data="sizeGuide()" class="mt-6">
            <div class="flex items-center justify-between">
              <h2 class="font-bold text-lg">Size</h2>
              <button type="button" @click="open = true" class="text-sm font-bold underline underline-offset-2 decoration-2 px-2 py-1 rounded transition hover:bg-gray-100 hover:decoration-4">
                Size guide
              </button>
            </div>

            <div x-data="{ selected:null, pick(s){ this.selected=s; $store.pdp.selectedSize=s; } }" class="mt-3 grid grid-cols-5 gap-2">
              @forelse($sizes as $s)
                <button type="button"
                        @click="{{ $s['disabled']
                          ? "\$store.flash.show('Sorry to inform there is no stock available in size {$s['label']}.','error',6000)"
                          : "pick('{$s['label']}')" }}"
                        class="h-11 border rounded flex items-center justify-center text-sm font-semibold transition
                               {{ $s['disabled'] ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:border-black' }}"
// to solve highlight glitches on some IDEs
                        :class="{ 'ring-2 ring-black' : selected === '{{ $s['label'] }}' }"
                        @if($s['disabled']) disabled aria-disabled="true" @else aria-disabled="false" @endif
                        title="{{ $s['disabled'] ? 'Sorry to inform there is no stock available in size ' . $s['label'] : 'In stock: ' . $s['qty'] }}">
                  {{ $s['label'] }}
                </button>
              @empty
                <div class="col-span-5 text-gray-500">No sizes configured.</div>
              @endforelse
            </div>

            {{-- Size Guide Modal --}}
            <div x-show="open" x-cloak class="fixed inset-0 z-50">
              <div class="absolute inset-0 bg-black/60" @click="open=false" aria-hidden="true"></div>
              <div class="absolute inset-0 flex items-start sm:items-center justify-center p-4 sm:p-6">
                <div class="w-full max-w-6xl bg-white rounded-2xl shadow-xl overflow-hidden">
                  <div class="flex items-center justify-between px-5 sm:px-8 py-4 border-b">
                    <div class="space-y-1">
                      <h3 class="text-2xl font-black tracking-wide">Size Guide</h3>
                      <p class="text-sm text-gray-500">Choose a category and unit.</p>
                    </div>
                    <button class="h-10 w-10 rounded-full hover:bg-gray-100" @click="open=false" aria-label="Close">✕</button>
                  </div>

                  <div class="px-5 sm:px-8 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap gap-2">
                      <button @click="tab='babies'"   :class="tab==='babies'  ? 'bg-black text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'" class="px-3 py-1.5 rounded-full text-sm font-medium">Babies & Toddlers</button>
                      <button @click="tab='children'" :class="tab==='children'? 'bg-black text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'" class="px-3 py-1.5 rounded-full text-sm font-medium">Children</button>
                      <button @click="tab='youth'"    :class="tab==='youth'   ? 'bg-black text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'" class="px-3 py-1.5 rounded-full text-sm font-medium">Youth & Teens</button>
                      <button @click="tab='adults'"   :class="tab==='adults'  ? 'bg-black text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'" class="px-3 py-1.5 rounded-full text-sm font-medium">Men’s & Women’s</button>
                    </div>

                    <div class="inline-flex rounded-full border overflow-hidden">
                      <button @click="unit='in'" :class="unit==='in' ? 'bg-black text-white' : 'bg-white text-gray-800'" class="px-3 py-1.5 text-sm">Inches</button>
                      <button @click="unit='cm'" :class="unit==='cm' ? 'bg-black text-white' : 'bg-white text-gray-800'" class="px-3 py-1.5 text-sm border-l">cm</button>
                    </div>
                  </div>

                  <div class="px-5 sm:px-8 pb-6">
                    <div class="overflow-x-auto">
                      <table class="min-w-[860px] w-full text-sm">
                        <thead>
                          <tr class="bg-black text-white">
                            <th class="text-left px-3 py-3 font-semibold">Heel-toe</th>
                            <template x-for="c in columns" :key="c">
                              <th class="px-3 py-3 font-semibold text-left" x-text="formatCol(c)"></th>
                            </template>
                          </tr>
                        </thead>
                        <tbody>
                          <tr class="border-b">
                            <th class="px-3 py-3 text-left font-semibold">UK</th>
                            <template x-for="(v,i) in currentRows.uk" :key="i"><td class="px-3 py-3" x-text="v"></td></template>
                          </tr>
                          <tr class="border-b">
                            <th class="px-3 py-3 text-left font-semibold">US</th>
                            <template x-for="(v,i) in currentRows.us" :key="i"><td class="px-3 py-3" x-text="v"></td></template>
                          </tr>
                          <tr>
                            <th class="px-3 py-3 text-left font-semibold">EU</th>
                            <template x-for="(v,i) in currentRows.eu" :key="i"><td class="px-3 py-3" x-text="v"></td></template>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <div class="mt-6 rounded-xl bg-gray-50 p-4 sm:p-6">
                      <h4 class="font-bold mb-2">How to measure</h4>
                      <ol class="list-decimal pl-5 space-y-1 text-sm text-gray-700">
                        <li>Place a sheet of paper on the floor with the heel against a wall, then stand on it.</li>
                        <li>Mark the end of the longest toe and measure from the wall to the mark.</li>
                        <li>Measure both feet, use the larger measurement, then match it in the chart.</li>
                      </ol>
                    </div>
                  </div>
                </div> {{-- /.modal panel --}}
              </div>   {{-- /.positioner --}}
            </div>     {{-- /.x-show open --}}
          </div>       {{-- /.x-data sizeGuide --}}

          {{-- CTA --}}
          <div x-data="{ canAdd: {{ $inStock ? 'true' : 'false' }} }" class="mt-6 flex items-center gap-3">
            <button type="button"
                    @click="$store.actions.addToWishlist({{ $product->product_id }})"
                    class="h-14 w-14 border rounded flex items-center justify-center hover:bg-gray-50"
                    aria-label="Add to wishlist" title="Add to wishlist">♥</button>

            <button type="button"
                    :disabled="!canAdd || !$store.pdp.selectedSize"
                    @click="$store.actions.addToCart({{ $product->product_id }})"
                    class="h-12 w-40 sm:w-48 bg-black text-white font-bold rounded shadow disabled:opacity-40 disabled:cursor-not-allowed hover:shadow-md transition">
              ADD TO CART
            </button>
          </div>

          {{-- Collapsibles --}}
          <div x-data="{ open: { delivery: true, returns: false, care: false } }" class="mt-6 border rounded-xl divide-y">
            <section class="p-4">
              <button type="button" class="w-full flex items-center justify-between text-left"
                      @click="open.delivery = !open.delivery" :aria-expanded="open.delivery" aria-controls="sec-delivery">
                <h3 class="text-lg font-semibold">Free Delivery & Returns</h3>
                <svg class="h-5 w-5 transition-transform duration-200" :class="open.delivery ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.17l3.71-2.94a.75.75 0 1 1 .94 1.17l-4.24 3.36a.75.75 0 0 1-.94 0L5.21 8.4a.75.75 0 0 1 .02-1.19z" clip-rule="evenodd"/></svg>
              </button>
              <div id="sec-delivery" x-show="open.delivery" x-transition.opacity.duration.150ms class="mt-3 text-sm text-gray-700 space-y-3">
                <p>Free standard delivery with your membership.</p>
                <ul class="list-disc pl-5 space-y-1">
                  <li>You can return your order free of charge within 30 days. <a href="#" class="underline">Some exclusions apply</a>.</li>
                </ul>
              </div>
            </section>

            <section class="p-4">
              <button type="button" class="w-full flex items-center justify-between text-left"
                      @click="open.returns = !open.returns" :aria-expanded="open.returns" aria-controls="sec-options">
                <h3 class="text-lg font-semibold">Delivery Options</h3>
                <svg class="h-5 w-5 transition-transform duration-200" :class="open.returns ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.17l3.71-2.94a.75.75 0 1 1 .94 1.17l-4.24 3.36a.75.75 0 0 1-.94 0L5.21 8.4a.75.75 0 0 1 .02-1.19z" clip-rule="evenodd"/></svg>
              </button>
              <div id="sec-options" x-show="open.returns" x-transition.opacity.duration.150ms class="mt-3 text-sm text-gray-700 space-y-1">
                <p>Standard (3–5 business days) — Free</p>
                <p>Express (1–2 business days) — $9.99</p>
                <p>Next-day — $19.99</p>
              </div>
            </section>

            <section class="p-4">
              <button type="button" class="w-full flex items-center justify-between text-left"
                      @click="open.care = !open.care" :aria-expanded="open.care" aria-controls="sec-care">
                <h3 class="text-lg font-semibold">Care & Product Info</h3>
                <svg class="h-5 w-5 transition-transform duration-200" :class="open.care ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.17l3.71-2.94a.75.75 0 1 1 .94 1.17l-4.24 3.36a.75.75 0 0 1-.94 0L5.21 8.4a.75.75 0 0 1 .02-1.19z" clip-rule="evenodd"/></svg>
              </button>
              <div id="sec-care" x-show="open.care" x-transition.opacity.duration.150ms class="mt-3 text-sm text-gray-700 space-y-2">
                <p>Spot clean only. Do not machine wash.</p>
                <p>Upper: mesh & synthetic. Outsole: rubber.</p>
                <p>1-year manufacturer warranty.</p>
              </div>
            </section>
          </div>
        </aside>
      </div>
    </div>

    @php
      $wishlistIcon        = asset('storage/products/wishlist.png');
      $wishlistIconActive  = asset('storage/products/wishlist1.png');
      $frameWidthDesktop   = 'calc(33cm + 24px * 5)';
      $wishlistIds         = $wishlistIds ?? [];
    @endphp

    @if(!empty($related) && count($related))
      <section class="mt-12"
         x-data="{
           step:0, atStart:true, atEnd:false,
           init(){ this.$nextTick(()=>{ const first=this.$refs.scroller?.querySelector('[data-card]'); const gap=24; this.step=(first?first.offsetWidth:227)+gap; this.onScroll(); }); },
           onScroll(){ const el=this.$refs.scroller; if(!el) return; this.atStart=el.scrollLeft<=1; this.atEnd=Math.ceil(el.scrollLeft+el.clientWidth)>=el.scrollWidth-1; },
           prev(){ this.$refs.scroller.scrollBy({left:-this.step,behavior:'smooth'}); },
           next(){ this.$refs.scroller.scrollBy({left: this.step,behavior:'smooth'}); }
         }" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-[40px] sm:text-[44px] md:text-[48px] font-black tracking-[0.08em] uppercase mb-6">Still Interested?</h2>
          <div class="relative">
            <div class="mx-auto overflow-hidden" style="max-width: {{ $frameWidthDesktop }};">
              <div x-ref="scroller" class="no-scrollbar flex gap-6 overflow-x-auto scroll-smooth py-1" style="scroll-snap-type:x mandatory;" @scroll="onScroll">
                @foreach($related as $p)
                  @php
                    $rid   = $p['id'] ?? ($p['product_id'] ?? null);
                    $href  = $rid ? route('user.product.preview', ['product' => $rid]) : '#';
                    $inWish = $rid ? in_array($rid, ($wishlistIds ?? []), true) : false;
                  @endphp

                  <div data-card class="snap-start shrink-0 block group" style="width:6cm" x-data="{ inWish: {{ $inWish ? 'true' : 'false' }} }">
                    <a href="{{ $href }}" class="block relative {{ $rid ? '' : 'pointer-events-none opacity-60' }}"
                       @if(!$rid) aria-disabled="true" tabindex="-1" @endif aria-label="{{ $p['name'] ?? 'Product' }}">

                      {{-- IMAGE: no frame, fill the box --}}
                      <div class="relative overflow-hidden" style="width:6cm;height:7cm;">
                        <img
                          src="{{ $p['img'] ?? asset('storage/products/placeholder.png') }}"
                          alt="{{ $p['name'] ?? 'Product' }}"
                          class="absolute inset-0 w-full h-full object-cover"
                          loading="lazy" decoding="async">
                        @if($rid)
                          <button type="button"
                                  class="absolute top-3 right-3 h-7 w-7 rounded-full bg-white/0"
                                  @click.stop="$store.actions.addToWishlist({{ $rid }}).then(ok => { if (ok) inWish = true; })"
                                  aria-label="Add to wishlist">
                            <img :src="inWish ? '{{ $wishlistIconActive }}' : '{{ $wishlistIcon }}'" alt="" class="h-7 w-7">
                          </button>
                        @endif
                      </div>

                      <div class="pt-3">
                        <div class="font-extrabold text-[18px]">${{ number_format($p['price'] ?? 0, 0) }}</div>
                        <div class="mt-1 font-semibold text-[16px] leading-snug line-clamp-1">{{ $p['name'] ?? 'Product' }}</div>
                        <div class="mt-1 text-gray-600 text-[15px]">{{ $p['cat'] ?? '' }}</div>
                      </div>
                    </a>
                  </div>
                @endforeach
              </div>
            </div>

            <button @click="prev" x-show="!atStart" x-cloak class="flex items-center justify-center absolute left-0 top-1/2 -translate-y-1/2 h-10 w-10 md:h-11 md:w-11 rounded-full bg-white shadow-md ring-1 ring-black/10 z-10 hover:bg-gray-50 active:scale-95" aria-label="Scroll left">‹</button>
            <button @click="next" x-show="!atEnd" x-cloak class="flex items-center justify-center absolute right-0 top-1/2 -translate-y-1/2 h-10 w-10 md:h-11 md:w-11 rounded-full bg-white shadow-md ring-1 ring-black/10 z-10 hover:bg-gray-50 active:scale-95" aria-label="Scroll right">›</button>
          </div>
        </div>
      </section>
    @endif

    {{-- MINI CART POPUP --}}
    <div x-data="{}" x-show="$store.miniCart.open" x-cloak class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/40" @click="$store.miniCart.open=false"></div>
      <div class="absolute right-4 top-16 w-[360px] bg-white rounded-xl shadow-xl ring-1 ring-black/10">
        <div class="flex items-center justify-between px-4 py-3 border-b">
          <div class="font-semibold">Added to Bag</div>
          <button class="h-8 w-8 rounded-full hover:bg-gray-100" @click="$store.miniCart.open=false">✕</button>
        </div>
        <div class="max-h-[50vh] overflow-y-auto divide-y">
          <template x-for="it in $store.miniCart.items" :key="it.id">
            <div class="flex gap-3 px-4 py-3">
              <div class="h-16 w-16 bg-gray-100 rounded overflow-hidden">
                <img :src="it.img" alt="" class="w-full h-full object-cover">
              </div>
              <div class="flex-1">
                <div class="text-sm font-semibold" x-text="it.name"></div>
                <div class="text-xs text-gray-600">Size: <span x-text="it.size"></span></div>
                <div class="text-xs text-gray-600">Qty: <span x-text="it.quantity"></span></div>
                <div class="text-sm font-bold" x-text="$store.miniCart.currency(it.price)"></div>
              </div>
              <button class="text-xs text-red-600 hover:underline" @click="$store.miniCart.remove(it.id)">Remove</button>
            </div>
          </template>
          <template x-if="!$store.miniCart.items.length">
            <div class="px-4 py-6 text-sm text-gray-500">Your bag is empty.</div>
          </template>
        </div>
        <div class="px-4 py-3 border-t flex items-center justify-between">
          <a href="{{ route('user.viewbag') }}" class="px-3 py-2 rounded border hover:bg-gray-50 text-sm">View Bag</a>
          @auth
            <a href="{{ route('user.checkout') }}" class="px-3 py-2 rounded bg-black text-white text-sm font-semibold">Checkout</a>
          @else
            <a href="{{ route('register') }}" class="px-3 py-2 rounded bg-black text-white text-sm font-semibold">Checkout</a>
          @endauth
        </div>
      </div>
    </div>
  </main>

  <div class="h-6 md:h-8 bg-white"></div>
  @include('user.footer')

  {{-- Global config for JS --}}
  <script>
    window.__APP = {
      isAuth: {{ auth()->check() ? 'true' : 'false' }},
      registerUrl: @js(route('register')),
      baseUrl: @js(url('/')),
      csrf: @js(csrf_token()),
    };
  </script>

  @vite('resources/js/user-productpreview.js')
</x-app-layout>
