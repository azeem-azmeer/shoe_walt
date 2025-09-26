<div class="min-h-screen bg-cover bg-center bg-fixed p-6"
     style="background-image: url('{{ asset('storage/products/dashboard.jpg') }}')"
     wire:poll.60s>

  <div class="space-y-6">
    {{-- Header + Filters --}}
    <div class="rounded-2xl bg-white/90 backdrop-blur shadow-lg p-6">
      <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">
        
        {{-- Title --}}
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">
            All Orders <span class="text-gray-500">({{ number_format($orderCount) }})</span>
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Showing latest orders placed by all customers.
          </p>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-end gap-3">

          {{-- Status filter --}}
          <div>
            <label class="block text-xs text-gray-600 mb-1">Status</label>
            <select wire:model.live="status"
                    class="rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-500/60">
              <option value="">Any</option>
              @foreach(['Pending','Confirmed','Cancelled'] as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
              @endforeach
            </select>
          </div>

          
          {{-- Search --}}
          <form wire:submit.prevent="go" class="flex flex-col sm:flex-row items-start sm:items-end gap-2">
            <div class="w-full sm:w-auto">
              <label class="block text-xs text-gray-600 mb-1">Search</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="7" stroke-width="1.5"></circle>
                    <path d="M20 20l-3-3" stroke-width="1.5"></path>
                  </svg>
                </span>
                <input type="search"
                      wire:model.defer="q"
                      placeholder="Order #, email, name"
                      class="w-full sm:w-72 rounded-xl border px-9 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                      aria-label="Search orders"
                      @keydown.enter.stop />
              </div>
            </div>

            {{-- Button always goes below on mobile, side-by-side on desktop --}}
            <button type="submit"
                    class="w-full sm:w-auto h-[38px] px-4 rounded-xl bg-gray-900 text-white text-sm font-semibold hover:bg-black shadow">
              Search
            </button>
          </form>


          {{-- Date --}}
          <div>
            <label class="block text-xs text-gray-600 mb-1">Date</label>
            <input type="date"
                   wire:model.live="onDate"
                   class="rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-500/60" />
          </div>

          {{-- Per page --}}
          <div>
            <label class="block text-xs text-gray-600 mb-1">Per Page</label>
            <select wire:model.live="perPage"
                    class="rounded-xl border px-3 py-2 focus:ring-2 focus:ring-indigo-500/60">
              @foreach([10,20,30,50,100] as $n)
                <option value="{{ $n }}">{{ $n }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      {{-- Active date chip --}}
      @if($onDate)
        <div class="mt-3 text-xs text-gray-600">
          <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100">
            Date: {{ $onDate }}
          </span>
        </div>
      @endif
    </div>

    {{-- Orders List --}}
    @if($orders->isEmpty())
      {{-- Empty state --}}
      <div class="bg-white/90 backdrop-blur border rounded-2xl shadow p-10 text-center">
        <div class="mx-auto mb-4 h-14 w-14 rounded-2xl bg-gray-100 flex items-center justify-center">
          <svg class="h-7 w-7 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="7" stroke-width="1.5"></circle>
            <path d="M20 20l-3-3" stroke-width="1.5"></path>
          </svg>
        </div>
        <p class="text-gray-700 text-lg font-medium">No orders found</p>
        <p class="text-gray-500 text-sm mt-1">Try a different date, status, or search.</p>
      </div>

    @else

      {{-- ===== MOBILE: card list (< sm) ===== --}}
      <div class="sm:hidden space-y-3">
        @foreach($orders as $order)
          <div class="bg-white/95 backdrop-blur border rounded-2xl shadow p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-semibold text-gray-900">#{{ $order->id }}</div>
                <div class="text-xs text-gray-600">
                  {{ $order->created_at->format('M d, Y h:i A') }}
                </div>
              </div>
              <a href="{{ route('user.orders.show', $order->id) }}"
                 class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shadow-sm">
                View
              </a>
            </div>

            <div class="mt-3 flex items-center gap-3">
              @php $initial = mb_strtoupper(mb_substr($order->user->name ?? 'U', 0, 1)); @endphp
              <div class="h-8 w-8 rounded-full bg-indigo-600/10 text-indigo-700 text-sm font-bold grid place-items-center">
                {{ $initial }}
              </div>
              <div class="min-w-0">
                <div class="text-sm font-semibold truncate">{{ $order->user->name ?? '—' }}</div>
                <div class="text-[11px] text-gray-500 truncate">{{ $order->user->email ?? '—' }}</div>
              </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
              <span class="text-sm text-gray-800">Items: <b>{{ $order->items_count }}</b></span>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-semibold">
                ${{ number_format($order->total ?? 0, 2) }}
              </span>

              <select class="ml-auto rounded-lg border px-2 py-1 text-xs font-semibold focus:ring-2 focus:ring-indigo-500/60"
                      wire:change="updateStatus({{ $order->id }}, $event.target.value)">
                @foreach(['Pending','Confirmed','Cancelled'] as $opt)
                  <option value="{{ $opt }}" @selected($order->status === $opt)>{{ $opt }}</option>
                @endforeach
              </select>
            </div>
          </div>
        @endforeach

        <div class="pt-2">
          <div class="flex justify-center">
            {{ $orders->links() }}
          </div>
        </div>
      </div>

      {{-- ===== DESKTOP: table (≥ sm) ===== --}}
      <div class="hidden sm:block bg-white/95 backdrop-blur border rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold sticky top-0 z-10">
              <tr>
                <th class="text-left px-4 py-3">Order #</th>
                <th class="text-left px-4 py-3">Placed</th>
                <th class="text-left px-4 py-3">Customer</th>
                <th class="text-left px-4 py-3">Items</th>
                <th class="text-left px-4 py-3">Total</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-left px-4 py-3">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach($orders as $order)
                <tr class="hover:bg-gray-50 transition" wire:key="order-{{ $order->id }}">
                  <td class="px-4 py-3 font-semibold text-gray-900">#{{ $order->id }}</td>
                  <td class="px-4 py-3 text-gray-700">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      @php $initial = mb_strtoupper(mb_substr($order->user->name ?? 'U', 0, 1)); @endphp
                      <div class="h-8 w-8 rounded-full bg-indigo-600/10 text-indigo-700 text-sm font-bold grid place-items-center">
                        {{ $initial }}
                      </div>
                      <div class="min-w-0">
                        <div class="font-semibold truncate max-w-[180px] text-gray-900">{{ $order->user->name ?? '—' }}</div>
                        <div class="text-[11px] text-gray-500 truncate max-w-[220px]">{{ $order->user->email ?? '—' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-gray-800">{{ $order->items_count }}</td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 font-semibold">
                      ${{ number_format($order->total ?? 0, 2) }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <select class="rounded-lg border px-2 py-1 text-xs font-semibold focus:ring-2 focus:ring-indigo-500/60"
                              wire:change="updateStatus({{ $order->id }}, $event.target.value)">
                        @foreach(['Pending','Confirmed','Cancelled'] as $opt)
                          <option value="{{ $opt }}" @selected($order->status === $opt)>{{ $opt }}</option>
                        @endforeach
                      </select>
                      <svg wire:loading.delay wire:target="updateStatus"
                           class="h-4 w-4 animate-spin text-indigo-600"
                           viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                        <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-width="4"></path>
                      </svg>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <a href="{{ route('user.orders.show', $order->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shadow-sm">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M7 2h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2V4a2 2 0 0 1 2-2Z" stroke-width="1.5"/>
                        <path d="M9 7h6M9 11h6M9 15h4" stroke-width="1.5"/>
                      </svg>
                      View
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="px-4 py-4">
          <div class="flex justify-center">
            {{ $orders->links() }}
          </div>
        </div>
      </div>

    @endif
  </div>
</div>
