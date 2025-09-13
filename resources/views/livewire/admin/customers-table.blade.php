<div>
  {{-- Filters --}}
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight">
        Customers <span class="text-gray-500">({{ number_format($totalCustomers) }})</span>
      </h1>
      <p class="text-sm text-gray-500">All registered customers (non-admin).</p>
    </div>

    <div class="flex flex-wrap items-end gap-3">
      {{-- Search --}}
      <form wire:submit.prevent="go" class="flex items-end gap-2">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Search</label>
          <input
            type="search"
            wire:model.defer="q"
            placeholder="ID, name or email"
            class="border rounded-md px-3 py-2 w-64"
            aria-label="Search customers"
            @keydown.enter.stop
          />
        </div>
        <button type="submit"
                class="h-[38px] px-4 rounded-lg bg-gray-900 text-white font-semibold hover:bg-black">
          Search
        </button>
      </form>

      {{-- Verified filter --}}
      <div>
        <label class="block text-xs text-gray-500 mb-1">Verified</label>
        <select wire:model.live="verified" class="border rounded-md px-3 py-2">
          <option value="">Any</option>
          <option value="yes">Verified</option>
          <option value="no">Unverified</option>
        </select>
      </div>

      {{-- With orders filter --}}
      <div>
        <label class="block text-xs text-gray-500 mb-1">With Orders</label>
        <select wire:model.live="withOrders" class="border rounded-md px-3 py-2">
          <option value="">Any</option>
          <option value="yes">Only customers with orders</option>
        </select>
      </div>

      {{-- Sort --}}
      <div>
        <label class="block text-xs text-gray-500 mb-1">Sort By</label>
        <select wire:model.live="sort" class="border rounded-md px-3 py-2">
          <option value="recent">Most Recent</option>
          <option value="orders">Orders Count</option>
        </select>
      </div>

      {{-- Per page --}}
      <div>
        <label class="block text-xs text-gray-500 mb-1">Per Page</label>
        <select wire:model.live="perPage" class="border rounded-md px-3 py-2">
          @foreach([10,20,30,50,100] as $n)
            <option value="{{ $n }}">{{ $n }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  {{-- Table --}}
  @if($users->isEmpty())
    <div class="bg-white border rounded-xl shadow-sm p-8 text-center">
      <p class="text-gray-600 text-lg">No customers found.</p>
    </div>
  @else
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
            <tr>
              <th class="text-left px-4 py-3">ID</th>
              <th class="text-left px-4 py-3">Name</th>
              <th class="text-left px-4 py-3">Email</th>
              <th class="text-left px-4 py-3">Verified</th>
              <th class="text-left px-4 py-3">Orders</th>
              <th class="text-left px-4 py-3">Joined</th>
              <th class="text-left px-4 py-3">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @foreach($users as $u)
              <tr class="hover:bg-gray-50" wire:key="u-{{ $u->id }}">
                <td class="px-4 py-3 font-semibold">{{ $u->id }}</td>
                <td class="px-4 py-3">{{ $u->name }}</td>
                <td class="px-4 py-3">
                  <div class="font-medium">{{ $u->email }}</div>
                </td>
                <td class="px-4 py-3">
                  @if($u->email_verified_at)
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                      Verified
                    </span>
                  @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                      Unverified
                    </span>
                  @endif
                </td>
                <td class="px-4 py-3 font-semibold">{{ $u->orders_count }}</td>
                <td class="px-4 py-3 text-gray-700">
                  {{ optional($u->created_at)->format('M d, Y') }}
                </td>
                <td class="px-4 py-3">
                  {{-- Jump to admin orders, pre-filtered by this email --}}
                  <a href="{{ route('admin.orders', ['q' => $u->email]) }}"
                     class="text-blue-600 font-semibold hover:underline">
                    View Orders
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3">
        {{ $users->links() }}
      </div>
    </div>
  @endif
</div>
