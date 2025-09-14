{{-- resources/views/user/order-show.blade.php --}}
<x-app-layout>
  <x-slot name="header"></x-slot>

  <div class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-6">
        <h1 class="text-3xl font-black">Order #{{ $order->id }}</h1>
        @if(session('success'))
          <p class="mt-2 text-emerald-700 font-semibold">{{ session('success') }}</p>
        @endif
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- LEFT: Items --}}
        <div class="lg:col-span-8 space-y-4">
          <div class="border rounded-xl p-5">
            <h2 class="font-extrabold mb-3">Items</h2>
            <ul class="divide-y">
              @foreach($items as $it)
                <li class="py-3 flex items-start justify-between gap-3">
                  {{-- Product Image --}}
                  <div class="w-16 h-16 flex-shrink-0 rounded-md overflow-hidden border bg-gray-50">
                    <img src="{{ $it->product->main_image
                                  ? (\Illuminate\Support\Str::startsWith($it->product->main_image, ['http://','https://'])
                                      ? $it->product->main_image
                                      : asset('storage/'.$it->product->main_image))
                                  : asset('storage/products/placeholder.webp') }}"
                         alt="{{ $it->product->product_name ?? 'Product' }}"
                         class="w-full h-full object-cover">
                  </div>

                  {{-- Product Details --}}
                  <div class="flex-1 min-w-0">
                    <div class="font-semibold">{{ $it->product->product_name ?? 'Product' }}</div>
                    <div class="text-sm text-gray-600">
                      Size: {{ $it->size }} · Qty: {{ $it->quantity }}
                    </div>
                  </div>

                  {{-- Price --}}
                  <div class="font-semibold">
                    ${{ number_format($it->unit_price, 2) }}
                  </div>
                </li>
              @endforeach
            </ul>
          </div>
        </div>

        {{-- RIGHT: Summary + Review (ONE sticky wrapper to avoid overlap) --}}
        <aside class="lg:col-span-4">
          <div class="sticky top-6 space-y-6">
            {{-- Summary (not sticky itself) --}}
            <div class="border rounded-xl p-5">
              <h2 class="font-extrabold mb-3">Summary</h2>
              <dl class="text-sm space-y-2">
                <div class="flex justify-between">
                  <dt>Status</dt>
                  <dd class="font-semibold">{{ $order->status }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt>Total</dt>
                  <dd class="font-extrabold">${{ number_format($order->total, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt>Your Orders</dt>
                  <dd class="font-semibold">{{ $orderCount }}</dd>
                </div>
              </dl>

              <div class="mt-4 text-sm">
                <div class="font-semibold mb-1">Ship to</div>
                <p class="text-gray-700">{{ $order->street_address }}</p>
              </div>

              <a href="{{ route('user.index') }}"
                 class="mt-5 inline-block px-5 py-3 rounded-xl font-bold text-white bg-gray-900 hover:bg-black">
                 Continue shopping
              </a>
            </div>

            {{-- Your Review --}}
            <div class="border rounded-xl p-5">
              <h2 class="font-extrabold mb-3">Your Review</h2>
              <div id="reviewContent" data-order-id="{{ $order->id }}" class="text-sm"></div>
              <p id="reviewMsg" class="mt-3 text-sm"></p>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>

  @include('user.footer')

  {{-- Load review JS via Vite --}}
  @vite('resources/js/user-review.js')
</x-app-layout>
