{{-- resources/views/admin/products.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Products</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 space-y-6 bg-white shadow-sm sm:rounded-lg">

        @if(session('success'))
          <div class="p-3 bg-green-100 text-green-800 rounded-lg shadow">
            ✅ {{ session('success') }}
          </div>
        @endif

        {{-- Livewire filters (search + category only) --}}
        <livewire:admin.product-filters />

        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100 border-b text-gray-700">
              <tr>
                <th class="py-3 px-4 text-left">Image</th>
                <th class="py-3 px-4 text-left">Name</th>
                <th class="py-3 px-4 text-left">Sold</th>
                <th class="py-3 px-4 text-left">Status</th>
                <th class="py-3 px-4 text-left">Inventory</th>
                <th class="py-3 px-4 text-left">Category</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @forelse ($products as $product)
                <tr class="hover:bg-gray-50">
                  <td class="py-3 px-4">
                    @if($product->main_image)
                      <img src="{{ asset('storage/'.$product->main_image) }}" loading="lazy"
                           class="w-14 h-14 object-cover rounded-md border" alt="">
                    @else
                      <div class="w-14 h-14 rounded-md bg-gray-100 flex items-center justify-center text-gray-400">?</div>
                    @endif
                  </td>

                  <td class="py-3 px-4 font-medium text-gray-900">{{ $product->product_name }}</td>

                  <td class="py-3 px-4 text-gray-600">{{ (int)($product->sold_pieces ?? 0) }} Sold</td>

                  {{-- Livewire Status Toggle --}}
                  <td class="py-3 px-4">
                    <livewire:admin.status-toggle
                      :product-id="$product->product_id"
                      :status="$product->status"
                      wire:key="st-{{ $product->product_id }}"
                    />
                  </td>

                  {{-- INVENTORY: mobile 1-col; sm:3-col; lg:5-col; qty=0 in red --}}
                  <td class="py-3 px-4">
                    @if($product->sizes && $product->sizes->count())
                      <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-2">
                        @foreach($product->sizes as $s)
                          @php $qty = (int) $s->qty; @endphp
                          <span
                            class="px-2 py-1 rounded text-xs border text-center
                                   {{ $qty === 0
                                       ? 'bg-red-50 text-red-700 border-red-300'
                                       : 'bg-gray-100 text-gray-800 border-gray-300' }}">
                            UK {{ $s->size }}:
                            <span class="font-semibold">{{ $qty }}</span>
                          </span>
                        @endforeach
                      </div>
                    @else
                      <span class="text-gray-400">No sizes</span>
                    @endif
                  </td>

                  <td class="py-3 px-4">
                    <span class="px-3 py-1 text-xs rounded-full
                      @if($product->category === 'Men') bg-blue-100 text-blue-700
                      @elseif($product->category === 'Women') bg-pink-100 text-pink-700
                      @elseif($product->category === 'Kids') bg-purple-100 text-purple-700
                      @else bg-gray-100 text-gray-600 @endif">
                      {{ $product->category }}
                    </span>
                  </td>

                  <td class="py-3 px-4 text-right space-x-2">
                    <a href="{{ route('admin.products.edit', $product->product_id) }}"
                       class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 text-xs shadow">
                      Edit
                    </a>
                    <button type="button"
                            onclick="deleteProduct({{ $product->product_id }})"
                            class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600 text-xs shadow">
                      Delete
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="py-8 px-4 text-center text-gray-500">No products found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div id="pagination" class="mt-3">
          {{ $products->links() }}
        </div>

      </div>
    </div>
  </div>

  @vite('resources/js/admin-products.js')
</x-app-layout>
