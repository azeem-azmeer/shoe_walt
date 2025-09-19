<div wire:poll.60s>
  {{-- Background wrapper --}}
  <div class="min-h-screen bg-cover bg-center bg-fixed p-6"
       style="background-image: url('{{ asset('storage/products/dashboard.jpg') }}')">

    {{-- Dashboard grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

      {{-- Total Products --}}
      <a href="{{ route('admin.products') }}" 
         class="group relative rounded-2xl p-[1px] bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 shadow-lg hover:shadow-xl transition">
        <div class="rounded-2xl bg-white p-5 h-full">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="h-11 w-11 rounded-xl bg-indigo-600/10 flex items-center justify-center">
                {{-- cube icon --}}
                <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M12 2l9 5v10l-9 5-9-5V7l9-5Z" stroke-width="1.5"/>
                  <path d="M21 7l-9 5-9-5" stroke-width="1.5"/>
                  <path d="M12 22V12" stroke-width="1.5"/>
                </svg>
              </div>
              <div>
                <div class="text-sm text-gray-500">Total Products</div>
                <div class="mt-1 text-3xl sm:text-4xl font-extrabold tracking-tight">
                  {{ number_format($totalProducts) }}
                </div>
              </div>
            </div>
            <div class="opacity-0 group-hover:opacity-100 text-indigo-600/70 text-xs font-semibold transition">
              View →
            </div>
          </div>
        </div>
      </a>

      {{-- Men Products --}}
      <a href="{{ route('admin.products') }}" 
         class="group relative rounded-2xl p-[1px] bg-gradient-to-r from-sky-500 to-indigo-500 shadow-lg hover:shadow-xl transition">
        <div class="rounded-2xl bg-white p-5 h-full">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-sky-600/10 flex items-center justify-center">
              {{-- male user --}}
              <svg class="h-6 w-6 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="8" r="3.5" stroke-width="1.5"/>
                <path d="M4 20c0-3.5 3.5-6 8-6s8 2.5 8 6" stroke-width="1.5"/>
              </svg>
            </div>
            <div>
              <div class="text-sm text-gray-500">Men Products</div>
              <div class="mt-1 text-3xl sm:text-4xl font-extrabold tracking-tight">
                {{ number_format($menProducts) }}
              </div>
            </div>
          </div>
        </div>
      </a>

      {{-- Women Products --}}
      <a href="{{ route('admin.products') }}" 
         class="group relative rounded-2xl p-[1px] bg-gradient-to-r from-rose-500 to-pink-500 shadow-lg hover:shadow-xl transition">
        <div class="rounded-2xl bg-white p-5 h-full">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-rose-600/10 flex items-center justify-center">
              {{-- heart icon --}}
              <svg class="h-6 w-6 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M20.8 8.6a5 5 0 0 0-8.8-3.3L12 5.4l.1-.1a5 5 0 1 0 8.7 3.3c0 5.1-8.8 10-8.8 10S2 13.7 2 8.6" stroke-width="1.5"/>
              </svg>
            </div>
            <div>
              <div class="text-sm text-gray-500">Women Products</div>
              <div class="mt-1 text-3xl sm:text-4xl font-extrabold tracking-tight">
                {{ number_format($womenProducts) }}
              </div>
            </div>
          </div>
        </div>
      </a>

      {{-- Kids Products --}}
      <a href="{{ route('admin.products') }}" 
         class="group relative rounded-2xl p-[1px] bg-gradient-to-r from-amber-400 to-orange-500 shadow-lg hover:shadow-xl transition">
        <div class="rounded-2xl bg-white p-5 h-full">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-amber-500/10 flex items-center justify-center">
              {{-- sparkles --}}
              <svg class="h-6 w-6 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M12 2l2 5 5 2-5 2-2 5-2-5-5-2 5-2 2-5Z" stroke-width="1.5"/>
                <path d="M19 14l1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z" stroke-width="1.5"/>
              </svg>
            </div>
            <div>
              <div class="text-sm text-gray-500">Kids Products</div>
              <div class="mt-1 text-3xl sm:text-4xl font-extrabold tracking-tight">
                {{ number_format($kidsProducts) }}
              </div>
            </div>
          </div>
        </div>
      </a>

      {{-- Total Orders --}}
      <a href="{{ route('admin.orders') }}" 
         class="group relative rounded-2xl p-[1px] bg-gradient-to-r from-violet-500 to-indigo-600 shadow-lg hover:shadow-xl transition">
        <div class="rounded-2xl bg-white p-5 h-full">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="h-11 w-11 rounded-xl bg-violet-600/10 flex items-center justify-center">
                {{-- receipt --}}
                <svg class="h-6 w-6 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M7 2h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2V4a2 2 0 0 1 2-2Z" stroke-width="1.5"/>
                  <path d="M9 7h6M9 11h6M9 15h4" stroke-width="1.5"/>
                </svg>
              </div>
              <div>
                <div class="text-sm text-gray-500">Total Orders</div>
                <div class="mt-1 text-3xl sm:text-4xl font-extrabold tracking-tight">
                  {{ number_format($totalOrders) }}
                </div>
              </div>
            </div>
            <div class="opacity-0 group-hover:opacity-100 text-violet-600/70 text-xs font-semibold transition">
              Manage →
            </div>
          </div>
        </div>
      </a>

      {{-- Total Sales --}}
      <div class="group relative rounded-2xl p-[1px] bg-gradient-to-r from-emerald-500 to-teal-500 shadow-lg hover:shadow-xl transition">
        <div class="rounded-2xl bg-white p-5 h-full">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="h-11 w-11 rounded-xl bg-emerald-600/10 flex items-center justify-center">
                {{-- dollar --}}
                <svg class="h-6 w-6 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M12 1v22" stroke-width="1.5"/>
                  <path d="M17 6.5c0-2-2.2-3.5-5-3.5S7 4.5 7 6.5s2.2 3.5 5 3.5 5 1.5 5 3.5-2.2 3.5-5 3.5-5-1.5-5-3.5" stroke-width="1.5"/>
                </svg>
              </div>
              <div>
                <div class="text-sm text-gray-500">Total Sales</div>
                <div class="mt-1 text-3xl sm:text-4xl font-extrabold tracking-tight">
                  ${{ number_format($totalSales, 2) }}
                </div>
                <div class="mt-1 text-xs text-gray-400">Sum of Confirmed orders</div>
              </div>
            </div>
            <div class="opacity-0 group-hover:opacity-100 text-emerald-600/70 text-xs font-semibold transition">
              Nice! ✨
            </div>
          </div>
        </div>
      </div>

      {{-- Today’s Sales --}}
      <div class="group relative rounded-2xl p-[1px] bg-gradient-to-r from-teal-500 to-cyan-500 shadow-lg hover:shadow-xl transition">
        <div class="rounded-2xl bg-white p-5 h-full">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-teal-600/10 flex items-center justify-center">
              {{-- sun --}}
              <svg class="h-6 w-6 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="4" stroke-width="1.5"/>
                <path d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.5-7.5L18 5m-12 14l-1.5 1.5m0-15L6 5m12 14l1.5 1.5" stroke-width="1.5"/>
              </svg>
            </div>
            <div>
              <div class="text-sm text-gray-500">Today’s Sales</div>
              <div class="mt-1 text-3xl sm:text-4xl font-extrabold tracking-tight">
                ${{ number_format($todaySales, 2) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- This Month --}}
      <div class="group relative rounded-2xl p-[1px] bg-gradient-to-r from-fuchsia-500 to-pink-600 shadow-lg hover:shadow-xl transition">
        <div class="rounded-2xl bg-white p-5 h-full">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-fuchsia-600/10 flex items-center justify-center">
              {{-- calendar --}}
              <svg class="h-6 w-6 text-fuchsia-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="4" width="18" height="17" rx="2" stroke-width="1.5"/>
                <path d="M8 2v4M16 2v4M3 9h18" stroke-width="1.5"/>
              </svg>
            </div>
            <div>
              <div class="text-sm text-gray-500">This Month</div>
              <div class="mt-1 text-3xl sm:text-4xl font-extrabold tracking-tight">
                ${{ number_format($monthSales, 2) }}
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
