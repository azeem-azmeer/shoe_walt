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
        <div class="lg:col-span-8 space-y-4">
          <div class="border rounded-xl p-5">
            <h2 class="font-extrabold mb-3">Items</h2>
            <ul class="divide-y">
              @foreach($items as $it)
                <li class="py-3 flex items-start justify-between gap-3">
                  <div>
                    <div class="font-semibold">{{ $it->product->product_name ?? 'Product' }}</div>
                    <div class="text-sm text-gray-600">
                      Size: {{ $it->size }} · Qty: {{ $it->quantity }}
                    </div>
                  </div>
                  <div class="font-semibold">
                    ${{ number_format($it->unit_price, 2) }}
                  </div>
                </li>
              @endforeach
            </ul>
          </div>
        </div>

        <aside class="lg:col-span-4">
          <div class="border rounded-xl p-5">
            <h2 class="font-extrabold mb-3">Summary</h2>
            <dl class="text-sm space-y-2">
              <div class="flex justify-between">
                <dt>Status</dt><dd class="font-semibold">{{ $order->status }}</dd>
              </div>
              <div class="flex justify-between">
                <dt>Total</dt><dd class="font-extrabold">${{ number_format($order->total, 2) }}</dd>
              </div>
            </dl>

            <div class="mt-4 text-sm">
              <div class="font-semibold mb-1">Ship to</div>
              <p class="text-gray-700">{{ $order->street_address }}</p>
            </div>

            <a href="{{ route('user.index') }}" class="mt-5 inline-block px-5 py-3 rounded-xl font-bold text-white bg-gray-900 hover:bg-black">Continue shopping</a>
          </div>
        </aside>
      </div>
    </div>
  </div>

  @include('user.footer')
</x-app-layout>
