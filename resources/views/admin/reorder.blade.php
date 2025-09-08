<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stock Reorder</h2>
      <a href="{{ route('admin.products.create') }}"
         class="rounded-lg bg-blue-600 text-white px-4 py-2 font-semibold hover:bg-blue-700">
        + Add Product
      </a>
    </div>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow-sm sm:rounded-lg">
        <livewire:admin.stock-reorder-table />
      </div>
    </div>
  </div>
</x-app-layout>
