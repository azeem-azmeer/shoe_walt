{{-- resources/views/user/cart.blade.php --}}
@php use Illuminate\Support\Str; @endphp
<x-app-layout>
  <x-slot name="header"></x-slot>

  <div class="py-6 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      {{-- Greeting --}}
      <div class="rounded-xl px-6 py-4 mb-6 bg-white border shadow-sm">
        <div class="flex items-center gap-3">
          
          <p class="font-semibold tracking-wide text-gray-800">
            HI, {{ auth()->check() ? Str::of(auth()->user()->name)->upper()->finish('!') : 'THERE!' }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- LEFT: Items --}}
        <section class="lg:col-span-8">
          <div class="flex items-end justify-between">
            <h1 class="text-4xl font-black tracking-wide text-gray-900">YOUR BAG</h1>
            <div class="text-sm bg-white rounded-full px-3 py-1 border shadow-sm">
              <span class="font-semibold text-gray-800">TOTAL:</span>
              <span class="ml-1 text-gray-900 font-bold">({{ $count }} {{ Str::plural('item', $count) }})</span>
              <span class="ml-1 text-gray-900 font-semibold">${{ number_format($subtotal + $tax, 2) }}</span>
            </div>
          </div>

          <p class="mt-2 text-gray-600 text-sm">
            Items in your bag are not reserved — check out now to make them yours.
          </p>

          <div id="cartItems" class="mt-5 space-y-4">
            @forelse($items as $it)
              <div class="border rounded-xl overflow-hidden bg-white shadow-sm hover:shadow transition">
                <div class="grid grid-cols-12">
                  <div class="col-span-12 sm:col-span-3 bg-gray-100/60 p-4 flex items-center justify-center">
                    <img
                      src="{{ $it['img'] ?? asset('storage/products/placeholder.png') }}"
                      alt=""
                      class="max-h-36 object-contain rounded-md ring-1 ring-black/5 bg-white p-2"
                    >
                  </div>

                  <div class="col-span-12 sm:col-span-9 p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-4">
                      <div>
                        <div class="font-semibold text-lg leading-6 text-gray-900">{{ $it['name'] }}</div>

                        <div class="mt-2 flex flex-wrap items-center gap-3">
                          <span class="inline-flex items-center gap-2">
                            <span class="text-[11px] px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200 uppercase tracking-wider">Size</span>
                            <span class="text-sm text-gray-800">{{ $it['size'] }}</span>
                          </span>
                          <span class="inline-flex items-center gap-2">
                            <span class="text-[11px] px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200 uppercase tracking-wider">Qty</span>
                            <span class="text-sm text-gray-800">{{ $it['quantity'] }}</span>
                          </span>
                        </div>
                      </div>

                      <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">${{ number_format($it['price'], 2) }}</div>

                        <button
                          onclick="removeCartItem({{ $it['id'] }})"
                          class="mt-3 inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-gray-600 hover:text-red-600 hover:border-red-300 hover:bg-red-50 transition"
                          title="Remove item" aria-label="Remove"
                        >
                          ✕
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="p-8 text-gray-600 text-sm border rounded-xl bg-white shadow-sm text-center">
                Your bag is empty.
              </div>
            @endforelse
          </div>
        </section>

        {{-- RIGHT: Summary --}}
        <aside class="lg:col-span-4">
          <div id="orderSummary" class="bg-white border rounded-2xl p-5 shadow-sm sticky top-8">
            <div class="flex items-center gap-2 mb-3">
              
              <h2 class="text-xl font-black tracking-wide text-gray-900">ORDER SUMMARY</h2>
            </div>

            <dl class="text-sm space-y-2">
              <div class="flex items-center justify-between">
                <dt class="text-gray-600">{{ $count }} {{ Str::plural('item', $count) }}</dt>
                <dd class="font-semibold text-gray-900">${{ number_format($subtotal, 2) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-600">Sales Tax*</dt>
                <dd class="font-semibold text-gray-900">${{ number_format($tax, 2) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-600">Delivery</dt>
                <dd class="text-emerald-700 font-semibold">Free</dd>
              </div>
              <div class="pt-3 border-t flex items-center justify-between font-extrabold text-gray-900">
                <dt>Total</dt>
                <dd>${{ number_format($total, 2) }}</dd>
              </div>
            </dl>

            <p class="text-xs text-gray-500 mt-2">
              * Tax shown is an estimate — final amount may vary slightly.
            </p>

            <details class="mt-4 rounded-lg border border-dashed border-gray-300 p-3">
              <summary class="text-sm font-semibold cursor-pointer select-none">
                <span class="underline underline-offset-2">USE A PROMO CODE</span>
              </summary>
              <div class="mt-3 flex gap-2">
                <input type="text" class="flex-1 border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Enter code" disabled>
                <button class="px-4 py-2 border rounded-md text-sm cursor-not-allowed opacity-60">Apply</button>
              </div>
            </details>

            <a href="{{ route('user.checkout') }}"
               class="mt-5 block text-center px-5 py-3 rounded-xl font-bold text-white bg-gray-900 hover:bg-black transition shadow">
              CHECKOUT
            </a>

            {{-- Payment methods image --}}
            <div class="mt-5 rounded-xl bg-gray-50 border border-gray-200 p-3">
              <img
                src="{{ asset('storage/products/payemt.png') }}"
                alt="Accepted payment methods"
                class="block mx-auto w-full max-w-md h-auto object-contain"
              >
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>

  @vite('resources/js/user-cart.js')
  @include('user.footer')
</x-app-layout>
