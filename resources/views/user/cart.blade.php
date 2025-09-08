{{-- resources/views/user/cart.blade.php --}}
@php use Illuminate\Support\Str; @endphp
<x-app-layout>
  <x-slot name="header"></x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      {{-- Greeting bubble --}}
      <div class="bg-gray-100 rounded px-6 py-4 mb-6">
        <div class="font-semibold text-gray-700">
          HI, {{ auth()->check() ? Str::of(auth()->user()->name)->upper()->finish('!') : 'THERE!' }}
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- LEFT: Items --}}
        <section class="lg:col-span-8">
          <h1 class="text-4xl font-black tracking-wide">YOUR BAG</h1>

          <div class="mt-2 text-sm">
            <span class="font-semibold">TOTAL: ({{ $count }} {{ Str::plural('item', $count) }})</span>
            <span class="ml-1">${{ number_format($subtotal + $tax, 2) }}</span>
          </div>
          <p class="mt-1 text-gray-600 text-sm">
            Items in your bag are not reserved — check out now to make them yours.
          </p>

          <div id="cartItems" class="mt-5 space-y-4">
            @forelse($items as $it)
              <div class="border rounded-lg grid grid-cols-12">
                <div class="col-span-12 sm:col-span-3 bg-gray-100 p-4 flex items-center justify-center">
                  <img src="{{ $it['img'] ?? asset('storage/products/placeholder.png') }}"
                       alt="" class="max-h-32 object-contain">
                </div>

                <div class="col-span-12 sm:col-span-9 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                        <div class="font-semibold text-lg leading-6">{{ $it['name'] }}</div>
                        <div class="text-gray-600 text-sm mt-1">SIZE: {{ $it['size'] }}</div>
                        <div class="text-gray-600 text-sm">QTY: {{ $it['quantity'] }}</div>
                        </div>
                        <div class="text-right">
                        <div class="font-semibold">${{ number_format($it['price'], 2) }}</div>
                        <button onclick="removeCartItem({{ $it['id'] }})"
                                class="mt-2 text-gray-600 hover:text-black"
                                title="Remove item" aria-label="Remove">✕</button>
                        </div>
                    </div>
                    </div>

              </div>
            @empty
              <div class="p-6 text-gray-600 text-sm border rounded-lg">
                Your bag is empty.
              </div>
            @endforelse
          </div>
        </section>

        {{-- RIGHT: Summary --}}
        <aside class="lg:col-span-4">
          <div id="orderSummary" class="bg-white border rounded-lg p-4 sticky top-8">
            <h2 class="text-xl font-black tracking-wide mb-2">ORDER SUMMARY</h2>

            <dl class="text-sm space-y-2">
              <div class="flex items-center justify-between">
                <dt>{{ $count }} {{ Str::plural('item', $count) }}</dt>
                <dd>${{ number_format($subtotal, 2) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt>Sales Tax*</dt>
                <dd>${{ number_format($tax, 2) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt>Delivery</dt>
                <dd>Free</dd>
              </div>
              <div class="pt-2 border-t flex items-center justify-between font-semibold">
                <dt>Total</dt>
                <dd>${{ number_format($total, 2) }}</dd>
              </div>
            </dl>

            <p class="text-xs text-gray-500 mt-2">
              * Please note: Tax amount is an estimate — the amount you are charged may differ slightly
            </p>

            <details class="mt-4">
              <summary class="text-sm font-semibold underline underline-offset-2 cursor-pointer">
                USE A PROMO CODE
              </summary>
              <div class="mt-3 flex gap-2">
                <input type="text" class="flex-1 border rounded px-3 py-2 text-sm" placeholder="Enter code" disabled>
                <button class="px-4 py-2 border rounded text-sm" disabled>Apply</button>
              </div>
            </details>

            <a href="{{ route('user.checkout') }}"
               class="mt-5 block text-center px-4 py-3 bg-black text-white font-bold rounded hover:opacity-90">
              CHECKOUT
            </a>

            <div class="mt-4 text-xs text-gray-500">
              Accepted payment methods: Visa, MasterCard, Amex, Discover, PayPal, GPay, Apple Pay, Afterpay, Klarna, Affirm
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>

  @vite('resources/js/user-cart.js')
</x-app-layout>
