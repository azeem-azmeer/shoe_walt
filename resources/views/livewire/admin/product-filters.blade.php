<div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-2 mt-2">
  {{-- Title (left on desktop, top on mobile) --}}
  <h1 class="text-2xl font-bold text-gray-800">Products</h1>

  {{-- Filters + Search (grow to fill; full width on mobile) --}}
  <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 sm:flex-1 w-full">
    {{-- Category --}}
    <select
      wire:model="category"
      wire:change="apply"
      class="w-full sm:w-[180px] px-3 py-2 rounded-lg border text-sm text-gray-700 shadow-sm"
      aria-label="Filter by category"
    >
      <option value="">All Categories</option>
      <option value="Men">Men</option>
      <option value="Women">Women</option>
      <option value="Kids">Kids</option>
    </select>

    {{-- Search --}}
    <div class="relative w-full">
      <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="11" cy="11" r="7" stroke-width="1.5"></circle>
          <path d="M20 20l-3.5-3.5" stroke-width="1.5"></path>
        </svg>
      </div>
      <input
        type="text"
        wire:model.live.debounce.400ms="search"
        wire:input.debounce.400ms="apply"
        wire:keydown.enter="apply"
        placeholder="Search name, category, or description…"
        class="w-full pl-10 pr-4 h-11 rounded-2xl border border-gray-300 bg-white shadow-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               placeholder:text-gray-400 transition"
        aria-label="Search products"
      />
    </div>
  </div>

  {{-- Add button (right on desktop, full width on mobile) --}}
  <a href="{{ route('admin.products.create') }}"
     class="w-full sm:w-auto sm:ml-auto h-11 px-5 inline-flex items-center justify-center
            bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700">
    + Add Product
  </a>
</div>
