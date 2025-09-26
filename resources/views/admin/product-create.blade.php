<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Product</h2>
  </x-slot>

  {{-- Important for token mint --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow-sm sm:rounded-lg space-y-8">
        <a href="{{ route('admin.products') }}" class="px-3 py-1 rounded border hover:bg-gray-50 text-sm">← Back</a>

        <form id="createForm" class="space-y-8" onsubmit="return submitCreate(event);" enctype="multipart/form-data">
          @csrf
          <h1 class="text-2xl md:text-3xl font-semibold text-gray-800 text-center">Add Product</h1>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
            <div>
              <label class="block text-sm font-medium mb-2 text-center md:text-left">Main Image</label>
              <div class="relative border-2 border-dashed border-gray-300 rounded-xl bg-white h-72 md:h-80 flex items-center justify-center hover:bg-gray-50">
                <img id="mainPreview" class="absolute inset-0 w-full h-full object-cover rounded-xl hidden" alt="">
                <div id="mainPlaceholder" class="text-5xl text-gray-300 select-none">+</div>
                <input id="mainInput" type="file" name="main_image"
                       accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif"
                       class="absolute inset-0 opacity-0 cursor-pointer" required>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2 text-center md:text-left">View Product Images</label>
              <div class="grid grid-cols-2 gap-4">
                @for ($i = 0; $i < 4; $i++)
                  <div class="relative border-2 border-dashed border-gray-300 rounded-xl bg-white aspect-square flex items-center justify-center hover:bg-gray-50">
                    <img id="vPreview-{{ $i }}" class="absolute inset-0 w-full h-full object-contain p-2 rounded-xl hidden" alt="">
                    <div id="vPlaceholder-{{ $i }}" class="text-3xl text-gray-300 select-none">+</div>
                    <input id="vInput-{{ $i }}" type="file" name="view_images[]"
                           accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif"
                           class="absolute inset-0 opacity-0 cursor-pointer">
                  </div>
                @endfor
              </div>
            </div>
          </div>

          <div class="grid md:grid-cols-2 grid-cols-1 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Product Name</label>
              <input type="text" name="product_name" class="w-full border rounded p-2" required>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Price</label>
              <input type="number" step="0.01" name="price" class="w-full border rounded p-2" required>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea name="description" rows="3" class="w-full border rounded p-2"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Category</label>
              <select name="category" class="w-full border rounded p-2" required>
                <option value="">Select Category</option>
                <option value="Men">Men</option>
                <option value="Women">Women</option>
                <option value="Kids">Kids</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Status</label>
              <select name="status" class="w-full border rounded p-2" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>

          <input type="hidden" name="sizes" id="sizes_json">
            <div x-data="{
                  rows: [{ size: '', qty: 0 }],
                  sync(){ document.getElementById('sizes_json').value = JSON.stringify(this.rows); }
                }" x-init="sync()" x-effect="sync()" class="space-y-2">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium">Sizes & Quantities</label>
                <button type="button" class="text-blue-600 text-sm hover:underline"
                        @click="rows.push({size:'',qty:0}); sync()">+ Add Size</button>
              </div>
              <template x-for="(row, i) in rows" :key="i">
                <div class="flex gap-2 items-center">
                  <!-- Size input -->
                  <input type="text"
                        class="flex-1 border rounded p-2 
                                text-sm md:text-base
                                px-2 py-1 md:px-3 md:py-2"
                        placeholder="Size"
                        x-model="row.size" @input="sync()">

                  <!-- Qty input -->
                  <input type="number" min="0"
                        class="flex-1 border rounded p-2
                                text-sm md:text-base
                                px-2 py-1 md:px-3 md:py-2"
                        placeholder="Qty"
                        x-model.number="row.qty" @input="sync()">

                  <!-- Remove button -->
                  <button type="button" class="text-red-600 text-xs md:text-sm"
                          @click="rows.splice(i,1); if(rows.length===0){rows.push({size:'',qty:0})}; sync()">
                    Remove
                  </button>
                </div>
              </template>
            </div>


          <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Add Product</button>
            <a href="{{ route('admin.products') }}" class="px-4 py-2 border rounded">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Provide config to JS (respects sub-folder deployments) --}}
 <script>
  window.__APP = Object.assign({}, window.__APP || {}, {
    baseUrl: @js(url('/')),
    csrf: @js(csrf_token()),
    adminProductsUrl: @js(route('admin.products')),
  });
</script>


  @vite('resources/js/admin-product-create.js')
</x-app-layout>
