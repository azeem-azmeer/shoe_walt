{{-- resources/views/user/checkout.blade.php --}}
@php
  /**  Data passed from controller:
   * $items:  [
   *   ['id'=>cart_id,'product_id'=>..,'name'=>..,'img'=>..,'size'=>..,'qty'=>..,'price'=>..,'line_total'=>..]
   * ]
   * $subtotal, $tax, $shipping, $total
   * $states: ['AL'=>'Alabama', ...]
   * $user: auth user (or null)
   */
@endphp

<x-app-layout>
  <x-slot name="header"></x-slot>

  <div class="bg-white">
    {{-- FREE SHIPPING STRIP --}}
    <div class="border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm">
        <span class="font-semibold">Free shipping</span> for members
      </div>
    </div>

    {{-- ADDRESS ERROR (shows if validation fails) --}}
    @if ($errors->any())
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3">
          There was an issue processing your address. Please update the address or contact Customer Care.
          <ul class="list-disc pl-5 mt-2 text-sm">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('user.checkout.store') }}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      @csrf

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- LEFT: Shipping address + payment --}}
        <div class="lg:col-span-8 space-y-8">
          {{-- Shipping address --}}
          <section aria-labelledby="shipping-heading" class="border rounded-xl">
            <div class="px-4 py-3 border-b flex items-center justify-between">
              <h2 id="shipping-heading" class="text-lg font-bold">Shipping address</h2>
              @auth
                <span class="text-xs text-gray-500">Signed in as {{ auth()->user()->email }}</span>
              @endauth
            </div>

            <div class="p-4 sm:p-6 space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">First Name *</label>
                  <input name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}"
                         class="w-full border rounded-md px-3 py-2" required>
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Last Name *</label>
                  <input name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}"
                         class="w-full border rounded-md px-3 py-2" required>
                </div>
              </div>

              <div>
                <label class="block text-sm text-gray-600 mb-1">Street Address *</label>
                <input name="street_address" value="{{ old('street_address') }}"
                       class="w-full border rounded-md px-3 py-2" required>
              </div>

              <div>
                <label class="block text-sm text-gray-600 mb-1">Apartment, Suite, etc. (Optional)</label>
                <input name="address2" value="{{ old('address2') }}"
                       class="w-full border rounded-md px-3 py-2">
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">City *</label>
                  <input name="city" value="{{ old('city') }}" class="w-full border rounded-md px-3 py-2" required>
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">State *</label>
                  <select name="state" class="w-full border rounded-md px-3 py-2" required>
                    <option value="" disabled {{ old('state') ? '' : 'selected' }}>Select state</option>
                    @foreach($states as $code => $label)
                      <option value="{{ $code }}" @selected(old('state')===$code)>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">ZIP Code *</label>
                  <input name="zip" value="{{ old('zip') }}" class="w-full border rounded-md px-3 py-2" required>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Phone Number *</label>
                  <input name="phone" value="{{ old('phone') }}"
                         class="w-full border rounded-md px-3 py-2" required>
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Email *</label>
                  <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                         class="w-full border rounded-md px-3 py-2" required>
                </div>
              </div>
            </div>
          </section>

          {{-- Payment (placeholder fields) --}}
          <section aria-labelledby="payment-heading" class="border rounded-xl">
            <div class="px-4 py-3 border-b">
              <h2 id="payment-heading" class="text-lg font-bold">Payment Method</h2>
            </div>
            <div class="p-4 sm:p-6 space-y-4">
              <div>
                <label class="block text-sm text-gray-600 mb-1">Credit Card Number *</label>
                <input inputmode="numeric" placeholder="1234 5678 9012 3456"
                       class="w-full border rounded-md px-3 py-2">
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Expiration Date *</label>
                  <input placeholder="MM/YY" class="w-full border rounded-md px-3 py-2">
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Security Code *</label>
                  <input placeholder="3 digits" class="w-full border rounded-md px-3 py-2">
                </div>
              </div>

              {{-- You are not charging in this demo; the order is created on submit --}}
              <p class="text-xs text-gray-500">For demo purposes, submitting will create the order.</p>
            </div>
          </section>
        </div>

        {{-- RIGHT: Summary --}}
        <aside class="lg:col-span-4">
          <div class="border rounded-2xl p-4 sm:p-5 sticky top-6">
            <h3 class="text-lg font-black mb-3">Order Summary</h3>

            {{-- Items mini list --}}
            <div class="space-y-3 mb-4">
              @foreach($items as $it)
                <div class="flex gap-3">
                  <div class="h-16 w-16 rounded-md overflow-hidden bg-gray-50 border">
                    <img src="{{ $it['img'] }}" alt="" class="w-full h-full object-cover"
                         onerror="this.onerror=null;this.src='{{ asset('storage/products/placeholder.webp') }}'">
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="truncate font-medium">{{ $it['name'] }}</div>
                    <div class="text-xs text-gray-500">Size: {{ $it['size'] }} &middot; Qty: {{ $it['qty'] }}</div>
                  </div>
                  <div class="font-semibold">${{ number_format($it['line_total'],2) }}</div>
                </div>
              @endforeach
            </div>

            <dl class="text-sm space-y-2">
              <div class="flex items-center justify-between">
                <dt class="text-gray-600">{{ count($items) }} item{{ count($items)>1 ? 's':'' }}</dt>
                <dd class="font-semibold text-gray-900">${{ number_format($subtotal,2) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-600">Sales Tax*</dt>
                <dd class="font-semibold text-gray-900">${{ number_format($tax,2) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-600">Shipping</dt>
                <dd class="text-emerald-700 font-semibold">{{ $shipping == 0 ? 'Free' : '$'.number_format($shipping,2) }}</dd>
              </div>
              <div class="pt-3 border-t flex items-center justify-between font-extrabold text-gray-900">
                <dt>Total</dt>
                <dd>${{ number_format($total,2) }}</dd>
              </div>
            </dl>

            <button type="submit"
                    class="mt-5 w-full text-center px-5 py-3 rounded-xl font-bold text-white bg-gray-900 hover:bg-black transition shadow">
              PLACE ORDER
            </button>

            <p class="text-xs text-gray-500 mt-2">* Tax shown is an estimate.</p>
          </div>
        </aside>
      </div>
    </form>

    <div class="h-8"></div>
  </div>

  @include('user.footer')
</x-app-layout>
