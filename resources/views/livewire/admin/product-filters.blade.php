<div class="flex flex-wrap items-center gap-3 mb-2 mt-2">
  <h1 class="text-2xl font-bold text-gray-800">Products</h1>

  {{-- Category: change triggers filtering immediately --}}
  <div class="flex items-center gap-2 ml-2">
    <select wire:model="category" wire:change="apply"
            class="px-3 py-1 rounded-lg border text-sm text-gray-700 shadow-sm">
      <option value="">All Categories</option>
      <option value="Men">Men</option>
      <option value="Women">Women</option>
      <option value="Kids">Kids</option>
    </select>
  </div>

  {{-- Search: press Enter to search --}}
  <div class="flex items-center gap-2">
    <input type="text"
           wire:model.defer="search"
           wire:keydown.enter="apply"
           placeholder="Search name/category/description…"
           class="px-3 py-1 rounded-lg border text-sm text-gray-700 shadow-sm w-56" />

    <button type="button" wire:click="apply"
            class="px-3 py-1 text-sm rounded-lg border shadow hover:bg-gray-50">
      Apply
    </button>

    <button type="button" wire:click="clear"
            class="px-3 py-1 text-sm rounded-lg border shadow hover:bg-gray-50">
      Clear
    </button>

    <span wire:loading class="text-xs text-gray-500">Updating…</span>
  </div>

  <a href="{{ route('admin.products.create') }}"
     class="ml-auto px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg shadow hover:from-blue-700 hover:to-blue-600">
    + Add Product
  </a>
</div>
