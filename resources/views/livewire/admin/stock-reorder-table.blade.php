<div class="space-y-5">
  {{-- tiny helpers --}}

  {{-- ====== SUMMARY BAR ====== --}}
  @php
    $pageZeroCount = 0;
    foreach ($products as $pp) {
      $pageZeroCount += ($pp->sizes ? $pp->sizes->where('qty', 0)->count() : 0);
    }
  @endphp
 {{-- ====== FILTERS (Stock Reorder label + Category + Search + Clear) ====== --}}
<div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
  {{-- Stock Reorder (label only) --}}
  {{-- Stock Reorder (plain heading like "Products") --}}
<div class="sm:col-span-2 flex items-end">
  <h2 class="text-2xl font-bold text-gray-900 leading-none">Stock Reorder</h2>
</div>


  {{-- Category --}}
  <div class="sm:col-span-3">
    <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
    <select
      wire:model.live="category"
      class="h-11 w-full rounded-xl border border-gray-300 px-3 text-sm
             focus:ring-2 focus:ring-gray-900/10 focus:border-gray-300"
    >
      <option value="">All Categories</option>
      <option value="Men">Men</option>
      <option value="Women">Women</option>
      <option value="Kids">Kids</option>
    </select>
  </div>

  {{-- Search --}}
  <div class="sm:col-span-5">
    <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
    <div class="relative">
      <form wire:submit.prevent="go">
        <input
          type="search"
          wire:model.debounce.400ms="search"
          placeholder="Search products"
          class="h-11 w-full rounded-xl border border-gray-300 pl-10 pr-3 text-sm
                 focus:ring-2 focus:ring-gray-900/10 focus:border-gray-300"
        />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-gray-400"
             viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M15.5 14h-.8l-.3-.3a6.5 6.5 0 1 0-.9.9l.3.3v.8l5 5 1.5-1.5-5-5Zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14Z"/>
        </svg>
      </form>
    </div>
  </div>

  {{-- Clear --}}
  <div class="sm:col-span-2 flex items-end sm:justify-end">
    <button type="button"
            wire:click="clear"
            class="h-11 w-full sm:w-auto rounded-xl border border-gray-300 bg-white px-4 text-sm hover:bg-gray-50">
      Clear
    </button>
  </div>
</div>


  {{-- ====== TABLE ====== --}}
  <div class="relative bg-white rounded-xl border shadow-sm overflow-hidden glow">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm zebra">
        <thead class="tbl-head text-gray-700">
          <tr>
            <th class="py-2.5 px-3 text-left">Image</th>
            <th class="py-2.5 px-3 text-left">Name</th>
            <th class="py-2.5 px-3 text-left">Category</th>
            <th class="py-2.5 px-3 text-left">Inventory (Size · Stock)</th>
          </tr>
        </thead>

        <tbody class="divide-y">
          @forelse($products as $p)
            @php
              $zeroSizes = $p->sizes ? $p->sizes->filter(fn($s) => (int)$s->qty === 0) : collect();
            @endphp

            <tr class="hover:bg-rose-50/40 transition">
              <td class="py-2.5 px-3">
                @if($p->main_image)
                  <img src="{{ asset('storage/'.$p->main_image) }}" alt=""
                       class="w-14 h-14 rounded-md object-cover border ring-1 ring-gray-100" loading="lazy">
                @else
                  <div class="w-14 h-14 rounded-md bg-gray-100 flex items-center justify-center text-gray-400 text-xs">N/A</div>
                @endif
              </td>

              <td class="py-2.5 px-3">
                <div class="font-medium text-gray-900">{{ $p->product_name }}</div>
                @if($zeroSizes->isNotEmpty())
                  <div class="text-[11px] text-rose-600 mt-0.5">
                    {{ $zeroSizes->count() }} size{{ $zeroSizes->count()>1?'s':'' }} out of stock
                  </div>
                @endif
              </td>

              <td class="py-2.5 px-3">
                <span class="px-2 py-0.5 text-[11px] rounded-full
                  @if($p->category==='Men') bg-blue-100 text-blue-700
                  @elseif($p->category==='Women') bg-pink-100 text-pink-700
                  @elseif($p->category==='Kids') bg-purple-100 text-purple-700
                  @else bg-gray-100 text-gray-700 @endif">
                  {{ $p->category }}
                </span>
              </td>

              <td class="py-2.5 px-3">
                @if($zeroSizes->isNotEmpty())
                  {{-- Always 3 per row --}}
                  <div class="grid grid-cols-3 gap-1.5">
                    @foreach($zeroSizes as $s)
                      <span class="px-1.5 py-1 text-[11px] rounded border text-center bg-rose-50 text-rose-700 border-rose-300">
                        <span class="chip-dot bg-rose-600 mr-1"></span>UK {{ $s->size }} · <b>0</b>
                      </span>
                    @endforeach
                  </div>
                @else
                  <span class="inline-flex items-center gap-2 text-sm text-emerald-700">
                    <span class="chip-dot bg-emerald-500"></span>Good: no zero-stock sizes
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="py-6 px-3 text-center text-gray-500">
                All good! No items at zero stock right now.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Loading veil --}}
    <div wire:loading.delay
         class="absolute inset-0 bg-white/65 backdrop-blur-[1px] grid place-items-center">
      <div class="flex items-center gap-3 text-gray-600">
        <div class="animate-spin h-6 w-6 border-2 border-gray-300 border-t-gray-700 rounded-full"></div>
        <span class="text-sm">Updating…</span>
      </div>
    </div>
  </div>

  <div>
    {{ $products->links() }}
  </div>
</div>
