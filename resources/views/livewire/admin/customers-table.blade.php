<div class="min-h-screen bg-cover bg-center bg-fixed"
     style="background-image: url('{{ asset('storage/products/dashboard.jpg') }}')">

  {{-- Content wrapper now spans full width --}}
  <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Filters --}}
    <div class="bg-white/95 backdrop-blur-md rounded-xl shadow-lg p-6 mb-6">
      <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        
        {{-- Left section --}}
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">
            Customers <span class="text-gray-600">({{ number_format($totalCustomers) }})</span>
          </h1>
          <p class="text-sm text-gray-700">All registered customers (non-admin).</p>
        </div>

        {{-- Right section: filters --}}
        <div class="flex flex-wrap items-end gap-4">
          {{-- Search --}}
          <div>
            <label class="block text-xs text-gray-600 mb-1">Search</label>
            <form wire:submit.prevent="go" class="flex gap-2">
              <input
                type="search"
                wire:model.defer="q"
                placeholder="ID, name or email"
                class="border rounded-md px-3 py-2 w-64 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                aria-label="Search customers"
                @keydown.enter.stop
              />
              <button type="submit"
                      class="h-[38px] px-4 rounded-lg bg-gray-900 text-white font-semibold hover:bg-black transition">
                Search
              </button>
            </form>
          </div>

          {{-- With orders --}}
          <div>
            <label class="block text-xs text-gray-600 mb-1">With Orders</label>
            <select wire:model.live="withOrders" class="border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
              <option value="">Any</option>
              <option value="yes">Only customers with orders</option>
            </select>
          </div>

          {{-- Sort --}}
          <div>
            <label class="block text-xs text-gray-600 mb-1">Sort By</label>
            <select wire:model.live="sort" class="border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
              <option value="recent">Most Recent</option>
              <option value="orders">Orders Count</option>
            </select>
          </div>

          {{-- Per page --}}
          <div>
            <label class="block text-xs text-gray-600 mb-1">Per Page</label>
            <select wire:model.live="perPage" class="border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
              @foreach([10,20,30,50,100] as $n)
                <option value="{{ $n }}">{{ $n }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    {{-- Table --}}
    @if($users->isEmpty())
      <div class="bg-white/95 backdrop-blur-md border rounded-xl shadow-md p-8 text-center">
        <p class="text-gray-700 text-lg">No customers found.</p>
      </div>
    @else
      <div class="bg-white/95 backdrop-blur-md border rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
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
                <tr class="hover:bg-gray-50/70 transition" wire:key="u-{{ $u->id }}">
                  <td class="px-4 py-3 font-semibold text-gray-900">{{ $u->id }}</td>
                  <td class="px-4 py-3 text-gray-800">{{ $u->name }}</td>
                  <td class="px-4 py-3">
                    <div class="font-medium text-gray-900">{{ $u->email }}</div>
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
                  <td class="px-4 py-3 font-semibold text-gray-900">{{ $u->orders_count }}</td>
                  <td class="px-4 py-3 text-gray-700">
                    {{ optional($u->created_at)->format('M d, Y') }}
                  </td>
                  <td class="px-4 py-3">
                    <a href="{{ route('admin.orders', ['q' => $u->email]) }}"
                       class="text-indigo-600 font-semibold hover:underline">
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
</div>
