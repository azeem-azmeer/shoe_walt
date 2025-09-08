{{-- resources/views/admin/product-edit.blade.php --}}
<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg space-y-6">

                <a href="{{ route('admin.products') }}"
                   class="px-3 py-1 rounded border hover:bg-gray-50 text-sm">← Back</a>

                <form id="editForm"
                      class="space-y-8"
                      onsubmit="return submitEdit(event, {{ $product->product_id }});"
                      enctype="multipart/form-data">
                    @csrf

                    <h1 class="text-2xl md:text-3xl font-semibold text-gray-800 text-center">
                        Edit Product — {{ $product->product_name }}
                    </h1>

                    {{-- Images block (always side-by-side) --}}
                    <div class="grid grid-cols-2 gap-8 items-start">
                        {{-- Main image --}}
                        <div>
                            <label class="block text-sm font-medium mb-2 text-center">Main Image</label>

                            <div class="relative border-2 border-dashed border-gray-300 rounded-xl bg-white h-80
                                        flex items-center justify-center hover:bg-gray-50">
                                <img id="mainPreview"
                                     src="{{ $product->main_image ? asset('storage/'.$product->main_image) : '' }}"
                                     class="absolute inset-0 w-full h-full object-cover rounded-xl {{ $product->main_image ? '' : 'hidden' }}"
                                     alt="Main image preview">

                                <div id="mainPlaceholder"
                                     class="text-5xl text-gray-300 select-none {{ $product->main_image ? 'hidden' : '' }}">+
                                </div>

                                <input id="mainInput" type="file" name="main_image"
                                       accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif"
                                       class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                        </div>

                        {{-- View images (2x2) --}}
                        <div>
                            <label class="block text-sm font-medium mb-2 text-center">View Product Images</label>

                            <div class="grid grid-cols-2 gap-4">
                                @for ($i = 0; $i < 4; $i++)
                                    @php
                                        $vi = $viewImages[$i] ?? null;
                                        $src = $vi ? asset('storage/'.$vi) : '';
                                        $has = !empty($vi);
                                    @endphp

                                    <div class="relative border-2 border-dashed border-gray-300 rounded-xl bg-white aspect-square
                                                flex items-center justify-center hover:bg-gray-50">
                                        <img id="vPreview-{{ $i }}"
                                             src="{{ $src }}"
                                             class="absolute inset-0 w-full h-full object-contain p-2 rounded-xl {{ $has ? '' : 'hidden' }}"
                                             alt="View image {{ $i+1 }} preview">

                                        <div id="vPlaceholder-{{ $i }}"
                                             class="text-3xl text-gray-300 select-none {{ $has ? 'hidden' : '' }}">+
                                        </div>

                                        <input id="vInput-{{ $i }}" type="file" name="view_images[{{ $i }}]"
                                        accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif"
                                        class="absolute inset-0 opacity-0 cursor-pointer">
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- Fields --}}
                    <div class="grid md:grid-cols-2 grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Product Name</label>
                            <input type="text" name="product_name" value="{{ $product->product_name }}"
                                   class="w-full border rounded p-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Price</label>
                            <input type="number" step="0.01" name="price" value="{{ $product->price }}"
                                   class="w-full border rounded p-2" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Description</label>
                            <textarea name="description" rows="3"
                                      class="w-full border rounded p-2">{{ $product->description }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Category</label>
                            <select name="category" class="w-full border rounded p-2" required>
                                @foreach(['Men','Women','Kids'] as $cat)
                                    <option value="{{ $cat }}" @selected($product->category===$cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select name="status" class="w-full border rounded p-2" required>
                                <option value="Active" @selected($product->status==='Active')>Active</option>
                                <option value="Inactive" @selected($product->status==='Inactive')>Inactive</option>
                            </select>
                        </div>
                    </div>

                    {{-- Sizes --}}
                    <div x-data="{ rows: @js($sizes ?: [['size'=>'','qty'=>0]]) }" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium">Sizes & Quantities</label>
                            <button type="button" @click="rows.push({size:'',qty:0})"
                                    class="text-blue-600 text-sm hover:underline">+ Add Size</button>
                        </div>

                        <template x-for="(row, i) in rows" :key="i">
                            <div class="flex gap-2 items-center">
                                <input type="text" class="flex-1 border rounded p-2"
                                       :name="`sizes[${i}][size]`" x-model="row.size" placeholder="Size">
                                <input type="number" class="flex-1 border rounded p-2"
                                       :name="`sizes[${i}][qty]`" x-model.number="row.qty" placeholder="Qty" min="0">
                                <button type="button" class="text-red-600 text-sm" @click="rows.splice(i,1)">Remove</button>
                            </div>
                        </template>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                            Update Product
                        </button>
                        <a href="{{ route('admin.products') }}" class="px-4 py-2 border rounded">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Live previews + submit via API helper --}}
    <script>
        function bindPreview(inputId, previewId, placeholderId) {
            const input = document.getElementById(inputId);
            const img   = document.getElementById(previewId);
            const ph    = document.getElementById(placeholderId);
            if (!input || !img || !ph) return;

            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (file) {
                    const url = URL.createObjectURL(file);
                    img.src = url;
                    img.classList.remove('hidden');
                    ph.classList.add('hidden');
                } else {
                    img.src = '';
                    img.classList.add('hidden');
                    ph.classList.remove('hidden');
                }
            });
        }

        // main + four view images
        bindPreview('mainInput', 'mainPreview', 'mainPlaceholder');
        for (let i = 0; i < 4; i++) {
            bindPreview(`vInput-${i}`, `vPreview-${i}`, `vPlaceholder-${i}`);
        }

        async function submitEdit(ev, id) {
            ev.preventDefault();
            const fd = new FormData(ev.target);
            fd.append('_method', 'PUT');

            const res = await api(`/api/admin/products/${id}`, {
                method: 'POST',  // multipart + _method=PUT for Laravel
                body: fd
            });

            if (res.status === 401) { alert('Not authenticated. Please log in again.'); return false; }
            if (res.status === 419) { alert('CSRF expired. Refresh and try again.'); return false; }
            if (!res.ok) {
                let msg = 'Update failed';
                try { const data = await res.json(); if (data?.message) msg = data.message; } catch {}
                alert(msg); return false;
            }
            window.location.href = "{{ route('admin.products') }}";
            return false;
        }
    </script>
</x-app-layout>
