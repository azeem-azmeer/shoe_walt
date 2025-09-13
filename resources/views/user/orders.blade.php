{{-- resources/views/user/orders.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-bold text-xl">My Orders</h2>
  </x-slot>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-extrabold tracking-tight">
        My Orders ({{ $orderCount ?? $orders->count() ?? 0 }})
      </h1>
    </div>

    @if($orders->isEmpty())
      <div class="bg-white border rounded-xl shadow-sm p-8 text-center">
        <img src="{{ asset('storage/products/empty-cart.png') }}" alt="No Orders" 
             class="mx-auto mb-4 w-20 h-20 opacity-70">
        <p class="text-gray-600 text-lg">You have not placed any orders yet.</p>
        <a href="{{ route('user.index') }}"
           class="mt-4 inline-block px-5 py-3 rounded-xl font-semibold text-white bg-gray-900 hover:bg-black">
          Start Shopping
        </a>
      </div>
    @else
      <div class="grid gap-6">
        @foreach($orders as $order)
          <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <h2 class="text-lg font-bold">
                  Order <span class="text-gray-700">#{{ $order->id }}</span>
                </h2>
                <p class="text-sm text-gray-500">
                  Placed on {{ $order->created_at->format('M d, Y h:i A') }}
                </p>
              </div>

              <div class="flex flex-wrap gap-4 text-sm">
                <span class="px-3 py-1 rounded-full 
                  {{ $order->status === 'Completed' ? 'bg-emerald-100 text-emerald-700' : 
                     ($order->status === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                  {{ $order->status }}
                </span>
                <span class="font-semibold text-gray-800">
                  ${{ number_format($order->total, 2) }}
                </span>
                <a href="{{ route('user.orders.show', $order->id) }}"
                   class="text-blue-600 font-semibold hover:underline">
                   View Details →
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</x-app-layout>
