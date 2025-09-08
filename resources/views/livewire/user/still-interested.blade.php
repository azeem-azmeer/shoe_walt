<div>
  <style>
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
  </style>

  {{-- STILL INTERESTED? (Kids + Women only) --}}
  <section
    x-data="{
      step: 320,
      init(){ this.$nextTick(() => { const el = this.$refs.first; if (el) this.step = el.offsetWidth + 24; }); },
      prev(){ this.$refs.scroller.scrollBy({ left: -this.step, behavior: 'smooth' }) },
      next(){ this.$refs.scroller.scrollBy({ left:  this.step, behavior: 'smooth' }) },
    }"
    x-init="init()"
    class="w-full bg-white/20 py-10"
  >
    <div class="px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl sm:text-4xl font-extrabold tracking-wide mb-6 uppercase">Still Interested?</h2>
    </div>

    <div class="relative">
      {{-- Desktop/Tablet arrows --}}
      <button @click="prev"
        class="hidden md:flex absolute left-2 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/80 text-white items-center justify-center hover:bg-black z-10"
        aria-label="Scroll left">‹</button>
      <button @click="next"
        class="hidden md:flex absolute right-2 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/80 text-white items-center justify-center hover:bg-black z-10"
        aria-label="Scroll right">›</button>

      {{-- Mobile arrows --}}
      <button @click="prev"
        class="flex md:hidden absolute left-2 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/80 text-white items-center justify-center hover:bg-black z-10"
        aria-label="Scroll left">‹</button>
      <button @click="next"
        class="flex md:hidden absolute right-2 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/80 text-white items-center justify-center hover:bg-black z-10"
        aria-label="Scroll right">›</button>

      {{-- scroller --}}
      <div x-ref="scroller" class="no-scrollbar flex gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory px-4">
        @forelse($products as $i => $p)
          <a
            href="{{ route('user.product.preview', $p) }}" {{-- route-model binding via product_id --}}
            class="snap-start shrink-0 w-[260px] md:w-[300px] xl:w-[320px] group cursor-pointer
                   transition-transform duration-300 ease-out hover:-translate-y-1 hover:shadow-xl rounded-md"
            @if($i === 0) x-ref="first" @endif
            aria-label="View {{ $p->product_name }}"
          >
            <div class="relative w-full aspect-[4/3] overflow-hidden rounded-md">
              <img src="{{ $p->main_image_url }}" alt="{{ $p->product_name }}"
                   class="absolute inset-0 w-full h-full object-contain transition-transform duration-300 ease-out group-hover:scale-105 will-change-transform" />
            </div>

            <div class="px-1 mt-3">
              <div class="text-[#dd1d21] text-lg font-extrabold">
                ${{ number_format((float) $p->price, 2) }}
              </div>
              <div class="mt-1 font-semibold text-gray-900 truncate group-hover:underline">
                {{ $p->product_name }}
              </div>
              <div class="text-gray-500">
                {{ $p->category }}
              </div>
            </div>
          </a>
        @empty
          <p class="text-gray-500 px-4">No items yet.</p>
        @endforelse
      </div>
    </div>
  </section>
</div>
