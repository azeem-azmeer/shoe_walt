<div class="space-y-5">
  {{-- tiny helpers --}}
  <style>
    .tbl-head{position:sticky;top:0;z-index:10;background:linear-gradient(180deg,#fcfcfc,#f5f5f5);border-bottom:1px solid #eee;box-shadow:0 1px 0 rgba(0,0,0,.03)}
    .zebra tbody tr:nth-child(odd){background:rgba(0,0,0,.015)}
    .chip-dot{width:.45rem;height:.45rem;border-radius:9999px;display:inline-block}
    .glow{box-shadow:0 10px 20px -8px rgba(0,0,0,.08), inset 0 1px 0 rgba(255,255,255,.8)}
  </style>

  {{-- ====== SUMMARY BAR ====== --}}
  @php
    $pageZeroCount = 0;
    foreach ($products as $pp) {
      $pageZeroCount += ($pp->sizes ? $pp->sizes->where('qty', 0)->count() : 0);
    }
  @endphp
  <div class="flex flex-wrap items-center gap-3 justify-between bg-white/80 rounded-xl border px-3 sm:px-4 py-2 glow">
    <div class="flex items-center gap-2 text-sm text-gray-600">
      <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M3 5h18v2H3V5Zm0 6h18v2H3v-2Zm0 6h18v2H3v-2Z"/>
      </svg>
      <span>
        Showing <strong>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</strong> of
        <strong>{{ $products->total() }}</strong>
      </span>
    </div>

    <div class="flex items-center gap-2">
      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-sm">
        <span class="chip-dot bg-rose-600"></span>
        <strong>{{ $pageZeroCount }}</strong> 0-stock sizes on this page
      </span>
    </div>
  </div>

  {{-- ====== FILTERS ====== --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
    <select wire:model.live="category"
            class="col-span-2 sm:col-span-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900/10">
      <option value="">All Categories</option>
      <option value="Men">Men</option>
      <option value="Women">Women</option>
      <option value="Kids">Kids</option>
    </select>

    <div class="col-span-2 sm:col-span-2">
      <div class="flex items-center justify-between">
        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
      </div>
      <div class="relative">
        <input type="search"
               wire:model.debounce.400ms="search"
               placeholder="Search products"
               class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-300" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-gray-400"
             viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M15.5 14h-.8l-.3-.3a6.5 6.5 0 1 0-.9.9l.3.3v.8l5 5 1.5-1.5-5-5Zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14Z"/>
        </svg>
      </div>
    </div>

    <div class="col-span-2 sm:col-span-1 flex sm:justify-end">
      <button type="button"
              wire:click="clear"
              class="w-full sm:w-auto rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">
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
