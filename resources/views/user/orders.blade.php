{{-- resources/views/user/orders.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-bold text-xl">My Orders</h2>
  </x-slot>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-black mb-6">
        My Orders ({{ $orderCount ?? $orders->count() ?? 0 }})
    </h1>

    @if($orders->isEmpty())
      <p class="text-gray-600">You have not placed any orders yet.</p>
    @else
      <div class="space-y-6">
        @foreach($orders as $order)
          <div class="border rounded-xl p-5">
            <div class="flex justify-between items-center">
              <h2 class="font-bold">Order #{{ $order->id }}</h2>
              <span class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
            </div>

            <p class="text-sm text-gray-600">Status:
              <span class="font-semibold">{{ $order->status }}</span>
            </p>

            <p class="text-sm text-gray-600">Total:
              <span class="font-bold">${{ number_format($order->total, 2) }}</span>
            </p>

            <a href="{{ route('user.orders.show', $order->id) }}"
               class="text-sm font-semibold text-blue-600 hover:underline">
               View Details
            </a>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</x-app-layout>
