<div class="space-y-3">
  {{-- Filters (kept the same sizes) --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
    <select wire:model.live="category"
            class="col-span-2 sm:col-span-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
      <option value="">All Categories</option>
      <option value="Men">Men</option>
      <option value="Women">Women</option>
      <option value="Kids">Kids</option>
    </select>

    <input type="search"
           wire:model.debounce.400ms="search"
           placeholder="Search products"
           class="col-span-2 sm:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm" />

    <div class="col-span-2 sm:col-span-1 flex sm:justify-end">
      <button type="button"
              wire:click="clear"
              class="w-full sm:w-auto rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50">
        Clear
      </button>
    </div>
  </div>

  {{-- Table (compact) --}}
  <div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full text-s"> 
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="py-2 px-3 text-left">Image</th>     
          <th class="py-2 px-3 text-left">Name</th>
          <th class="py-2 px-3 text-left">Category</th>
          <th class="py-2 px-3 text-left">Inventory (Size_Stock)</th>
        </tr>
      </thead>

      <tbody class="divide-y">
        @forelse($products as $p)
          @php
            $zeroSizes = $p->sizes ? $p->sizes->filter(fn($s) => (int)$s->qty === 0) : collect();
          @endphp

          <tr class="hover:bg-red-50/40">
            <td class="py-2 px-3">
              @if($p->main_image)
                <img src="{{ asset('storage/'.$p->main_image) }}" alt=""
                     class="w-14 h-14 rounded-md object-cover border" loading="lazy"> 
              @else
                <div class="w-10 h-10 rounded-md bg-gray-100 flex items-center justify-center text-gray-400 text-[10px]">?</div>
              @endif
            </td>

            <td class="py-2 px-3 font-medium text-gray-900">
              {{ $p->product_name }}
            </td>

            <td class="py-2 px-3">
              <span class="px-2 py-0.5 text-[11px] rounded-full
                @if($p->category==='Men') bg-blue-100 text-blue-700
                @elseif($p->category==='Women') bg-pink-100 text-pink-700
                @elseif($p->category==='Kids') bg-purple-100 text-purple-700
                @else bg-gray-100 text-gray-700 @endif">
                {{ $p->category }}
              </span>
            </td>

            {{-- size_stock: only sizes with qty = 0, 3 per row always --}}
            <td class="py-2 px-3">
              @if($zeroSizes->isNotEmpty())
                <div class="grid grid-cols-3 gap-1.5"> {{-- ← ALWAYS 3 PER ROW --}}
                  @foreach($zeroSizes as $s)
                    <span
                      class="px-1.5 py-0.5 text-[11px] rounded border text-center
                             bg-red-50 text-red-700 border-red-300">
                      UK {{ $s->size }}: <b>0</b>
                    </span>
                  @endforeach
                </div>
              @else
                <span class="text-gray-400">No zero-stock sizes</span>
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

  <div>
    {{ $products->links() }}
  </div>
</div>
